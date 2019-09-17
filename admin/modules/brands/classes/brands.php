<?php

class Brands_Admin{

    function index(){}
    
    function __construct(){
        $this->brands = Module::factory('brands',TRUE);
    }
    
    function fetch(){
        // Обработка действий     
        if(Request::method('post')){

            // Действия с выбранными
            $ids = Request::post('check');

            if(is_array($ids))
            switch(Request::post('action')){
                case 'delete':
                {
                    foreach($ids as $id)
                        $this->brands->delete_brand($id);    
                    break;
                }
            }
        }    
        
        $brands = $this->brands->get_brands();
        
        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_brands',array('brands'=>$brands));
    }
    
    
}