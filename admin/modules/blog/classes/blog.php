<?php

class blog_Admin{

    function index(){}
    
    function __construct(){
        $this->blog = Module::factory('blog',TRUE);    
    }
    
    function fetch(){
        
        // Обработка действий
        if(Request::method('post')){
            // Действия с выбранными
            $ids = Request::post('check');
            if(is_array($ids))
            switch(Request::post('action')){
                case 'disable':
                {
                    $this->blog->update_post($ids, array('visible'=>0));          
                    break;
                }
                case 'enable':
                {
                    $this->blog->update_post($ids, array('visible'=>1));          
                    break;
                }
                case 'delete':
                {
                    foreach($ids as $id)
                        $this->blog->delete_post($id);    
                    break;
                }
            }                
        }

        $filter = array();
        $filter['page'] = max(1, Request::get('page', 'integer'));         
        $filter['limit'] = 20;
        
        $posts_count = $this->blog->count_posts($filter);
        // Показать все страницы сразу
        if(Request::get('page') == 'all')
            $filter['limit'] = $posts_count;    
        
        $posts = $this->blog->get_posts($filter);

        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_blog',array('posts_count'=>$posts_count,"pages_count"=>ceil($posts_count/$filter['limit']),"current_page"=>$filter['page'],"posts"=>$posts));
    }
    
    
}