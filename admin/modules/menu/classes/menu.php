<?php

class Menu_Admin{

    function index(){}
    
    function __construct(){
        $this->pages = Module::factory('page',TRUE);
    }
    
    function fetch(){
         // Меню
        $page = array();
        
        if(Request::method('POST')){
        
            $menu['id'] = Request::param(Request::post('id', 'integer'));
            $menu['name'] = Request::param(Request::post('name'),TRUE);
            $menu['visible'] = Request::post('visible', 'boolean');
    
            ## Не допустить одинаковые имен.
            $name = FALSE;
            foreach($this->pages->get_menus() as $m){
                if(($m['name'] == $menu['name']) AND $menu['id'] != $m['id']){
                    $name = TRUE;
                    break;
                }
            }
            
            if($name === TRUE){    
                Request::$design->error('message_error', 'Такое имя уже есть');
            }else{
                if(empty($menu['id'])){
                      $menu['id'] = $this->pages->add_menu($menu);
                      $menu = $this->pages->get_menu($menu['id']);
                      Request::$design->massage('message_success', 'Меню добавлено');
                  }else{
                      $this->pages->update_menu($menu['id'], $menu);
                      $menu = $this->pages->get_menu($menu['id']);
                      Request::$design->massage('message_success', 'Страница обновлена');
                   }
            }
            header("Location: ".Request::get('return'));
        }else{
            $id = Request::get('id', 'integer');
            if(!empty($id)){
                $menu = $this->pages->get_menu(intval($id));
            }else{
                $menu['visible'] = 1;
            }
        }    

        Request::$design->menu = $menu;
        Request::$design->menus = $this->pages->get_menus();
        
        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_menu',array());
    }
    
    
}