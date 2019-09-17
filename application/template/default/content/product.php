<?php
$settings = Registry::i()->settings;
$design = Request::$design;
if(isset($product['images'])){
    $imageName = array_shift($product['images']);
    $image = Request::$design->resizeimage($imageName['filename'], NULL, 178, 235);
    $imageOriginal = Request::$design->get_image($imageName['filename']);
}else{
    $image = Request::$design->resizeimage(NULL, NULL, 178, 235);
    $imageOriginal = '/' . Registry::i()->settings['no-image'];
}

foreach($product['categories'] as $cat){
    $category = $cat;
    break;
}
foreach($product['features'] as $key => $features){
    if($features['name'] == 'Ед.'){
        $item = $features['value'];
        unset($product['features'][$key]);
        break;
    }
}
if(isset($_SESSION['shopping_cart'][$product['variants']['id']])){
    $buy = TRUE;
}else{
    $buy = FALSE;
}
// Добавляем мини картинки
$lignts = Request::$design->light($product['features']);

?>
<div class="padding_product">
    <!-- Вывод хлебных крошек если они нужны -->

    <a class="product_h2" href="/catalog/<?=$category->url?>" ><?=$category->name?></a>

    <div class="product_main">
        <div id="offset">
            <!--Сделать картинку большой-->
            <div class="product_top_left section_1">
                <div class="image">
                    <a class="zoom" rel="group" href="<?=$imageOriginal?>">
                        <img src="<?=$image?>" height="178" />
                    </a>
                </div>
                <?php
                if(!empty($product['images'])):
                ?>
                <div class="image_box">
                    
                    <div class="line"></div>
                    <div class="lock">
                        <div class="box">
                            <div class="batton_box prev"></div>
                            <div class="hidden">
                                <div class="scroll">
                                    <?php
                                    foreach($product['images'] as $images):
                                    ?>
                                    <div class="conteiner">
                                        <a class="zoom" rel="group" href="<?=Request::$design->get_image($images['filename'])?>">
                                            <img src="<?=Request::$design->resizeimage($images['filename'], NULL, 88, 117)?>" />
                                        </a>
                                    </div>
                                    <?php
                                    endforeach;    
                                    ?>
                                </div>
                            </div>
                            <div class="batton_box next"></div>
                        </div>
                    </div>
                    <div class="angle">
                        <span class="string">Фотографии(<?=count($product['images'])?>)</span>
                    </div>
                </div>
                <?php
                endif;    
                ?>
            </div>
            <div class="product_top_left section_2">
                <h1 class="product_name">
                    <?=$product['name']?>
                </h1>
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
                
                <div class="product_content">
                    <p>Описание:</p>
                    <div class="product_body">
                    <?php
                        if(!empty($product['body']) AND ($product['body'] != ' ')){
                            echo $product['body'];
                        }else{
                            ?>
                                Нет описания
                            <?php
                        }
                    ?>
                    </div>
                </div>
            </div>
            <div class="product_top_left section_3 <?=($buy === TRUE) ? 'buy_complit' : ''; ?>">
                <!-- Корзина -->
                <div class="cart_product">
                    <?php
                        if($buy !== TRUE):
                    ?>
                    <form class="variants_form" action="/cart/add" method="POST">
                        <span class="compare_price">
                            <?=Str::money($product['variants']['price'])?> 
                            <img height='27' src="<?=$design->root?>/img/product/rub.png" />
                            <div class="string">Цена за 1 <?=!empty($item)? $item : 'растение' ?></div>
                        </span>
                        
                        <input type="radio" name="variant" value="<?=$product['variants']['id']?>" checked="checked" style="display:none;" />
                        
                        <button id="buy_button" type="submit"><span class="string">Купить</span></button>
                        <div class="buy_bottom">
                            <div class="box">
                                <span class="string">Количество</span> 
                                <input class="amount" name="amount" value="1" type="input" />
                            </div>
                        </div>
                    </form>
                    <?php
                        else:
                    ?>
                        <form class="variants_form" action="/cart" method="GET">
                            <span class="compare_price">
                                <div class="string">Товар добавлен в корзину</div>
                            </span>
                            <button id="buy_button" type="submit"><span class="string">Оформить</span></button>
                            <div class="buy_bottom">
                                <div>
                                    <span class="string">Изменить количество можно при оформлении</span>
                                </div>
                            </div>
                        </form>
                    <?php
                        endif;
                    ?>
                </div>    
            </div>
            <div style="clear:both;"></div>
        </div>
        <!-- Характиристики -->
        <?php
            if(!empty($product['features'])):
        ?>
        <div id="option_product">
            
            <div class="head_string">Характеристики</div>
            <div class="box">
                <table class="box_table">
                    <tbody>
                        <?php
                            foreach($product['features'] as $features):
                                $features_lower = trim(Utf8::strtolower($features['name']));
                                
                                // Делаем с большой буквы, если допустили ошибку
                                $features['value'] = Utf8::ucfirst($features['value']);
                                
                                if($features_lower == 'рост'){
                                    $f_img = '<div class="box_arrow"></div>';
                                    $features['value'] = $f_img . $features['value'];
                                }
                                elseif($features_lower == 'декоративная листва и кора'){
                                    $value = Utf8::strtolower(trim($features['value']));
                                    if($value == 'да'){
                                        $f_img = '<div class="box_yes-no yes"></div>';
                                    }else{
                                        $f_img = '<div class="box_yes-no no"></div>';
                                    }
                                    $features['value'] = $f_img;
                                }
                                elseif($features_lower == 'посадка у воды'){
                                    $value = Utf8::strtolower(trim($features['value']));
                                    if($value == 'да'){
                                        $f_img = '<div class="box_yes-no yes"></div>';
                                    }else{
                                        $f_img = '<div class="box_yes-no no"></div>';
                                    }
                                    $features['value'] = $f_img;
                                }
                                ?>
                                    <tr>
                                        <td class="box_padding">
                                            <div class="box_left">
                                                <div><?=$features['name']?></div>
                                            </div>
                                        </td>
                                        <td class="box_padding">
                                            <div class="box_right">
                                                <div><?=$features['value']?></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <div class="hr_line"></div>
                                        </td>
                                    </tr>
                                    
                                <?php
                            endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
            endif;
        ?>
    </div>
</div>
<div style="clear:both;"></div>
<script type="text/javascript">
/*Отчечает за дополнительные картинки*/
var imageBox = {
    /*
        next  - имя
        $next - jQuery коллекция
    */
    main   : 'angle',
    // Основной блок
    box    : 'box',
    $box    : '',
    // Для скрытия
    lock    : 'lock',
    $lock    : '',
    // Класс на открытый элемнт
    close  : 'close',
    // Кнопка на следующие картинки
    next   : 'next',
    $next   : '',
    // Кнопка на предыдущие картинки
    prev   : 'prev',
    $prev   : '',
    // Все картинки
    $images : '',
    // Массив смещения
    position : new Array(),
    // Массив смещения
    all_height : 0,
    // Блок для скролла
    scroll : 'scroll',
    $scroll : '',
    
    init : function(){
        var object = this;
        (object).$box = $('.'+(object).box);
        (object).$lock = $('.'+(object).lock);
        
        (object).$prev = $('.'+(object).prev);
        (object).$next = $('.'+(object).next);
        
        (object).$images = $('.'+(object).box+' img');
        (object).$scroll = $('.'+(object).box+' .'+(object).scroll);
        (object).position[0] = 0;
        
        $('.'+(object).main).click(function(){
            if(!$(this).hasClass((object).close)){
                (object).opens($(this));
            }else{
                (object).closes($(this));
            }
                
        });
        
        (object).$prev.click(function(){
            (object).prevs();
        });
        
        (object).$next.click(function(){
            (object).nexts();
        });
    },
    
    opens : function(jQuery){
        var object = this;
        var box_heiht = (object).$box.innerHeight();
        (object).$lock.css({'height':'20px'}).animate({height: box_heiht+'px'}, 300);
        
        // Проверяем количество картинок для определения высоты
        if((object).$images.length > 4){
            (object).$next.css('display','block');
        }
        
        jQuery.addClass((object).close);
        
    },
    
    closes : function(jQuery){
        var object = this;
        // сброс высоты
        (object).$lock.animate({height:'0px'}, 300,function(){
            (object).position = new Array();
            (object).position[0] = 0;
            (object).$scroll.css({'top' : '0'});
            (object).$prev.css('display','none');
        });
        jQuery.removeClass((object).close);
    },
    
    nexts : function(){
        var object = this;
        var scroll_height = (object).$scroll.height();
        
        var step = (object).$box.height();
        
        var top = Math.abs(parseInt((object).$scroll.css('top')));
        
        var dropNext = (scroll_height - (top + step + step));
        
        if(dropNext != Math.abs(dropNext)){
            (object).$next.css('display','none');
        }
        // Заполняем массив для возврата
        (object).position.push(step);

        (object).$prev.css('display','block');

        (object).$scroll.animate({'top' : '-'+(top + step)+'px'},300);

    },
    
    prevs : function(){
        var object = this;
        (object).$next.css('display','block');
        var step = (object).position.pop();
        var top = Math.abs(parseInt((object).$scroll.css('top')));
        
        (object).$scroll.animate({'top' : '-'+(top - step)+'px'},300);
        
        if((object).position.length == 1){
            (object).$prev.css('display','none');
        }
    }
}
imageBox.init();
</script>