<?php

class Product_Admin{

    function index(){}
    
    function __construct(){
        $this->categories = Module::factory('categories',TRUE);
        $this->brands = Module::factory('brands',TRUE);
        $this->variants = Module::factory('variants',TRUE);
        $this->products = Module::factory('products',TRUE);
        $this->features = Module::factory('features',TRUE);
        $this->image = Module::factory('image',TRUE);
    }
    
    function fetch(){
        $options = array();
        $product_categories = array();
        $variants = array();
        $images = array();
        $product_features = array();
        $related_products = array();
        
        if(Request::method('post') && !empty($_POST)){
        
            $product['id'] = Request::post('id', 'integer');
            $product['name'] = Request::post('name');
            $product['visible'] = Request::post('visible', 'boolean');
            $product['featured'] = Request::post('featured');
            $product['brand_id'] = Request::post('brand_id', 'integer');

            $product['url'] = Request::post('url', 'string');
            $product['meta_title'] =Request::post('meta_title');
            $product['meta_description'] = Request::post('meta_description');
            
            $product['annotation'] = Request::post('annotation');
            $product['body'] = Request::post('body');


            // Варианты товара
            if(Request::post('variants'))
                foreach(Request::post('variants') as $n => $va){
                    foreach($va as $i => $v){
                        if(empty($variants[$i]))
                            $variants[$i] = array();
                        $variants[$i][$n] = $v;
                    }
                }
            
            // Категории товара
            $product_categories = Request::post('categories');
            if(is_array($product_categories)){
                foreach($product_categories as $c){
                    $x = array();
                    $x['id'] = $c;
                    $pc[] = $x;
                }
                $product_categories = $pc;
            }
            
            // Свойства товара
               $options = Request::post('options');

            if(is_array($options)){
                foreach($options as $f_id=>$val){
                    $po[$f_id] = array();
                    $po[$f_id]['feature_id'] = $f_id;
                    $po[$f_id]['value'] = $val;
                }
                $options = $po;
            }
            // Не допустить пустое название товара.
            if(empty($product['name'])){
                Request::$design->error('error_name','Название товара пустое');
                if(!empty($product['id']))
                    $images = $this->products->get_images(array('product_id'=>$product['id']));
            }
            // Не допустить одинаковые URL разделов.
            elseif(($p = $this->products->get_product((string)$product['url'])) && $p['id'] != $product['id']){
                Request::$design->error('error_url','Такой URL уже есть');
                if(!empty($product['id']))
                    $images = $this->products->get_images(array('product_id'=>$product['id']));
            }
            //Если все хорошо
            else{
                if(empty($product['id'])){
                      $product['id'] = $this->products->add_product($product);
                      $product = $this->products->get_product(intval($product['id']));
                    Request::$design->massage('message_success','Товар добавлен');
                  }else{
                      $this->products->update_product($product['id'], $product);
                      $product = $this->products->get_product(intval($product['id']));
                    Request::$design->massage('message_success','Товар обнавлен');
                  }    
                   
                   if($product['id']){
                    
                       // Категории товара
                       $sql = "DELETE FROM __products_categories WHERE products_id=:product_id";
                    $sql = DB::placehold($sql);
                    $query = DB::query(Database::DELETE, $sql);
                        //Параметры
                    $query->param(':product_id',intval($product['id']));
                    
                    $query->execute();
                       if(is_array($product_categories)){
                    
                    
                          foreach($product_categories as $i => $category)
                               $this->categories->add_product_category($product['id'], $category['id'], $i);
                      }
    
                       // Варианты
                      if(is_array($variants)){
                         $variants_ids = array();
                        foreach($variants as $index => &$variant){
                            if($variant['stock'] == '∞' || $variant['stock'] == '')
                                $variant['stock'] = null;
                                

                            /*Проверка на удаление*/
                            if(!empty($variant['id'])){
                                $this->variants->update_variant($variant['id'], $variant);
                            }else{
                                $variant['product_id'] = $product['id'];
                                $variant['id'] = $this->variants->add_variant($variant);
                            }
                            $variant = $this->variants->get_variant($variant['id']);
                            if(!empty($variant['id']))
                                 $variants_ids[] = $variant['id'];
                        }
                        
    
                        // Удалить непереданные варианты
                        $current_variants = $this->variants->get_variants(array('product_id'=>$product['id']));
                        foreach($current_variants as $current_variant)
                            if(!in_array($current_variant['id'], $variants_ids))
                                 $this->variants->delete_variant($current_variant['id']);
                                                      
                        
                        // Отсортировать  варианты
                        asort($variants_ids);
                        $i = 0;
                        foreach($variants_ids as $variant_id){
                            $this->variants->update_variant($variants_ids[$i], array('position'=>$variant_id));
                            $i++;
                        }
                    }
                    
                    // Берем путь к изображениям товара

                    Registry::i()->category_image = $this->categories->get_path_image(array('product_id' =>$product['id']));
                    
                    // Берем изображения с формы
                    $images = (array)Request::post('images');
                    
                    // Если существуют изображения загруженные с сервера
                    $new_images = (array)Request::post('new_images');
                    if(!empty($new_images)){
                        $add_image = array();
                        foreach($new_images as $new_i){
                            $add_image[] = $this->products->add_image($product['id'], $new_i);
                        }
                        $images = Arr::merge($images, $add_image);
                    }
                    // Удаление изображений
                    $current_images = $this->products->get_images(array('product_id'=>$product['id']));
                    
                    foreach($current_images as $image){
                        /*if(!empty($new_images))
                            foreach($new_images as $key => $new_img){
                                if(in_array($new_img, $image))
                                    unset($new_images[$key]);
                            }*/
                        if(!in_array($image['id'], $images))
                             $this->products->delete_image($image['id']);
                    }
                    
                    
                    
                    // Порядок изображений
                    //$images = Request::post('images')
                    
                    if(!empty($images)){
                        $i=0;
                        foreach($images as $id){
                            $this->products->update_image($id, array('position' => $i));
                            $i++;
                        }
                    }
                       // Загрузка изображений
                      if($images = Request::files('images')){
                        for($i = 0; $i < count($images['name']); $i++){
                             if ($image_name = $this->image->upload_image($images['tmp_name'][$i], $images['name'][$i])){
                                     $this->products->add_image($product['id'], $image_name);
                                 }else{
                                if(!empty($images['name'][0]))
                                    Request::$design->error('error','Ошибка загрузки изображения');
                                else
                                    Request::$design->error('error','Ошибка загрузки изображения (пустая форма)');
                            }
                        }
                    }            
                    
                       // Загрузка изображений из интернета и drag-n-drop файлов
                      if($images = Request::post('images_urls')){
                        foreach($images as $url){
                            // Если не пустой адрес и файл не локальный
                            if(!empty($url) && $url != 'http://' && strstr($url,'/')!==false){
                            
                                 $this->products->add_image($product['id'], $url);
                                
                            }
                             elseif($dropped_images = Request::files('dropped_images')){
                            
                                 $key = array_search($url, $dropped_images['name']);
                                 if ($key!==false && $image_name = $this->image->upload_image($dropped_images['tmp_name'][$key], $dropped_images['name'][$key]))
                                         $this->products->add_image($product['id'], $image_name);
                                            
                            }
                        }
                    }
                    $images = $this->products->get_images(array('product_id'=>$product['id']));
    
                       // Характеристики товара
                       
                       // Удалим все из товара
                    foreach($this->features->get_product_options($product['id']) as $po)
                        $this->features->delete_option($product['id'], $po['feature_id']);
                        
                    // Свойства текущей категории
                    /*$category_features = array();
                    foreach($this->features->get_features(array('category_id'=>$product_categories[0])) as $f)
                        $category_features[] = $f['id'];*/
                    /*Добавим свойства в категорию*/
                    $category_features = array();
                    foreach($this->features->get_features(array('category_id'=>$product_categories[0])) as $f)
                        $category_features[$f['id']] = $f['id'];
                    
                    $option_features = array();
                    // Если есть опции отсеиваем их
                    if(is_array($options)){
                        foreach($options as $option)
                            if(!empty($option['value']))
                                $option_features[$option['feature_id']] = $option['feature_id'];
                        
                        $pc = reset($product_categories);
                        $category_features = array_diff_key($option_features, $category_features);
                        if(is_array($category_features) AND !empty($category_features))
                            foreach($category_features AS $cf)
                                $this->features->add_feature_category($cf, $pc['id']);
                    }
                    /********/
                    
                      if(is_array($options))
                    foreach($options as $option){
                        if(is_array($option))
                            $this->features->update_option($product['id'], $option['feature_id'], $option['value']);
                    }
                    
                    // Новые характеристики
                    $new_features_names = Request::post('new_features_names');
                    $new_features_values = Request::post('new_features_values');
                    
                    if(is_array($new_features_names) && is_array($new_features_values)){
                        foreach($new_features_names as $i => $name){
                            $value = trim($new_features_values[$i]);
                            
                            if(!empty($name) && !empty($value)){

                                $sql = "SELECT * FROM __features WHERE name=:name LIMIT 1";
                                $sql = DB::placehold($sql);
                                
                                $query = DB::query(Database::SELECT, $sql);
                                    //Параметры
                                $query->param(':name',trim($name));
                                
                                $result =  $query->execute();
                                
                                $feature_id = $result[0]['id'];
    
                                if(empty($feature_id)){
                                    
                                    $feature_id = $this->features->add_feature(array('name'=>trim($name)));
                                }
                                $product_categories = reset($product_categories);
                                $this->features->add_feature_category($feature_id, $product_categories['id']);
                                $this->features->update_option($product['id'], $feature_id, $value);
                            }
                        }
                        // Свойства товара
                        $options = $this->features->get_product_options($product['id']);
                    }
                  }
            }
            //header("Location: /".Url::instance());
        }
        else{
            $id = Request::get('id', 'integer');
            $product = $this->products->get_product(intval($id));

            if($product){
                
                // Категории товара
                $product_categories = $this->categories->get_categories(array('product_id'=>$product['id']), TRUE);
                
                // Варианты товара
                $variants = $this->variants->get_variants(array('product_id'=>$product['id']));
                
                // Изображения товара
                $images = $this->products->get_images(array('product_id'=>$product['id']));
                
                // Свойства товара
                $options = $this->features->get_options(array('product_id'=>$product['id']));
            }else{
                // Сразу активен
                $product = array();
                $product['visible'] = 1;            
            }
        }
        
        if(empty($variants))
            $variants = array(1);
        if(empty($product_categories)){
            if($category_id = Request::get('category_id'))
                $product_categories[0]['id'] = $category_id;        
            else
                $product_categories = array(1);
        }
        if(empty($product['brand_id']) && $brand_id = Request::get('brand_id')){
            $product['brand_id'] = $brand_id;
        }
            
        if(is_array($options)){
            $temp_options = array();
            foreach($options as $option)
                $temp_options[$option['feature_id']] = $option;
            $options = $temp_options;
        }

        Request::$design->product = $product;

        Request::$design->product_categories = $product_categories;
        Request::$design->product_variants = $variants;
        Request::$design->product_images = $images;
        Request::$design->options = $options;
        
        // Все бренды
        $brands = $this->brands->get_brands();
        Request::$design->brands = $brands;
        
        // Все категории
        $categories = $this->categories->get_categories_tree();
        Request::$design->categories = $categories;
        
        // Все свойства товара
        $category = reset($product_categories);

        if(is_array($category)){
            $features = $this->features->get_features(array('category_id'=>$category->id));
            //Request::$design->features = $features;
            Request::$design->features = $this->features->get_features();
        }

        // Загружаем все характиристики
        Request::$design->features = $this->features->get_features();
        
        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_product',array());
    }
    
    
}