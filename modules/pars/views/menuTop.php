<ul class="menu_top">
    <?php foreach($items AS $menu):
        if(empty($menu['url']))
            $menu['url'] = '/';
    ?>
    <li class="navigation_item"><a href="<?=$menu['url']?>"><?=$menu['name']?></a></li>
    <?php endforeach;?>
</ul>
                    