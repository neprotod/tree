<?php defined('MODPATH') OR exit();

class Mail_Module implements I_Module{

    const VERSION = '0.0.1';
    
    function __construct(){

    }
    
    function index($setting = null){}
    
    function email($to, $subject, $message, $from = '', $reply_to = ''){
        echo $from . "<br>";
        $headers = "MIME-Version: 1.0\n" ;
        $headers .= "Content-type: text/html; charset=utf-8; \r\n"; 
        $headers .= "From: $from\r\n";
        if(!empty($reply_to))
            $headers .= "reply-to: $reply_to\r\n";
        
        $headers .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
        
        $subject = "=?utf-8?B?".base64_encode($subject)."?=";

        @mail($to, $subject, $message, $headers);
    }
    
    
}