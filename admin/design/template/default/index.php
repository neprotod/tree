<!DOCTYPE html>
<html>
<head>
<title><?=Request::param(Registry::i()->meta_title)?></title>

<link href="<?=Request::$design->root?>/css/common.css" rel="stylesheet" type="text/css" />

<script class="drop_script" type="text/javascript" src="/media/js/jquery/jquerymin.js"></script>
<script class="drop_script" type="text/javascript" src="/media/js/jquery/jquery.form.js"></script>
<script class="drop_script" type="text/javascript" src="/media/js/jquery/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" href="/media/js/jquery/jquery-ui.css" media="screen" />
</head>
<body>
    <div id="main">
        <ul id="main_menu">
                <li>
                    <a href="<?=Url::query_root(array('module'=>'products'))?>">
                        <b>Каталог</b>
                    </a>
                </li>
                <li>
                    <a href="<?=Url::query_root(array('module'=>'orders','status'=>'0'))?>">
                        <b>Заказы</b>
                    </a>
                </li>
                <li>
                    <a href="<?=Url::query_root(array('module'=>'pages'))?>">
                        <b>Страницы</b>
                    </a>
                </li>
                <li>
                    <a href="<?=Url::query_root(array('module'=>'blog'))?>">
                        <b>Новости</b>
                    </a>
                </li>
                <li>
                    <a href="<?=Url::query_root(array('module'=>'settings'))?>">
                        <b>Настройки</b>
                    </a>
                </li>
                <li>
                    <a href="<?=Url::query_root(array('module'=>'prices'))?>">
                        <b>Прайсы</b>
                    </a>
                </li>
                <li style="float:right;">
                    <a href="<?=Url::query_root(array('module'=>'user','method'=>'loguot'))?>">
                        <b>Выйти</b>
                    </a>
                </li>
                <div class="clear"></div>
        </ul>
        <ul id="tab_menu">
            <?=Request::$design->tabs()?>
        </ul>
        <div id="middle">
            <?=$content?>
        </div>
    </div>
</body>
</html>
