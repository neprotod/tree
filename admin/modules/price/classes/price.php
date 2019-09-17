<?php

class Price_Admin{
    
    private $allowed_image_extentions = array('png', 'gif', 'jpg', 'jpeg', 'ico');
    
    function index(){}
    
    function __construct(){
        $this->prices = Module::factory('prices',TRUE);
    }
    
    function fetch(){

        $page = array();
        if(Request::method('POST')){
            $price['name'] = Request::param(Request::post('name'),TRUE);
            
            $price['id'] = ($id = Request::post('id', 'integer'))? $id : $price['name'];
            
            ## Не допустить одинаковые URL разделов.
            if(($p = $this->prices->get_price($price['id'])) && ($p['name'] == $price['name'] AND $p['id'] != $price['id'])){
                Request::$design->error('message_error', 'Такое имя прайса уже есть');
            }else{
                if(empty($price['id']) OR !is_int($price['id'])){
                        echo 1;
                      $price['id'] = $this->prices->add_price($price);
                    // Добавляем позицию
                    $this->prices->update_price($price['id'],array('position'=>$price['id']));
                    
                      $price = $this->prices->get_price(intval($price['id']));
                      Request::$design->massage('message_success', 'Прайс добавлен');
                  }else{
                        echo 2;
                      $this->prices->update_price(intval($price['id']), $price);
                      $price = $this->prices->get_price(intval($price['id']));
                      Request::$design->massage('message_success', 'Прайс обнавлен');
                   }
                // Удаление изображения
                  if(Request::post('delete_img')){
                      $this->prices->delete_image($price['id']);
                  }
                // Загрузка изображения
                  $image = Request::files('img');
                
                  if(!empty($image['name']) && in_array(strtolower(pathinfo($image['name'], PATHINFO_EXTENSION)), $this->allowed_image_extentions)){
                    
                      $this->prices->delete_image($price['id']);  

                      move_uploaded_file($image['tmp_name'], trim($this->prices->folder.'/'.$image['name'],'/'));
                    
                      $this->prices->update_price($price['id'], array('img'=>$image['name']));
                  }
                
                //Добавляем файл прайса.
                $file = Request::files('price');
                if(!empty($file['name'])){
                    
                    $this->prices->drop_file($price['id']);
                    
                    move_uploaded_file($file['tmp_name'], trim($this->prices->folder.'/'.$file['name'],'/'));
                    
                    $this->prices->update_price($price['id'], array('price'=>$file['name']));
                }
                
                  $price = $this->prices->get_price(intval($price['id']));
            }
        }else{
            $id = Request::get('id', 'integer');
            if(!empty($id)){
                $price = $this->prices->get_price(intval($id));            
            }
        }

    

        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_price',array('price'=>$price,'folder'=>$this->prices->folder));
    }
}