<?php
$base = Core::$base_url;
$brand = Request::$design->brand;
$design = Request::$design;
$settings = Registry::i()->settings;
if($category && $brand){
    $canonical = $base.'catalog/'.$category->url.'/type/'.$brand['url'];
}
elseif($category){
    $canonical = $base.'catalog/'.$category->url;
}

$to_category = $base.'catalog/'.$category->url;

$catalog = $base . 'catalog/';
$base_product = $base.'products/';

?>
<div class="padding">
    <?php
        // заголовое страницы
        if($brand){
            $title = $brand['name'];
        }
        elseif($category){
            $title = $category->name;
        }
    ?>
    <h1><?=$title?></h1>
    <div><?=$category->description?></div>
    
    <!-- БРЕНДЫ -->
    <div id="brands">
    <?php
            foreach($category->brands as $b){
                //echo Template::factory($settings['theme'],'content_catalog_brands',array('b'=>$b,'settings'=>$settings,'catalog'=>$catalog,'category'=>$category));
                include "catalog/brands.php";
            }
            if(isset($brand)):
    ?>
            <a href="<?=$to_category?>">Вернутся в категорию</a>
    <?php
            endif;
    ?>
    </div>
    
    <!--Сортировка-->
    <?php
        if($design->sort == 'price'){
            $price = 'selected';
        }else{
            $name = 'selected';
        }
    ?>
    <div class="sort">
        Сортировать по    
        <a></a>
        <a class="<?=$price?>" href="<?=$canonical?>?sort=price">цене</a>
        <a class="<?=$name?>" href="<?=$canonical?>?sort=name">названию</a>
    </div>
    
    
    <!-- Список товаров-->
<ul class="products">
    <?php
        if($products):
            foreach($products as $product){
                //echo Template::factory($settings['theme'],'content_catalog_products',array('product'=>$product,'settings'=>$settings, 'base_product'=>$base_product));
                include "catalog/products.php";
            }
        // Листалка страниц
        include "catalog/pagination.php";
        else:
    ?>
        <p class="big">Товаров не найдено</p>
    <?php
        endif;
    ?>
</ul>

</div>