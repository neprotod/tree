<?php 
if(Request::$design->category)
    $catId = Request::$design->category->id;
foreach($categories as $category):
    if($category['id'] == $catId){
                $class = 'select';
            }else{
                $class = 'droppable category';
            }
?>

<li class="<?=$class?>">
        <a href="<?=Url::query_root(array('module'=>'products','category_id'=>$category['id']))?>"><?=$category['name']?></a>
</li>
<?php endforeach;?>