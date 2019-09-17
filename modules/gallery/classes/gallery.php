<?php defined('MODPATH') OR exit();

class Gallery_Module implements I_Module{

    const VERSION = '0.0.1';
    
    function __construct(){
        
    }
    
    function index($setting = null){}
    
    function fetch(){
        $gallery_url = Registry::i()->page_url;
        $gallery_path = Registry::i()->settings['gallery'];
        $gallery_resize = Registry::i()->settings['resize_gallery'];

        if($gallery_url == ''){
            // Достаем все галлереи
            $galleries = $this->get_galleries();
            
            Request::$design->meta_title = "Фотогаллерея";
            Request::$design->meta_description = '';
            
            return Template::factory(Registry::i()->settings['theme'],'content_gallery_galleries',array('galleries' => $galleries,'gallery_path' => $gallery_path,'gallery_resize' => $gallery_resize));
        }
        
        // Достаем галлерею
        if(!$gallery = $this->get_gallery($gallery_url))
            return false;

        $gallery_full_path = $gallery_path .'/'. $gallery['gallery_path'];
        
        $catalog = $gallery['gallery_path'];

        if($dirs = scandir($gallery_full_path))
            unset($dirs[0],$dirs[1]);
        
        Request::$design->meta_title = $gallery['meta_title'];
        Request::$design->meta_description = $gallery['meta_description'];
        
        return Template::factory(Registry::i()->settings['theme'],'content_gallery_gallery',array('gallery' => $gallery,'gallery_path' => $gallery_path,'gallery_resize' => $gallery_resize, 'dirs'=>$dirs, 'catalog'=>$catalog,'gallery_full_path' => $gallery_full_path));
    }
    
    
    function get_galleries(){
        $sql = "SELECT url, name, gallery_path, img
                    FROM __gallery
                    ORDER BY position;";
        
        $sql = DB::placehold($sql);
        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute();
        
        return $result;
    }
    
    function get_gallery($url){
        $sql = "SELECT url, name, gallery_path, meta_title, meta_description 
                    FROM __gallery
                    WHERE url = :url;";
        
        $sql = DB::placehold($sql);
        $query = DB::query(Database::SELECT, $sql);
        $query->param(':url',$url);
        $result = $query->execute();

        return $result[0];
    }
}