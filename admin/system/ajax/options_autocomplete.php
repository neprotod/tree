<?php
header("Cache-control: no-store,max-age=0");
header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");    
include 'bootstrap.php';

$limit = 100;

$keyword =  Request::get('query', 'string');
$feature_id =  Request::get('feature_id', 'integer');

$sql = Str::__('SELECT DISTINCT po.value FROM __options po
                                    WHERE value LIKE :keyword AND feature_id=:feature_id ORDER BY po.value LIMIT :limit', array(':keyword'=>DB::escape($keyword.'%'),':feature_id'=>$feature_id,':limit'=>$limit));
$sql = DB::placehold($sql);

$query = DB::query(Database::SELECT, $sql);

        
    
$options = $query->execute();


foreach($options as $option){
    $value[] = $option['value'];
}

$res = array();
$res['query'] = $keyword;
$res['suggestions'] = $options;
header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");        
print json_encode($res);
