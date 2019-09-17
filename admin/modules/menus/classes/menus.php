<?php

class Menus_Admin{

    function index(){}
    
    function __construct(){
        $this->pages = Module::factory('page',TRUE);
    }
    
    function fetch(){
         // Меню
        $menus = $this->pages->get_menus();
        
        // Обработка действий
        if(Request::method('post')){
            // Сортировка
            $positions = Request::post('positions');         
            $ids = array_keys($positions);
            sort($positions);
            foreach($positions as $i=>$position)
                $this->pages->update_menu($ids[$i], array('position'=>$position)); 
                
                // Действия с выбранными
                $ids = Request::post('check');
                if(is_array($ids))
                    switch(Request::post('action')){
                        case 'delete':
                        {
                            foreach($ids as $id)
                                $this->pages->delete_menu($id);    
                            break;
                        }
                    }
                    
            $menus = $this->pages->get_menus();
        }
        
        Request::$design->menus = $menus;

        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_menus',array());
    }
    
    
}