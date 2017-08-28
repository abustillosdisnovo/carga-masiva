<?php

class Interbank_model extends CI_Model
{

    /*public function processForm283($row){
        $efs = [
        "1" = $row[1], //Actualizar coordenada
        "2" = $row[2], //Persona de contacto
        "3" = $row[3], //Telefono fijo local
        "4" = $row[4], //Celular local operador
        "5" = $row[5], //Horario de atencion
        "6" = $row[6], //Dias de Atencion
        "7" = 283rubro($row[7]), //Rubro
        "23" = $row[8], //Otros Rubros
        "25" = $row[9], //Desde:
        "26" = $row[10], //Hasta:
        "8" = $row[11], //Telefono fijo titular
        "9" = $row[12], //Celular del titular
        "10" = $row[13], //Email del titular
        "24" = $row[14], //Referencia de Direccion del Local
        "11" = $row[15], //Competencia bancos
        "12" = $row[16], //Competencia cajas
        "13" = $row[17], //Competencia procesadores
        "14" = $row[18], //Tipo de IBA
        "15" = $row[19], //Departamento
        "16" = $row[20], //Provincia
        "17" = $row[21], //Distrito
        "18" = $row[22], //Tienda Asignada
        "19" = $row[23], //Observacion
        "20" = $row[24], //Agente BIM
        "21" = $row[25] //Observacion BIM
        ];

        return $efs;
    }*/

    public function formSorting($id, $row){
        switch ($id) {
            case '283':
                $efs = $this->processForm283($row);
                break;
            
            default:
                # code...
                break;
        }

        return $efs;
    }

    //Datos del Agente
    public function processForm283($row){
        $efs = [
        "id" => $row[0],
        "2" => $row[1], //Persona de contacto
        "3" => strval($row[2]), //Telefono fijo local
        "4" => strval($row[3]), //Celular local operador
        "7" => strval($this->rubro283($row[4])), //Rubro
        "8" => strval($row[5]), //Telefono fijo titular
        "9" => strval($row[6]), //Celular del titular
        "10" => $row[7], //Email del titular
        "24" => $row[8] //Referencia de Direccion del Local
        ];

        return $efs;
    }

    //Rubro with ID = 7, from Datos del Agente
    public function rubro283($rubro){
        $rubroId = null;
        switch ($rubro) {
            case 'Bodegas y Minimarkets':
                $rubroId = 1;
                break;

            case 'Boticas y Farmacias':
                $rubroId = 2;
                break;

            case 'Centros de Cobranza':
                $rubroId = 3;
                break;

            case 'Centros de Entretenimiento':
                $rubroId = 4;
                break;

            case 'Centros de Estudios':
                $rubroId = 5;
                break;

            case 'Centros de Servicios Financieros':
                $rubroId = 6;
                break;

            case 'Centros y Galerías Comerciales':
                $rubroId = 7;
                break;

            case 'Clínicas & Hospitales':
                $rubroId = 8;
                break;

            case 'Clubes Gimnasios y Spas':
                $rubroId = 9;
                break;

            case 'Comercios Turísticos':
                $rubroId = 10;
                break;

            case 'Distribuidoras de gas':
                $rubroId = 11;
                break;

            case 'Estudios Fotográficos':
                $rubroId = 12;
                break;

            case 'Ferreterías':
                $rubroId = 13;
                break;

            case 'Hoteles':
                $rubroId = 14;
                break;

            case 'Lavanderías':
                $rubroId = 15;
                break;

            case 'librerías':
                $rubroId = 16;
                break;

            case 'locutorios':
                $rubroId = 17;
                break;

            case 'Mercados':
                $rubroId = 18;
                break;

            case 'Otros':
                $rubroId = 19;
                break;

            case 'Restaurantes Panaderías y Pastelerías':
                $rubroId = 20;
                break;

            case 'Supermercados y Mayoristas':
                $rubroId = 21;
                break;

            case 'Tiendas Comerciales':
                $rubroId = 22;
                break;

            case 'Tiendas de Suministros de Cómputo':
                $rubroId = 23;
                break;

            case 'Tiendas de Vestir':
                $rubroId = 24;
                break;

            default:
                $rubroId = 100;
                break;
        }
        return $rubroId;
    }
}