<?php
header("Cache-control: no-store,max-age=0");
header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");    
include 'bootstrap.php';

$image_module = Module::factory('image', TRUE);

$limit = 100;

$keyword =  Request::get('query', 'string');

$route = new Route();

$settings = $route->settings();

$sql = Str::__('SELECT p.id, p.name, i.filename as image FROM __products p
                        LEFT JOIN __images i ON i.product_id=p.id AND i.position=(SELECT MIN(position) FROM __images WHERE product_id=p.id LIMIT 1)
                        LEFT JOIN __variants pv ON pv.product_id=p.id AND (pv.stock IS NULL OR pv.stock>0) AND pv.price>0
                        WHERE p.name LIKE "%:keyword%"
                        ORDER BY p.name LIMIT :limit', array(':keyword'=>$keyword,':limit'=>$limit));

$sql = DB::placehold($sql);

$query = DB::query(Database::SELECT, $sql);

        
    
$result = $query->execute();

$products = array();
foreach($result as $product){
    $products[$product['id']] = $product;
}

$variants = array();
if(!empty($products)){
    $sql = Str::__('SELECT v.id, v.name, v.price, IFNULL(v.stock, :stock) as stock, (v.stock IS NULL) as infinity, v.product_id FROM __variants v WHERE v.product_id in(:product_id) AND (v.stock IS NULL OR v.stock>0) AND v.price>0 ORDER BY v.position', array(":stock"=>$settings['max_order_amount'],":product_id"=>implode(',',array_keys($products))));

    $sql = DB::placehold($sql);

    $query = DB::query(Database::SELECT, $sql);
    
    $variants = $query->execute();
}

foreach($variants as $variant)
    if(isset($products[$variant['product_id']]))
        $products[$variant['product_id']]['variants'][] = $variant;
            
$suggestions = array();
foreach($products as $product){
    if(!empty($product['variants'])){
        $suggestion = array();
        if(!empty($product['image'])){
            $product['image'] = $image_module->resizeimage($product['image'], array('height'=>35,'resizeWidth'=>35), $settings); //resize 35, 35
        }
        $suggestion['value'] = $product['name'];        
        $suggestion['data'] = $product;        
        $suggestions[] = $suggestion;
    }
}

$res = array();
$res['query'] = $keyword;
$res['suggestions'] = $suggestions;
header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");        
print json_encode($res);
