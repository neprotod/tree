<?php
    if(Request::$design->category)
        $catId = Request::$design->category->id;
?>
<ul>
    <li class="<?=($catId == NULL)? 'select' : '' ?>"><a href="<?=Url::query_root(array('module'=>'products'))?>">Все категории</a></li>
<?php foreach($categories as $category): ?>
        <?php
            if($category['id'] == $catId){
                $class = 'select';
            }else{
                $class = 'droppable category';
            }
        ?>

            <li class="<?=$class?>">
                <a href="<?=Url::query_root(array('module'=>'products','category_id'=>$category['id']))?>"><?=$category['name']?></a>
            </li>
            <?php if(isset($category['subcategories'])): ?>
            <ul>
                <?php echo $menu->subcategories_tree($category['subcategories']);?>
            </ul>
            <?php endif;?>
<?php endforeach;?>
</ul>