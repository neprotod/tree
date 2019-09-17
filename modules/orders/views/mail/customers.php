<?php
$settings = Registry::i()->settings;
?>
<div class="main" style="width:760px">
    <h1>
        <?=$settings['company_name']?>
    </h1>
    <p>
        Здравствуйте, <?=$order['name']?>
    </p>
    
    <p>
        <b>Ваша заявка принята. Для подтверждения и уточнения заказа наш менеджер свяжется с Вами в ближайшее время. </b>
    </p>
    
    <p>
        Будем рады ответить на Ваши вопросы по телефону:<br />
        <?=$settings['phone']?>
    </p>
    
    <table id="purchases" style="border: 1px solid #CDCDCD; border-radius: 10px; padding: 10px; width: 650px;">
        <tbody>
            <tr>
                <td colspan="4">
                    <div id="anotation_cart" style="padding-bottom: 10px;">
                        <span style="font-size:24px; font-weight: bold;">Заказ №<?=$order['id']?></span>
                        <span style="float:right;">Дата заказа: <?=$order['date']?></span>
                    </div>
                    <div class="hr_line" style="border-bottom: 1px dotted #D4D4D4; height: 1px; width: 100%;"></div>
                </td>
            </tr>
            <?php
            foreach($purchases as $purchase):
            ?>
            <tr>
                <!-- Картинка товара -->
                <td class="cart_td_padding" style="padding-bottom: 35px; padding-top: 35px;">            
                    <div class="cart_image" style="overflow: hidden; padding-left: 10px; width: 143px;">
                        <a href="<?=Core::$root_url?>/products/bereza-pendula-golden-gloud-pa-s10-100-140sm">
                            <img style="border: medium none; display: block; margin: auto;" height="109" src="<?=Core::$root_url.Request::$design->resizeimage($purchase['product']['images'][0]['filename'],NULL,109, 143);?>" />
                        </a>
                    </div>
                </td>
                <!-- Имена и миникартинки -->
                <td class="cart_td_padding td_align_top" style="text-align:left;">
                    <div class="cart_content" style="width:252;">
                        <div class="cart_product_name">
                            <a href="<?=Core::$root_url?>/products/<?=$purchase['product']['url']?>">
                                <?=$purchase['product_name']?>
                            </a>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                </td>
                <!-- количество -->
                <td class="cart_td_padding">
                    <div class="cart_amount">
                        <span class="amount"><?=$purchase['amount']?></span>
                        <span class="unit">шт.</span>
                    </div>
                </td>
                <!-- цена -->
                <td class="cart_td_padding">
                    <div class="cart_price">                
                        <span class="price"><?=Str::money($purchase['price'])?> р.</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <div class="hr_line" style="border-bottom: 1px dotted #D4D4D4; height: 1px; width: 100%;"></div>
                </td>
            </tr>
            <?php
            endforeach;
            ?>
            <tr>
                <td style="text-align:right;" colspan="4">
                    <div class="cart_price">
                        <div class="total_price">
                            <span class="string">Общая строимость:</span>
                            <span class="price"><b><?=Str::money($order['total_price'])?> р.</b></span>
                        </div>
                    </div>    
                </td>
            </tr>
        </tbody>
    </table>
</div>