<?php

class Disnovo_model extends CI_Model
{
    public function formSorting($id, $row){
        switch ($id) {
            case '106':
                $efs = $this->processForm106($row);
                break;

            case '109':
                $efs = $this->processForm109($row);
                break;

            case '107':
                $efs = $this->processForm107($row);
            
            default:
                # code...
                break;
        }

        return $efs;
    }

    //Datos del Cliente
    public function processForm106($row){
        $efs = [
        "id" => $row[0],
        "1" => $row[1], //nombre
        "2" => strval($row[2]), //apellidos
        "6" => strval($this->hora106($row[3])), //hora
        "8" => strval($row[4]), //direccion
        "9" => strval($row[5]) //referencia
        ];

        return $efs;
    }

    //Modelo Combox
    public function processForm107($row){
        $efs = [
        "id" => $row[0],
        "1" => $this->manejoDelCampo107($row[1]), //Manejo del Campo
        "2" => $this->poda107($row[2]), //Poda
        "3" => $this->limpiezaDelCampo107($row[3]), //Limpieza del Campo
        "4" => $this->riegos107($row[4]), //Riegos
        "5" => $this->recomendacionesPecuarias107($row[5]), //Recomendaciones Pecuarias
        "6" => $row[6], //Otros
        "7" => $row[7], //Como se llama el analista
        "8" => strval($this->calificacion107($row[8])), //Como califica la atencion
        "9" => strval($this->tiempoDemoro107($row[9])) //Cuanto tiempo demoro
        ];

        return $efs;        
    }

    //Modelo Mapa
    public function processForm109($row){
        $efs = [
        "id" => $row[0],
        "1" => strval($this->nombreCompetencia109($row[1])), //Nombre de competencia
        "2" => $row[2], //Otros
        "3" => $row[3], //Comentarios
        "4" => $row[4], //Direccion
        "5" => $this->ubicacion109($row[5]) //Ubicacion
        ];

        return $efs;
    }

    public function hora106($time){
        $explodedTime = explode(' ', $time);
        $apiTime = $explodedTime[0] . 'T' . $explodedTime[1] . ':00.000Z';
        return $apiTime;
    }

    public function nombreCompetencia109($nombre){
        $nombreId = null;

        switch ($nombre) {
            case 'Prosegur':
                $nombreId = 1;
                break;

            case 'Clave3':
                $nombreId = 2;
                break;

            case 'Boxer':
                $nombreId = 3;
                break;

            case 'Otros':
                $nombreId = 4;
                break;
            
            default:
                break;
        }

        return $nombreId;
    }

    public function ubicacion109($coordinates){
        $coordinates = strtr($coordinates, ',', '(');
        $coordinates = strtr($coordinates, ')', '(');
        $coordinates = explode('(', $coordinates);

        $coordinatesArray = [
        "la" => $coordinates[1],
        "lo" => $coordinates[2]
        ];

        return $coordinatesArray;
    }

    public function manejoDelCampo107($values){
        $checkValues = array();
        $arrayValues = explode(', ', $values);
        foreach ($arrayValues as $components) {
            switch ($components) {
                case 'Deshierbo':
                    array_push($checkValues, '1');
                    break;
                
                case 'Fertilizacion':
                    array_push($checkValues, '2');
                    break;

                case 'Cambio de Surco':
                    array_push($checkValues, '3');
                    break;

                case 'Poda de Formacion':
                    array_push($checkValues, '4');
                    break;

                default:
                    # code...
                    break;
            }
        }
        
        return $checkValues;
    }

    public function poda107($values){
        $checkValues = array();

        $arrayValues = explode(', ', $values);
        foreach ($arrayValues as $components) {
            switch ($components) {
                case 'Formacion Sanitaria':
                    array_push($checkValues, '1');
                    break;
                
                case 'Produccion':
                    array_push($checkValues, '2');
                    break;

                case 'Mantenimiento':
                    array_push($checkValues, '3');
                    break;

                default:
                    # code...
                    break;
            }
        }
        
        return $checkValues;
    }

    public function limpiezaDelCampo107($values){
        $checkValues = array();

        $arrayValues = explode(', ', $values);
        foreach ($arrayValues as $components) {
            switch ($components) {
                case 'Aplicacion de Herbicidas':
                    array_push($checkValues, '1');
                    break;
                
                case 'Deshierbo manual':
                    array_push($checkValues, '2');
                    break;

                default:
                    # code...
                    break;
            }
        }
        
        return $checkValues;
    }

    public function riegos107($values){
        $checkValues = array();

        $arrayValues = explode(', ', $values);
        foreach ($arrayValues as $components) {
            switch ($components) {
                case 'Incrementar riegos':
                    array_push($checkValues, '1');
                    break;
                
                case 'Reducir riegos':
                    array_push($checkValues, '2');
                    break;

                case 'Hacer drenes':
                    array_push($checkValues, '3');
                    break;

                default:
                    # code...
                    break;
            }
        }
        
        return $checkValues;
    }

    public function recomendacionesPecuarias107($values){
        $checkValues = array();

        $arrayValues = explode(', ', $values);
        foreach ($arrayValues as $components) {
            switch ($components) {
                case 'Suplementacion del Alimento':
                    array_push($checkValues, '1');
                    break;
                
                case 'Dosificar productos de animales':
                    array_push($checkValues, '2');
                    break;

                case 'Arreglo de Establos/Cercos':
                    array_push($checkValues, '3');
                    break;

                default:
                    # code...
                    break;
            }
        }
        
        return $checkValues;
    }

    public function calificacion107($values){
        $calificacionId = null;

        switch ($values) {
            case 'Excelente':
                $calificacionId = 1;
                break;

            case 'Buena':
                $calificacionId = 2;
                break;

            case 'Regular':
                $calificacionId = 3;
                break;

            case 'Mala':
                $calificacionId = 4;
                break;

            
            default:
                $calificacionId = 100;
                break;
        }
        return $calificacionId;
    }

    public function tiempoDemoro107($values){
        $tiempoId = null;
        
        switch ($values) {
            case 'Horas':
                $tiempoId = 1;
                break;

            case 'Medio dia':
                $tiempoId = 2;
                break;

            case '1 dia':
                $tiempoId = 3;
                break;

            case 'Mas de 3 dias':
                $tiempoId = 4;
                break;

            
            default:
                $tiempoId = 100;
                break;
        }
        return $tiempoId;
    }
    
}