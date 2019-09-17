<?php defined('MODPATH') OR exit();

class Pars_Module implements I_Module{


    function index($setting = null){}
    

    
    function fetch(){
        $row = 0;
        
        if (($handle = fopen("media/pars/bush_fruit.csv", "r")) !== FALSE) {
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
        //echo '<pre>';

        $indents = array('decorative' => array(1 => 'Да', 0 => 'Нет'),'landing' => array(1 => 'Да', 0 => 'Нет'));
        
        $fonds = $this->yes_nou($fonds, $indents);
        //print_r($fonds);
        //exit();
        /*
            Категория : 7
            Категория : 7
            
            // для характиристик
            $sql = INSERT INTO options(product_id, feature_id, value, position) VALUES
                ();
             -- Вставка продукта
             
            $sql = DB::placehold("INSERT INTO __products(url, name, annotation, body, meta_title, meta_description, created) VALUES
                    ('', '', '', '', '', '', NOW());");
        */

        $categor = 6;
        
        
        // Вставка содержимого
        /*ВСТАВЛЯЕМ ПРОДУКТЫ*/
        foreach($fonds as $fond){
            // для продукта
            $url = Translit::url($fond['name']);
            $name = $fond['name'];
            $annotation = $fond['description'];
            $body = $fond['description'];
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
            
            // Получаем индитификатор вставленого продукта
            $sql = DB::placehold("SELECT id FROM __products WHERE url = '$url'");

            $query = DB::query(Database::SELECT, $sql);
            $result = $query->execute();
            
            $product_id = $result[0]['id'];
            // Заполняем в категорию
            $sql = DB::placehold("INSERT INTO __products_categories(products_id, categories_id) VALUES
                    ('$product_id', $categor)");
            $query = DB::query(Database::INSERT, $sql);
            $query->execute();
            
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
            
            $price = $fond['price'];
            
            $sql = "INSERT INTO __variants(product_id, price) VALUES
                    ('$product_id', $price)";
            $sql = DB::placehold($sql);
            $query = DB::query(Database::INSERT, $sql);
            $query->execute();
        }
        
        // Что бы не шел дальше
        exit();
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
    
}