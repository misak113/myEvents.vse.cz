<?php

include('qqUploader.php');
include('LogoResizer.php');

// list of valid extensions, ex. array("jpeg", "xml", "bmp")
$allowedExtensions = array("png","jpg","jpeg","gif","bmp");
// max file size in bytes
$sizeLimit = 10 * 1024 * 1024;

$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);

$result = $uploader->handleUpload('img/picture/');

$image = 'img/picture/' . $result['filename'];
$max_new_width = 100;
$max_new_height = 67;
LogoResizer::imageResize($image, $max_new_width, $max_new_height, $save = $image);

// to pass data through iframe you will need to encode all html tags
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);




