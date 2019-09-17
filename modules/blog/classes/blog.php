<?php defined('MODPATH') OR exit();

class Blog_Module implements I_Module{

    const VERSION = '0.0.1';
    
    function __construct(){}
    
    function index($setting = null){}
    
    /*
    *
    * Функция возвращает пост по его id или url
    * (в зависимости от типа аргумента, int - id, string - url)
    * @param $id id или url поста
    *
    */
    public function get_post($id){
        if(is_int($id))
            $where = DB::placehold(' WHERE b.id=:id ', array(':id'=>intval($id)));
        else
            $where = DB::placehold(' WHERE b.url=":url" ', array(':url'=>$id));
        
        $sql = DB::placehold("SELECT b.id, b.url, b.name, b.annotation, b.text, b.meta_title,
                                       b.meta_description, b.visible, b.date
                                       FROM __news b $where LIMIT 1");
        
        $query = DB::query(Database::SELECT, $sql);
            
        if($result = $query->execute()){
            return reset($result);
        }else{
            return FALSE;
        }
    }
    
    /*
    *
    * Функция возвращает массив постов, удовлетворяющих фильтру
    * @param $filter
    *
    */
    public function get_posts($filter = array()){    
        // По умолчанию
        $limit = 1000;
        $page = 1;
        $post_id_filter = '';
        $visible_filter = '';
        $posts = array();
        
        if(isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if(isset($filter['page']))
            $page = max(1, intval($filter['page']));

        if(!empty($filter['id']))
            $post_id_filter = DB::placehold('AND b.id in(:id)', array(':id'=>implode(',',(array)$filter['id'])));
            
        if(isset($filter['visible']))
            $visible_filter = DB::placehold('AND b.visible = :visible', array('visible'=>intval($filter['visible'])));        
        

        $sql_limit = DB::placehold(' LIMIT :limit1, :limit2 ', array(":limit1"=>($page-1)*$limit,":limit2"=>$limit));

        $sql = DB::placehold("SELECT b.id, b.url, b.name, b.annotation, b.text,
                                              b.meta_title, b.meta_description, b.visible,
                                              b.date
                                              FROM __news b WHERE 1 $post_id_filter $visible_filter
                                              ORDER BY date DESC, id DESC $sql_limit");
        
        $query = DB::query(Database::SELECT, $sql);
        
        $result = $query->execute();
        
        return $result;
    }
    
    /*
    *
    * Функция вычисляет количество постов, удовлетворяющих фильтру
    * @param $filter
    *
    */
    public function count_posts($filter = array()){    
        $post_id_filter = '';
        $visible_filter = '';
        
        if(!empty($filter['id']))
            $post_id_filter = DB::placehold('AND b.id in(:id)', array(':id'=>implode(',',(array)$filter['id'])));
            
        if(isset($filter['visible']))
            $visible_filter = DB::placehold('AND b.visible = ":visible"', array('visible'=>intval($filter['visible'])));
        
        $sql = DB::placehold("SELECT COUNT(distinct b.id) as count
                  FROM __news b WHERE 1 $post_id_filter $visible_filter");
        
        $query = DB::query(Database::SELECT, $sql);
        
        if($result = $query->execute()){
            return $result[0]['count'];
        }else{
            return FALSE;
        }
    }
    
    /*
    *
    * Создание поста
    * @param $post
    *
    */    
    public function add_post($post){    
        if(!isset($post['date']))
            $date_query = ', date=NOW()';
        else
            $date_query = '';
        $sql = DB::placehold("INSERT INTO __news SET :blog $date_query", array(':blog'=>Str::key_value($post)));
        
        $query = DB::query(Database::INSERT, $sql);
        
        if($result = $query->execute()){
            return $result[0];
        }else{
            return FALSE;
        }
    }
    
    /*
    *
    * Обновить пост(ы)
    * @param $post
    *
    */    
    public function update_post($id, $post){
        $sql = DB::placehold("UPDATE __news SET :blog WHERE id in(:id) LIMIT :limit", array(':blog'=>Str::key_value($post),':id'=>implode(',',(array)$id),':limit'=>count((array)$id)));
        
        $query = DB::query(Database::UPDATE, $sql);
        
        $query->execute();
        return $id;
    }
    
    /*
    *
    * Удалить пост
    * @param $id
    *
    */    
    public function delete_post($id){
        if(!empty($id))
        {
            $sql = DB::placehold("DELETE FROM __news WHERE id=:id LIMIT 1", array(':id'=>intval($id)));
            $query = DB::query(Database::DELETE, $sql);
            /*if($query->execute()){
                $sql = DB::placehold("DELETE FROM __comments WHERE type='blog' AND object_id=:object_id", array(':object_id'=>intval($id)));
                
                $query = DB::query(Database::DELETE, $sql);
                
                if($query->execute())
                    return TRUE;
            }*/
            $query->execute();
            return TRUE;
        }
        return FALSE;
    }
    
    
    /*
    *
    * Следующий пост
    * @param $post
    *
    */    
    public function get_next_post($id){
        $sql = DB::placehold("SELECT date FROM __news WHERE id=:id LIMIT 1", array(':id'=>$id));
        
        $query = DB::query(Database::SELECT, $sql);
        
        $result = $query->execute();
        $date = $result[0];

        $sql = DB::placehold("(SELECT id FROM __news WHERE date=:date AND id>:id AND visible  ORDER BY id limit 1)
                           UNION
                          (SELECT id FROM __news WHERE date>:date AND visible ORDER BY date, id limit 1)",
                          array(':date'=>$date,':id'=>$id));
        
        $query = DB::query(Database::SELECT, $sql);
        
        $result = $query->execute();
        
        $next_id = $result[0];
        if($next_id)
            return $this->get_post(intval($next_id));
        else
            return false; 
    }
    
    /*
    *
    * Предыдущий пост
    * @param $post
    *
    */    
    public function get_prev_post($id){
        $sql = DB::placehold("SELECT date FROM __news WHERE id=:id LIMIT 1", array(':id'=>$id));
        
        $query = DB::query(Database::SELECT, $sql);
        
        $result = $query->execute();
        $date = $result[0];

        $sql = DB::placehold("(SELECT id FROM __news WHERE date=:date AND id<:id AND visible  ORDER BY id limit 1)
                           UNION
                          (SELECT id FROM __news WHERE date<:date AND visible ORDER BY date, id limit 1)",
                          array(':date'=>$date,':id'=>$id));
        
        $query = DB::query(Database::SELECT, $sql);
        
        $result = $query->execute();
        
        $prev_id = $result[0];
        if($prev_id)
            return $this->get_post(intval($prev_id));
        else
            return false; 
    }
}