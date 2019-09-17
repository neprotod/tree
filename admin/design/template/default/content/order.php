<?php
$design = Request::$design;
$variant_word = array('заказ','заказа','заказов');
$word = array('товар','товара','товаров');
$last_word = array('остался','осталось');

$image_module = Module::factory('image', TRUE);
?>

<!-- Вкладки -->
<?php
$design->tabs('start');
echo Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_tabs_orders',array('active'=>Request::param($order['status'],NULL,0)));
$design->tabs('end');
?>

<!-- Вкладки END -->
<?php
if($order['id']){
    Registry::i()->meta_title = 'Заказ №' . $order['id'];
}else{
    Registry::i()->meta_title = 'Новый заказ';
}
?>

<form method="post" id="order" class="categories orders" enctype="multipart/form-data">
    <input type="hidden" name="session_id" value="<?=session_id()?>">
    
     <div id="name">
        <input name="id" type="hidden" value="<?=$order['id']?>"/> 
        <h1>
            <?=(isset($order['id']))? 'Заказ №' . $order['id'] : 'Новый заказ'?>
            <select class="status" name="status">
                <option value='0' <?=($order['status'] == 0)? 'selected' : ''?>>Заказы</option>
                <option value='1' <?=($order['status'] == 1)? 'selected' : ''?>>Принят</option>
                <option value='2' <?=($order['status'] == 2)? 'selected' : ''?>>Выполнен</option>
                <option value='3' <?=($order['status'] == 3)? 'selected' : ''?>>Удален</option>
            </select>
        </h1>
        <a href="<?=Url::query_root(array('module'=>'order','id'=>$order['id'], 'view'=>'print'))?>" target="_blank"><img src="./design/images/printer.png" name="export" title="Печать заказа" alt="Печать заказа"></a>
        
        <div id="next_order">
            <?php
            if(!empty($prev_order)):
            ?>
            <a class="prev_order" href="<?=Url::query_root(array('id'=>$prev_order['id']),TRUE,'auto')?>">←</a>
            <?php
            endif;
            if(!empty($next_order)):
            ?>
            <a class="next_order" href="<?=Url::query_root(array('id'=>$next_order['id']),TRUE,'auto')?>">→</a>
            <?php
            endif;
            ?>
        </div>
    </div>
    
    <?php
    if(is_array($design->massage) AND !empty($design->massage)):
    ?>
    <div class="message message_success">
        <?php
        foreach($design->massage as $massage):
        ?>
        <span><?=$massage?></span>
        <?php
        endforeach;
        ?>
        <?php
        if($return = Request::get('return')):
        ?>
        <a class="button" href="<?=Request::get('return')?>">Вернуться</a>
        <?php
        endif;
        ?>
    </div>
    <?php
    endif;
    ?>

    <?php
    if(is_array($design->error) AND !empty($design->error)):
    ?>
    <div class="message message_error">
        <?php
        foreach($design->error as $error):
        ?>
        <span><?=$error?></span>

        <?php
        endforeach;
        if($return = Request::get('return')):
        ?>
        <a class="button" href="<?=$return?>">Вернуться</a>
        <?php
        endif;
        ?>
    </div>
    <?php
    endif;
    ?>
    <div id="order_details">
        <h2>Детали заказа <a href='#' class="edit_order_details"><img src='design/images/pencil.png' alt='Редактировать' title='Редактировать'></a></h2>
        
        <div id="user">
        <ul class="order_details">
            <li>
                <label class="property">Дата</label>
                <div class="edit_order_detail view_order_detail">
                    <?=Str::sql_date(Registry::i()->settings['date_format'],$order['date'])?> в <?=Str::sql_date('H:i',$order['date'])?>    
                </div>
            </li>
            <li>
                <label class="property">Имя</label> 
                <div class="edit_order_detail" style='display:none;'>
                    <input name="name" class="simpla_inp" type="text" value="<?=Request::param($order['name'],TRUE)?>" />
                </div>
                <div class="view_order_detail">
                    <?=Request::param($order['name'],TRUE)?>
                </div>
            </li>
            <li>
                <label class="property">Email</label>
                <div class="edit_order_detail" style='display:none;'>
                    <input name="email" class="simpla_inp" type="text" value="<?=Request::param($order['email'],TRUE)?>" />
                </div>
                <div class="view_order_detail">
                    <a href="mailto:<?=Request::param($order['email'],TRUE)?>?subject=Заказ%20№<?=$order['id']?>"><?=Request::param($order['email'],TRUE)?></a>
                </div>
            </li>
            <li>
                <label class="property">Телефон</label>
                <div class="edit_order_detail" style='display:none;'>
                    <input name="phone" class="simpla_inp " type="text" value="<?=Request::param($order['phone'],TRUE)?>" />
                </div>
                <div class="view_order_detail">
                    <?php
                    if(!empty($order['phone'])):
                    ?>
                    <span class="ip_call" data-phone="<?=Request::param($order['phone'],TRUE)?>" target="_blank"><?=Request::param($order['phone'],TRUE)?></span>
                    <?php
                    else:
                    ?>
                    <?=Request::param($order['phone'],TRUE)?>
                    <?php
                    endif;
                    ?>
                </div>
            </li>
            <li>
                <label class="property">Адрес <a href='http://maps.yandex.ru/' id="address_link" target="_blank"><img align="absmiddle" src='design/images/map.png' alt='Карта в новом окне' title='Карта в новом окне'></a></label>
                <div class="edit_order_detail" style='display:none;'>
                    <textarea name="address"><?=Request::param($order['address'],TRUE)?></textarea>
                </div>
                <div class="view_order_detail">
                    <?=Request::param($order['address'],TRUE)?>
                </div>
            </li>
            <li>
                <label class="property">Комментарий пользователя</label>
                <div class="edit_order_detail" style='display:none;'>
                <textarea name="comment"><?=Request::param($order['comment'],TRUE)?></textarea>
                </div>
                <div class="view_order_detail">
                    <?=nl2br(Request::param($order['comment'],TRUE))?>
                </div>
            </li>
        </ul>
        </div>

        <?php
        if(!empty($labels)):
        ?>
        <div class='layer'>
        <h2>Метка</h2>
        <!-- Метки -->
        <ul>
            <?php
            foreach($labels as $l):
            ?>
            <li>
            <label for="label_<?=$l['id']?>">
                <input id="label_<?=$l['id']?>" type="checkbox" name="order_labels[]" value="<?=$l['id']?>" <?=(in_array($l['id'], $order_labels))? 'checked' : ''?>>
                <span style="background-color:#<?=$l['color']?>;" class="order_label"></span>
                <?=$l['name']?>
            </label>
            </li>
            <?php
            endforeach;
            ?>
        </ul>
        <!-- Метки -->
        </div>
        <?php
        endif;
        ?>        
    
        <div class='layer'>
        <h2>
            Примечание 
            <a href='#' class="edit_note"><img src='design/images/pencil.png' alt='Редактировать' title='Редактировать'></a>
        </h2>
        <ul class="order_details">
            <li>
                <div class="edit_note" style='display:none;'>
                    <label class="property">Ваше примечание (не видно пользователю)</label>
                    <textarea name="note"><?=Request::param($order['note'],TRUE)?></textarea>
                </div>
                <div class="view_note" <?=(!$order['note'])? 'style="display:none;"' : '' ?>>
                    <label class="property">Ваше примечание (не видно пользователю)</label>
                    <div class="note_text"><?=Request::param($order['note'],TRUE)?></div>
                </div>
            </li>
        </ul>
        </div>
            
    </div>
    
        
    <div id="purchases">
        <div id="list" class="purchases">
            <?php
            if($purchases)
            foreach($purchases as $purchase):
            ?>
            <div class="row">
                <div class="image cell">
                    <input type="hidden" name="purchases[id][<?=$purchase['id']?>]" value='<?=$purchase['id']?>'>
                    <?php
                    $image = reset($purchase['product']['images']);
                    if(!empty($image)):
                    ?>
                    <img width="35" height="35" class="product_icon" src='<?=$image_module->resizeimage($image['filename'], array('height'=>35,'resizeWidth'=>35))?>'>
                    <?php
                    endif;
                    ?>
                </div>
                <div class="purchase_name cell">
                
                    <div class="purchase_variant">                
                    <span class="edit_purchase" style='display:none;'>
                    <select name="purchases[variant_id][<?=$purchase['id']?>]" <?=(count($purchase['product']['variants']) == 1 && $purchase['variant_name'] == '' && $purchase['variant']['sku'] == '')? 'style="display:none;"' : '' ?>>                    
                    <?php
                    var_dump($purchase);
                    if(!$purchase['variant']):
                    ?>
                        <option price="<?=$purchase['price']?>" amount="<?=$purchase['amount']?>" value="">
                            <?=Request::param($purchase['variant_name'],TRUE)?>
                            <?=($purchase['sku'])? "арт. {$purchase['sku']}" : ''?>
                        </option>
                    <?php
                    endif;
                    
                    if(!empty($purchase['product']['variants']))
                    foreach($purchase['product']['variants'] as $v):
                        if($v['stock']>0 || $v['id'] == $purchase['variant']['id']):
                    ?>
                        <option price="<?=$v['price']?>" amount="<?=$v['stock']?>" value="<?=$v['id']?>" <?=($v['id'] == $purchase['variant_id'])? 'selected' : ''?>>
                            <?=$v['name']?>
                            <?=($v['sku'])? "(арт. {$v['sku']})" : ''?>
                        </option>
                    <?php
                        endif;
                    endforeach;
                    ?>
                    </select>
                    </span>
                    <span class="view_purchase">
                        <?=$purchase['variant_name']?>
                        <?=($purchase['sku'])? "(арт. {$purchase['sku']})" : ''?>
                    </span>
                    </div>
            
                    <?php
                    if($purchase['product']):
                    ?>
                    <a class="related_product_name" href="<?=Url::query_root(array('module'=>'product','id'=>$purchase['product']['id'],'return'=>urlencode(Url::root(NULL))),TRUE)?>"><?=$purchase['product_name']?></a>
                    <?php
                    else:
                    ?>
                    <?=$purchase['product_name']?>
                    <?php
                    endif;
                    ?>
                </div>
                <div class="price cell">
                    <span class="view_purchase"><?=Str::money($purchase['price'])?></span>
                    <span class="edit_purchase" style='display:none;'>
                    <input type="text" name="purchases[price][<?=$purchase['id']?>]" value="<?=$purchase['price']?>" size=5>
                    </span>
                    <?=Registry::i()->currency['sign']?>.
                </div>
                <div class="amount cell">            
                    <span class="view_purchase">
                        <?=$purchase['amount']?> <?=Registry::i()->settings['units']?>
                    </span>
                    <span class="edit_purchase" style='display:none;'>
                        <?php
                        if($purchase['variant']):
                        ?>
                        <?=min(max($purchase['variant']['stock']+$purchase['amount']*($order['closed']),$purchase['amount']),$settings['max_order_amount'])?>
                        <?php
                        else:
                        ?>
                        <?=$purchase['amount']?>
                        <?php
                        endif;
                        ?>
                        <input name="purchases[amount][<?=$purchase['id']?>]" type="text" value="<?=$purchase['amount']?>" />
                    </span>            
                </div>
                <div class="icons cell">        
                    <?php
                    if(!$order['closed']):
                    ?>
                        <?php
                        if(!$purchase['product']):
                        ?>
                        <img src='design/images/error.png' alt='Товар был удалён' title='Товар был удалён' >
                        <?php
                        elseif(!$purchase['variant']):
                        ?>
                        <img src='design/images/error.png' alt='Вариант товара был удалён' title='Вариант товара был удалён' >
                        <?php
                        elseif($purchase['variant']['stock'] < $purchase['amount']):
                        ?>
                        <img src='design/images/error.png' alt='На складе остал<?=Translit::declension_words($purchase['variant']['stock'], $last_word)?> <?=$purchase['variant']['stock']?>  <?=Translit::declension_words($purchase['variant']['stock'], $word)?>' title='На складе остал<?=Translit::declension_words($purchase['variant']['stock'], $last_word)?> <?=$purchase['variant']['stock']?>  <?=Translit::declension_words($purchase['variant']['stock'], $word)?>'  >
                        <?php
                        endif;
                        ?>
                    <?php
                    endif;
                    ?>
                    <a href='#' class="delete" title="Удалить">Удалить</a>        
                </div>
                <div class="clear"></div>
            </div>
            <?php
            endforeach;
            ?>
            <div id="new_purchase" class="row" style='display:none;'>
                <div class="image cell">
                    <input type="hidden" name="purchases[id][]" value="">
                    <img width='35' height='35' class="product_icon" src="">
                </div>
                <div class="purchase_name cell">
                    <div class="purchase_variant">                
                        <select name="purchases[variant_id][]" style="display:none;"></select>
                    </div>
                    <a class="purchase_name" href=""></a>
                </div>
                <div class="price cell">
                    <input type="text" name="purchases[price][]" value="" size="5"> <?=Registry::i()->currency['sign']?>.
                </div>
                <div class="amount cell">
                    <input name="purchases[amount][]"></select>
                </div>
                <div class="icons cell">
                    <a href='#' class="delete" title="Удалить">Удалить</a>    
                </div>
                <div class="clear"></div>
            </div>
        </div>

        <div id="add_purchase" <?=(!empty($purchases))? 'style="display:none;"' : ''?>>
            <input type="text" name="related" id="add_purchase" class="input_autocomplete" placeholder='Выберите товар чтобы добавить его'>
        </div>
        <?php
        if(!empty($purchases)):
        ?>
        <a href='#' class="dash_link edit_purchases">редактировать покупки</a>


        <div class="subtotal">
        Всего<b> <?=Str::money(Request::param($subtotal,NULL,0))?> <?=Registry::i()->currency['sign']?>.</b>
        </div>
        <?php
        endif;
        ?>

        <div class="block discount layer">
            <h2>Скидка</h2>
            <input type="text" name="discount" value="<?=$order['discount']?>"> <span class="currency">%</span>        
        </div>

        <div class="subtotal layer">
        С учетом скидки<b> <?=Str::money(Request::param(round($subtotal-$subtotal*$order['discount']/100,2)))?> <?=Registry::i()->currency['sign']?>.</b>
        </div> 
        
        <?php
        if(!empty($deliveries)):
        ?>
        <div class="block delivery">
            <h2>Доставка</h2>
                    <select name="delivery_id">
                    <option value="0">Не выбрана</option>
                        <?php
                        foreach($deliveries as $d):
                        ?>
                        <option value="<?=$d['id']?>" <?=($d['id'] == $delivery['id'])? 'selected' : ''?>><?=$d['name']?></option>
                        <?php
                        endforeach;
                        ?>
                    </select>    
                    <input type="text" name="delivery_price" value="<?=$order['delivery_price']?>"> <span class="currency"><?=Registry::i()->currency['sign']?>.</span>
                    <div class="separate_delivery">
                        <input type="checkbox" id="separate_delivery" name="separate_delivery" value="1" <?=($order['separate_delivery'])? 'checked' : ''?>> <label  for="separate_delivery">оплачивается отдельно</label>
                    </div>
        </div>
        <?php
        endif;
        ?>
        <div class="total layer">
        Итого<b> <?=Str::money($order['total_price'])?> <?=Registry::i()->currency['sign']?>.</b>
        </div>
            
        <?php
        if($payment_methods):
        ?>
        <div class="block payment">
            <h2>Оплата</h2>
                    <select name="payment_method_id">
                        <option value="0">Не выбрана</option>
                        <?php
                        foreach($payment_methods as $pm):
                        ?>
                        <option value="<?=$pm['id']?>" <?=($pm['id'] == $payment_method['id'])? 'selected' : ''?>><?=$pm['name']?></option>
                        <?php
                        endforeach;
                        ?>
                    </select>
            
            <input type="checkbox" name="paid" id="paid" value="1" <?=($order['paid'])? 'checked' : ''?>> <label for="paid" <?=($order['paid'])? 'class="green"' : ''?>>Заказ оплачен</label>        
        </div>
        
        <div class="subtotal layer">
        К оплате<b> <!-- сделать преобразование {$order->total_price|convert:$payment_currency->id} {$payment_currency->sign}--></b>
        </div>
        <?php
        endif;
        ?>

        <!-- Временное место для оплаченных заказов -->
        <div style="margin-bottom:20px;">
            <input type="checkbox" name="paid" id="paid" value="1" <?=($order['paid'])? 'checked' : ''?>> <label for="paid" <?=($order['paid'])? 'class="green"' : ''?>>Заказ оплачен</label>        
        </div>
        <div class="block_save">
        <input type="checkbox" value="1" id="notify_user" name="notify_user">
        <label for="notify_user">Уведомить покупателя о состоянии заказа</label>

        <input class="button_green button_save" type="submit" name="" value="Сохранить" />
        </div>


    </div>
</form>

<script src="/media/js/autocomplete/jquery.autocomplete-min.js"></script>
<?php
// Подключаем редактор
include('tinymce_init.php');
?>
<script>
$(function() {
    // Раскраска строк
    function colorize(){
        $("#list div.row:even").addClass('even');
        $("#list div.row:odd").removeClass('even');
    }
    // Раскрасить строки сразу
    colorize();    
    
    // Удаление товара
    $(".purchases a.delete").live('click', function() {
         $(this).closest(".row").fadeOut(200, function() { $(this).remove(); });
         return false;
    });
    
    // Добавление товара 
    var new_purchase = $('.purchases #new_purchase').clone(true);
    $('.purchases #new_purchase').remove().removeAttr('id');

    $("input#add_purchase").autocomplete({
      serviceUrl:'<?=Url::root()?>/system/ajax/add_order_product.php',
      minChars:0,
      noCache: false, 
      onSelect:
          function(suggestion){
              new_item = new_purchase.clone().appendTo('.purchases');
              new_item.removeAttr('id');
              new_item.find('a.purchase_name').html(suggestion.data.name);
              new_item.find('a.purchase_name').attr('href', '<?=URL::query_root(array('module'=>'product'),TRUE)?>&id='+suggestion.data.id);
              
              // Добавляем варианты нового товара
              var variants_select = new_item.find('select[name*=purchases][name*=variant_id]');
            for(var i in suggestion.data.variants){
                  variants_select.append("<option value='"+suggestion.data.variants[i].id+"' price='"+suggestion.data.variants[i].price+"' amount='"+suggestion.data.variants[i].stock+"'>"+suggestion.data.variants[i].name+"</option>");
              }
              if(suggestion.data.variants.length>1 || suggestion.data.variants[0].name != '')
                  variants_select.show();
                                    
            variants_select.bind('change', function(){change_variant(variants_select);});
                change_variant(variants_select);
              
              if(suggestion.data.image)
                  new_item.find('img.product_icon').attr("src", suggestion.data.image);
              else
                  new_item.find('img.product_icon').remove();

            $("input#add_purchase").val('').focus().blur(); 
              new_item.show();
          },
        formatResult:
            function(suggestion, currentValue){
                var reEscape = new RegExp('(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'].join('|\\') + ')', 'g');
                var pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';
                  return (suggestion.data.image?"<img width='35' height='35' align=absmiddle src='"+suggestion.data.image+"'> ":'') + suggestion.value.replace(new RegExp(pattern, 'gi'), '<strong>$1<\/strong>');
            }
          
    });
    
    
     function change_variant(element){
        price = element.find('option:selected').attr('price');
        amount = element.find('option:selected').attr('amount');
        element.closest('.row').find('input[name*=purchases][name*=price]').val(price);
        
        // 
        amount_select = element.closest('.row').find('input[name*=purchases][name*=amount]');
        selected_amount = amount_select.val(1);
        /*amount_select.html('');
        for(i=1; i<=amount; i++)
            amount_select.append("<option value='"+i+"'>"+i+" {/literal}{$settings->units}{literal}</option>");
        amount_select.val(Math.min(selected_amount, amount));*/


        return false;
  }
    
    // Редактировать покупки
    $("a.edit_purchases").click( function() {
         $(".purchases span.view_purchase").hide();
         $(".purchases span.edit_purchase").show();
         $(".edit_purchases").hide();
         $("div#add_purchase").show();
         return false;
    });
  
    // Редактировать получателя
    $("div#order_details a.edit_order_details").click(function() {
         $("ul.order_details .view_order_detail").hide();
         $("ul.order_details .edit_order_detail").show();
         return false;
    });
  
    // Редактировать примечание
    $("#order_details .edit_note").click(function() {
         $("div.view_note").hide();
         $("div.edit_note").show();
         return false;
    });
  
    
  
    // Удалить пользователя
    $("#order_details .delete_user").click(function() {
        $('input[name="user_id"]').val(0);
        $('div.view_user').hide();
        $('div.edit_user').hide();
        return false;
    });

    // Посмотреть адрес на карте
    $("#address_link").attr('href', 'http://maps.yandex.ru/?text='+$('#order_details textarea[name="address"]').val());
    
    
    // Подтверждение удаления
    $('select[name*=purchases][name*=variant_id]').bind('change', function(){change_variant($(this));});
    $("input[name='status_deleted']").click(function() {
        if(!confirm('Подтвердите удаление'))
            return false;    
    });
});
</script>

<style>
.autocomplete-suggestions{
    background-color: #ffffff; 
    width: 100px; 
    overflow: hidden;
    border: 1px solid #e0e0e0;
    padding: 5px;
}
.autocomplete-suggestions .autocomplete-suggestion{
    cursor: default;
}
.autocomplete-suggestions .selected {
    background:#F0F0F0;
}
.autocomplete-suggestions div {
    padding:2px 5px; 
    white-space:nowrap; 
}
.autocomplete-suggestions strong {
    font-weight:normal; 
    color:#3399FF; 
}
</style>