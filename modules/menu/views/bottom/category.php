<?php
if(is_array(Request::$design->category)){
    Request::$design->category = (object)reset(Request::$design->category);
}
$i = 0;
?>
<?php foreach($categories as $category): ?>
    <?php if($category['visible'] == 1): ?>
        <?php
            if(isset($category['subcategories'])){
                $line = 'dashed';
            }else{
                $line = 'underline';
            }
        ?>
        <td class="navigation_menu <?=((Request::$design->category->parent_id == $category['id']) OR (Request::$design->category->id == $category['id']))? 'active_menu': ''?>">
            <div id="box_menu_<?=++$i?>" class="box_menu">
                <a href="<?=Core::$base_url?>catalog/<?=$category['url']?>" class="<?=$line?>"><?=$category['name']?></a>
            </div>
            <?php if(isset($category['subcategories'])): ?>
            <ul class="subcategories">
                <?php echo $menu->subcategories_tree($category['subcategories']);?>
            </ul>
            <?php endif;?>
        </td>
    <?php endif;?>
<?php endforeach;?>