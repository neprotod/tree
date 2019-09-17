<?php defined('MODPATH') OR exit();

class Page_Module implements I_Module{
    
    const VERSION = '0.0.1';
    
    private $page;    
        
    function __construct(){

    }
    
    function index($setting = null){
        
    }
    
    function fetch(){
        header("Cache-control: public,max-age=600");
        $page = $this->get_page(Registry::i()->page_url);
        
        Request::$design->meta_title = empty($page['meta_title']) ? $page['title'] : $page['meta_title'];
        Request::$design->meta_description = $page['meta_description'];
        if(!empty($page))
            return Template::factory(Registry::i()->settings['theme'],'content_'.$page['type'],array('page' => $page));
    }
    /*
    *
    * Функция возвращает страницу по ее id или url (в зависимости от типа)
    * @param $id id или url страницы
    *
    */
    function get_page($id){
    
        if(gettype($id) == 'string')
            $where = ' WHERE p.url=:id ';
        else
            $where = ' WHERE p.id=:id ';
            
        $sql = "SELECT p.id, p.url, p.title, p.name, p.meta_title, p.meta_description, p.body,p.menu_id, t.type, t.name as type_name, p.type_id, p.visible, p.format_id
                  FROM __pages p 
                  INNER JOIN __type t ON t.id = p.type_id
                  $where";
        $sql = DB::placehold($sql);
                  
        $query = DB::query(Database::SELECT, $sql);
        // Параметры
        $query->param(':id',$id);
        
        $result = $query->execute();
    
        return $result[0];
    }
    /*
    *
    * Функция возвращает типы данных
    * @param $id id или url страницы
    *
    */
    function get_types(){
        $types = array();
        $sql = "SELECT id, type, name, description
                  FROM __type
                  ORDER BY position";
        $sql = DB::placehold($sql);
                  
        $query = DB::query(Database::SELECT, $sql);
        
        $result = $query->execute();

        return $result;
    }
    /*
    *
    * Функция возвращает форматы текста
    *
    */
    function get_formats(){
        $formats = array();
        $sql = "SELECT id, format, name, description
                  FROM __format
                  ORDER BY position";
        $sql = DB::placehold($sql);
                  
        $query = DB::query(Database::SELECT, $sql);
        
        $results = $query->execute();
        if(!empty($results))
            foreach($results as $result){
                $formats[$result['id']] = $result;
            }
        return $formats;
    }
    /*
    *
    * Функция возвращает формат текста
    *
    */
    /*function get_format($format_id){
        $types = array();
        $sql = "SELECT id, type, name, description
                  FROM __type
                  ORDER BY position";
        $sql = DB::placehold($sql);
                  
        $query = DB::query(Database::SELECT, $sql);
        
        $result = $query->execute();

        return $result;
    }*/
    
    /*
    *
    * Функция возвращает массив страниц, удовлетворяющих фильтру
    * @param $filter
    *
    */
    public function get_pages($filter = array()){
        $menu_filter = '';
        $visible_filter = '';
        $pages = array();

        if(isset($filter['menu_id'])){
            $menu_filter = Str::__('AND menu_id in (:menu_id)', array(':menu_id'=>implode(',',(array)$filter['menu_id'])));
        }
        
        if(isset($filter['visible'])){
            $visible_filter = Str::__('AND visible = :visible', array(':visible'=>intval($filter['visible'])));
        }
        $sql = "SELECT id, url, title as header, name, meta_title, meta_description, body, menu_id, position, visible
                  FROM __pages WHERE 1 $menu_filter $visible_filter ORDER BY position";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        
        $result = $query->execute();
        
        foreach($result as $page)
            $pages[$page['id']] = $page;
            
        return $pages;
    }
    /*
    *
    * Функция возвращает меню по id
    * @param $id
    *
    */
    public function get_menu($menu_id){
        $sql = "SELECT * FROM __menu WHERE id=:id LIMIT 1";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        // Параметры
        $query->param(':id',$menu_id);
        
        $result = $query->execute();
        
        return $result[0];
    }
    
    /*
    *
    * Функция возвращает массив меню
    *
    */
    public function get_menus(){
        $menus = array();
        $sql = "SELECT * FROM __menu ORDER BY position";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        
        $result = $query->execute();
        
        foreach($result as $menu)
            $menus[$menu['id']] = $menu;
        return $menus;
    }
    /*
     * Добовляет новый пукт меню
     */
    function add_menu($menu){
        $menu = Str::key_value($menu);
        
        $sql = Str::__('INSERT INTO __menu SET :menu', array(':menu'=>$menu));
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::INSERT, $sql);
        
        $result = $query->execute();
        
        if(empty($result))
            return false;
        
        $id = $result[0];

        $sql = "UPDATE __menu SET position=id WHERE id=:id";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::UPDATE, $sql);
        // Параметры
        $query->param(':id',$id);
        
        $query->execute();
        
        return $id;
    }
    /*
     * Удаляет пукт меню
     */
    function delete_menu($id){
        if(!empty($id)){
            $sql = "DELETE FROM __menu WHERE id=:id LIMIT 1";
            $sql = DB::placehold($sql);
            
            $query = DB::query(Database::DELETE, $sql);
            // Параметры
            $query->param(':id',intval($id));
            
            $result = $query->execute();
            
            if(!empty($result))
                return true;
        }
        return false;
    }
    /*
     * Обновить пукт меню
     */
    function update_menu($id, $menu){
        $menu = Str::key_value($menu);
        
        $sql = Str::__('UPDATE __menu SET :menu WHERE id IN(:id)', array(':menu'=>$menu,':id'=>implode(',',(array)$id)));
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::UPDATE, $sql);
        
        $result = $query->execute();

        if(empty($result))
            return false;
        return $id;
    }
    /*
    *
    * Создание страницы
    *
    */    
    public function add_page($page){
        
        $page = Str::key_value($page);
        
        $sql = Str::__('INSERT INTO __pages SET :page', array(':page'=>$page));
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::INSERT, $sql);
        
        $result = $query->execute();
        
        if(empty($result))
            return false;
        
        $id = $result[0];

        $sql = "UPDATE __pages SET position=id WHERE id=:id";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::UPDATE, $sql);
        // Параметры
        $query->param(':id',$id);
        
        $query->execute();
        
        return $id;
    }
        /*
    *
    * Обновить страницу
    *
    */
    public function update_page($id, $page){
        $page = Str::key_value($page);
        
        $sql = Str::__('UPDATE __pages SET :page WHERE id IN(:id)', array(':page'=>$page,':id'=>implode(',',(array)$id)));
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::UPDATE, $sql);
        
        $result = $query->execute();

        if(empty($result))
            return false;
        return $id;
    }
    /*
    *
    * Удалить страницу
    *
    */    
    public function delete_page($id){
        if(!empty($id)){
            $sql = "DELETE FROM __pages WHERE id=:id LIMIT 1";
            $sql = DB::placehold($sql);
            
            $query = DB::query(Database::DELETE, $sql);
            // Параметры
            $query->param(':id',intval($id));
            
            $result = $query->execute();
            
            if(!empty($result))
                return true;
        }
        return false;
    }
}