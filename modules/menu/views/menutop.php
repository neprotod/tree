<ul class="menu_top">
    <?php
        $url = (empty(Url::instance()->url))? Core::$base_url : '/'.Url::instance()->url;
        if(Registry::i()->module == 'page'){
            $bool = TRUE;
        }else{
            $bool = FALSE;
        }
    ?>
    <?php
        foreach($items AS $menu):
            if(empty($menu['url']))
                $menu['url'] = Core::$base_url;
            else{
                $menu['url'] = '/' . $menu['url'];
            }
    ?>
    <li class="navigation_item"><a class="<?=(($menu['url'] == $url))? 'seletct':'' ?>" href="<?=$menu['url']?>"><?=$menu['name']?></a></li>
    <?php endforeach;?>
</ul>
                    