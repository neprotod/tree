<?php

class Prices_Admin{

    function index(){}
    
    function __construct(){
        $this->prices = Module::factory('prices',TRUE);
    }
    
    function fetch(){
        // Обработка действий
        if(Request::method('post')){
            // Сортировка
            $positions = Request::post('positions'); 
            $ids = array_keys($positions);
            sort($positions);
            foreach(array_reverse($positions) as $i=>$position)
                $this->prices->update_price($ids[$i], array('position'=>$position)); 
            // Действия с выбранными
            $ids = Request::post('check');
            if(is_array($ids))
            switch(Request::post('action')){
                case 'delete':
                {
                    foreach($ids as $id)
                        $this->prices->delete_price($id);    
                    break;
                }
            }                
        }
        $prices = $this->prices->get_prices();
        
        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_prices',array('prices'=>$prices));
    }
    
    
}