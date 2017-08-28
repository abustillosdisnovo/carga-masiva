<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Akeneo\Component\SpreadsheetParser\SpreadsheetParser;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

class UploadExcel extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->load->view('upload_view');
	}


    //Convert the dates of the first excel sheet to what's needed
    public function convertDate($date, $time)
    {
        $date_iso = date(DATE_ISO8601, strtotime($date . $time));
        $date_exploded  = explode('+', $date_iso);
        $date_for_ws = $date_exploded[0].'.000Z';
        return $date_for_ws;
    }


    //Get the company name with the company's code
    public function getCompanyName($code)
    {
        $this->load->database();
        $this->load->model('Token_model');
        $this->Token_model->codigo = $code;
        $company = $this->Token_model->getCompanyNameByCompanyCode();
        $company = $company->value;
        
        return $company;
    }

    //Get the token of the company with the company's code
    public function getToken($code)
    {
        $this->load->database();
        $this->load->model('Token_model'); 
        $this->Token_model->codigo = $code;
        $token = $this->Token_model->getTokenByCompanyCode();
        $token = $token->value;
        return $token;
    }


    //Upload the Excel
    public function upload()
    {

        //Folder where the excel will be saved
        //FCPATH returns the whole path of the folder where the proyect is in the server
        $upload_directory = FCPATH . 'upload';

        //Makes new folder for the uploaded excel if there isn't one already
        if (file_exists($upload_directory) == false) {
            mkdir($upload_directory, 0777, true);
        }

        //Contiene the excel's binary
        $archivo = $_FILES['archivo'];

        //File's name
        $nombre = $archivo['name'];
        $input_file_name = $archivo['tmp_name'];

        //Split the extension from the name
        $array_nombre  = explode('.', $nombre); // subidaservice.xls --> ['subidaservice','xls']
        $extension = end ($array_nombre);
        $extension = strtoupper($extension);

        //Log messages
        log_message('debug','Archivo '.$nombre.' ha subido en '.$input_file_name);
        log_message('debug','Archivo con extension '.$extension);

        //Change file's name
        $file_name = "subida" . "-" . date("YmdHis") . "." . $extension;
        $new_path = $upload_directory. "/" . $file_name;

        //Move folder
        rename($input_file_name, $new_path);

        //For reading in Windows
        $new_path = str_replace("\\","/",$new_path );

        if ($extension != 'XLS' && $extension != 'XLSX') //not an Excel file
        {
            log_message('debug','Archivo no es un excel');
            $message = 'El archivo no es un excel, no se pudo procesar.';
            $result = [
            "message" => $message
            ];

            //$this->deleteFile($new_path);
            $this->loadResultView($result);
        }
        else
        {
        	$this->processExcel($new_path);
        }	
    }


    //Delete the file from the server
    public function deleteFile($path)
    {
        //$new_path = str_replace("/","\\",$new_path ); // only for windows
        //unlink($new_path);
    }


    //Load the view with results
    public function loadResultView($result)
    {
        $this->load->view('validacion_resultado_view', $result);
    }


    //Process Excels
    public function processExcel($path)
    {
        $workbook = SpreadsheetParser::open($path);

        $sheetIndex = 0;
        $token = null;
        $code = null;
        
        //Traverse worksheets    
        foreach ($workbook->getWorksheets() as $sheetName){
            log_message('debug','Sheet: '.strval($sheetName));
            
            $hasDash = strpos($sheetName, '-');

            if ($hasDash === false && $sheetName != 'Servicios') {
                $sheetIndex++;
                continue;
            }
            //Traverse rows
            foreach ($workbook->createRowIterator($sheetIndex) as $rowIndex => $Row) {                

                if($sheetIndex == 0) //Services sheet
                {   
                    if($rowIndex == 1) //Code row
                    {
                        $code = $Row[0];

                        if(!isset($code))
                        {
                            $message = 'El archivo no contiene un codigo.';
                            break 2;
                        }
                        else
                        {
                            $token = $this->getToken($code);

                            if(!isset($token))
                            {
                                $message = 'El codigo no corresponde.';
                                break 2;
                            }
                        }

                    }
                    if($rowIndex > 2) //Services rows
                    {
                        $this->processFirstSheet($Row); 
                    }
                }
                else //Form sheets
                {
                    if($rowIndex > 2) //Skip names, info rows
                    {   
                        $this->processForm($code, $Row, $sheetName);
                    }
                }
            }

            $firstRow = null; //reset to get new id's of new form
            $sheetIndex++; //next sheet
        }

        if(isset($message)) //an error happened
        {
            $result = [
            "message" => $message
            ];

            $this->deleteFile($path);
            $this->loadResultView($result);   
        }
        else
        {
            // Once all pages are processed, send all services from the db
            $this->sendWS($token);
            $message = 'El archivo se procesó y se hizo la carga masiva.';
            $result = [
            "message" => $message
            ];

            $this->deleteFile($path);
            $this->loadResultView($result);
        }   

    }


    //Process form sheets
    public function processForm($code, $currentRow, $currentSheet)
    {   
        if(is_null($currentRow[0]))
        {
            return;
        }
        //Get the form id
        $sheetNameExploded = explode('-', strval($currentSheet));
        $formId = end ($sheetNameExploded);

        $company = $this->getCompanyName($code);
        
        switch($company){
            case "interbank":
                $this->load->model('Interbank_model'); 
                $efs = $this->Interbank_model->formSorting($formId, $currentRow);
                break;

            case "disnovo":
                $this->load->model('Disnovo_model');
                $efs = $this->Disnovo_model->formSorting($formId, $currentRow);
                break;

            default:
                $message = 'Algo salió mal en el procesamiento.';
                $result = [
                    "message" => $message
                ];
                $this->loadResultView($result);
        }

        //Take out the temporal id and save it
        $id = $efs['id'];
        unset($efs['id']);
        


        //Make the form_add object
        $form_add = [
        "f" => $formId,
        "efs" => $efs
        ];
        //echo "<pre>"; var_dump($form_add); echo "</pre>";
        $this->addFormToService($id, $form_add);       
    }

    
    //Process the Services sheet
    public function processFirstSheet($Row)
    {

        $id_number = $Row[0];
        log_message('debug', 'Numero de id del servicio' . json_encode($id_number, JSON_PRETTY_PRINT));

        $header = [
        "user_name" => $Row[12],
        "codigo_tipo_servicio" => $Row[1],
        "fecha_programada" => $this->convertDate($Row[3], $Row[4]),
        "cliente_nombre" => $Row[5],
        "cliente_apepat" => $Row[6],
        "cliente_apemat" => $Row[7],
        "cliente_direccion" => $Row[8],
        "cliente_latitud" => $Row[9],
        "cliente_longitud" => $Row[10],
        "cliente_observaciones" => $Row[13],
        "codigo_referencia" => $Row[14],
        "form_adds" => array()
        ];

        log_message('debug', 'Contenido de la cabecera' . json_encode($header, JSON_PRETTY_PRINT));

        $this->load->database();
        $this->load->model('Services_model');

        $this->Services_model->id = $id_number;
        $this->Services_model->service_array = serialize($header);
        $service = $this->Services_model->insertService();
    }


    //Get service from db, add the form to the service, put it on the db again
    public function addFormToService($id, $form_add)
    {
        $this->load->database();
        $this->load->model('Services_model');

        $this->Services_model->id = $id;
        $service = unserialize($this->Services_model->getServiceById($id));
        array_push($service['form_adds'], $form_add);

        $this->Services_model->id = $id;
        $this->Services_model->service_array = serialize($service);
        $service = $this->Services_model->updateService();
    }


    //Reads all services in db, sends them as ws and delete them from the db
    public function sendWS($token){
        $this->load->database();
        $this->load->model('Services_model');

        $result = $this->Services_model->getAllServices();

        foreach ($result as $service) {
            $serviceArray = unserialize($service['service_array']);
            log_message('debug', 'Contenido del servicio ya listo' . json_encode($serviceArray, JSON_PRETTY_PRINT));

            $base_url_navego = 'http://api.navego360.com/api/';
            $get_servicio = 'servicio/create';
            $headerName = 'apitoken';

            $body = $serviceArray;
            $jsonBody = json_encode($body);

            $ch = curl_init($base_url_navego.$get_servicio);                                                  
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');                                                  
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);                                               
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                    
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                 
                'Content-Type: application/json',                                                    
                'Content-Length: ' .strlen($jsonBody),
                $headerName.': '.$token )                                
            ); 

            $resultJson = curl_exec($ch);
            echo trim(json_encode($resultJson, JSON_PRETTY_PRINT)); 
        }

        $this->Services_model->truncateTable();
    }


    
    public function toMapJson($coordinates)
    {
        $coordinates = strtr($coordinates, ',', '{');
        $coordinates = strtr($coordinates, '}', '{');
        $coordinates = explode('{', $coordinates);

        $coordinatesArray = [
        "la" => $coordinates[1],
        "lo" => $coordinates[2]
        ];

        return $coordinatesArray;
    }

    public function toMultiCheck($value)
    {
        $value = strtr($value, '[', ',');
        $value = strtr($value, ']', ',');
        $value = explode(',', $value);
        $checkValues = array();

        foreach ($value as $components) {
            if($components != "")
            {
                array_push($checkValues, $components);
            }
        }
        return $checkValues;
    }
}
