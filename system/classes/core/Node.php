<?php defined('SYSPATH') OR exit();
// empty class
class Core_Node{
    static function separator($node, $delimiter = '/'){
        $node = explode($delimiter, $node);
        return $node;
    }
    
    
    
}