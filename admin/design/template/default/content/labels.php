<?php
$design = Request::$design;

?>
<!-- Вкладки -->
<?php
$design->tabs('start');
echo Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_tabs_orders',array('active'=>4));
$design->tabs('end');
?>
<!-- Вкладки END -->

<!-- Title -->
<?php
    if(isset($category))
        Registry::i()->meta_title = $category->name;
    else
        Registry::i()->meta_title = 'Бренды';
?>

<!-- Заголовок -->

<div id="header">
    <h1>Метки</h1>
    <a class="add" href="<?=URL::query_root(array('module'=>'label','status'=>0,'return'=>urlencode(Url::root(NULL))),TRUE,'auto')?>">Добавить метку</a>
</div>
<div class="clear"></div>
<hr />
<?php
if(!empty($labels)):
?>
<div id="main_list" class="categories brands labels">
    <form id="list_form" method="post">
    <input type="hidden" name="session_id" value="<?=session_id()?>">
            <div id="list" class="sortable">
            <?php
            foreach ($labels as $label):
            ?>
                <div class="row">
                    <input type="hidden" name="positions[<?=$label['id']?>]" value="<?=$label['position']?>" />
                    <div class="move cell"><div class="move_zone"></div></div>
                    <div class="checkbox cell">
                        <input type="checkbox" name="check[]" value="<?=$label['id']?>"/>                
                    </div>
                    <div class="name cell">
                        <span style="background-color:#<?=$label['color']?>;" class="order_label"></span>
                        <a href="<?=Url::query_root(array('module'=>'label','id'=>$label['id'],'return'=>urlencode(Url::root(NULL))),TRUE,'auto')?>"><?=$label['name']?></a>                  
                    </div>                    
                    <div class="icons cell">            
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
            <select name="action">
                <option value="delete">Удалить</option>
            </select>
            </span>
            
            <input id="apply_action" class="button_green" type="submit" value="Применить">
            
        </div>
    
    </form>
</div>
<?php
else:
?>
Нет меток
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