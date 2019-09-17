<?php
header("Cache-control: no-store,max-age=0");
header("Content-type: application/json; charset=UTF-8");
include 'bootstrap.php';
header("GOOD: YES");
$id = intval(Request::post('id'));
$object = Request::post('object');
$values = Request::post('values');

switch ($object){
    case 'product':
        $products = Module::factory('products', TRUE);
        $result = $products->update_product($id, $values);
        break;
    case 'category':
        $categories = Module::factory('categories', TRUE);
        $result = $categories->update_category($id, $values);
        break;
    case 'page':
        $pages = Module::factory('page', TRUE);
        $result = $pages->update_page($id, $values);
        break;
    case 'feature':
        $features = Module::factory('features', TRUE);
        $result = $features->update_feature($id, $values);
        break;
    case 'blog':
        $blog = Module::factory('blog', TRUE);
        $result = $blog->update_post($id, $values);
        break;
    case 'price':
        $prices = Module::factory('prices', TRUE);
        $result = $prices->update_price($id, $values);
        break;
}
$json = json_encode($result);
print $json;
