<?php
header("Cache-control: no-store,max-age=0");
header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");    
include 'bootstrap.php';

$category_id = Request::get('category_id', 'integer');
$product_id = Request::get('product_id', 'integer');

$features = Module::factory('features', TRUE);

if(!empty($category_id)){
    $features = $features->get_features(array('category_id'=>$category_id));
}else{
    $features = $features->get_features();
}
$options = array();
if(!empty($product_id)){
    $opts = $features->get_product_options($product_id);
    foreach($opts as $opt)
        $options[$opt['feature_id']] = $opt;
}
    
foreach($features as &$f)
{
    if(isset($options[$f['id']]))
        $f['value'] = $options[$f['id']]['value'];
    else
        $f['value'] = '';
}


print json_encode($features);
