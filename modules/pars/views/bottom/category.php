<?php foreach($categories as $category): ?>
    <?php if($category['visible'] == 1): ?>
        <?php
            if(isset($category['subcategories'])){
                $line = 'dashed';
            }else{
                $line = 'underline';
            }
        ?>
        <td class="navigation_menu">
            <div class="box_menu">
                <a href="/<?=$category['url']?>" class="<?=$line?>"><?=$category['name']?></a>
            </div>
            <?php if(isset($category['subcategories'])): ?>
            <ul class="subcategories">
                <?php echo $menu->subcategories_tree($category['subcategories']);?>
            </ul>
            <?php endif;?>
        </td>
    <?php endif;?>
<?php endforeach;?>