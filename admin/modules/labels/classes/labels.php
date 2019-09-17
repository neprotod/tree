<?php

class Labels_Admin{

    function index(){}
    
    function __construct(){
        $this->orders = Module::factory('orders',TRUE);
    }
    
    function fetch(){
        // Обработка действий
        if(Request::method('post')){
            // Сортировка
            $positions = Request::post('positions');         
            $ids = array_keys($positions);
            sort($positions);
            foreach($positions as $i=>$position)
                $this->orders->update_label($ids[$i], array('position'=>$position)); 

            
            // Действия с выбранными
            $ids = Request::post('check');
            if(is_array($ids))
            switch(Request::post('action')){
                case 'delete':
                {
                    foreach($ids as $id)
                        $this->orders->delete_label($id);    
                    break;
                }
            }                
        }

        // Отображение
        $labels = $this->orders->get_labels();
        
        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_labels',array('labels'=>$labels));
    }
    
    
}