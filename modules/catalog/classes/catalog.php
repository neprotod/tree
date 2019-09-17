<?php defined('MODPATH') OR exit();

class Catalog_Module implements I_Module{

    const VERSION = '0.0.1';
    
    function __construct(){
        $this->categories = Module::factory('categories', TRUE);
        $this->products = Module::factory('products', TRUE);
        $this->variants = Module::factory('variants', TRUE);
        $this->brands = Module::factory('brands', TRUE);
        $this->features = Module::factory('features', TRUE);
    }
    
    function index($setting = null){
        
    }
    
    function fetch(){
        // Будемз заливать фильтры
        $filter = array();
        // Что бы мы получали только видимые товары
        $filter['visible'] = 1;
        
        $catefory_brand = explode('/type/',Registry::i()->page_url);
        
        // Убераем от любопытных возможность перейти на 404
        if(strpos($catefory_brand[0], '/type')){
            $catefory = explode('/type',$catefory_brand[0]);
            $refresh =  Core::$root_url . Core::$base_url. Registry::i()->module.'/'.$catefory[0];
            header('HTTP/1.1 301 Moved Permanently');
            header('Location:'.$refresh);
            exit();
        }
        
        $category_url = (isset($catefory_brand[0]))? $catefory_brand[0]: '';
        $brand_url = (isset($catefory_brand[1]))? $catefory_brand[1]: '';
        
        Request::$design->brand_url = $brand_url;
        
        if (!empty($brand_url)){
            $brand = $this->brands->get_brand((string)$brand_url);
            if (empty($brand))
                return false;
            Request::$design->brand = $brand;
            $filter['brand_id'] = $brand['id'];
        }
        
        // Выбираем текущую категорию
        if(!empty($category_url)){
            $category = $this->categories->get_category($category_url);
            if (empty($category) || (!$category->visible))
                    return FALSE;
                    
            // Берем детей категории
            $filter['category_id'] = $category->children;
        }
        $keyword = Request::get('keyword');
        if(!empty($keyword)){
            $filter['keyword'] = $keyword;
        }
        
        // Проверка на найденое содержание
        if(empty($keyword) AND empty($category_url)){
            header('Location: '. Core::$root_url);
            exit();
        } 
        
        // Сортировка товаров, сохраняем в сесси, чтобы текущая сортировка оставалась для всего сайта
        if($sort = Request::get('sort', 'string'))
            $_SESSION['sort'] = $sort;
        if(!empty($_SESSION['sort']))
            $filter['sort'] = $_SESSION['sort'];
        else
            $filter['sort'] = 'name';
        Request::$design->sort = $filter['sort'];
        
        if($order = Request::get('order', 'string'))
            $_SESSION['order'] = $order;
        if(!empty($_SESSION['order']))
            $filter['order'] = $_SESSION['order'];
        else
            $filter['order'] = 'ASC';
        Request::$design->order = $filter['order'];
        
        // Свойства товаров
        /*if(!empty($category)){
            $features = array();
            foreach($this->features->get_features(array('category_id' => $category->id, 'in_filter'=>1)) as $feature){ 
                $features[$feature['id']] = $feature;
                if(($val = strval(Request::get($feature['id'])))!='')
                    $filter['features'][$feature['id']] = $val;    
            }
            
            $options_filter['visible'] = 1;
            
            $features_ids = array_keys($features);
            if(!empty($features_ids))
                $options_filter['feature_id'] = $features_ids;
            $options_filter['category_id'] = $category->children;
            if(isset($filter['features']))
                $options_filter['features'] = $filter['features'];
            if(!empty($brand))
                $options_filter['brand_id'] = $brand['id'];
            
            $options = $this->features->get_options($options_filter);
            
            foreach($options as $option){
                if(isset($features[$option['feature_id']]))
                    $features[$option['feature_id']]['options'][] = $option;
            }
            
            foreach($features as $i=>&$feature){
                if(empty($feature['options']))
                    unset($features[$i]);
            }
            
            Request::$design->features = $features;
        }*/
        
        // Постраничная навигация
        $items_per_page = Registry::i()->settings['products_num'];        
        // Текущая страница в постраничном выводе
        $current_page = Request::get('page', 'int');    
        // Если не задана, то равна 1
        $current_page = max(1, $current_page);
        Request::$design->current_page_num = $current_page;
        // Вычисляем количество страниц
        $products_count = $this->products->count_products($filter);
        $filter['products_count'] = $products_count;

        // Показать все страницы сразу
        if(Request::get('page') == 'all')
            $items_per_page = $products_count;    
        
        // Сколько страниц всего
        $pages_num = ceil($products_count/$items_per_page);
        
        Request::$design->total_pages_num = $pages_num;
        Request::$design->total_products_num = $products_count;
        
        $filter['page'] = $current_page;
        $filter['limit'] = $items_per_page;
        ///////////////////////////////////////////////
        // Постраничная навигация END
        ///////////////////////////////////////////////
        
        // Тут можно сделать дисконт
        
        // Товары
        $products = array();
        foreach($this->products->get_products($filter) as $p)
            $products[$p['id']] = $p;
        
        
        if(!empty($products)){
            $products_ids = array_keys($products);
            
            // Чере ссылку добавляем поля
            foreach($products as &$product){
                $product['variants'] = array();
                $product['images'] = array();
                $product['properties'] = array();
            }
            
            $variants = $this->variants->get_variants(array('product_id'=>$products_ids, 'in_stock'=>true));
            
            foreach($variants as &$variant){
                $products[$variant['product_id']]['variants'][] = $variant;
            }
            
            $images = $this->products->get_images(array('product_id'=>$products_ids));

            foreach($images as $image)
                $products[$image['product_id']]['images'][] = $image;

            foreach($products as &$product){
                if(isset($product['variants'][0])){
                    $product['variant'] = $product['variants'][0];
                }
                if(isset($product->images[0])){
                    $product['image'] = $product['images'][0];
                }
            }
            
            // свойства продуктов
            $properties = $this->features->get_options(array('product_id'=>$products_ids, 'in_filter' => 1));
            foreach($properties as $property)
                $products[$property['product_id']]['options'][] = $property;

            // Заполняем
            Request::$design->products = $products;
            Request::$design->category = $category;
            
        }
        
        if(!empty($category)){
            $brands = $this->brands->get_brands(array('category_id'=>$category->children));
            $category->brands = $brands;        
        }
        
        if(isset($category)){
            if(empty($category->meta_title)){
                Request::$design->meta_title = $category->name;
            }else{
                Request::$design->meta_title = $category->meta_title;
            }
            Request::$design->meta_description = $category->meta_description;
        }
        elseif(isset($brand)){
            Request::$design->meta_title = $brand['meta_title'];
            Request::$design->meta_description = $brand['meta_description'];
        }
        elseif(isset($keyword)){
            Request::$design->meta_title = "Поиск $keyword";
            Request::$design->meta_description = $keyword;
            Request::$design->keyword = $keyword;
            $category = NULL;
        }

        return Template::factory(Registry::i()->settings['theme'],'content_catalog',array('products' => $products, 'category' => $category));
    }
    
}