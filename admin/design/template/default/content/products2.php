<?php
$design = Request::$design;
$keyword = Request::param(Request::$design->keyword);
$products_count = Request::param(Request::$design->products_count);
$brand = Request::param(Request::$design->brand);
$brands = Request::param(Request::$design->brands);
$all_brands = Request::param(Request::$design->all_brands);
$word = array('товар','товара','товаров');
$search_word = array('Найден','Найдено','Найдено');
$variant_word = array('вариант','варианта','вариантов');
?>
<!-- Вкладки -->
<?php
$design->tabs('start');
?>
<li class="active"><a href="<?=Url::query_root(array('module'=>'products'))?>">Товары</a></li>
<li><a href="<?=Url::query_root(array('module'=>'categories'))?>">Категории</a></li>
<?php
$design->tabs('end');
?>
<!-- Вкладки END -->

<!-- Title -->
<?php
    if(isset($category))
        Registry::i()->meta_title = $category->name;
    else
        Registry::i()->meta_title = 'Товары';
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
    <?php
        if(!empty($products_count)):
            if(!empty($category->name) || !empty($brand['name'])):
                ?>
                
                <h1><?=$category->name?> <?=$brand['name']?> (<?=$products_count?> <?=Translit::declension_words($products_count, $word)?>)</h1>
                
                <?php
            
            elseif(!empty($keyword)):
                ?>
                
                <h1><?=Translit::declension_words($products_count, $search_word)?> <?=$products_count?> <?=Translit::declension_words($products_count, $word)?></h1>
                
                <?php
            else:
                ?>
                
                <h1><?=$products_count?> <?=Translit::declension_words($products_count, $word)?></h1>
                
                <?php
            endif;
        else:
            ?>
                
                <h1>Нет Товаров</h1>
                
                <?php
        endif;
        $add = array();
        
    ?>
    <a class="add" href="<?=URL::query_root(array('module'=>'product','return'=>urlencode(Url::root(NULL))),TRUE,'auto')?>">Добавить товар</a>
</div>
<div class="clear"></div>
<hr />
<div id="main_list" class="products_main">
    <?php
        $chdir = getcwd();
        chdir(realpath(dirname(__FILE__)));
        include 'pagination.php';
        chdir($chdir);
    ?>
    <?php
    if(!empty($products)):
    ?>
        <div id="expand">
        <!-- Свернуть/развернуть варианты -->
        <a href="#" class="dash_link" id="expand_all">Развернуть все варианты ↓</a>
        <a href="#" class="dash_link" id="roll_up_all" style="display:none;">Свернуть все варианты ↑</a>
        <!-- Свернуть/развернуть варианты (The End) -->
        </div>
        <div class="clear"></div>
    <?php
    endif;
    ?>
    <form id="list_form" method="post">
        <input type="hidden" name="session_id" value="<?=session_id()?>" />
        <div id="list">
        <?php
        foreach($products as $product):
            $visible = ($product['visible'] == 0)? 'invisible' :'';
            $featured = !empty($product['featured'])? 'featured' :'';
        ?>
            <div class="<?=$visible?> <?=$featured?> row">
                <input type="hidden" name="positions[<?=$product['id']?>]" value="<?=$product['position']?>" />
                <div class="move cell"><div class="move_zone"></div></div>
                <div class="checkbox cell">
                    <input type="checkbox" name="check[]" value="<?=$product['id']?>"/>                
                </div>
            
                <div class="image cell">
                    <?php
                    $image = Request::$design->resizeimage($product['images'][0]['filename'], NULL, 35, 35);
                    ?>
                    <a href="<?=Url::query_root(array('module'=>'product','id'=>$product['id'],'return'=>urlencode(Url::root(NULL))))?>"><img src="<?=$image?>" /></a>
                    <?php
                    ?>
                </div>
                <div class="name product_name cell">
                    
                    <div class="variants">
                        <ul>
                        <?php
                        foreach($product['variants'] as $variant):
                        ?>
                            <li class="<?=($variant['id'] != $product['variants'][0]['id'])? 'variant' : ''?>">
                                <i title="<?=$variant['name']?>"><?=$variant['name']?></i>
                                
                                <input class="price" type="text" name="price[<?=$variant['id']?>]" value="<?=$variant['price']?>" /> <?=Registry::i()->currencies['sign']?> 
                                
                                <input class="stock" type="text" name="stock[<?=$variant['id']?>]" value="<?=($variant['infinity'] == 1)? '∞': $variant['stock']?>" /> <?=Registry::i()->settings['units']?>
                            </li>
                        <?php
                        endforeach;
                        ?>
                        </ul>
                        <?php
                        $variants_num = count($product['variants']);
                        if($variants_num>1):
                        ?>
                            <div class="expand_variant">
                                <a class="dash_link expand_variant" href="#"><?=$variants_num?> <?=Translit::declension_words($variants_num, $variant_word)?> ↓</a>
                                <a class="dash_link roll_up_variant" style="display:none;" href="#"><?=$variants_num?> <?=Translit::declension_words($variants_num, $variant_word)?> ↑</a>
                            </div>
                        <?php
                        endif;
                        ?>
                    </div>
                    
                    <a href="<?=Url::query_root(array('module'=>'product','id'=>$product['id'], 'return'=>urlencode(Url::root(NULL))))?>"><?=$product['name']?></a>
                </div>
                <div class="clear"></div>
                <div class="icons cell_right">
                    <a class="preview"   title=""  href="../products/<?=$product['url']?>" target="_blank">Предпросмотр в новом окне</a>            
                    <a class="enable"    title=""  href="#">Активен</a>
                    <a class="duplicate" title=""  href="#">Дублировать</a>
                    <a class="delete"    title=""  href="#">Удалить</a>
                </div>

                <div class="clear"></div>
                <hr />
            </div>
        <?php
        endforeach;
        ?>
        </div>
        <div id="action">
            <label id="check_all" class="dash_link">Выбрать все</label>
        
            <span id="select">
            <select name="action">
                <option value="0">Не указано</option>
                <option value="enable">Сделать видимыми</option>
                <option value="disable">Сделать невидимыми</option>
                <option value="duplicate">Создать дубликат</option>
                <?php
                if($design->pages_count > 1):
                ?>
                <option value="move_to_page">Переместить на страницу</option>
                <?php
                endif;
                ?>
                <?php
                if(count($design->categories) > 1):
                ?>
                <option value="move_to_category">Переместить в категорию</option>
                <?php
                endif;
                ?>
                <?php
                if(count($all_brands) > 0):
                ?>
                <option value="move_to_brand">Указать бренд</option>
                <?php
                endif;
                ?>
                <option value="delete">Удалить</option>
            </select>
            </span>
            
            <span id="move_to_page">
                <select name="target_page">
                    <?php
                    for($i = 0;$i < $design->pages_count;$i++):
                    ?>
                    <option value="<?=$i+1?>"><?=$i+1?></option>
                    <?php
                    endfor;
                    ?>
                </select> 
            </span>
            
            <span id="move_to_category">
                <select name="target_category">
                    <option value="0">Не указанa</option>
                    <?php
                        echo $design->category_select($design->categories);
                    ?>
                </select> 
            </span>
            
            <span id="move_to_brand">
            <select name="target_brand">
                <option value="0">Не указан</option>
                <?php
                foreach(Request::$design->all_brands as $b):
                ?>
                <option value="<?=$b['id']?>"><?=$b['name']?></option>
                <?php
                endforeach;
                ?>
            </select> 
            </span>
        
            <input id="apply_action" class="button_green" type="submit" value="Применить" />        
        </div>
    </form>
    <?php
        include 'pagination.php';
    ?>
    <!-- Меню -->
</div>
<div id="right_menu">
    <?php
        $menu = Module::factory('menu', TRUE);
        echo $menu->admin_category($design->categories);
    ?>
    <?php
    if(!empty($brands)):
    ?>
    <!-- Бренды -->
    <ul>
        <li class="<?=($brand['id'] == NULL)? 'select':'droppable brand' ?>"><a href="<?=Url::query_root(array('module'=>'products','brand_id'=>NULL),TRUE,'auto')?>">Все бренды</a></li>
        <?php
        foreach($brands as $b):
        ?>
        <li brand_id="<?=$b['id']?>" class="<?=($brand['id'] == $b['id'])? 'select':'droppable brand' ?>">
            <a href="<?=Url::query_root(array('module'=>'products','brand_id'=>$b['id']),TRUE,'auto')?>"><?=$b['name']?></a>
        </li>
        <?php
        endforeach;
        ?>
    </ul>
    <!-- Бренды (The End) -->
    <?php
    endif;
    ?>
</div>

<script>
$(function() {

    // Сортировка списка
    $("#list").sortable({
        items:             ".row",
        tolerance:         "pointer",
        handle:            ".move_zone",
        scrollSensitivity: 40,
        opacity:           0.7, 

        helper: function(event, ui){        
            if($('input[type="checkbox"][name*="check"]:checked').size()<1) return ui;
            var helper = $('<div/>');
            $('input[type="checkbox"][name*="check"]:checked').each(function(){
                var item = $(this).closest('.row');
                helper.height(helper.height()+item.innerHeight());
                if(item[0]!=ui[0]) {
                    helper.append(item.clone());
                    $(this).closest('.row').remove();
                }
                else {
                    helper.append(ui.clone());
                    item.find('input[type="checkbox"][name*="check"]').attr('checked', false);
                }
            });
            return helper;            
        },    
         start: function(event, ui) {
              if(ui.helper.children('.row').size()>0)
                $('.ui-sortable-placeholder').height(ui.helper.height());
        },
        beforeStop:function(event, ui){
            if(ui.helper.children('.row').size()>0){
                ui.helper.children('.row').each(function(){
                    $(this).insertBefore(ui.item);
                });
                ui.item.remove();
            }
        },
        update:function(event, ui)
        {
            $("#list_form input[name*='check']").attr('checked', false);
            $("#list_form").ajaxSubmit(function() {
                colorize();
            });
        }
    });
    
    // для неактивных продуктов
    $("#list .invisible .enable").text('Неактивный');

    // Перенос товара на другую страницу
    $("#action select[name=action]").change(function() {
        if($(this).val() == 'move_to_page')
            $("span#move_to_page").show();
        else
            $("span#move_to_page").hide();
    });
    $("#pagination a.droppable").droppable({
        activeClass: "drop_active",
        hoverClass: "drop_hover",
        tolerance: "pointer",
        drop: function(event, ui){
            $(ui.helper).find('input[type="checkbox"][name*="check"]').attr('checked', true);
            $(ui.draggable).closest("form").find('select[name="action"] option[value=move_to_page]').attr("selected", "selected");        
            $(ui.draggable).closest("form").find('select[name=target_page] option[value='+$(this).html()+']').attr("selected", "selected");
            $(ui.draggable).closest("form").submit();
            return false;    
        }        
    });


    // Перенос товара в другую категорию
    $("#action select[name=action]").change(function() {
        if($(this).val() == 'move_to_category')
            $("span#move_to_category").show();
        else
            $("span#move_to_category").hide();
    });
    $("#right_menu .droppable.category").droppable({
        activeClass: "drop_active",
        hoverClass: "drop_hover",
        tolerance: "pointer",
        drop: function(event, ui){
            $(ui.helper).find('input[type="checkbox"][name*="check"]').attr('checked', true);
            $(ui.draggable).closest("form").find('select[name="action"] option[value=move_to_category]').attr("selected", "selected");    
            $(ui.draggable).closest("form").find('select[name=target_category] option[value='+$(this).attr('category_id')+']').attr("selected", "selected");
            $(ui.draggable).closest("form").submit();
            return false;            
        }
    });


    // Перенос товара в другой бренд
    $("#action select[name=action]").change(function() {
        if($(this).val() == 'move_to_brand')
            $("span#move_to_brand").show();
        else
            $("span#move_to_brand").hide();
    });
    $("#right_menu .droppable.brand").droppable({
        activeClass: "drop_active",
        hoverClass: "drop_hover",
        tolerance: "pointer",
        drop: function(event, ui){
            $(ui.helper).find('input[type="checkbox"][name*="check"]').attr('checked', true);
            $(ui.draggable).closest("form").find('select[name="action"] option[value=move_to_brand]').attr("selected", "selected");            
            $(ui.draggable).closest("form").find('select[name=target_brand] option[value='+$(this).attr('brand_id')+']').attr("selected", "selected");
            $(ui.draggable).closest("form").submit();
            return false;            
        }
    });


    // Если есть варианты, отображать ссылку на их разворачивание
    if($("li.variant").size()>0)
        $("#expand").show();


    // Раскраска строк
    function colorize()
    {
        $("#list div.row:even").addClass('even');
        $("#list div.row:odd").removeClass('even');
    }
    // Раскрасить строки сразу
    colorize();


    // Показать все варианты
    $("#expand_all").click(function() {
        $("a#expand_all").hide();
        $("a#roll_up_all").show();
        $("a.expand_variant").hide();
        $("a.roll_up_variant").show();
        $(".variants ul li.variant").fadeIn('fast');
        return false;
    });


    // Свернуть все варианты
    $("#roll_up_all").click(function() {
        $("a#roll_up_all").hide();
        $("a#expand_all").show();
        $("a.roll_up_variant").hide();
        $("a.expand_variant").show();
        $(".variants ul li.variant").fadeOut('fast');
        return false;
    });

 
    // Показать вариант
    $("a.expand_variant").click(function() {
        $(this).closest("div.cell").find("li.variant").fadeIn('fast');
        $(this).closest("div.cell").find("a.expand_variant").hide();
        $(this).closest("div.cell").find("a.roll_up_variant").show();
        return false;
    });

    // Свернуть вариант
    $("a.roll_up_variant").click(function() {
        $(this).closest("div.cell").find("li.variant").fadeOut('fast');
        $(this).closest("div.cell").find("a.roll_up_variant").hide();
        $(this).closest("div.cell").find("a.expand_variant").show();
        return false;
    });

    // Выделить все
    $("#check_all").click(function() {
        $('#list input[type="checkbox"][name*="check"]').attr('checked', $('#list input[type="checkbox"][name*="check"]:not(:checked)').length>0);
    });    

    // Удалить товар
    $("a.delete").click(function() {
        $('#list input[type="checkbox"][name*="check"]').attr('checked', false);
        $(this).closest("div.row").find('input[type="checkbox"][name*="check"]').attr('checked', true);
        $(this).closest("form").find('select[name="action"] option[value=delete]').attr('selected', true);
        $(this).closest("form").submit();
    });
    
    // Дублировать товар
    $("a.duplicate").click(function() {
        $('#list input[type="checkbox"][name*="check"]').attr('checked', false);
        $(this).closest("div.row").find('input[type="checkbox"][name*="check"]').attr('checked', true);
        $(this).closest("form").find('select[name="action"] option[value=duplicate]').attr('selected', true);
        $(this).closest("form").submit();
    });
    
    // Показать товар
    $("a.enable").click(function() {
        var icon        = $(this);
        var line        = icon.closest("div.row");
        var id          = line.find('input[type="checkbox"][name*="check"]').val();
        var state       = line.hasClass('invisible')?1:0;
        $.ajax({
            type: 'POST',
            url: '<?=Url::root()?>/system/ajax/update_object.php',
            data: {'object': 'product', 'id': id, 'values': {'visible': state}, 'session_id': '<?=session_id()?>'},
            success: function(data){
                if(state){
                    line.removeClass('invisible');
                    icon.text('Активен');
                }
                else{
                    line.addClass('invisible');
                    icon.text('Неактивный');
                }
            },
            dataType: 'text'
        });
        return false;    
    });

    // Сделать хитом
    $("a.featured").click(function() {
        var icon        = $(this);
        var line        = icon.closest("div.row");
        var id          = line.find('input[type="checkbox"][name*="check"]').val();
        var state       = line.hasClass('featured')?0:1;
        icon.addClass('loading_icon');
        $.ajax({
            type: 'POST',
            url: '<?=Url::root()?>/system/ajax/update_object.php',
            data: {'object': 'product', 'id': id, 'values': {'featured': state}, 'session_id': '{/literal}{$smarty.session.id}{literal}'},
            success: function(data){
                icon.removeClass('loading_icon');
                if(state)
                    line.addClass('featured');
                else
                    line.removeClass('featured');
            },
            dataType: 'json'
        });    
        return false;    
    });


    // Подтверждение удаления
    $("form").submit(function() {
        if($('select[name="action"]').val()=='delete' && !confirm('Подтвердите удаление'))
            return false;    
    });
    
    
    // Бесконечность на складе
    $("input[name*=stock]").focus(function() {
        if($(this).val() == '∞')
            $(this).val('');
        return false;
    });
    $("input[name*=stock]").blur(function() {
        if($(this).val() == '')
            $(this).val('∞');
    });
    
    // Считывает GET переменные из URL страницы и возвращает их как ассоциативный массив.
    function getUrlVars(){
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for(var i = 0; i < hashes.length; i++){
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    }
    // Запись скролла
    $(".image a, .product_name a").each(function(){
        var position = Math.round($(this).offset().top);
        href = $(this).attr('href');
        $(this).attr('href',href+'&scroll='+position);
    });
    // если есть scroll
    if(getUrlVars().scroll > 0){
        $(window).scrollTop(getUrlVars().scroll - 80);
    }
});

</script>