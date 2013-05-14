<?php
function widget_gt_search(&$setting,&$system){
    $objCat = $system->loadModel('goods/productCat');
    $data=$objCat->getTreeList();

    for($i=0;$i<count($data);$i++){
        $cat_path=$data[$i]['cat_path'];
        $cat_name=$data[$i]['cat_name'];
        $cat_id=$data[$i]['cat_id'];
        if(empty($cat_path) or $cat_path==","){//һ
            $myData['menu'][$cat_id]['label']=$cat_name;    
            $myData['menu'][$cat_id]['cat_id']=$cat_id;
        }
    }
    $sitemap = &$system->loadModel('content/sitemap');
    $myData['cat']=$sitemap->getMap(1);
    return $myData;
}
?>