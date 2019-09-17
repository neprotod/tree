<?php

class Label_Admin{

    function index(){}
    
    function __construct(){
        $this->orders = Module::factory('orders',TRUE);
    }
    
    function fetch(){
    
        $label = array();
        $label['color'] = 'ffffff';
        if(Request::method('POST')){
            $label['id'] = Request::post('id', 'integer');
            $label['name'] = Request::post('name');
            $label['color'] = Request::post('color');
            if(empty($label['id'])){
                  $label['id'] = $this->orders->add_label($label);
                  $label = $this->orders->get_label(intval($label['id']));
                Request::$design->massage('message_success','Метка добавлена');
            }else{
                $this->orders->update_label($label['id'], $label);
                $label = $this->orders->get_label($label['id']);
                Request::$design->massage('message_success','Метка обнавлена');
            }
        }else{
            $id = Request::get('id', 'integer');
            if(!empty($id))
                $label = $this->orders->get_label(intval($id));            
        }    

        // Отображение
        $labels = $this->orders->get_labels();
        
        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_label',array('label'=>$label));
    }
    
    
}