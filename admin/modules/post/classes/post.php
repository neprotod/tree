<?php

class Post_Admin{

    function index(){}
    
    function __construct(){
        $this->blog = Module::factory('blog',TRUE);
    }
    
    function fetch(){
        $post = array();
        if(Request::method('post')){
            $post['id'] = Request::post('id', 'integer');
            $post['name'] = Request::post('name');
            $post['date'] = date('Y-m-d', strtotime(Request::post('date')));
            
            $post['visible'] = Request::post('visible', 'boolean');

            $post['url'] = Request::post('url', 'string');
            $post['meta_title'] = Request::post('meta_title');
            $post['meta_description'] = Request::post('meta_description');
            
            $post['annotation'] = Request::post('annotation');
            $post['text'] = Request::post('body');

             // Не допустить одинаковые URL разделов.
            if(($a = $this->blog->get_post($post['url'])) && $a['id']!=$post['id']){            
                Request::$design->error('error','Такой URL уже есть');
            }else{
                if(empty($post['id'])){
                      $post['id'] = $this->blog->add_post($post);
                      $post = $this->blog->get_post(intval($post['id']));
                    Request::$design->massage('message_success','Запись добавлена');
                  }else{
                      $this->blog->update_post($post['id'], $post);
                      $post = $this->blog->get_post(intval($post['id']));
                    Request::$design->massage('message_success','Запись обнавлена');
                  }
            }
        }else{
            $post['id'] = Request::get('id', 'integer');
            $post = $this->blog->get_post(intval($post['id']));
        }

        if(empty($post)){
            $post = array();
            $post['date'] = date(Registry::i()->settings['date_format'], time());
            $post['visible'] = 1;
        }else{
            if(isset($post['date']))
                $post['date'] = Str::sql_date(Registry::i()->settings['date_format'],$post['date']);
        }
         
        
        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_post',array('post'=>$post));
    }
    
    
}