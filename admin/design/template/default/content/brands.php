<?php
$design = Request::$design;

?>
<!-- Вкладки -->
<?php
$design->tabs('start');
echo Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_tabs_catalog',array('active'=>'brands'));
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

<!-- Поиск -->
<form method="get">
<div id="search">
    <input type="hidden" name="module" value="products" />
    <input class="search" type="text" name="keyword" value="<?=$keyword?>" />
    <input class="search_button" type="submit" value=""/>
</div>
</form>

<!-- Заголовок -->

<div id="header">
    <h1>Бренды</h1>
    <a class="add" href="<?=URL::query_root(array('module'=>'brand','return'=>urlencode(Url::root(NULL))),TRUE,'auto')?>">Добавить бренд</a>
</div>
<div class="clear"></div>
<hr />
<?php
if(!empty($brands)):
?>
<div id="main_list" class="categories brands">
    <form id="list_form" method="post">
    <input type="hidden" name="session_id" value="<?=session_id()?>">
            <div id="list" class="sortable">
            <?php
            foreach ($brands as $brand):
            ?>
                <div class="row">        
                    <div class="checkbox cell">
                        <input type="checkbox" name="check[]" value="<?=$brand['id']?>"/>                
                    </div>
                    <div class="cell">
                        <a href="<?=Url::query_root(array('module'=>'brand','id'=>$brand['id'],'return'=>urlencode(Url::root(NULL))))?>"><?=$brand['name']?></a>                  
                    </div>                    <div class="icons cell">
                        <a class="preview" title="" href="../type/<?=$brand['url']?>" target="_blank">Предпросмотр в новом окне</a>                
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
Нет брендов
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