<?php foreach($categories as $category):?>
<li>
    <div class="subcategories_div">
        <a href="/<?=$category['url']?>" class="underline"><?=$category['name']?></a>
    </div>
</li>
<?php endforeach;?>