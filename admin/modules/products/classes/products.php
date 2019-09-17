<?php

class Products_Admin{

    function index(){}
    
    function __construct(){
        $this->categories = Module::factory('categories',TRUE);
        $this->brands = Module::factory('brands',TRUE);
        $this->variants = Module::factory('variants',TRUE);
        $this->products = Module::factory('products',TRUE);
    }
    
    function fetch(){
        $filter = array();
        
        $filter['page'] = max(1, Request::get('page', 'integer'));
        
        $filter['limit'] = Registry::i()->settings['products_num_admin'];
        
        // Категории
        $categories = $this->categories->get_categories_tree();
        Request::$design->categories = $categories;
        
        // Текущая категория
        $category_id = Request::get('category_id', 'integer');
        if($category_id && $category = $this->categories->get_category($category_id))
              $filter['category_id'] = $category->children;
            
        // Бренды категории
        $brands = $this->brands->get_brands(array('category_id'=>$category_id));
        Request::$design->brands = $brands;
        
        // Все бренды
        $all_brands = $this->brands->get_brands();
        Request::$design->all_brands = $all_brands;
        
        // Текущий бренд
        $brand_id = Request::get('brand_id', 'integer');
        if($brand_id && $brand = $this->brands->get_brand($brand_id))
            $filter['brand_id'] = $brand['id'];

        // Текущий фильтр
        if($f = Request::get('filter', 'string')){
            if($f == 'featured')
                $filter['featured'] = 1; 
            elseif($f == 'discounted')
                $filter['discounted'] = 1; 
        }
        
        // Поиск
        $keyword = Request::get('keyword');
        if(!empty($keyword)){
              $filter['keyword'] = $keyword;
            Request::$design->keyword = $keyword;
        }
        
                    
        // Обработка действий     
        if(Request::method('post')){
            // Сохранение цен и наличия
            $prices = Request::post('price');
            $stocks = Request::post('stock');
            foreach($prices as $id=>$price){
                $stock = $stocks[$id];
                if($stock == '∞' || $stock == ''){
                    $stock = null;
                }
                    
                $this->variants->update_variant($id, array('price'=>$price, 'stock'=>$stock));
            }
        
            // Сортировка
            $positions = Request::post('positions');         
                $ids = array_keys($positions);
            sort($positions);
            $positions = array_reverse($positions);
            foreach($positions as $i=>$position)
                $this->products->update_product($ids[$i], array('position'=>$position)); 
        
            
            // Действия с выбранными
            $ids = Request::post('check');
            if(!empty($ids))
            switch(Request::post('action')){
                case 'disable':
                {
                    $this->products->update_product($ids, array('visible'=>0));
                    break;
                }
                case 'enable':
                {
                    $this->products->update_product($ids, array('visible'=>1));
                    break;
                }
                case 'set_featured':
                {
                    $this->products->update_product($ids, array('featured'=>1));
                    break;
                }
                case 'unset_featured':
                {
                    $this->products->update_product($ids, array('featured'=>0));
                    break;
                }
                case 'delete':
                {
                    foreach($ids as $id)
                        $this->products->delete_product($id);    
                    break;
                }
                case 'duplicate':
                {
                    foreach($ids as $id)
                        $this->products->duplicate_product(intval($id));
                    break;
                }
                case 'move_to_page':
                {
        
                    $target_page = Request::post('target_page', 'integer');
                    
                    // Сразу потом откроем эту страницу
                    $filter['page'] = $target_page;
        
                    // До какого товара перемещать
                    $limit = $filter['limit']*($target_page-1);
                    if($target_page >  Request::get('page', 'integer'))
                        $limit += count($ids)-1;
                    else
                        $ids = array_reverse($ids, true);
        

                    $temp_filter = $filter;
                    $temp_filter['page'] = $limit+1;
                    $temp_filter['limit'] = 1;
                    $target_product = $this->products->get_products($temp_filter);
                    $target_product = array_pop($target_product);
                    
                    $target_position = $target_product['position'];
                       
                       // Если вылезли за последний товар - берем позицию последнего товара в качестве цели перемещения
                    if($target_page >  Request::get('page', 'integer') && !$target_position){
                        
                        $sql = "SELECT distinct p.position AS target FROM __products p LEFT JOIN __products_categories AS pc ON pc.product_id = p.id WHERE 1 :category_id_filter :brand_id_filter ORDER BY p.position DESC LIMIT 1";
                        $sql = DB::placehold($sql);
                        
                        $query = DB::query(Database::SELECT, $sql);
                        
                        //Параметры
                        $query->param(':category_id_filter',$category_id_filter);
                        $query->param(':brand_id_filter',$brand_id_filter);
                        
                        $result = $query->execute();
                        
                           $target_position = $result[0]['target'];
                    }
                       
                    foreach($ids as $id){
                        $sql = "SELECT position FROM __products WHERE id=:id LIMIT 1";
                        $sql = DB::placehold($sql);
                        
                        $query = DB::query(Database::SELECT, $sql);
                        
                        //Параметры
                        $query->param(':id',$id);
                        
                        $result = $query->execute();
                            
                        $initial_position = $result[0]['position'];
        
                        if($target_position > $initial_position){
                            $sql = "UPDATE __products set position=position-1 WHERE position>:initial_position AND position<=:target_position";
                            $sql = DB::placehold($sql);
                            
                            $query = DB::query(Database::UPDATE, $sql);
                            
                            //Параметры
                            $query->param(':initial_position',$initial_position);
                            $query->param(':target_position',$target_position);
                            
                        }else{
                            $sql = "UPDATE __products set position=position+1 WHERE position<:initial_position AND position>=:target_position";
                            $sql = DB::placehold($sql);
                            
                            $query = DB::query(Database::UPDATE, $sql);
                            
                            //Параметры
                            $query->param(':initial_position',$initial_position);
                            $query->param(':target_position',$target_position);
                        }    

                        $result = $query->execute();
                        
                        $sql = "UPDATE __products SET __products.position = :target_position WHERE __products.id = :id";
                        $sql = DB::placehold($sql);
                        
                        $query = DB::query(Database::UPDATE, $sql);
                        
                        //Параметры
                        $query->param(':target_position',$target_position);
                        $query->param(':id',$id);
                        
                        $result = $query->execute();    
                    }
                    break;
                }
                case 'move_to_category':
                {
                    
                    $category_id = Request::post('target_category', 'integer');
                    $filter['page'] = 1;
                    
                    $category = $this->categories->get_category($category_id);
                      $filter['category_id'] = $category->children;
                    
                    foreach($ids as $id){
                        $sql = "UPDATE IGNORE __products_categories set categories_id=:category_id WHERE products_id=:product_id ORDER BY position DESC LIMIT 1";
                        $sql = DB::placehold($sql);
                        
                        $query = DB::query(Database::UPDATE, $sql);
                        
                        //Параметры
                        $query->param(':category_id',$category_id);
                        $query->param(':product_id',$id);
                        
                        $result = $query->execute();
                        
                        if($result[0] == 0){
                            $sql = "INSERT IGNORE INTO __products_categories set categories_id=:category_id, products_id=:product_id";
                            $sql = DB::placehold($sql);
                            
                            $query = DB::query(Database::INSERT, $sql);
                            
                            //Параметры
                            $query->param(':category_id',$category_id);
                            $query->param(':product_id',$id);
                            
                            $query->execute();
                        }
                    }
                    break;
                }
                case 'move_to_brand':
                {
                    $brand_id = Request::post('target_brand', 'integer');
                    $brand = $this->brands->get_brand($brand_id);
                    $filter['page'] = 1;
                      $filter['brand_id'] = $brand_id;
                    
                    $sql = Str::__("UPDATE __products set brand_id=:brand_id WHERE id in (:ids)",array(':ids' => implode(',', (array)$ids)));
                    $sql = DB::placehold($sql);
                        
                    $query = DB::query(Database::UPDATE, $sql);
                        
                    //Параметры
                    $query->param(':brand_id',$brand_id);
                        
                    $query->execute();
                    

                    // Заново выберем бренды категории
                    $brands = $this->brands->get_brands(array('category_id'=>$category_id));
                    Request::$design->brands = $brands;
                                              
                    break;
                }
                
            }
            // Сбрасываем форму
            header("Location: /".Url::instance());
        }
        
        // Отображение
        if(isset($brand))
            Request::$design->brand = $brand;
        if(isset($category))
            Request::$design->category = $category;
        
        
        $products_count = $this->products->count_products($filter);
        
        // Показать все страницы сразу
        if(Request::get('page') == 'all')
            $filter['limit'] = $products_count;
            
        if($filter['limit']>0)          
              $pages_count = ceil($products_count/$filter['limit']);
        else
              $pages_count = 0;
        
          $filter['page'] = min($filter['page'], $pages_count);
        
        Request::$design->products_count = $products_count;
        Request::$design->pages_count = $pages_count;
        Request::$design->current_page = $filter['page'];
        
        $products = array();
        foreach($this->products->get_products($filter) as $p)
            $products[$p['id']] = $p;

        if(!empty($products)){
            // Товары 
            $products_ids = array_keys($products);
            foreach($products as &$product)
            {
                $product['variants'] = array();
                $product['images'] = array();
                $product['properties'] = array();
            }
        
            $variants = $this->variants->get_variants(array('product_id'=>$products_ids));
        
         
            foreach($variants as &$variant){
                $products[$variant['product_id']]['variants'][] = $variant;
            }
        
            $images = $this->products->get_images(array('product_id'=>$products_ids));
            foreach($images as $image)
                $products[$image['product_id']]['images'][] = $image;
            
        }
        Request::$design->products = $products;

        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_products',array('products' => $products, 'category' => $category));
    }
    
    
}