<?php defined('MODPATH') OR exit();

class Menu_Module implements I_Module{

    const VERSION = '0.0.1';
    
    public $menu;
    
    function __construct(){
        $this->menu = Model::factory('menu','menu');
    }
    
    function index($setting = null){}
    
    // Создаем верхнее меню
    function top(){
        $result = $this->menu->get(1);
        
        return View::factory('menuTop','menu',array('items' => $result));
    }
    
    function bottom(){
        // Строится из древа категорий
        $result = $this->menu->category_tree(Registry::i()->categories_tree, 'bottom');
        return $result;
    }
    function admin_category($category = NULL){
        if($category = NULL)
        // Строится из древа категорий
        $result = $this->menu->category_tree(Registry::i()->categories_tree,'admin');
        return $result;
    }
    
        function get($id){
        $where = '1';
        if(is_int($id)){
            $where = "m.id = :id";
        }else{
            $where = "m.name = :id";
        }
        $sql = DB::placehold("SELECT p.name, p.url
        FROM __pages p 
        INNER JOIN __menu m ON m.id = p.menu_id
        WHERE $where
        ORDER BY p.position;");
          
        $query = DB::query(Database::SELECT, $sql);
        $query->param(':id',$id);
        
        $result = $query->execute();

        return $result;
    }
    
}