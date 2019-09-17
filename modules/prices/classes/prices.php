<?php defined('MODPATH') OR exit();

class Prices_Module implements I_Module{

    const VERSION = '0.0.1';
    
    public $folder = "/media/other/prices";
    
    function __construct(){
        
    }
    
    function index($setting = null){}
    
    function get_prices(){
        //Запрос на получение прайсов.
        $sql = "SELECT id, name, price, img, visible, position, no_name
                    FROM __price
                    ORDER BY position DESC";
                  
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        
        return $query->execute();
    }
    /*
    *
    * Обновить прайс(ы)
    * @param $post
    *
    */    
    public function update_price($id, $price){
        $sql = DB::placehold("UPDATE __price SET :price WHERE id in(:id) LIMIT :limit", array(':price'=>Str::key_value($price),':id'=>implode(',',(array)$id),':limit'=>count((array)$id)));

        $query = DB::query(Database::UPDATE, $sql);
        
        $query->execute();
        return $id;
    }
    /*
    *
    * Удалить прайс
    * @param $id
    *
    */    
    public function delete_price($id){
        if(!empty($id)){
            $sql = DB::placehold("DELETE FROM __price WHERE id=:id LIMIT 1", array(':id'=>intval($id)));
            $query = DB::query(Database::DELETE, $sql);

            $query->execute();
            return TRUE;
        }
        return FALSE;
    }
    ///////////////////////////
    public function get_price($id){
        if(is_int($id))
            $where = DB::placehold(' WHERE id=:id ', array(':id'=>intval($id)));
        else
            $where = DB::placehold(' WHERE name=":id" ', array(':id'=>$id));
        
        $sql = DB::placehold("SELECT id, name, price, img, visible, position
                                       FROM __price $where LIMIT 1");

        $query = DB::query(Database::SELECT, $sql);
            
        if($result = $query->execute()){
            return reset($result);
        }else{
            return FALSE;
        }
    }
    
        /*
    *
    * Добавление прайс
    * @param $price
    *
    */
    public function add_price($price){
        $price = (array)$price;
        
        $prices = Str::key_value($price);
        
        $sql = Str::__("INSERT INTO __price SET :prices",array(':prices'=>$prices));
        $sql = DB::placehold($sql);
            
        $query = DB::query(Database::INSERT, $sql);
        //$query->param(':prices',$prices);
        
        $result = $query->execute();
        
        return $result[0];
    }
    
    /*
    *
    * Удаление изображения прайса
    * @param $id
    *
    */
    public function delete_image($price_id){

        $sql = DB::placehold("SELECT img FROM __price WHERE id=:id");
        
        $query = DB::query(Database::SELECT, $sql);
        $query->param(':id',intval($price_id));
            
        $result = $query->execute();
        if(!empty($result))
            $filename = reset($result[0]);

        if(!empty($filename)){
            $sql = DB::placehold("UPDATE __price SET img=NULL WHERE id=:id");
        
            $query = DB::query(Database::UPDATE, $sql);
            $query->param(':id',intval($price_id));
            
            $query->execute();
            
            // Проверяем, есть ли такой файл
            $sql = DB::placehold("SELECT count(*) as count FROM __price WHERE img=:img LIMIT 1");
        
            $query = DB::query(Database::SELECT, $sql);
            $query->param(':img',$filename);
            
            $result = $query->execute();
            $count = reset($result[0]);

            if($count == 0){
                @unlink(trim($this->folder,'/').'/'.$filename);        
            }
        }
    }
    /*
    *
    * Удаление изображения прайса
    * @param $id
    *
    */
    public function drop_file($price_id){

        $sql = DB::placehold("SELECT price FROM __price WHERE id=:id");
        
        $query = DB::query(Database::SELECT, $sql);
        $query->param(':id',intval($price_id));
            
        $result = $query->execute();
        if(!empty($result))
            $filename = reset($result[0]);

        if(!empty($filename)){
            $sql = DB::placehold("UPDATE __price SET price=NULL WHERE id=:id");
        
            $query = DB::query(Database::UPDATE, $sql);
            $query->param(':id',intval($price_id));
            
            $query->execute();
            
            // Проверяем, есть ли такой файл
            $sql = DB::placehold("SELECT count(*) as count FROM __price WHERE price=:price LIMIT 1");
        
            $query = DB::query(Database::SELECT, $sql);
            $query->param(':price',$filename);
            
            $result = $query->execute();
            $count = reset($result[0]);

            if($count == 0){
                @unlink(trim($this->folder,'/').'/'.$filename);        
            }
        }
    }
}