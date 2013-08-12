<?php 
//include the Facebook PHP SDK
include_once 'fbk-api/facebook.php';
 
//instantiate the Facebook library with the APP ID and APP SECRET
$facebook = new Facebook(array(
   'appId' => '190429214467897',
   'secret' => '8fa21ba73855a1d56d2b3c7cbbbc9e9c',
   'cookie' => true
));

//print_r($facebook);

?>