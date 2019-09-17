<?php
    $lignts = Request::$design->light($product['options']);

    /*echo '<pre>';
    print_r($product['images'][0]['filename']);
    exit();*/
?>
<tr>
    <td colspan="4">
        <div class="hr_line"></div>
    </td>
</tr>
<!-- фото товара -->
<tr>
    <td class="product product_photo">
        <div class="image">
            <a href="<?=$base_product.$product['url']?>">
                <?php
                    if($product['images'][0]):
                    $image = Request::$design->resizeimage($product['images'][0]['filename'],NULL,109,132);
                ?>
                <img src="<?=$image?>" alt="<?=$product['images'][0]['name']?>" height="109" />
                <?php
                    else:
                    $image = Request::$design->resizeimage(NULL,NULL,109,132);
                ?>
                    <img src="<?=$image?>" alt="<?$product['name']?>" height="109" />
                <?php
                    endif;
                ?>
            </a>
        </div>
    </td>

    <!-- Информация о продукте -->

    <td class="product td_align_top">
        <div class="product_info">
            <!-- Название товара -->
            <h3 class="product_name">
                <a href="<?=$base_product.$product['url']?>"><?=$product['name']?></a>
            </h3>
            
            <?php
            // Заполняем миникартинками
            if(isset($lignts) AND $lignts != FALSE):
            ?>
            <div class="product_sun" style="float:left; overflow:hidden;">
                <?php
                    foreach($lignts as $lignt):
                ?>
                    <div class="product_sun_image" style="height:<?=$lignt['height']?>px;width:<?=$lignt['width']?>px;background:url('<?=$lignt['image']?>') no-repeat -<?=$lignt['position_left']?>px 0;"></div>
                    
                <?php
                    endforeach;
                ?>
            </div>
            <div style="clear:both;"></div>
            <?php
            endif;
            ?>
            <!-- Описание товара -->
            <div class="annotation"><?=$product['annotation']?>...</div>
        </div>
    </td>


    <td class="product">
        <div class="variants">
            <span class="price"><?=Str::money($product['variants'][0]['price'])?> р.</span>
        </div>
    </td>


    <td class="product">
            <a class="product_button" href="<?=$base_product.$product['url']?>"></a>
    </td>
</tr>
