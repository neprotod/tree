<?php

class Category_Admin{

    function index(){}
    
    function __construct(){
        $this->categories = Module::factory('categories',TRUE);
        $this->image = Module::factory('image',TRUE);
        
    }
    
    function fetch(){
    
        $category = array();
        
        if(Request::method('post')){
            $category['id'] = Request::post('id', 'integer');
            $category['parent_id'] = Request::post('parent_id', 'integer');
            $category['name'] = Request::post('name');
            $category['visible'] = Request::post('visible', 'boolean');
            $category['url'] = Request::post('url');
            $category['meta_title'] = Request::post('meta_title');
            $category['meta_keywords'] = Request::post('meta_keywords');
            $category['meta_description'] = Request::post('meta_description');
            
            $category['description'] = Request::post('description');
    
            // Не допустить одинаковые URL разделов.
            if(($c = $this->categories->get_category($category['url'])) && $c->id != $category['id']){
                Request::$design->error('error_url','Такой URL уже есть');
            }else{
                if(empty($category['id'])){
                      $category['id'] = $this->categories->add_category($category);
                    Request::$design->massage('message', 'Категория добавлена');
                  }else{
                      $this->categories->update_category($category['id'], $category);
                    Request::$design->massage('message', 'Категория обнавлена');
                  }
                  // Удаление изображения
                  if(Request::post('delete_image')){
                      $this->categories->delete_image($category['id']);
                  }
                  // Загрузка изображения
                  if($image = Request::files('image')){
                      $this->categories->delete_image($category['id']);
                    $image_name = $this->image->upload_image($image['tmp_name'], $image['name'],Registry::i()->settings['categories_image']);
                      $this->categories->update_category($category['id'], array('image'=>$image_name));
                  }
                  $category = $this->categories->get_category(intval($category['id']),TRUE);
            }
        }else{
            $category['id'] = Request::get('id', 'integer');
            $category = $this->categories->get_category($category['id'],TRUE);
            print_r($category[0]['name']);
        }
        

        $categories = $this->categories->get_categories_tree();

        Request::$design->category = $category;

        Request::$design->categories = $categories;
        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_category',array());
    }
    
}