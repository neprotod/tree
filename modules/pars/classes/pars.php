<?php defined('MODPATH') OR exit();

class Pars_Module implements I_Module{


    function index($setting = null){}
    

    
    function fetch(){
        /*
        $fonds = $this->csv('bush_fruit');
        $this->bush_tree($fonds, 8);
        
        $fonds = $this->csv('bush_list');
        $this->bush_tree($fonds, 7);
        
        $fonds = $this->csv('bush_nidells');
        $this->bush_tree($fonds, 6);
        

        $fonds = $this->csv('tree_fruit');
        $this->bush_tree($fonds, 4);

        $fonds = $this->csv('tree_list');
        $this->bush_tree($fonds, 3);
        
        $fonds = $this->csv('tree_nidells');
        $this->bush_tree($fonds, 2);
        
        
        $fonds = $this->csv('lian');
        $this->lian($fonds, 9);
        
        $fonds = $this->csv('flowers');
        $this->flowers($fonds, 12);
        
        $fonds = $this->csv('ground-cover');
        $this->flowers($fonds, 13);
        
        $fonds = $this->csv('herbares');
        $this->flowers($fonds, 14);
        
        
        $fonds = $this->csv('building');
        $this->other($fonds, 15);
        
        $fonds = $this->csv('fertilizers');
        $this->other($fonds, 17);
        
        $fonds = $this->csv('seeds');
        $this->other($fonds, 10);
        
        $fonds = $this->csv('inventory');
        $this->inventory($fonds, 18);
        */
        //$fonds = $this->csv('flowers_image');
        //echo '<pre>';
        //print_r($fonds);
        //$this->insert_image($fonds);
        /*$sql = DB::placehold("SELECT  pc.products_id, p.name as product_name, c.name AS cat_name FROM products_categories pc
                            INNER JOIN categories c ON c.id = pc.categories_id
                            INNER JOIN products p ON p.id = pc.products_id
                            WHERE pc.categories_id IN (18)
                            ORDER BY pc.categories_id, pc.products_id;");
        $query = DB::query(Database::SELECT, $sql);
        $results = $query->execute();

        foreach($results as $result){
            $product = str_replace(';',':',$result['product_name']);
            echo "{$result['products_id']};{$product};{$result['cat_name']}<br>";
        }*/
        // Что бы не шел дальше
        $sql = DB::placehold("SELECT id, url FROM products");
        $query = DB::query(Database::SELECT, $sql);
        $results = $query->execute();
        
        foreach($results as $result){
            $sql = DB::placehold("UPDATE products SET url = :url WHERE id = :id");
            $query = DB::query(Database::UPDATE, $sql);
            
            $query->param(':url',str_replace('/','-',$result['url']));
            $query->param(':id',$result['id']);
            $query->execute();
        }
        exit();
    }
    
    
    
    
    /********************/
    function insert_image($fonds){
        foreach($fonds as $fond){
            if(empty($fond['img'])){
                continue;
            }
            $file = $fond['path'].$fond['img'].$fond['exp'];
            $id = $fond['id'];
            $sql = DB::placehold("INSERT INTO __images(product_id, filename, name) VALUES
                        ('$id', '$file','')");
            $query = DB::query(Database::INSERT, $sql);
            $query->execute();
        }
    }
    /*Для заполнение лиан*/
    function inventory($fonds, $categor){

        foreach($fonds as $fond){
        
            // для продукта
            $url = Translit::url($fond['name']);
            $name = $fond['name'];
            $annotation = empty($fond['description']) ? ' ' : $fond['description'];
            $body = empty($fond['description']) ? ' ' : $fond['description'];
            $meta_title = $fond['name'];
            $meta_description = $fond['description'];
            
            $product_id = $this->product_id($url);
            if(empty($product_id)){
                // Вставляем продукт
                $sql = DB::placehold("INSERT INTO __products(url, name, annotation, body, meta_title, meta_description, created) VALUES
                        (:url, :name, :annotation, :body, :meta_title, :meta_description, NOW());");
                $query = DB::query(Database::INSERT, $sql);
                
                $query->param(':url',$url);
                $query->param(':name',$name);
                $query->param(':annotation',$annotation);
                $query->param(':body',$body);
                $query->param(':meta_title',$meta_title);
                $query->param(':meta_description',$meta_description);
            
                $query->execute();
                
                // получаем id продукта
                $product_id = $this->product_id($url);
                
                
                // Заполняем в категорию
                $this->category_insert($product_id, $categor);
                //Создаем набор переменнх для характиристик
                $o1 = $fond['unit'];
                $o2 = $fond['type'];
            
                $sql = "INSERT INTO __options(product_id, feature_id, value, position) VALUES
                        ('$product_id', '12', '$o1', '8'),
                        ('$product_id', '8', '$o2', '9')";
                $sql = DB::placehold($sql);
                $query = DB::query(Database::INSERT, $sql);
                $query->execute();
            }
            //Заполняем цену
            $color = $fond['color'];
            $price = $fond['price'];
            
            $sql = "INSERT INTO __variants(product_id, price, name) VALUES
                    ('$product_id', '$price','$color')";
            $sql = DB::placehold($sql);
            $query = DB::query(Database::INSERT, $sql);
            $query->execute();
        }
    }
    
    function other($fonds, $categor){
            
        $indents = array('decorative' => array(1 => 'Да', 0 => 'Нет'),'landing' => array(1 => 'Да', 0 => 'Нет'));
        
        $fonds = $this->yes_nou($fonds, $indents);

        foreach($fonds as $fond){
        
            // вставляем продукт
            $url = $this->product_insert($fond);
            
            // получаем id продукта
            $product_id = $this->product_id($url);
            
            
            // Заполняем в категорию
            $this->category_insert($product_id, $categor);
            //Создаем набор переменнх для характиристик
            $o1 = $fond['unit'];
            
            $sql = "INSERT INTO __options(product_id, feature_id, value, position) VALUES
                    ('$product_id', '12', '$o1', '8')";
            $sql = DB::placehold($sql);
            $query = DB::query(Database::INSERT, $sql);
            $query->execute();
            
            //Заполняем цену
            $this->price($product_id, $fond['price']);
        }
    }
    
    /*Для заполнение лиан*/
    function flowers($fonds, $categor){
            
        $indents = array('decorative' => array(1 => 'Да', 0 => 'Нет'),'landing' => array(1 => 'Да', 0 => 'Нет'));
        
        $fonds = $this->yes_nou($fonds, $indents);

        foreach($fonds as $fond){
        
            // вставляем продукт
            $url = $this->product_insert($fond);
            
            // получаем id продукта
            $product_id = $this->product_id($url);
            
            
            // Заполняем в категорию
            $this->category_insert($product_id, $categor);
            
            //Создаем набор переменнх для характиристик
            $o1 = $fond['cycle'];
            $o2 = $fond['lighting'];
            $o3 = $fond['form'];
            $o4 = $fond['watering'];
            
            $sql = "INSERT INTO __options(product_id, feature_id, value, position) VALUES
                    ('$product_id', '9', '$o1', '1'),
                    ('$product_id', '11', '$o2', '2'),
                    ('$product_id', '4', '$o3', '3'),
                    ('$product_id', '10', '$o4', '4')";
            $sql = DB::placehold($sql);
            $query = DB::query(Database::INSERT, $sql);
            $query->execute();
            //Заполняем цену
            $this->price($product_id, $fond['price']);
        }
    }
    
    /*Для заполнение лиан*/
    function lian($fonds, $categor){
            
        $indents = array('decorative' => array(1 => 'Да', 0 => 'Нет'),'landing' => array(1 => 'Да', 0 => 'Нет'));
        
        $fonds = $this->yes_nou($fonds, $indents);

        foreach($fonds as $fond){
        
            // вставляем продукт
            $url = $this->product_insert($fond);
            
            // получаем id продукта
            $product_id = $this->product_id($url);
            
            
            // Заполняем в категорию
            $this->category_insert($product_id, $categor);
            
            //Создаем набор переменнх для характиристик
            $o1 = $fond['height'];
            $o2 = $fond['soil'];
            $o3 = $fond['lighting'];
            $o4 = $fond['area'];
            $o6 = $fond['decorative'];
            $o7 = $fond['landing'];
            
            $sql = "INSERT INTO __options(product_id, feature_id, value, position) VALUES
                    ('$product_id', '1', '$o1', '1'),
                    ('$product_id', '2', '$o2', '2'),
                    ('$product_id', '11', '$o3', '3'),
                    ('$product_id', '3', '$o4', '4'),
                    ('$product_id', '5', '$o6', '6'),
                    ('$product_id', '6', '$o7', '7')";
            $sql = DB::placehold($sql);
            $query = DB::query(Database::INSERT, $sql);
            $query->execute();
            //Заполняем цену
            $this->price($product_id, $fond['price']);
        }
    }
    
    /*Для заполнение кустов*/
    function bush_tree($fonds, $categor){
        
        $indents = array('decorative' => array(1 => 'Да', 0 => 'Нет'),'landing' => array(1 => 'Да', 0 => 'Нет'));
        
        $fonds = $this->yes_nou($fonds, $indents);

        foreach($fonds as $fond){
            // вставляем продукт
            $url = $this->product_insert($fond);
            
            // получаем id продукта
            $product_id = $this->product_id($url);
            
            
            // Заполняем в категорию
            $this->category_insert($product_id, $categor);
            
            //Создаем набор переменнх для характиристик
            $o1 = $fond['height'];
            $o2 = $fond['soil'];
            $o3 = $fond['lighting'];
            $o4 = $fond['area'];
            $o5 = $fond['form'];
            $o6 = $fond['decorative'];
            $o7 = $fond['landing'];
            
            $sql = "INSERT INTO __options(product_id, feature_id, value, position) VALUES
                    ('$product_id', '1', '$o1', '1'),
                    ('$product_id', '2', '$o2', '2'),
                    ('$product_id', '11', '$o3', '3'),
                    ('$product_id', '3', '$o4', '4'),
                    ('$product_id', '4', '$o5', '5'),
                    ('$product_id', '5', '$o6', '6'),
                    ('$product_id', '6', '$o7', '7')";
            $sql = DB::placehold($sql);
            $query = DB::query(Database::INSERT, $sql);
            $query->execute();
            
            //Заполняем цену
            $this->price($product_id, $fond['price']);
        }
    }
    
    /*Вставляем продукт*/
    function product_insert($fond){
        // для продукта
            $url = Translit::url($fond['name']);
            $name = $fond['name'];
            $annotation = empty($fond['description']) ? ' ' : $fond['description'];
            $body = empty($fond['description']) ? ' ' : $fond['description'];
            $meta_title = $fond['name'];
            $meta_description = $fond['description'];
            // Вставляем продукт
            $sql = DB::placehold("INSERT INTO __products(url, name, annotation, body, meta_title, meta_description, created) VALUES
                    (:url, :name, :annotation, :body, :meta_title, :meta_description, NOW());");
            $query = DB::query(Database::INSERT, $sql);
            
            $query->param(':url',$url);
            $query->param(':name',$name);
            $query->param(':annotation',$annotation);
            $query->param(':body',$body);
            $query->param(':meta_title',$meta_title);
            $query->param(':meta_description',$meta_description);
            
            $query->execute();
            
            return $url;
    }
    
    /*Записываем в категорию*/
    function category_insert($product_id, $categor){
        $sql = DB::placehold("INSERT INTO __products_categories(products_id, categories_id) VALUES
                ('$product_id', $categor)");
        $query = DB::query(Database::INSERT, $sql);
        $query->execute();
    }
    
    /*Получаем ключь продукта*/
    function product_id($url){
            // Получаем индитификатор вставленого продукта
            $sql = DB::placehold("SELECT id FROM __products WHERE url = '$url'");

            $query = DB::query(Database::SELECT, $sql);
            $result = $query->execute();
            
            return $result[0]['id'];
    }
    /*Получаем ключь продукта*/
    function price($product_id, $price){        
            $sql = "INSERT INTO __variants(product_id, price) VALUES
                    ('$product_id', '$price')";
            $sql = DB::placehold($sql);
            $query = DB::query(Database::INSERT, $sql);
            $query->execute();
    }
    
    // Заполняет поля значениями
    function yes_nou($arrey, $lines){
        foreach($arrey as $Akey => $Avalue){
            foreach($lines as $k => $v){
                $ind = $Avalue[$k];
                if(isset($arrey[$Akey][$k])){
                    $arrey[$Akey][$k] = $v[$ind];
                }
            }
        }
        return $arrey;
    }
    
    function csv($file){
        $row = 0;
        
        if (($handle = fopen("media/pars/$file.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 991000, ";")) !== FALSE) {
                $num = count($data);
                
                if($row == 1){
                    $get = array();
                    $data[0] = trim($data[0]);
                    foreach($ids as $k => $v){
                        
                        $get[$v] =  $data[$k];
                    }
                    $fonds[] = $get;
                }else{
                    $ids = $data;
                }
                $row = 1;
            }
            fclose($handle);
        }
        return $fonds;
    }
}