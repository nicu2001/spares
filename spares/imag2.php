<?php 

$im = new Imagick();
$im->readImage("c:/devel/tecdoc/htdocs/img/wizard.jpg"); 
$im->resetIterator(); 
# Combine multiple images into one, stacked vertically. 
$ima = $im->appendImages(true); 
$ima->setImageFormat("png"); 
header("Content-Type: image/png"); 
echo $ima; 

# $im->setImageFormat("png");
# $im->roundCorners(5,3);
# $type=$im->getFormat();
# header("Content-type: $type");
# echo $im->getimageblob();
?>

