<?php defined('MODPATH') OR exit();

class Products_Module implements I_Module{

    const VERSION = '0.0.1';
    
    function __construct(){
        $this->variants = Module::factory('variants', TRUE);
        $this->categories = Module::factory('categories', TRUE);
        $this->features = Module::factory('features', TRUE);
        $this->design = Module::factory('design', TRUE);
    }
    
    function index($setting = null){
        
    }
    
    function fetch(){
        $product_url = Registry::i()->page_url;
        if(empty($product_url))
            return false;
        
        // Выбираем товар из базы
        $product = $this->get_product((string)$product_url);
        if(empty($product)){
            return false;
        }
        elseif(!$product['visible']){
            return Template::factory(Registry::i()->settings['theme'],'content_empty',array('massage' => $product));
        }
            
        $product['images'] = $this->get_images(array('product_id'=>$product['id']));
        
        //$product['images'] = reset($product['images']);
        
        $variants = array();
        foreach($this->variants->get_variants(array('product_id'=>$product['id'], 'in_stock'=>true)) as $v)
            $variants[$v['id']] = $v;
            
        $product['variants'] = $variants;
        
        // Вариант по умолчанию
        if(($v_id = Request::get('variant', 'integer'))>0 && isset($variants[$v_id]))
            $product['variants'] = $variants[$v_id];
        else
            $product['variants'] = reset($variants);
        
        $product['features'] = $this->features->get_product_options(array('product_id'=>$product['id']));        
         
        $product['categories'] = $this->categories->get_categories(array('product_id' => $product['id']));
         
        Request::$design->product = $product;
        
        Request::$design->category = $product['categories'];
        
        Request::$design->meta_title = empty($product['meta_title'])? $product['name'] : $product['meta_title'];
        
        Request::$design->meta_description = $product['meta_description'];
        
        return Template::factory(Registry::i()->settings['theme'],'content_product',array('product' => $product));
    }
    
    
    function get_product($id){
        if(is_int($id)){
            $filter = 'p.id = :id';
        }else{
            $filter = "p.url = :id";
        }

        $sql = "SELECT DISTINCT
                    p.id,
                    p.url,
                    p.brand_id,
                    p.name,
                    p.annotation,
                    p.body,
                    p.position,
                    p.created as created,
                    p.visible, 
                    p.featured, 
                    p.meta_title,
                    p.meta_description
                FROM __products AS p
                LEFT JOIN __brands b ON p.brand_id = b.id
                WHERE $filter
                GROUP BY p.id
                LIMIT 1";
        
        $sql = DB::placehold($sql);

        $query = DB::query(Database::SELECT, $sql);
        $query->param(':id', $id);
        $result = $query->execute();

        return $result[0];
    }
    
    function count_products($filter){
        $category_id_filter = '';
        $brand_id_filter = '';
        $product_id_filter = '';
        $keyword_filter = '';
        $visible_filter = '';
        $is_featured_filter = '';
        $in_stock_filter = '';
        $discounted_filter = '';
        $features_filter = '';
        
        
        if(!empty($filter['category_id']))
            $category_id_filter = Str::__("INNER JOIN __products_categories pc ON pc.products_id = p.id AND pc.categories_id IN(:category_id)", array(':category_id' => implode(',', (array)$filter['category_id'])));
        
        if(!empty($filter['brand_id']))
            $brand_id_filter = Str::__("AND p.brand_id in(:brand_id)", array(':brand_id' => implode(',', (array)$filter['brand_id'])));
        
        if(!empty($filter['id']))
            $product_id_filter = Str::__("AND p.id in(:id)", array(':id' => implode(',', (array)$filter['id'])));
    
        if(isset($filter['keyword'])){
        
            if(Utf8::strlen($filter['keyword']) >= Registry::i()->settings['ft_min_world_len']){
                $keywords = explode(' ', $filter['keyword']);
                $keywords = '*'.implode('*',$keywords).'*';
                $keyword_filter = "AND MATCH (p.name) 
                AGAINST (".Db::escape($keywords)." IN BOOLEAN MODE)";
            }else{
                $keywords = explode(' ', $filter['keyword']);
                $cononical = $filter['keyword'];
                $keyword_filter = 'AND (p.name LIKE '.Db::escape('%'.$cononical.'%').')';
                foreach($keywords as $keyword)
                    $keyword_filter .= 'AND (p.name LIKE '.Db::escape('%'.$keyword.'%').')';
                
                $keyword_filter = DB::placehold($keyword_filter);
            }
            
        }
    
        if(!empty($filter['featured']))
            $is_featured_filter = Str::__("AND p.featured=:featured", array(':featured' => intval($filter['featured'])));
        
        if(!empty($filter['in_stock']))
            $in_stock_filter = Str::__("AND (SELECT 1 FROM __variants pv WHERE pv.product_id=p.id AND pv.price>0 AND (pv.stock IS NULL OR pv.stock>0) LIMIT 1) = :in_stock", array(':in_stock' => intval($filter['in_stock'])));
    
        if(!empty($filter['discounted']))
            $discounted_filter = Str::__("AND (SELECT 1 FROM __variants pv WHERE pv.product_id=p.id AND pv.compare_price>0 LIMIT 1) = :discounted", array(':discounted' => intval($filter['discounted'])));
        
        
        if(!empty($filter['visible']))
            $visible_filter = Str::__("AND p.visible=:visible", array(':visible' => intval($filter['visible'])));
        
        if(!empty($filter['features']) && !empty($filter['features']))
            foreach($filter['features'] as $feature=>$value)
                $features_filter .= Str::__("AND p.id in (SELECT product_id FROM __options WHERE feature_id=:feature AND value=:value )", array(':feature' => $feature, ':value' => $value));
                
        $sql = "SELECT count(distinct p.id) as count
                FROM __products AS p
                $category_id_filter
                WHERE 1
                    $brand_id_filter
                    $product_id_filter
                    $is_featured_filter
                    $in_stock_filter
                    $keyword_filter
                    $discounted_filter
                    $visible_filter
                    $features_filter ";
        
        $sql = DB::placehold($sql);
        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute();
        return $result[0]['count'];
    }
    
    function get_products($filter){
        // по умолчанию
        $limit = 100;
        $page = 1;
        $category_id_filter = '';
        $brand_id_filter = '';
        $product_id_filter = '';
        $features_filter = '';
        $visible_filter = '';
        $keyword_filter = '';
        $is_featured_filter = '';
        $discounted_filter = '';
        $in_stock_filter = '';
        $group_by = '';
        $desc = 'DESC';
        $order = "p.position $desc";
        $rel = '';
        
        if(isset($filter['order'])){
            switch(strtoupper($filter['order'])){
                case 'DESC':
                    $desc = $filter['order'];
                break;
                case 'ASC':
                    $desc = $filter['order'] ;
                break;
                default:$desc = 'DESC';
            }
        }
        if(isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));
            
        if(isset($filter['page']))
            $page = max(1, intval($filter['page']));
    
        // Ковертируем строку в лимит
        $sql_limit = Str::__('LIMIT :page, :limit', array(':page'=> ($page-1)*$limit, ':limit' => $limit) );    
        
        if(isset($filter['keyword'])){

            if(Utf8::strlen($filter['keyword']) >= Registry::i()->settings['ft_min_world_len']){
                $filter['sort'] = 'relevant';
                $keywords = explode(' ', $filter['keyword']);
                $keywords = '*'.implode('*',$keywords).'*';
                $rel = "MATCH (p.name) 
                AGAINST (".Db::escape($filter['keyword'])." IN BOOLEAN MODE) AS rel,";
                $keyword_filter = "AND MATCH (p.name) 
                AGAINST (".Db::escape($keywords)." IN BOOLEAN MODE)";
            }else{
                $filter['sort'] = '';
                $order = "''";
                $keywords = explode(' ', $filter['keyword']);
                $cononical = $filter['keyword'];
                $keyword_filter = 'AND (p.name LIKE '.Db::escape('%'.$cononical.'%').')';
                foreach($keywords as $keyword)
                    $keyword_filter .= 'AND (p.name LIKE '.Db::escape('%'.$keyword.'%').')';
                
                $keyword_filter = DB::placehold($keyword_filter);
            }
        }
        
        if(!empty($filter['id']))
            $product_id_filter = Str::__('AND p.id IN(:id)', array(':id' => implode(',', (array)$filter['id'])));
        
            
        if(!empty($filter['category_id'])){
            $category_id_filter = Str::__("INNER JOIN __products_categories pc ON pc.products_id = p.id AND pc.categories_id IN(:category_id)", array(':category_id' => implode(',', (array)$filter['category_id'])));
            $group_by = "GROUP BY p.id";
        }
        if(!empty($filter['brand_id'])){
            $brand_id_filter = Str::__('AND p.brand_id IN(:id)', array(':id' => $filter['brand_id']));
        }
        
        if(!empty($filter['featured']))
            $is_featured_filter = Str::__('AND p.featured=:features', array(':featured' => $filter['featured']));
        
        
        if(!empty($filter['discounted']))
            $discounted_filter = Str::__('AND (SELECT 1 FROM __variants pv WHERE pv.product_id=p.id AND pv.compare_price>0 LIMIT 1) = :discounted', array(':discounted' => $filter['discounted']));
        
        
        if(!empty($filter['in_stock']))
            $in_stock_filter = Str::__('AND p.visible=:visible', array(':visible' => $filter['visible']));
        
        
        if(!empty($filter['visible']))
            $visible_filter = Str::__('AND p.visible=:visible', array(':visible'=>intval($filter['visible'])));
        
        if(!empty($filter['sort'])){
            switch (strtolower($filter['sort'])){
                case 'position':
                $order = "p.position $desc";
                break;
                case 'name':
                $order = "p.name $desc";
                break;
                case 'created':
                $order = "p.created $desc";
                break;
                case 'relevant':
                $order = "rel DESC";
                break;
                case 'price':
                    $order = "(SELECT -pv.price FROM __variants pv WHERE (pv.stock IS NULL OR pv.stock>0) AND p.id = pv.product_id AND pv.position=(SELECT MIN(position) FROM __variants WHERE (stock>0 OR stock IS NULL) AND product_id=p.id LIMIT 1) LIMIT 1) $desc";
                break;
            }
        }
        
        if(!empty($filter['features']) && !empty($filter['features']))
            foreach($filter['features'] as $feature=>$value)
                $features_filter .= Str::__('AND p.id in (SELECT product_id FROM __options WHERE feature_id=:feature_id AND value=:value)', array(':feature_id' => $feature, ':visible' => $value));
        
        
            $sql = "SELECT  
                    p.id,
                    p.url,
                    p.brand_id,
                    p.name,
                    p.annotation,
                    p.body,
                    $rel
                    p.position,
                    p.created as created,
                    p.visible, 
                    p.featured, 
                    p.meta_title, 
                    p.meta_description, 
                    b.name as brand,
                    b.url as brand_url
                FROM __products p        
                $category_id_filter 
                LEFT JOIN __brands b ON p.brand_id = b.id
                WHERE 
                    1
                    $product_id_filter
                    $brand_id_filter
                    $features_filter
                    $keyword_filter
                    $is_featured_filter
                    $discounted_filter
                    $in_stock_filter
                    $visible_filter
                $group_by
                ORDER BY $order
                    $sql_limit";
                    
        $sql = DB::placehold($sql);

        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute();
        return $result;
    }
    
    function get_images($filter = array()){
        $product_id_filter = '';
        $group_by = '';

        if(!empty($filter['product_id']))
            $product_id_filter = Str::__("AND i.product_id in(:product_id)", array(':product_id' => implode(',', (array)$filter['product_id'])));

        // images
        $sql = DB::placehold("SELECT i.id, i.product_id, i.name, i.filename, i.position
                                    FROM __images AS i WHERE 1 $product_id_filter $group_by ORDER BY i.product_id, i.position");
        
        
        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute();

        return $result;
    }
    
    function add_image($product_id, $filename, $name = ''){
        $sql = "SELECT id FROM __images WHERE product_id=:product_id AND filename=:filename";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        //Параметры
        $query->param(':product_id',intval($product_id));
        $query->param(':filename',$filename);
        
        $result = $query->execute();
        
        $id = $result[0]['id'];

        if(empty($id)){
            $sql = "INSERT INTO __images SET product_id=:product_id, filename=:filename";
            $sql = DB::placehold($sql);
            
            $query = DB::query(Database::INSERT, $sql);
            //Параметры
            $query->param(':product_id',intval($product_id));
            $query->param(':filename',$filename);
            
            $result = $query->execute();
            
            $id = $result[0];
            // изменяем позицию
            $sql = "UPDATE __images SET position=id WHERE id=:id";
            $sql = DB::placehold($sql);
            
            $query = DB::query(Database::UPDATE, $sql);
            //Параметры
            $query->param(':id',intval($id));
            
            $query->execute();
        }
        return($id);
    }
    
    public function update_image($id, $image){
        //?%
        $image = Str::key_value($image);
        
        $sql = Str::__("UPDATE __images SET :image WHERE id=:id",array(':image'=>$image));
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::UPDATE, $sql);
        //Параметры
        $query->param(':id',intval($id));
        
        $query->execute();
        
        return($id);
    }
    
    function delete_image($id){
        // Получаем путь
        $sql = "SELECT filename FROM __images WHERE id=:id";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
            //Параметры
        $query->param(':id',intval($id));
        
        $result = $query->execute();
        
        $filename = $result[0]['filename'];
        
        // Удаляем картинку с базы
        $sql = "DELETE FROM __images WHERE id=:id LIMIT 1";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::DELETE, $sql);
            //Параметры
        $query->param(':id',intval($id));
        
        $query->execute();
        
        // Проверяем
        $sql = "SELECT count(*) as count FROM __images WHERE filename=:filename LIMIT 1";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
            //Параметры
        $query->param(':filename',$filename);
        
        $result = $query->execute();
        
        $count = $result[0]['count'];
        if($count == 0){
            $dir = Registry::i()->settings['original'];
            $original = $dir . '/'.$filename;
            if(is_file($original)){
                foreach($this->design->get_image_db($original) as $image)
                    $this->design->delete_image($image['resize']);
                $this->design->delete_image($original);
            }
        }
    }
    ////////////////
    // Добавление - изменение
    ///////////////
    function update_product($id, $product){
        if(isset($product['url'])){
            $product['url'] = Translit::url($product['url']);
        }
        $product = Str::key_value($product);
        
        $sql = Str::__("UPDATE __products SET :product WHERE id in (:id) LIMIT :count",array(':product' =>$product, ':id' => implode(',', (array)$id),':count' => count($id)));
        
        $sql = DB::placehold($sql);
            
        $query = DB::query(Database::UPDATE, $sql);
        
        if($query->execute())
            return $id;
        else
            return false;
    }
    
    function add_product($product){
        $product = (array) $product;
        
        if(empty($product['url'])){
            $product['url'] = Translit::url($product['name']);
        }else{
            $product['url'] = Translit::url($product['url']);
        }

        // Если есть товар с таким URL, добавляем к нему число

        if($this->get_product((string)$product['url'])){

            if(preg_match('/(.+)_([0-9]+)$/', $product['url'], $parts))
                $product['url'] = $parts[1].'_'.($parts[2]+1);
            else
                $product['url'] = $product['url'].'_2';
        }
    
        $product = Str::key_value($product);

        $sql = Str::__("INSERT INTO __products SET :product", array(':product' => implode(',', (array)$product)));

        
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::INSERT, $sql);
        
        $result = $query->execute();
        
        return $result[0];
        
        if(!empty($result[0])){
            return $result[0];
        }else
            return false;
    }
    
    /*
    *
    * Удалить товар
    *
    */    
    public function delete_product($id){
        if(!empty($id)){
            // Удаляем варианты
            $variants = $this->variants->get_variants(array('product_id'=>$id));
            foreach($variants as $v)
                $this->variants->delete_variant($v['id']);
            
            // Удаляем изображения
            $images = $this->get_images(array('product_id'=>$id));
            foreach($images as $i)
                $this->delete_image($i['id']);
            
            // Удаляем категории
            $categories = $this->categories->get_categories(array('product_id'=>$id));
            if(is_array($categories) OR is_object($categories))
                foreach($categories as $c)
                    $this->categories->delete_product_category($id, $c->id);

            // Удаляем свойства
            $options = $this->features->get_options(array('product_id'=>$id));
            if(is_array($options))
                foreach($options as $o)
                    $this->features->delete_option($id, $o['feature_id']);
            
            /*// Удаляем связанные товары
            $related = $this->get_related_products($id);
            foreach($related as $r)
                $this->delete_related_product($id, $r->related_id);*/
            
            /*// Удаляем отзывы
            $comments = $this->comments->get_comments(array('object_id'=>$id, 'type'=>'product'));
            foreach($comments as $c)
                $this->comments->delete_comment($c->id);*/
            
            // Удаляем из покупок
            $sql = "UPDATE __purchases SET product_id=NULL WHERE product_id=:product_id";
            $sql = DB::placehold($sql);
            
            $query = DB::query(Database::UPDATE, $sql);
                // Параметры
            $query->param(':product_id',intval($id));
            
            $query->execute();
            
            // Удаляем товар
            $sql = "DELETE FROM __products WHERE id=:id LIMIT 1";
            $sql = DB::placehold($sql);
            
            $query = DB::query(Database::DELETE, $sql);
                // Параметры
            $query->param(':id',intval($id));
            
            if($query->execute())
                return true;            
        }
        return false;
    }    
    
    public function duplicate_product($id){
        $product = $this->get_product($id);
        $product['id'] = null;
        $product['name'] .= ' [ДУБЛИКАТ]';
        $product['created'] = null;

        // Сдвигаем товары вперед и вставляем копию на соседнюю позицию
        $sql = 'UPDATE __products SET position=position+1 WHERE position>:position';
        $sql = DB::placehold($sql);
        $query = DB::query(Database::UPDATE, $sql);
        
        $query->param(':position',$product['position']);
        
        $query->execute();
        $new_id = $this->add_product($product);
        
        $sql = "UPDATE __products SET position=:position WHERE id=:id";
        $sql = DB::placehold($sql);
            
        $query = DB::query(Database::UPDATE, $sql);
            // Параметры
        $query->param(':position',$product['position']+1);
        $query->param(':id',$new_id);
            
        $query->execute();
        
        // Очищаем url
        $sql = "UPDATE __products SET url='' WHERE id=:id";
        $sql = DB::placehold($sql);
            
        $query = DB::query(Database::UPDATE, $sql);
            // Параметры
        $query->param(':id',$new_id);
            
        $query->execute();
        
        // Дублируем категории
        $categories = $this->categories->get_product_categories($id);
        foreach($categories as $c)
            $this->categories->add_product_category($new_id, $c['categories_id']);
        
        // Дублируем изображения
        $images = $this->get_images(array('product_id'=>$id));
        foreach($images as $image)
            $this->add_image($new_id, $image['filename']);
            
        // Дублируем варианты
        $variants = $this->variants->get_variants(array('product_id'=>$id));
        foreach($variants as $variant)
        {
            $variant['product_id'] = $new_id;
            unset($variant['id']);
            if($variant['infinity'])
                $variant['stock'] = null;
            unset($variant['infinity']);
            $this->variants->add_variant($variant);
        }
        
        // Дублируем свойства
        $options = $this->features->get_options(array('product_id'=>$id));
        foreach($options as $o)
            $this->features->update_option($new_id, $o['feature_id'], $o['value']);
            
        // Дублируем связанные товары
        /*$related = $this->get_related_products($id);
        foreach($related as $r)
            $this->add_related_product($new_id, $r->related_id);*/
            
            
        return $new_id;
    }
    
}