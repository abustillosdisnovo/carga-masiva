<?php

class Services_model extends CI_Model
{
    var $id = null;
    var $service_array = null;

    public function getServiceById($id) 
    {
        $this->db->select("service_array");
        $this->db->from("service");            
        $this->db->where("id", $id);
        $query = $this->db->get();  
        
        $result = $query->result_array();
        //echo '<pre>' . var_dump($result) . '</pre>';
        
        $result2 = $result[0];
        //echo '<pre>' . var_dump($result2) . '</pre>';
        
        $result3 = $result2['service_array'];
       // echo '<pre>' . var_dump($result3) . '</pre>';
        return $result3;
        // $result['service_array'];
    }

    public function insertService(){
    	$this->db
    	->set('id',$this->id)
    	->set('service_array',$this->service_array)
    	->insert('service');

        return $this->db->insert_id();
    }

    public function updateService()
    {
        $this->db->set('service_array',$this->service_array);
        $this->db->where('id',$this->id);
        $this->db->update('service');        
        //$query = $this->db->get();     
    }

    public function getAllServices()
    {
        $this->db->select("*");
        $this->db->from("service");            
        $query = $this->db->get();
        $result = $query->result_array();  
        //echo '<pre>' . var_dump($result) . '</pre>';
        return $result;
    }

    public function truncateTable()
    {
        $this->db->truncate('service');
    }
}