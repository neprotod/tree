<?php defined('MODPATH') OR exit();

class Model_Menu_MENU{

    function get($id){
        $sql = DB::placehold("SELECT p.name, p.url
        FROM pages p 
        INNER JOIN menu m ON m.id = p.menu_id
        WHERE m.id = $id
        ORDER BY p.position;");
          
        $query = DB::query(DATABASE::SELECT, $sql);
        
        $result = $query->execute();
        
        return $result;
    }
    
    function category_tree($category){
        return View::factory('bottom_category','menu',array('categories' => $category, 'menu' => $this));
    }
    
    function subcategories_tree($category){
        return View::factory('bottom_subcategories','menu',array('categories' => $category));
    }
    
}