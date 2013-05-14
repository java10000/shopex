<?php
$version='shopex485';

if($_GET['version']){
  $version=$_GET['version'];
}

$dirs = array("css","css_src","js","js/coms","js/package","js_src","js_src/coms","images","images");
echo '{
"betaManifestVersion": 1,
"version": "'.$version.'",
"entries": [';
foreach($dirs as $dir){

  if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if (eregi('.js$',$file)||
                eregi('.css$',$file)||
                eregi('.jpg$',$file)||
                eregi('.gif$',$file)||
                eregi('.png$',$file)
               ) {
            echo "{\"url\":\"".$dir."/".$file."\"},";
            }
        }
        closedir($dh);
    }
  } 

}
echo '{"url":""}]}';
?>
