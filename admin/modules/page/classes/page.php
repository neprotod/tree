<?php

class Page_Admin{

    function index(){}
    
    function __construct(){
        $this->pages = Module::factory('page',TRUE);
    }
    
    function fetch(){

        $page = array();
        if(Request::method('POST')){
            $page['id'] = Request::post('id', 'integer');
            $page['name'] = Request::param(Request::post('name'),TRUE);
            $page['title'] = Request::param(Request::post('header'));
            $page['url'] = Request::post('url');
            $page['meta_title'] = Request::param(Request::post('meta_title'));
            $page['meta_description'] = Request::param(Request::post('meta_description'));
            $page['body'] = Request::post('body');
            $page['menu_id'] = Request::post('menu_id', 'integer');
            $page['type_id'] = Request::post('type_id', 'integer');
            $page['format_id'] = Request::post('format_id', 'integer');
            $page['visible'] = Request::post('visible', 'integer');
    
            ## Не допустить одинаковые URL разделов.
            if(($p = $this->pages->get_page($page['url'])) && $p['id']!=$page['id']){
                Request::$design->error('message_error', 'Такой URL уже есть');
            }elseif(empty($page['name'])){
                Request::$design->error('message_error', 'Вы не заполнини название пункта меню');
            }else{
                if(empty($page['id'])){
                      $page['id'] = $this->pages->add_page($page);
                      $page = $this->pages->get_page(intval($page['id']));
                      Request::$design->massage('message_success', 'Страница добавлена');
                  }else{
                      $this->pages->update_page(intval($page['id']), $page);
                      $page = $this->pages->get_page(intval($page['id']));
                      Request::$design->massage('message_success', 'Страница обновлена');
                   }
            }
        }else{
            $id = Request::get('id', 'integer');
            if(!empty($id)){
                $page = $this->pages->get_page(intval($id));            
            }else{
                $page['menu_id'] = Request::get('menu_id');
                $page['visible'] = 1;
                $page['type_id'] = 2;
                $page['format_id'] = 1;
            }
        }
        // дополняем типом
        $types = $this->pages->get_types();
        Request::$design->types = $types;
        // дополняем форматом
        $formats = $this->pages->get_formats();
        Request::$design->formats = $formats;
            
        Request::$design->page = $page;
        
        
           $menus = $this->pages->get_menus();
        Request::$design->menus = $menus;
        
        // Текущее меню
        if(isset($page['menu_id']))
              $menu_id = $page['menu_id']; 
            
          if(empty($menu_id) || !$menu = $this->pages->get_menu($menu_id)){
              $menu = reset($menus);
          }
        
         Request::$design->menu = $menu;

    

        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_page',array());
    }
}