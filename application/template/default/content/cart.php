<?php
$settings = Registry::i()->settings;

$word = array('товар','товара','товаров');

?>
<div class="padding cart">
    <h1 class="cart_h1">Корзина</h1>
    <!-- Оформление -->
    <?php
        if(!empty($cart['purchases'])):
    ?>
    <div class="cart_making">
        <div class="making">
            <span class="string">Итого <span class="number"><?=$cart['total_products']?></span> <?=Translit::declension_words($cart['total_products'], $word)?> на сумму</span>
            
            <div class="price">
                <span><?=Str::money($cart['total_price'])?> р.</span>
            </div>
            
            <div class="iform">
                Общая сумма заказа без учета стоимости доставки
            </div>
            <a href="/cart/making" id="button_making">
                <div class="button_div">Оформить заказ</div>
            </a>
        </div>
    </div>
    
    <table id="purchases">
        <tbody>
            <tr>
                <td colspan="4">
                    <div id="anotation_cart">Проверьте правельность заказа и выбранных параметров</div>
                    <div class="hr_line"></div>
                </td>
            </tr>
            <?php
                foreach($cart['purchases'] as $purchase):
                $lignts = Request::$design->light($purchase['product']['options']);
                $product = $purchase['product'];
            ?>
            <tr>
                <!-- Картинка товара -->
                <td class="cart_td_padding">            
                    <div class="cart_image">
                        <a href="<?='/products/'.$product['url']?>">
                            <?php
                                if($product['images']):
                                $image = Request::$design->resizeimage($product['images']['filename'],NULL,109, 143);
                            ?>
                            <img src="<?=$image?>" height="109" />
                            <?php
                                else:
                                $image = Request::$design->resizeimage(NULL,NULL,109, 143);
                            ?>
                                <img src="<?=$image?>" alt="<?$product['name']?>" height="109" />
                            <?php
                                endif;
                            ?>
                        </a>
                    </div>
                </td>
                <!-- Имена и миникартинки -->
                <td class="cart_td_padding td_align_top">
                    <div class="cart_content">
                        <div class="cart_product_name"><?=$product['name']?></div>
                        <?php
                        // Заполняем миникартинками
                        if(isset($lignts) AND $lignts != FALSE):
                        ?>
                        <div class="product_sun">
                            <?php
                                foreach($lignts as $lignt):
                            ?>
                                <div class="product_sun_image" style="height:<?=$lignt['height']?>px;width:<?=$lignt['width']?>px;background:url('<?=$lignt['image']?>') no-repeat -<?=$lignt['position_left']?>px 0;"></div>
                                
                            <?php
                                endforeach;
                            ?>
                        <div style="clear:both;"></div>
                        </div>
                        <div style="clear:both;"></div>
                        <?php
                        endif;
                        ?>
                    </div>
                </td>
                <!-- количество -->
                <td class="cart_td_padding">
                    <div class="cart_amount">
                        <form action="/cart/update">
                            <input class="amount" name="amount" type="text" size="1" value="<?=$purchase['amount']?>" /> шт.
                        <form>
                    </div>
                </td>
                <!-- цена -->
                <td class="cart_td_padding">
                    <!-- Для удаления -->
                    <div class="box">
                        <div class="drop"></div>
                    </div>
                    <input class="id" type="hidden" value="<?=$purchase['variant']['id']?>" />
                    <div class="cart_price">                
                        <span class="price"><?=Str::money($purchase['variant']['price']*$purchase['amount'])?> р.</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <div class="hr_line"></div>
                </td>
            </tr>
            <?php
                endforeach;
            ?>
            
            <tr>
                <td colspan="4" style="text-align:right;">
                    <div class="cart_price">
                        <div class="total_price">
                            <span class="string">Общая строимость:</span>
                            <span class="price"><?=Str::money($cart['total_price'])?> р.</span>
                        </div>
                    </div>    
                </td>
            </tr>
        </tbody>
    </table>
    <?php
        else:
    ?>
        В корзине нет товаров
    <?php
        endif;
    ?>
</div>

<!-- Скрипт для обработки корзины -->
<script type="text/javascript">
    var $purchases =  $("#purchases tr");
    // Удаление товара
    $(".drop").click(function(){
        var id = $(this).closest('td').find('.id').val();
        var $tr = $(this).closest('tr');
        
        $.ajax({
                url: "<?=Url::root()?>application/ajax/cart.php",
                data: {
                    id : id,
                    type : 'drop'
                },
                dataType: 'json',
                type: 'POST',
                success: function(data){
                    if(data.result = true){
                        $tr.next().fadeOut(200, function() { $(this).remove(); });
                        $tr.fadeOut(200, function() {
                            $(this).remove(); 
                            count();
                        });
                        
                        //.fadeOut(200, function() { $(this).remove(); });
                    }
                }
        });
        
        // Проверка удалены ли все элементы?
        var $purchases =  $("#purchases tr");

        if($purchases.length == 4){
            var $prent = $purchases.parent();
            $purchases.each(function(){
                $(this).remove();
            });
            $prent.html('<tr><td style="font-size:20px;">Корзина пуста</td></tr>');
            $("#button_making").remove()
        }
    });
    // Пересчет
    function count(){
        var total = 0;
        
        var $price = $(".cart_price .price").not(".total_price .price");
        
        var $amount = $(".cart_amount .amount");
        
        var length = 0;
        $amount.each(function(){
            length += number_cliar($(this).val());
        });
        
        
        $price.each(function(){
            // Убераем все лишнее из числа
            var price;
        
            price = $(this).text();
            total += number_cliar(price);
        });
        total = money(total);
        $(".total_price .price, .making .price span").each(function(){
            $(this).text(total + ' р.');
        });
        $('.making .number').text(length);
        $('#basket_logo').text(length);
    }
    
    function number_cliar(number){
        number = number.toString();
        if(!number)
            return 0;
        var regexp = /[0-9]/g;
        var result = number.match(regexp);
        
        var string = '';
        result.forEach(function(element,index){
            string += element.toString();
        });
        
        return parseInt(string);
    }
    
    function money(number){
        number = number.toString();
        var regexp = /[0-9]/g;
        var result = number.match(regexp);
        var string = '';
        var leng = result.length
        if(leng > 3){
            var arr = new Array();
            result.reverse().forEach(function(element,index){
                if(((index % 3) == 0) && (index != 0))
                    arr.push(' ');
                arr.push(element.toString());
            });
            arr.reverse().forEach(function(element,index){
                string += element;
            });
        }else{
            result.forEach(function(element,index){
                string += element.toString();
            });
        }
        return string;
    }
    
    // Обнавление количества
    /*$(".update").click(function(){
        var $tr = $(this).closest('tr');
        var id = $tr.find('.id').val();
        var $price = $tr.find('.cart_price .price');
        var amount = number_cliar($tr.find('.amount').val());
        
        $.ajax({
                url: "<?=Url::root()?>application/ajax/cart.php",
                data: {
                    id : id,
                    type : 'update',
                    amount : amount,
                    max_order_amount : <?=Registry::i()->settings['max_order_amount']?>
                },
                dataType: 'json',
                type: 'POST',
                success: function(data){
                    $price.text(money(data.price*data.amount)+' р.');
                    $tr.find('.amount').val(data.amount);
                    count();
                }
        });
    });*/
    $(".cart_amount").keyup(function(){
        var $tr = $(this).closest('tr');
        var id = $tr.find('.id').val();
        var $price = $tr.find('.cart_price .price');
        var amount = $tr.find('.amount').val();
        $.ajax({
                url: "<?=Url::root()?>application/ajax/cart.php",
                data: {
                    id : id,
                    type : 'update',
                    amount : amount,
                    max_order_amount : <?=Registry::i()->settings['max_order_amount']?>
                },
                dataType: 'json',
                type: 'POST',
                success: function(data){
                    $price.text(money(data.price*data.amount)+' р.');
                    //$tr.find('.amount').val(data.amount);
                    count();
                }
        });
    });
</script>