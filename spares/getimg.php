<?
require_once('config.php');
require_once('functions.php');

$conversation = false;

$img_file = "";
$doc_ext = "jpg";

while( true ){

// Проверка корректности протокола

    if( !isset($_REQUEST['id']) || !is_numeric($_REQUEST['id']) || (int)$_REQUEST['id'] == 0 ||
        !isset($_REQUEST['photo']) || $_REQUEST['photo'] == '')
        break;
        
    $art_id = $_REQUEST['id'];
    $photo = str_replace("/",$dir_delimiter,$_REQUEST['photo']);
    
    $dotpos = strrpos($photo,'.');
    $doc_ext = strtolower(substr($photo,$dotpos+1));
    
    $img_file = $img_dir.$dir_delimiter.$photo;

    if( $doc_ext != 'jpg' && $doc_ext != 'png') {
        $old_img_file = $img_file;
        $doc_ext = "jpg";
        $img_file = $tmp_dir.$dir_delimiter.mt_rand().".".$doc_ext;
        system( $convertor." ".$old_img_file." ".$img_file);
        $conversation = true;
    }
    
    break;
}

if( $img_file == '' || !file_exists($img_file) ) {
    $conversation = false;
    $img_file = $err_file;
    $doc_ext = "jpg";
}

header("Content-Type: image/".$doc_ext); 
echo file_get_contents($img_file);

if($conversation)
    unlink($img_file); 

?>
