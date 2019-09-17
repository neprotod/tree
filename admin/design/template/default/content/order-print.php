<?php
$design = Request::$design;
$variant_word = array('заказ','заказа','заказов');
?>
<!-- Вкладки -->
<?php
$design->tabs('start');
echo Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_tabs_orders',array('active'=>'orders'));
$design->tabs('end');
?>
<!-- Вкладки END -->

<!-- Title -->
<?php
        Registry::i()->meta_title = 'Заказы';
?>

<!-- Поиск -->
<form method="get">
<div id="search">
    <input type="hidden" name="module" value="orders" />
    <input class="search" type="text" name="keyword" value="<?=$keyword?>" />
    <input class="search_button" type="submit" value=""/>
</div>
</form>

<!-- Заголовок -->

<div id="header">
    <h1><?=($orders_count)? $orders_count : 'Нет'?> <?=Translit::declension_words($orders_count, $variant_word)?></h1>
    
    <a class="add" href="<?=URL::query_root(array('module'=>'order','return'=>urlencode(Url::root(NULL))),TRUE,'auto')?>">Добавить заказ</a>
</div>
<div class="clear"></div>
<hr />
<?php
if(!empty($orders)):
?>
<div id="main_list" class="categories orders">
    <form id="list_form" method="post">
    <input type="hidden" name="session_id" value="<?=session_id()?>">
            <div id="list" class="sortable">
            <?php
            foreach ($orders as $order):
            ?>
                <div class="<?=isset($order['paid'])? 'green' : '' ?> row">        
                    <div class="checkbox cell">
                        <input type="checkbox" name="check[]" value="<?=$order['id']?>"/>                
                    </div>
                    <div class="order_date cell">
                        <?=Str::sql_date(Registry::i()->settings['date_format'],$order['date'])?> в <?=Str::sql_date('H:i',$order['date'])?>    
                    </div>
                    <div class="order_name cell">
                        <a href="<?=Url::query_root(array('module'=>'order','id'=>$order['id'], 'return'=>urlencode(Url::root(NULL))))?>">Заказ №<?=$order['id']?></a>
                        <span class="customer"><?=Request::param($order['name'],TRUE)?></span>
                        <?php
                        if(isset($order['note'])):
                        ?>
                        <div class="note"><?=Request::param($order['note'],TRUE)?></div>
                        <?php
                        endif;
                        ?>
                    </div>
                    <div class="icons cell <?=($order['paid'])? 'paid': 'no-paid'?>">
                        <?php
                        if($order['paid']):
                        ?>
                            <span>Оплачен</span>
                        
                        <?php
                        else:
                        ?>
                            <span>Не оплачен</span>            
                        <?php
                        endif;
                        ?>             
                    </div>
                    <div class="name name_paid cell" style='white-space:nowrap;'>
                        <?php
                        if(isset($order['labels']))
                            foreach($order['labels'] as $l):
                        ?>
                        <div class="order_label" style="background-color:#<?=$l['color']?>;" title="<?=$l['name']?>"></div>
                        <?php
                            endforeach;
                        ?>
                        <?=Str::money(Request::param($order['total_price'],TRUE,0))?>
                        <?=Registry::i()->currency['sign']?>.
                    </div>
                    <div class="clear"></div>
                    <div class="icons cell bottom">
                        <a href="<?=Url::query_root(array('module'=>'order','id'=>$order['id'], 'view'=>'print'))?>" target="_blank" class="print">Печать заказа</a>        
                        <a class="delete" title="" href="#">Удалить</a>
                    </div>
                    <div class="clear"></div>
                </div>
            
            <?php
            endforeach;
            ?>
            </div>
        
        <div id="action">
            <label id="check_all" class="dash_link">Выбрать все</label>
            
            <span id="select">
                <span>Действие:</span>
                <select name="action">
                    <option value="none">Не указано</option>
                    <option value="delete">Удалить</option>
                </select> 
                <?php
                if(!empty($labels)):
                ?>
                <span style="padding-left:10px;">Метка:</span>
                <select name="label">
                    <option value="none">Не указана</option>
                    <?php
                    foreach($labels as $l):
                    ?>
                    <option value="set_label_<?=$l['id']?>">Отметить &laquo;<?=$l['name']?>&raquo;</option>
                    <?php
                    endforeach;
                    ?>
                    <?php
                    foreach($labels as $l):
                    ?>
                    <option value="unset_label_<?=$l['id']?>">Снять &laquo;<?=$l['name']?>&raquo;</option>
                    <?php
                    endforeach;
                    ?>
                </select>
                <?php
                endif;
                ?>
            </span>
            
            <input id="apply_action" class="button_green" type="submit" value="Применить">
            
        </div>
    
    </form>
</div>
<?php
else:
?>
Нет Заказов
<?php
endif;
?>
<script>
    // Раскраска строк
    function colorize(){
        $("#list div.row:even").addClass('even');
        $("#list div.row:odd").removeClass('even');
    }
    // Раскрасить строки сразу
    colorize();    
    
    // Выделить все
    $("#check_all").click(function() {
        $('#list input[type="checkbox"][name*="check"]:not(:disabled)').attr('checked', $('#list input[type="checkbox"][name*="check"]:not(:disabled):not(:checked)').length>0);
    });    
    
    // Удалить 
    $("a.delete").click(function() {
        $('#list input[type="checkbox"][name*="check"]').attr('checked', false);
        $(this).closest("div.row").find('input[type="checkbox"][name*="check"]:first').attr('checked', true);
        $(this).closest("form").find('select[name="action"] option[value=delete]').attr('selected', true);
        $(this).closest("form").submit();
    });

    
    // Подтвердить удаление
    $("form").submit(function() {
        if($('select[name="action"]').val()=='delete' && !confirm('Подтвердите удаление'))
            return false;    
    });
</script>