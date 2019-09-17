<?php defined('MODPATH') OR exit();

class Model_User_DESIGN{

    function email(){
        
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
        mail('vesta-sad.nsk@mail.ru', 'Письмо с vesta-sad.ru',$massage, $headers);
    }
    
    function contact_mail($content = array(), $subject,$theme){
        if(Request::method() != 'POST')
            return array();

        $error = array();
        
        if(empty($content['phone'])){
            $error['phone'] = 'Незаполнен телефон';
        }
        if(empty($content['email'])){
            $error['email'] = 'Незаполнен E-mail';
        }
        elseif(!strrchr($content['email'],'@')){
            $error['email'] = 'Вы не правельно заполнели E-mail';
        }
        if(!empty($error)){
            $error['error'] = TRUE;
            return $error;
        }
        $massage = "
                <p>
                    Имя: {$content['name']}
                </p>
                <p>
                    Телефон: {$content['phone']}
                </p>
                <p>
                    Емейл: {$content['email']}
                </p>
                <br />
                <p>
                    {$content['massage']}
                </p>
            <html>
        ";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
        mail($subject, $theme,$massage, $headers);
        header('Location: '.Url::query_root(array('result'=>'complete'),FALSE));
    }
    
    // Для получения пункта меню
    function get_footer_menu($id){
        $menu = Module::factory('menu',TRUE);
        
        $returns = $menu->get(intval($id));
        if(is_array($returns))
                foreach($returns  as $return):
            ?>
            <td>
                <a href="/<?=$return['url']?>"><?=$return['name']?></a>
            </td>
            <?php
            endforeach;
    }
    
}