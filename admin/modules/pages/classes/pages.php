<?php

class Pages_Admin{

    function index(){}
    
    function __construct(){
        $this->pages = Module::factory('page',TRUE);
    }
    
    function fetch(){
         // Меню
        $menus = $this->pages->get_menus();
        Request::$design->menus = $menus;
        
        // Текущее меню
        $menu_id = Request::get('menu_id', 'integer'); 
        if(!$menu_id || !$menu = $this->pages->get_menu($menu_id)){
            $menu = reset($menus);
        }
        Request::$design->menu = $menu;


        // Обработка действий
        if(Request::method('post')){
            // Сортировка
            $positions = Request::post('positions');         
            $ids = array_keys($positions);
            sort($positions);
            foreach($positions as $i=>$position)
                $this->pages->update_page($ids[$i], array('position'=>$position)); 

            
            // Действия с выбранными
            $ids = Request::post('check');
            if(is_array($ids))
                switch(Request::post('action')){
                    case 'disable':
                    {
                        $this->pages->update_page($ids, array('visible'=>0));          
                        break;
                    }
                    case 'enable':
                    {
                        $this->pages->update_page($ids, array('visible'=>1));          
                        break;
                    }
                    case 'delete':
                    {
                        foreach($ids as $id)
                            $this->pages->delete_page($id);    
                        break;
                    }
                }        
            
        }

      

        // Отображение
        $pages = $this->pages->get_pages(array('menu_id'=>$menu['id']));

        Request::$design->pages = $pages;

        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_pages',array());
    }
}