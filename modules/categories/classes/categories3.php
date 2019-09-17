<?php defined('MODPATH') OR exit();

class Categories_Module implements I_Module{

    const VERSION = '0.0.1';
    
    function __construct(){
        $this->design = Module::factory('design', TRUE);
    }
    
    function index($setting = null){
        
    }
    
    function get_categories($filter,$to_array = FALSE){
        if(!isset(Registry::i()->categories_tree))
            $this->init_categories();
            
        if(!empty($filter['product_id'])){

            $sql = Str::__("SELECT categories_id FROM __products_categories WHERE products_id in(:product_id) ORDER BY position", array(':product_id' => implode(',', (array)$filter['product_id'])));
            $sql = DB::placehold($sql);
        
            $query = DB::query(Database::SELECT, $sql);
            
            $result = $query->execute();

            $categories = $result;
            if(is_array($categories))
                foreach($categories as $cat_id)
                        if(isset(Registry::i()->all_categories[$cat_id['categories_id']]))
                            $category[$cat_id['categories_id']] = Registry::i()->all_categories[$cat_id['categories_id']];
            if($to_array === TRUE)
                return $this->grinding_tree($category);
            return $category;
        }
        
        return Registry::i()->all_categories;
    }
    
    function get_category($id,$to_array = FALSE){
        if(!isset(Registry::i()->all_categories))
            $this->init_categories();
        if(is_int($id) && array_key_exists(intval($id), Registry::i()->all_categories)){
            if($to_array === TRUE){
                $return = $this->grinding_tree($category = Registry::i()->all_categories[intval($id)]);
                return reset($return);
            }
            return $category = Registry::i()->all_categories[intval($id)];
        }elseif(is_string($id)){
            foreach (Registry::i()->all_categories as $category)
                if ($category->url == $id)
                    if($to_array === TRUE){
                        $return = $this->grinding_tree($category);
                        return reset($return);
                    }else{
                        return $category;
                    }
        }
        
        return false;
    }
    
    function init_categories(){
        // Дерево категорий
        $tree = new stdClass();
        $tree->subcategories = array();
        
        // Указатели на узлы дерева
        $pointers = array();
        $pointers[0] = &$tree;
        $pointers[0]->path = array();
        $pointers[0]->level = 0;
        
        $sql = DB::placehold("SELECT c.id, c.parent_id, c.name, c.description, c.url, c.meta_title, c.meta_keywords, c.meta_description, c.image, c.visible, c.position
                                        FROM __categories c ORDER BY c.parent_id, c.position");
                                        
        $query = DB::query(Database::SELECT, $sql);
        
        
        $categories = $query->execute();
        
        $tmp = array();
        
        // Создаем из массива объект
        foreach($categories as $category){
            $tmp[] = (object)$category;
        }
        $categories = $tmp;
        

        $finish = false;
        while(!empty($categories)  && !$finish){
            $flag = false;
            // Проходим все выбранные категории
            foreach($categories as $k=>$category){
                
                if(isset($pointers[$category->parent_id])){
                    // В дерево категорий (через указатель) добавляем текущую категорию
                    $pointers[$category->id] = $pointers[$category->parent_id]->subcategories[] = $category;
                    
                    // Путь к текущей категории
                    //$curr = $pointers[$category->id];
                    //$pointers[$category->id]->path = array_merge((array)$pointers[$category->parent_id]->path, array($curr));
                    
                    // Уровень вложенности категории
                    $pointers[$category->id]->level = 1+$pointers[$category->parent_id]->level;

                    // Убираем использованную категорию из массива категорий
                    unset($categories[$k]);
                    $flag = true;
                }
            }
            if(!$flag) $finish = true;
        }
        
        
        // Для каждой категории id всех ее детей найдем
        $ids = array_reverse(array_keys($pointers));
        foreach($ids as $id){
            if($id>0){
                $pointers[$id]->children[] = $id;
                
                if(isset($pointers[$pointers[$id]->parent_id]->children))
                    $pointers[$pointers[$id]->parent_id]->children = array_merge($pointers[$id]->children, $pointers[$pointers[$id]->parent_id]->children);
                else
                    $pointers[$pointers[$id]->parent_id]->children = $pointers[$id]->children;
                    
                // Добавляем количество товаров к родительской категории, если текущая видима
                 if(isset($pointers[$pointers[$id]->parent_id]) && $pointers[$id]->visible){
                    if(empty($pointers[$pointers[$id]->parent_id]->products_count))
                        $pointers[$pointers[$id]->parent_id]->products_count = '';
                    if(empty($pointers[$id]->products_count))
                        $pointers[$id]->products_count = '';
                    
                    $pointers[$pointers[$id]->parent_id]->products_count += $pointers[$id]->products_count;
                }
            }
        }
        unset($pointers[0]);
        unset($ids);

        //$this->categories_tree = $tree->subcategories;
        unset($pointers[0]);
        
        Registry::i()->categories_tree = $this->grinding_tree($tree->subcategories);
        Registry::i()->all_categories = $pointers;
        
        //print_r($this->categories_array);
        
        
        
    }
    public $i = 0;
    //Для того, что бы сделать массив из обьекта
    private function grinding_tree($objects){
        $grand = array();
        if(!empty($objects)){
            $objects = (array)$objects;
            $res = reset($objects);
            if(!is_array($res) AND !is_object($res)){
                $res = array();
                $res[] = $objects;
                $objects = $res;
            }
            unset($res);
            foreach($objects as $k=>$value){
                if(is_object($objects[$k])){
                    $grand[$k] = (array)$objects[$k];
                }else{
                    $grand[$k] = $objects[$k];
                }
                if(isset($grand[$k]['subcategories'])){
                    $grand[$k]['subcategories'] = $this->grinding_tree($grand[$k]['subcategories']);
                }
            }
        }
        return $grand;
    }
    
    // Функция возвращает дерево категорий
    function get_categories_tree(){
        if(!isset($this->categories_tree))
            $this->init_categories();
            
        return Registry::i()->categories_tree;
    }
    
    /*Работа с продуктами*/
    // Удалить категорию заданного товара
    function delete_category($ids){
        $ids = (array) $ids;
        foreach($ids as $id){
            if($category = $this->get_category(intval($id)))
                $this->delete_image($category->children);
            if(!empty($category->children)){
                // Удаляем категорию
                $sql = Str::__("DELETE FROM __categories WHERE id in(:category)", array(':category' => implode(',', (array)$category->children)));
                $sql = DB::placehold($sql);
        
                $query = DB::query(Database::DELETE, $sql);
                
                $query->execute();
                
                //Удаляем связи категория товар
                $sql = Str::__("DELETE FROM __products_categories WHERE categories_id in(:category)", array(':category' => implode(',', (array)$category->children)));
                $sql = DB::placehold($sql);
        
                $query = DB::query(Database::DELETE, $sql);
                
                $query->execute();
            }
        }
        unset(Registry::i()->categories_tree);            
        unset(Registry::i()->all_categories);    
        return true;
    }
    
    function delete_image($categories_ids){
        $categories_ids = (array)$categories_ids;
        $sql = Str::__("SELECT image FROM __categories WHERE id in(:categories_ids)", array(':categories_ids' => implode(',', (array)$categories_ids)));
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
                
        $result = $query->execute();
        $filenames = $result;
        if(!empty($filenames)){
            $sql = Str::__("UPDATE __categories SET image=NULL WHERE id in(:categories_ids)", array(':categories_ids' => implode(',', (array)$categories_ids)));
            
            $sql = DB::placehold($sql);
            
            $query = DB::query(Database::UPDATE, $sql);

            $query->execute();
            
            foreach($filenames as $filename){
                $sql = "SELECT count(*) as count FROM __categories WHERE image=:image";
            
                $sql = DB::placehold($sql);
                
                $query = DB::query(Database::SELECT, $sql);
                
                $query->param(':image',$filename['image']);
                
                $result = $query->execute();
                
                $count = $result[0]['count'];
                if($count == 0){
                    $dir = Registry::i()->settings['categories_image'];
                    $original = $dir . '/'.$filename['image'];
                    foreach($this->design->get_image_db($original) as $image)
                        $this->design->delete_image($image['resize']);
                    $this->design->delete_image($original);
                }
            }
            unset($this->categories_tree);
            unset($this->all_categories);    
        }
    }
    // Удалить категорию заданного товара
    function delete_product_category($product_id, $category_id){
        $sql = "DELETE FROM __products_categories WHERE products_id=:product_id AND categories_id=:category_id LIMIT 1";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::DELETE, $sql);
        
        //Параметры
        $query->param(':product_id',intval($product_id));
        $query->param(':category_id',intval($category_id));
        
        $query->execute();
    }
    // Функция возвращает id категорий для заданного товара
    function get_product_categories($product_id){
    
        $sql = Str::__("SELECT products_id, categories_id, position FROM __products_categories WHERE products_id in(:product_id) ORDER BY position", array(':product_id' => implode(',', (array)$product_id)));
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        
        return $query->execute();
    }    

    // Функция возвращает id категорий для всех товаров
    function get_products_categories(){
    
        $sql = "SELECT product_id, category_id, position FROM __products_categories ORDER BY position";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        
        return $query->execute();
        
    }
    
    // Добавить категорию к заданному товару
    function add_product_category($product_id, $category_id, $position=0){
        $sql = "INSERT IGNORE INTO __products_categories SET products_id=:product_id, categories_id=:category_id, position=:position";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::INSERT, $sql);
        
        //Параметры
        $query->param(':product_id',intval($product_id));
        $query->param(':category_id',intval($category_id));
        $query->param(':position',intval($position));
        
        $query->execute();
    }
    
    function get_path_image($filter = array()){
        if(empty($filter)){
            return FALSE;
        }
        $other = 'other';
        
        if(!empty($filter['product_id'])){
            $category = $this->get_categories(array('product_id' => $filter['product_id']));
            if(!empty($category)){
                $category = reset($category);
                $category = $category->id;
            }
        }
        
        if(!empty($filter['category_id'])){
            $category = $filter['category_id'];
        }
        if(!empty($category)){
            $sql = "SELECT path FROM __categories_path_image WHERE category_id =:category_id";
            $sql = DB::placehold($sql);
        
            $query = DB::query(Database::SELECT, $sql);
            $query->param(':category_id',intval($category));
            
            $result = $query->execute();

            if($path = $result[0]['path'])
                return $path;
            return $other;
        }else{
            return $other;
        }
    }
    
    /*Добалвение изменение*/
    
    // Добавление категории
    public function add_category($category){
        $category = (array)$category;
        if(empty($category['url'])){
            $category['url'] = Translit::url($category['name']);
        }else{
            $category['url'] = Translit::url($category['url']);
        }

        // Если есть категория с таким URL, добавляем к нему число
        while($this->get_category((string)$category['url'])){
            if(preg_match('/(.+)_([0-9]+)$/', $category['url'], $parts))
                $category['url'] = $parts[1].'_'.($parts[2]+1);
            else
                $category['url'] = $category['url'].'_2';
        }
        // Добовляем
        $category = Str::key_value($category);
        
        $sql = Str::__("INSERT INTO __categories SET :category",array(':category'=>$category));
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::INSERT, $sql);

        $result = $query->execute();
        $id = $result[0];
        
        // Изменяем позицию
        $sql = "UPDATE __categories SET position=id WHERE id=:id";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::UPDATE, $sql);
        // Параметры
        $query->param(':id',intval($id));
        $query->execute();
    
        unset($this->categories_tree);    
        unset($this->all_categories);    
        return $id;
    }
    // Изменение категории
    public function update_category($id, $category){
        $category = Str::key_value($category);
        
        $sql = Str::__("UPDATE __categories SET :category WHERE id=:id LIMIT 1",array(':category'=>$category));
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::UPDATE, $sql);
        // Параметры
        $query->param(':id',intval($id));
        $result = $query->execute();
        unset(Registry::i()->categories_tree);            
        unset(Registry::i()->all_categories);    
        return $id;
    }

}