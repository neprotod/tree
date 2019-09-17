<!doctype HTML>
<html>
<head>
    <title><?=$design->meta_title?></title>
    <meta charset="utf-8" />
    <meta content="<?=Request::param($design->meta_description,TRUE)?>" name="description">
    <link rel="stylesheet" type="text/css" href="<?=$design->root?>/css/common.css" />
    
    <link href="/media/other/favicon/favicon.ico" rel="icon">
    
    <script type="text/javascript" src="/media/js/jquery/jquerymin.js"></script>
    <!-- всплывающее картинка -->
    <link rel="stylesheet" type="text/css" href="/media/js/fancybox/jquery.fancybox.css" />
    
    <!-- Запрет индексации -->
    <?php
    if(Request::get("page")):
    ?>
    <meta content="noindex,follow" name="robots">
    <?php
    endif;
    ?>
    <script type="text/javascript" src="/media/js/fancybox/jquery.fancybox.pack.js"></script>
    <script>
        $(function() {
            // Зум картинок
            $("a.zoom").fancybox({
                prevEffect    : 'fade',
                nextEffect    : 'fade',
                 helpers : {
                        overlay : {
                            locked : false
                        }
                    }
                });
            });
    </script>
</head>
<body id="page_home">
<div id="hidden_x">
        <div id="header">
            <div id="header_top">
                <div id="nav" class="conteiner">
                    <?=$design->menu_top?>
                </div>
            </div>
            <div id="header_content">
                <div class="conteiner">
                    <a id="logo" href="/">
                        
                    </a>
                    <div id="header_phone">
                        <!--<span class="phone mobile">  <?=Registry::i()->settings['phone']?></span>-->
                        <span class="phone"><?=Registry::i()->settings['phone']?></span>
                        <span class="email"><?=Registry::i()->settings['order_email']?></span>
                    </div>
                    <div id="header_search">
                        <form action="/catalog">
                            <input name="keyword" type="text" />
                            <button type="submit" class="serach_button"></button>
                        </form>
                    </div>
                    <div id="basket">
                        <div id="basket_item">
                            <?php
                                if($design->cart['num'] == 0){
                                    $no_line = 'no_line';
                                    $href = "";
                                }else{
                                    $no_line = '';
                                    $href = "href={$design->cart['page']}";
                                }
                            ?>
                            <a class="<?=$no_line?>" <?=$href?> >Моя корзина</a>
                        </div>    
                        <div id="basket_logo" ><?=$design->cart['num']?></div>
                    </div>    
                </div>
            </div>
            <div id="header_bottom">
                <div class="background">
                    <div class="conteiner">
                        <table id="nav_menu">
                            <tbody>
                                <tr>
                                    <?=$design->menu_bottom?>                    
                                </tr>
                            </tbody>
                        </table>
                        <div class="shadow_left"></div>
                        <div class="shadow_right"></div>
                        <div class="shadow"></div>
                    </div>
                </div>
            </div>
        </div>
    <div id="content" class="conteiner">
        <?=$design->content?>
    </div>
    
    <div id="footer" class="center">
        <div id="footer_top">
            <div class="footer_menu">
                <table class="table_box">
                    <thead>
                        <tr>
                            <td class="table_head">
                                Ландшафт
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <?php
                            echo Request::$design->user->get_footer_menu(7);
                            ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="footer_bottom">
            <div class="shadow_footer"></div>
            
            <div class="footer_menu">
                <div class="leaf_footer"></div>
                <div id="footer_sheet"></div>
                <table class="table_box">
                    <thead>
                        <tr>
                            <td class="table_head">
                                Поддержка
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <a href="/contact">Обратная связь</a>
                            </td>
                            <?php
                            echo Request::$design->user->get_footer_menu(8);
                            ?>
                        </tr>
                    </tbody>
                </table>
                <div class="footer_madeby">
                    <?php
                    if(Registry::i()->home):
                    ?>
                    <span class="string">Made by <a href="http://webdzen.com">WebDzen</a></span>
                    <?php
                    endif;
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
