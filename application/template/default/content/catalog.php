<?php
$base = Core::$base_url;
if(isset(Request::$design->brand))
    $brand = Request::$design->brand;
$design = Request::$design;
$settings = Registry::i()->settings;
if(isset($category) && isset($brand)){
    $canonical = $base.'catalog/'.$category->url.'/type/'.$brand['url'];
}
elseif(isset($category)){
    $canonical = $base.'catalog/'.$category->url;
}
elseif(isset($category)){
    $canonical = $base.'catalog/'.$category->url;
}
elseif(isset(Request::$design->keyword)){
    $canonical = $base.'catalog?keyword='.Request::$design->keyword;
}


$to_category = $base.'catalog/'.$category->url;

$catalog = $base . 'catalog/';
$base_product = $base.'products/';

?>
<div class="padding">
    <?php
        // заголовое страницы
        if(isset($brand)){
            $title = $brand['name'];
            $description = '';
        }
        elseif(isset($category)){
            $title = $category->name;
            $description = $category->description;
        }
        elseif(isset(Request::$design->keyword)){
            $title = Request::$design->meta_title;
            $description = '';
        }
    ?>
    <h1 id="catalog_name"><?=$title?></h1>
    <div><?=$description?></div>
    
    <!-- БРЕНДЫ -->
    <?php
        if(!empty($category->brands)):
    ?>
    <div id="line_brands">
        <table id="brands">
            <tbody>
                <tr>
        <?php
                foreach($category->brands as $b){
                    //echo Template::factory($settings['theme'],'content_catalog_brands',array('b'=>$b,'settings'=>$settings,'catalog'=>$catalog,'category'=>$category));
                    include "catalog/brands.php";
                }
                if(isset($brand)):
        ?>
                
                    <td>
                        <a class="category_brand brand_padding" href="<?=$to_category?>">Вернутся в категорию</a>
                    </td>
                </tr>    
        <?php
                endif;
        ?>
            </tbody>
        </table>
    </div>
    <?php
        endif;
    ?>
    <div style="clear:both;"></div>
    <!--Сортировка-->
    <?php
        if($design->sort == 'price'){
            $price = 'selected';
        }else{
            $name = 'selected';
        }
    ?>
    <div class="sort">
        Сортировать:
        <select>
            <option value="<?=$canonical?>?sort=name&order=asc" <?=((Request::$design->sort == 'name') OR (!isset(Request::$design->sort)))? 'selected="selected"' :''?>>по названию</option>
            <option value="<?=$canonical?>?sort=price&order=asc" <?=((Request::$design->sort == 'price') AND (Request::$design->order == 'asc'))? 'selected="selected"' :''?>>сначала дорогие</option>
            <option value="<?=$canonical?>?sort=price&order=desc" <?=((Request::$design->sort == 'price') AND (Request::$design->order == 'desc'))? 'selected="selected"' :''?>>сначала дешевые</option>
        </select>
        <script type="text/javascript">
            $('.sort select').change(function(){
                window.location.href = $(this).val();
            })
        </script>
    </div>
    
    
    <!-- Список товаров-->
<table class="products">
    <tbody>
        <?php
            if($products):
                foreach($products as $product){
                    //echo Template::factory($settings['theme'],'content_catalog_products',array('product'=>$product,'settings'=>$settings, 'base_product'=>$base_product));
                    include "catalog/products.php";
                }
            
            else:
        ?>
            <p class="big">Товаров не найдено</p>
        <?php
            endif;
        ?>
    </tbody>
</table>
<?php
    // Листалка страниц
        include "catalog/pagination.php";
?>
</div>