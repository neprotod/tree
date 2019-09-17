<?php

class Feature_Admin{

    function index(){}
    
    function __construct(){
        $this->categories = Module::factory('categories',TRUE);
        $this->features = Module::factory('features',TRUE);
        
    }
    
    function fetch(){
        
        $feature = array();
        if(Request::method('post')){
            $feature['id'] = Request::post('id', 'integer');
            $feature['name'] = Request::post('name');
            $feature['in_filter'] = intval(Request::post('in_filter'));
            $feature_categories = Request::post('feature_categories');

            if(empty($feature['id'])){
                  $feature['id'] = $this->features->add_feature($feature);
                  $feature = $this->features->get_feature($feature['id']);
                Request::$design->massage('message_success','Свойство добавлено');
              }else{
                $this->features->update_feature($feature['id'], $feature);
                $feature = $this->features->get_feature($feature['id']);
                Request::$design->massage('message_success','Свойство обнавлено');
            }
            $this->features->update_feature_categories($feature['id'], $feature_categories);
        }else{
            $feature['id'] = Request::get('id', 'integer');
            $feature = $this->features->get_feature($feature['id']);
        }

        $feature_categories = array();    
        if($feature){
            $feature_categories = $this->features->get_feature_categories($feature['id']);
        }
        
        $categories = $this->categories->get_categories_tree();
        
        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_feature',array('categories'=>$categories,'feature'=>$feature,'feature_categories'=>$feature_categories));
    }
    
    
}