<?php

class Features_Admin{

    function index(){}
    
    function __construct(){
        $this->categories = Module::factory('categories',TRUE);
        $this->features = Module::factory('features',TRUE);
        
    }
    
    function fetch(){
        if(Request::method('post')){      
            // Действия с выбранными
            $ids = Request::post('check');
            if(is_array($ids))
            switch(Request::post('action')){
                case 'set_in_filter':
                {
                    $this->features->update_feature($ids, array('in_filter'=>1));    
                    break;
                }
                case 'unset_in_filter':
                {
                    $this->features->update_feature($ids, array('in_filter'=>0));    
                    break;
                }
                case 'delete':
                {
                    $current_cat = Request::get('category_id', 'integer');
                    foreach($ids as $id){
                        // текущие категории
                        $cats = $this->features->get_feature_categories($id);
                        
                        // В каких категориях оставлять
                        $diff = array_diff($cats, (array)$current_cat);
                        if(!empty($current_cat) && !empty($diff)){
                            $this->features->update_feature_categories($id, $diff);
                        }else{
                            $this->features->delete_feature($id); 
                        }
                    }
                    break;
                }
            }        
          
            // Сортировка
            $positions = Request::post('positions');
             $ids = array_keys($positions);
            sort($positions);
            foreach($positions as $i=>$position)
                $this->features->update_feature($ids[$i], array('position'=>$position)); 

        } 
    
        $categories = $this->categories->get_categories_tree();
        $category = null;
        
        $filter = array();
        $category_id = Request::get('category_id', 'integer');
        if($category_id)
        {
            $category = $this->categories->get_category($category_id,TRUE);
            $filter['category_id'] = $category['id'];
        }
        
        $features = $this->features->get_features($filter);

        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_features',array('categories'=>$categories,"category"=>$category,"features"=>$features));
    }
    
    
}