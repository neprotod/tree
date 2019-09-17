<?php
$array = array(
    'products'=> array(
        'active' => '',
        'name' => 'Товары',
    ),
    'categories'=> array(
        'active' => '',
        'name' => 'Категории'
    ),
    'brands'=> array(
        'active' => '',
        'name' => 'Бренды'
    ),
    'features'=> array(
        'active' => '',
        'name' => 'Свойства'
    ),
);
if(array_key_exists($active, $array)){
    $array[$active]['active'] = 'active';
    if(isset($return))
        $array[$active]['return'] = TRUE;
}

foreach($array as $module => $tabs):
?>
<li class="<?=$tabs['active']?>">
    <a href="<?=(isset($tabs['return']))? Request::get('return') : Url::query_root(array('module'=>$module)) ?>"><?=$tabs['name']?></a>
</li>
<?php
endforeach;
?>