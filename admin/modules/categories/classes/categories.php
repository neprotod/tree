<?php

class Categories_Admin{

    function index(){}
    
    function __construct(){
        $this->categories = Module::factory('categories',TRUE);
        
    }
    
    function fetch(){
        if(Request::method('post')){
            // Действия с выбранными
            $ids = Request::post('check');
            if(is_array($ids))
            switch(Request::post('action')){
                case 'disable':
                {
                    foreach($ids as $id)
                        $this->categories->update_category($id, array('visible'=>0));    
                    break;
                }
                case 'enable':
                {
                    foreach($ids as $id)
                        $this->categories->update_category($id, array('visible'=>1));    
                    break;
                }
                case 'delete':
                {
                    $this->categories->delete_category($ids);    
                    break;
                }
            }        
          
            // Сортировка
            $positions = Request::post('positions');
             $ids = array_keys($positions);
            sort($positions);
            foreach($positions as $i=>$position)
                $this->categories->update_category($ids[$i], array('position'=>$position)); 

        }  
  
        $categories = $this->categories->get_categories_tree();

        Request::$design->categories = $categories;
    
        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_categories',array());
    }
    
    
}