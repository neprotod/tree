<?php
$design = Request::$design;
$product = Request::param(Request::$design->product);
$brands = Request::param(Request::$design->brands);
$categories = Request::param(Request::$design->categories);
$categories = Request::param(Request::$design->categories);
$product_categories = Request::param(Request::$design->product_categories);
$product_variants = Request::param(Request::$design->product_variants);
$features = Request::param(Request::$design->features);
$options = Request::param(Request::$design->options);
$product_images = Request::param(Request::$design->product_images,NULL,array());
?>

<?php
$design->tabs('start');
echo Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_tabs_catalog',array('active'=>'products','return'=>TRUE));
$design->tabs('end');
?>

<script src="/media/js/autocomplete/jquery.autocomplete-min.js"></script>

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
    <a class="link" target="_blank" href="../products/<?= Request::param($product['url'])?>">Открыть товар на сайте</a>
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
<form method="post" id="product" enctype="multipart/form-data">
    <input type="hidden" name="session_id" value="<?=session_id()?>">
    
     <div id="name">
        <input class="name" name="name" type="text" value="<?=Request::param($product['name'],TRUE)?>"/> 
        <input name="id" type="hidden" value="<?=Request::param($product['id'])?>"/> 
        <div class="checkbox">
            <input name="visible" value='1' type="checkbox" id="active_checkbox" <?=($product['visible'] == 1)? 'checked="checked"' : '' ?> /> <label for="active_checkbox">Активен</label>
        </div>
    </div>
    <?php
    if(!empty($brands)):    
    ?>
    <div id="product_brand">
        <label>Бренд</label>
        <select name="brand_id">
            <option value="0" <?=(empty($product['brand_id']))? 'selected="selected"' : '' ?> brand_name="">Не указан</option>
            <?php
            foreach($brands as $brand):
            ?>
                <option value="<?=Request::param($brand['id'])?>" <?=($product['brand_id'] == $brand['id'])? 'selected="selected"' : '' ?> brand_name="<?=Request::param($brand['name'],TRUE)?>"><?=Request::param($brand['name'],TRUE)?></option>
            <?php
            endforeach;
            ?>
        </select>
    </div>
    <?php
    endif;
    ?>
    <!-- Категории -->
    <?php
    if(!empty($categories)):    
    ?>
    <div id="product_categories">
        <label>Категория</label>
        <div>
            <ul>
                <?php
                $foreachFerst = TRUE;
                foreach($product_categories as $product_category):
                ?>
                <li>
                    <select name="categories[]">
                        <?php
                            echo $design->category_select($categories,'',TRUE,$product_category['id']);
                        ?>
                    </select>
                    
                    <span style="<?=($foreachFerst === TRUE)? '' : 'display:none;' ?>" class="add"><i class="dash_link">Дополнительная категория</i></span>
            
                    <span style="<?=($foreachFerst === FALSE)? '' : 'display:none;' ?>" class="delete"><i class="dash_link">Удалить</i></span>
    
                </li>
                <?php
                $foreachFerst = FALSE;
                endforeach;
                ?>    
            </ul>
        </div>
    </div>
    <?php
    endif;
    ?>
    
    <!-- Варианты товара -->
    <div id="variants_block">
        <ul id="header">
            <li class="variant_move"></li>
            <li class="variant_name">Название варианта</li>    
            <li class="variant_sku">Артикул</li>    
            <li class="variant_price">Цена, <?=Registry::i()->currency['sign']?></li>    
            <li class="variant_discount">Старая, <?=Registry::i()->currency['sign']?></li>    
            <li class="variant_amount">Кол-во</li>
        </ul>
        <div id="variants">
        <?php
        foreach($product_variants as $variant):
        ?>
        <ul>
            <li class="variant_move"><div class="move_zone"></div></li>
            <li class="variant_name">
                <input name="variants[id][]" type="hidden" value="<?=Request::param($variant['id'])?>" /><input name="variants[name][]" type="text" value="<?=Request::param($variant['name'],TRUE)?>" /> 
                <a class="del_variant" href=""><img src="<?=$design->root?>/img/cross.png" alt="" /></a>
            </li>
            <li class="variant_sku">       
                <input name="variants[sku][]" type="text" value="<?=Request::param($variant['sku'],TRUE)?>" />
            </li>
            <li class="variant_price">
                <input name="variants[price][]" type="text" value="<?=Request::param($variant['price'],TRUE)?>" />
            </li>
            <li class="variant_discount">
                <input name="variants[compare_price][]" type="text" value="<?=Request::param($variant['compare_price'],TRUE)?>" />
            </li>
            <li class="variant_amount">
                <input name="variants[stock][]" type="text" value="<?=($variant['infinity'] == 1)? '∞': $variant['stock']?>" /><span><?=Registry::i()->settings['units']?></span>
            </li>

        </ul>
        <?php
        endforeach;
        ?>
        </div>
        <ul id="new_variant" style="display:none;">
            <li class="variant_move"><div class="move_zone"></div></li>
            <li class="variant_name"><input name="variants[id][]" type="hidden" value="" /><input name="variants[name][]" type="" value="" /><a class="del_variant" href=""><img src="<?=$design->root?>/img/cross.png" alt="" /></a></li>
            <li class="variant_sku"><input name="variants[sku][]" type="" value="" /></li>
            <li class="variant_price"><input  name="variants[price][]" type="" value="" /></li>
            <li class="variant_discount"><input name="variants[compare_price][]" type="" value="" /></li>
            <li class="variant_amount"><input name="variants[stock][]" type="" value="∞" /><?=Registry::i()->settings['units']?></li>
        </ul>

        <input class="button_green button_save" type="submit" name="" value="Сохранить" />
        <span class="add" id="add_variant"><i class="dash_link">Добавить вариант</i></span>
     </div>
    <div class="clear"></div>
    <!-- Варианты товара (The End)--> 
    <!-- Левая колонка свойств товара -->
    <div id="column_left">
            
        <!-- Параметры страницы -->
        <div class="block layer">
            <h2>Параметры страницы</h2>
            <ul>
                <li>
                    <label class="property">Адрес</label>
                    <div class="page_url"> /products/</div>
                    <input name="url" class="page_url" type="text" value="<?=Request::param($product['url'],TRUE)?>" />
                </li>
                <li>
                    <label class="property">Заголовок</label>
                    <input name="meta_title" class="inp" type="text" value="<?=Request::param($product['meta_title'],TRUE)?>" />
                </li>
                <li>
                    <label class="property">Описание</label>
                    <textarea name="meta_description" class="inp" /><?=Request::param($product['meta_description'],TRUE)?></textarea>
                </li>
            </ul>
        </div>
        <!-- Параметры страницы (The End)-->
                
        <div class="block layer" {if !$categories}style='display:none;'{/if}>
            <h2>Свойства товара
                <a href="#" id="properties_wizard">Открыть все существующие свойства</a>
            </h2>
            
            <ul class="prop_ul">
                <?php
                foreach($features as $feature):
                if(!isset($options[$feature['id']]['value']))
                ?>
                    <li feature_id="<?=Request::param($feature['id'])?>">
                        <label class="property"><?=Request::param($feature['name'])?></label>
                        
                        <input class="inp" type="text" name="options[<?=Request::param($feature['id'])?>]" value="<?=Request::param($options[$feature['id']]['value'])?>" />
                    </li>
                <?php
                endforeach;
                ?>
            </ul>
            <!-- Новые свойства -->
            <ul class="new_features">
                <li id="new_feature">
                    <label class="property">
                        <input type="text" name="new_features_names[]" />
                    </label>
                    <input class="inp" type="text" name="new_features_values[]" />
                </li>
            </ul>
            <span class="add">
                <i class="dash_link" id="add_new_feature">Добавить новое свойство</i>
            </span>
            <input class="button_green button_save" type="submit" name="" value="Сохранить" />            
        </div>
        <!-- Свойства товара (The End)-->    
        
    </div>
    
    <!-- Правая колонка свойств товара -->    
    <div id="column_right">
        
        <!-- Изображения товара -->    
        <div class="block layer images">
            <h2>
                Изображения товара
            </h2>
            <ul id="sort_bar">
                <?php
                foreach($product_images as $image):;
                ?>
                <li>
                    
                    <img src="<?=Request::$design->resizeimage($image['filename'], NULL, 100, 100)?>" alt="" />
                    <span><?=$image['filename']?></span>
                    <a href='#' class="delete">Удалить</a>
                    <input type="hidden" name='images[]' value='<?=$image['id']?>' />
                </li>
                <div class="clear"></div>
                <?php
                endforeach;
                ?>
            </ul>
            <h2 id="images_wizard"><span class="dash_link">Загрузить с сервера</span></h2>
            <ul id="new_image">
                
                <div class="clear"></div>
            </ul>
            <div id="add_image"></div>
            <span class="upload_image">
                <i class="dash_link" id="upload_image">Добавить изображение</i>
            </span> 
            или 
            <span class="add_image_url">
                <i class="dash_link" id="add_image_url">загрузить из интернета</i>
            </span>
            <div id="dropZone">
                <div id="dropMessage">Перетащите файлы с папки</div>
                <input type="file" name="dropped_images[]" multiple class="dropInput">
            </div>
        </div>
        

        <input class="button_green button_save" type="submit" name="" value="Сохранить" />
        
    </div>
    <!-- Правая колонка свойств товара (The End)--> 
    <!-- Описагние товара -->
    <div class="block layer">
        <h2>Краткое описание</h2>
        <textarea name="annotation" class="editor_small"><?=Request::param($product['annotation'])?></textarea>
    </div>
    <div class="block">        
        <h2>Полное  описание</h2>
        <textarea name="body" class="editor_large"><?=Request::param($product['body'])?></textarea>
    </div>
    
    <!-- Описание товара (The End)-->
    <input class="button_green button_save" type="submit" name="" value="Сохранить" />
</form>








<?php
// Подключаем редактор
include('tinymce_init.php');
?>
<script>
$(function() {

    // Добавление категории
    $('#product_categories .add').click(function() {
        $("#product_categories ul li:last").clone(false).appendTo('#product_categories ul').fadeIn('slow').find("select[name*=categories]:last").focus();
        $("#product_categories ul li:last span.add").hide();
        $("#product_categories ul li:last span.delete").show();
        return false;        
    });

    // Удаление категории
    $("#product_categories .delete").live('click', function() {
        $(this).closest("li").fadeOut(200, function() { $(this).remove(); });
        return false;
    });

    // Сортировка вариантов
    $("#variants_block").sortable({ items: '#variants ul' , axis: 'y',  cancel: '#header', handle: '.move_zone' });
    // Сортировка вариантов
    $("table.related_products").sortable({ items: 'tr' , axis: 'y',  cancel: '#header', handle: '.move_zone' });

    
    // Сортировка связанных товаров
    $(".sortable").sortable({
        items: "div.row",
        tolerance:"pointer",
        scrollSensitivity:40,
        opacity:0.7,
        handle: '.move_zone'
    });
        

    // Сортировка изображений
    $(".images ul").sortable({ tolerance: 'pointer'});

    // Удаление изображений
    $(".images a.delete").live('click', function() {
         $(this).closest("li").fadeOut(200, function() { $(this).remove(); });
         return false;
    });
    
    // Удаление загруженного файла с компьютера
    $(".load_image .delete").live('click', function() {
         $(this).closest(".load_image").fadeOut(200, function() { $(this).remove(); });
         return false;
    });
    // Загрузить изображение с компьютера
    $('#upload_image').click(function() {
        $('<div class="load_image"><input class="upload_image" name=images[] type="file" multiple  accept="image/jpeg,image/png,image/gif" /> <a href="#" class="delete">Удалить</a></div>').appendTo('div#add_image').children('input').focus().click();
    });
    // Или с URL
    $('#add_image_url').click(function() {
        $("<input class='remote_image' name=images_urls[] type=text value='http://'>").appendTo('div#add_image').focus().select();
    });
    // Или перетаскиванием
    if(window.File && window.FileReader && window.FileList)
    {
        $("#dropZone").show();
        $("#dropZone").on('dragover', function (e){
            $(this).css('border', '1px solid #8cbf32');
        });
        $(document).on('dragenter', function (e){
            $("#dropZone").css('border', '1px dotted #8cbf32').css('background-color', '#c5ff8d');
        });
    
        dropInput = $('.dropInput').last().clone();
        
        function handleFileSelect(evt){
            var files = evt.target.files; // FileList object
            // Loop through the FileList and render image files as thumbnails.
            for (var i = 0, f; f = files[i]; i++) {
                // Only process image files.
                if (!f.type.match('image.*')) {
                    continue;
                }
            var reader = new FileReader();
            // Closure to capture the file information.
            reader.onload = (function(theFile) {
                return function(e) {
                    // Render thumbnail.
                    $("<li class=wizard><img height="+100+" onerror='$(this).closest(\"li\").remove();' src='"+e.target.result+"' /><a href='' class='delete'>Удалить</a><input name=images_urls[] type=hidden value='"+theFile.name+"'></li>").appendTo('.images #sort_bar');
                    temp_input =  dropInput.clone();
                    $('.dropInput').hide();
                    $('#dropZone').append(temp_input);
                    $("#dropZone").css('border', '1px solid #d0d0d0').css('background-color', '#ffffff');
                    clone_input.show();
                };
              })(f);
        
              // Read in the image file as a data URL.
              reader.readAsDataURL(f);
            }
        }
        $('.dropInput').live("change", handleFileSelect);
    };

    // Удаление варианта
    $('a.del_variant').click(function() {
        if($("#variants ul").size()>1)
        {
            $(this).closest("ul").fadeOut(200, function() { $(this).remove(); });
        }
        else
        {
            $('#variants_block .variant_name input[name*=variant][name*=name]').val('');
            $('#variants_block .variant_name').hide('slow');
            $('#variants_block').addClass('single_variant');
        }
        return false;
    });

    // Загрузить файл к варианту
    $('#variants_block a.add_attachment').click(function() {
        $(this).hide();
        $(this).closest('li').find('div.browse_attachment').show('fast');
        $(this).closest('li').find('input[name*=attachment]').attr('disabled', false);
        return false;        
    });
    
    // Удалить файл к варианту
    $('#variants_block a.remove_attachment').click(function() {
        closest_li = $(this).closest('li');
        closest_li.find('.attachment_name').hide('fast');
        $(this).hide('fast');
        closest_li.find('input[name*=delete_attachment]').val('1');
        closest_li.find('a.add_attachment').show('fast');
        return false;        
    });


    // Добавление варианта
    var variant = $('#new_variant').clone(true);
    $('#new_variant').remove().removeAttr('id');
    $('#variants_block span.add').click(function() {
        if(!$('#variants_block').is('.single_variant'))
        {
            $(variant).clone(true).appendTo('#variants').fadeIn('slow').find("input[name*=variant][name*=name]").focus();
        }
        else
        {
            $('#variants_block .variant_name').show('slow');
            $('#variants_block').removeClass('single_variant');        
        }
        return false;        
    });
    
    
    function show_category_features(category_id)
    {
        $('ul.prop_ul').empty();
        $.ajax({
            url: "<?=Url::root()?>/system/ajax/get_features.php",
            data: {category_id: category_id, product_id: $("input[name=id]").val()},
            dataType: 'json',
            success: function(data){
                for(i=0; i<data.length; i++)
                {
                    feature = data[i];
                    
                    line = $("<li><label class=property></label><input class='inp' type='text'/></li>");
                    var new_line = line.clone(true);
                    new_line.find("label.property").text(feature.name);
                    new_line.find("input").attr('name', "options["+feature.id+"]").val(feature.value);
                    new_line.appendTo('ul.prop_ul').find("input")
                    .autocomplete({
                        serviceUrl:'<?=Url::root()?>/system/ajax/options_autocomplete.php',
                        minChars:0,
                        params: {feature_id:feature.id},
                        noCache: false
                    });
                }
            }
        });
        return false;
    }
    
    // Изменение набора свойств при изменении категории
    
    //////////
    ////Отключено
    //////////
    
    /*$('select[name="categories[]"]:first').change(function() {
        show_category_features($("option:selected",this).val());
    });*/

    // Автодополнение свойств
    $('ul.prop_ul input[name*=options]').each(function(index) {
        feature_id = $(this).closest('li').attr('feature_id');
        $(this).autocomplete({
            serviceUrl:'<?=Url::root()?>/system/ajax/options_autocomplete.php',
            minChars:0,
            params: {feature_id:feature_id},
            noCache: false
        });
    });     
    
    // Добавление нового свойства товара
    var new_feature = $('#new_feature').clone(true);
    $('#new_feature').remove().removeAttr('id');
    $('#add_new_feature').click(function() {
        $(new_feature).clone(true).appendTo('ul.new_features').fadeIn('slow').find("input[name*=new_feature_name]").focus();
        return false;        
    });

    
  

    // infinity
    $("input[name*=variant][name*=stock]").focus(function() {
        if($(this).val() == '∞')
            $(this).val('');
        return false;
    });

    $("input[name*=variant][name*=stock]").blur(function() {
        if($(this).val() == '')
            $(this).val('∞');
    });
    /*
    // Волшебные изображения
    name_changed = false;
    $("input[name=name]").change(function() {
        name_changed = true;
        images_loaded = 0;
    });    
    images_num = 8;
    images_loaded = 0;
    old_wizar_dicon_src = $('#images_wizard img').attr('src');
    $('#images_wizard').click(function() {
        
        $('#images_wizard img').attr('src', 'design/images/loader.gif');
        if(name_changed)
            $('div.images ul li.wizard').remove();
        name_changed = false;
        key = $('input[name=name]').val();
        $.ajax({
              url: "<?=Url::root()?>/system/ajax/get_images.php",
                  data: {keyword: key, start: images_loaded},
                  dataType: 'json',
                  success: function(data){
                    for(i=0; i<Math.min(data.length, images_num); i++)
                    {
                        image_url = data[i];
                        $("<li class=wizard><a href='' class='delete'><img src='design/images/cross-circle-frame.png'></a><a href='"+image_url+"' target=_blank><img onerror='$(this).closest(\"li\").remove();' src='"+image_url+"' /><input name=images_urls[] type=hidden value='"+image_url+"'></a></li>").appendTo('div .images ul');
                    }
                    $('#images_wizard img').attr('src', old_wizar_dicon_src);
                    images_loaded += images_num;
                  }
        });
        return false;
    });
    */
    // Волшебное описание
    name_changed = false;
    $("input[name=name]").change(function() {
        name_changed = true;
    });    
    old_prop_wizard_icon_src = $('#properties_wizard img').attr('src');
    $('#properties_wizard').click(function() {
        
        $('#properties_wizard img').attr('src', 'design/images/loader.gif');
        if(name_changed)
            $('div.images ul li.wizard').remove();
        name_changed = false;
        key = $('input[name=name]').val();
        $.ajax({
              url: "<?=Url::root()?>/system/ajax/get_info.php",
                  data: {keyword: key},
                  dataType: 'json',
                  success: function(data){
                    $('#properties_wizard img').attr('src', old_prop_wizard_icon_src);
                      if(data)
                      {
                          $('li#new_feature').remove();
                        for(i=0; i<data.options.length; i++)
                        {
                            option_name = data.options[i].name;
                            option_value = data.options[i].value;
                            // Добавление нового свойства товара
                            exists = false;
                                                        
                            if(!$('label.property:visible').filter(function(){ return $(this).text().toLowerCase() === option_name.toLowerCase();}).closest('li').find('input[name*=options]').val(option_value).length)
                            {
                                f = $(new_feature).clone(true);
                                f.find('input[name*=new_features_names]').val(option_name);
                                f.find('input[name*=new_features_values]').val(option_value);
                                f.appendTo('ul.new_features').fadeIn('slow').find("input[name*=new_feature_name]");
                            }
                           }
                           
                       }                    
                },
                error: function(xhr, textStatus, errorThrown){
                    alert("Error: " +textStatus);
                   }
        });
        return false;
    });
    

    // Автозаполнение мета-тегов
    meta_title_touched = true;
    meta_description_touched = true;
    url_touched = true;
    
    if($('input[name="meta_title"]').val() == generate_meta_title() || $('input[name="meta_title"]').val() == '')
        meta_title_touched = false;
    
    if($('textarea[name="meta_description"]').val() == generate_meta_description() || $('textarea[name="meta_description"]').val() == '')
        meta_description_touched = false;
    if($('input[name="url"]').val() == generate_url() || $('input[name="url"]').val() == '')
        url_touched = false;
        
    $('input[name="meta_title"]').change(function() { meta_title_touched = true; });
    $('textarea[name="meta_description"]').change(function() { meta_description_touched = true; });
    $('input[name="url"]').change(function() { url_touched = true; });
    
    $('input[name="name"]').keyup(function() { set_meta(); });
    $('select[name="brand_id"]').change(function() { set_meta(); });
    $('select[name="categories[]"]').change(function() { set_meta(); });
/**/
});//конечная функция

function set_meta()
{
    if(!meta_title_touched)
        $('input[name="meta_title"]').val(generate_meta_title());
    if(!meta_description_touched)
        $('textarea[name="meta_description"]').val(generate_meta_description());
    if(!url_touched)
        $('input[name="url"]').val(generate_url());
}

function generate_meta_title()
{
    name = $('input[name="name"]').val();
    return name;
}


function generate_meta_description()
{
    if(typeof(tinyMCE.get("annotation")) =='object')
    {
        description = tinyMCE.get("annotation").getContent().replace(/(<([^>]+)>)/ig," ").replace(/(\&nbsp;)/ig," ").replace(/^\s+|\s+$/g, '').substr(0, 512);
        return description;
    }
    else
        return $('textarea[name=annotation]').val().replace(/(<([^>]+)>)/ig," ").replace(/(\&nbsp;)/ig," ").replace(/^\s+|\s+$/g, '').substr(0, 512);
}

function generate_url()
{
    url = $('input[name="name"]').val();
    url = url.replace(/[\s]+/gi, '-');
    url = translit(url);
    url = url.replace(/[^0-9a-z_\-]+/gi, '').toLowerCase();    
    return url;
}

function translit(str)
{
    var ru=("А-а-Б-б-В-в-Ґ-ґ-Г-г-Д-д-Е-е-Ё-ё-Є-є-Ж-ж-З-з-И-и-І-і-Ї-ї-Й-й-К-к-Л-л-М-м-Н-н-О-о-П-п-Р-р-С-с-Т-т-У-у-Ф-ф-Х-х-Ц-ц-Ч-ч-Ш-ш-Щ-щ-Ъ-ъ-Ы-ы-Ь-ь-Э-э-Ю-ю-Я-я").split("-")   
    var en=("A-a-B-b-V-v-G-g-G-g-D-d-E-e-E-e-E-e-ZH-zh-Z-z-I-i-I-i-I-i-J-j-K-k-L-l-M-m-N-n-O-o-P-p-R-r-S-s-T-t-U-u-F-f-H-h-TS-ts-CH-ch-SH-sh-SCH-sch-'-'-Y-y-'-'-E-e-YU-yu-YA-ya").split("-")   
     var res = '';
    for(var i=0, l=str.length; i<l; i++)
    { 
        var s = str.charAt(i), n = ru.indexOf(s); 
        if(n >= 0) { res += en[n]; } 
        else { res += s; } 
    } 
    return res;
    

}
/*Для загрузки изображений с сервера*/
var filemanager = {
    result_name : 'file_result',
    opacity_name : 'file_box',
    
    root : 'media/original',
    
    first : 'images_wizard',
    
    url : '<?=Url::root()?>/media/js/filemanager/index.php',
    url_image : '<?=Url::root()?>/media/js/filemanager/image.php',
    
    session_id : '<?=session_id()?>',
    
    img_width: '100',
    img_height: '100',
    
    new_image: '#new_image',
    
    product_id: '<?=$product['id']?>',
    original: '<?=Registry::i()->settings['original']?>',
    resize: '<?=Registry::i()->settings['resize']?>',
    
    init : function(){
        if(!(this).result){
            (this).result = $('<div id="'+(this).result_name+'"></div>').appendTo("body");
        }
        if(!(this).opacity){
            (this).opacity = $('<div id="'+(this).opacity_name+'" />').appendTo("body");
        }
        // Инициализируем функцию удаляения
        if((this).drop_init != true){
            (this).drop_init = true;
            var object = (this);
            (this).opacity.click(function(){
                object.drop()}
            );
        }
    },
    
    drop: function(){
        // указываем что бы загрузить снова
        (this).drop_init = false;
        
        // удаляем оэлемент и свойство
        (this).result.detach();
        delete (this).result;
        
        (this).opacity.detach();
        delete (this).opacity;
    },
    
    // для начального старта
    start : function(){
        var object = (this);
        $('#'+object.first+'').click(function() {
            // инициализируем
            object.init();
            $.ajax({
                url: object.url,
                data: {
                    root: object.root, 
                    session_id : object.session_id, 
                    start : object.product_id,
                    original: object.original,
                    resize: object.resize
                },
                dataType: 'text',
                type: 'POST',
                success: function(data){
                    object.result.html(data);
                },
                error: function(xhr, textStatus, errorThrown){
                    alert("Error: " +textStatus);
                }
            });
        });
    },
    
    getFolder : function(folder){

        var object = (this);
        $.ajax({
                url: object.url,
                data: {
                    root: object.root, 
                    session_id : object.session_id, 
                    dir: folder,
                    original: object.original,
                    resize: object.resize
                },
                dataType: 'text',
                type: 'POST',
                success: function(data){
                    object.result.html(data);
                },
                error: function(xhr, textStatus, errorThrown){
                    alert("Error: " +textStatus);
                }
        });
    },
    getImage : function(image,cononical,dir){
        if(!image || !cononical){
            alert('Ошибка на странице');
            return;
        }
        var object = (this);
        $.ajax({
                url: object.url_image,
                data: {
                    dir: dir, 
                    cononical: cononical, 
                    session_id : object.session_id, 
                    image: image, 
                    width: object.img_width, 
                    height: object.img_height,
                    original: object.original,
                    resize: object.resize
                },
                dataType: 'json',
                type: 'POST',
                success: function(data){
                    object.drop();
                    object.returns(data);
                },
                error: function(xhr, textStatus, errorThrown){
                    alert("Error: " +textStatus);
                }
        });
    },
    
    getImages : function(arr_image){
        var object = (this);
        $.ajax({
                url: object.url_image,
                data: {
                image : arr_image, 
                session_id : object.session_id, 
                width : object.img_width, 
                height : object.img_height,
                original : '<?=Registry::i()->settings['original']?>',
                resize : '<?=Registry::i()->settings['resize']?>'},
                dataType : 'json',
                type : 'POST',
                success: function(datas){
                    object.drop();
                    for(data in datas){
                        object.returns(datas[data]);
                    }
                },
                error: function(xhr, textStatus, errorThrown){
                    alert("Error getImages: " +textStatus);
                }
        });
    },
    returns : function(data){
        var object = (this);
        $('<li><img src="'+data.src+'" /><span>'+data.image+'</span><a class="delete" href="#">Удалить</a><input type="hidden" value="'+data.image+'" name="new_images[]" /></li>').appendTo(""+object.new_image+"");
    }
}

filemanager.start();

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

$('.active a, .message .button').each(function(){
    var position = getUrlVars().scroll;
    if(position != undefined){
        href = $(this).attr('href');
        $(this).attr('href',href+'&scroll='+position);
    }
});
</script>