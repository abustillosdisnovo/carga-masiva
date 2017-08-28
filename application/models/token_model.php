<?php

class Token_model extends CI_Model
{
    var $id = null;
    var $codigo = null;
    var $nombre = null;
    var $token = null;

    public function getTokenByCompanyCode() 
    {
         return $this->db->select("t.token as value")
            ->from("token t")            
            ->where("t.codigo", $this->codigo)
            ->get()->row();
    }

    public function getCompanyNameByCompanyCode()
    {
        //echo '<pre>' . var_dump($this->codigo) . '</pre>';
        $nombre = $this->db->select("t.empresa as value")
            ->from("token t")            
            ->where("t.codigo", $this->codigo)
            ->get()->row();

       // echo '<pre>' . var_dump($nombre) . '</pre>';
        return $nombre;
    }

    public function createToken(){
    	$this->db
    	->set('codigo',$this->codigo)
    	->set('nombre',$this->nombre)
    	->set('token',$this->token)
    	->insert('token');

        return $this->db->insert_id();
    }
}