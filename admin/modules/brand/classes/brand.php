<?php

class Brand_Admin{

    private $allowed_image_extentions = array('png', 'gif', 'jpg', 'jpeg', 'ico');
    
    function index(){}
    
    function __construct(){
        $this->brands = Module::factory('brands',TRUE);
    }
    
    function fetch(){
    
        $brand = array();
        if(Request::method('post')){
            $brand['id'] = Request::post('id', 'integer');
            $brand['name'] = Request::post('name');
            $brand['description'] = Request::post('description');

            $brand['url'] = Request::post('url', 'string');
            $brand['meta_title'] = Request::post('meta_title');
            $brand['meta_description'] = Request::post('meta_description');

            // Не допустить одинаковые URL разделов.
            if(($c = $this->brands->get_brand($brand['url'])) && $c['id']!=$brand['id']){            
                Request::$design->error('error_url','Такой URL уже есть');
            }else{
                if(empty($brand['id'])){
                      $brand['id'] = $this->brands->add_brand($brand);
                    Request::$design->massage('message_success','Бренд добавлен');
                  }else{
                      $this->brands->update_brand($brand['id'], $brand);
                    Request::$design->massage('message_success','Бренд обнавлен');
                  }    
                  // Удаление изображения
                  if(Request::post('delete_image')){
                      $this->brands->delete_image($brand['id']);
                  }
                  // Загрузка изображения
                  $image = Request::files('image');
                  if(!empty($image['name']) && in_array(strtolower(pathinfo($image['name'], PATHINFO_EXTENSION)), $this->allowed_image_extentions)){
                      $this->brands->delete_image($brand['id']);                       
                      move_uploaded_file($image['tmp_name'], '/'.trim(Registry::i()->settings['brands_images_dir'],'/').'/'.$image['name']);
                      $this->brands->update_brand($brand['id'], array('image'=>$image['name']));
                  }
                  $brand = $this->brands->get_brand(intval($brand['id']));
            }
        }else{
            $brand['id'] = Request::get('id', 'integer');
            $brand = $this->brands->get_brand($brand['id']);
        }
        
        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_brand',array('brand'=>$brand));
    }
    
    
}