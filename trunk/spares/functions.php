<?php
function utf2ansi($s) {
 return $s ? iconv('UTF-8','CP1251',$s) : '';
}

function ansi2utf($s) {
 return $s ? iconv('CP1251','UTF-8',$s) : '';
}

function is_boolstr( $s ) {
    return is_string( $s ) && ( $s == 'true' || $s == 'false' );
}

function str_canonify( $istr ) {
    $astr = str_split( strtolower( $istr ) );
    $ostr = "";
    foreach( $astr as $c ) {
        if( ( $c >= '0' && $c <= '9' ) || ( $c >= 'a' && $c <= 'z' ) )
            $ostr .= $c;
    }
    return $ostr;
}

function echo_json_answer( $errcode, $errmess ) {
    $json_arr = array( 
        'errcode'=>$errcode, 
        'errmess'=>$errmess 
    );
    header("Content-type: text/script;charset=utf-8");
    echo json_encode( $json_arr );   
}

function echo_json_and_die( $errmess ) {
    echo_json_answer( 1, $errmess );
    exit(1);
}

function check_referer() {
 return preg_match("|^http(s)?://".$_SERVER['SERVER_NAME']."/|", $_SERVER['HTTP_REFERER']) ? true : false;
}

function check_ajax_referer() {
 return ( preg_match("|^http(s)?://".$_SERVER['SERVER_NAME']."/|", $_SERVER['HTTP_REFERER']) &&
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) ? true : false;
}

function die_on_get_error( $errcode, $errmess ) {
    $data->page    = 0;
    $data->total   = 0;
    $data->records = 0;
    $data->rows    = array();
    $data->userdata = array( 'errcode'=>$errcode, 'errmess'=>$errmess );    
    header("Content-type: text/script;charset=utf-8");
    echo json_encode( $data );
    exit(1);
}

function is_numeric_array( $arr ) {
    $res = true;
    if( !count($arr) ) return false;
    foreach( $arr as $value ) {
        if( !is_numeric($value) ) {
            $res = false; break;
        }
    }
    return $res;
}

function read_app_data( $fname, $pname ) {
    $fsize = file_exists($fname) ? filesize($fname) : 0;
    if( $fsize ) {
        $fp = fopen( $fname, "rb" );
        flock( $fp, LOCK_SH );
        $data = unserialize(fread($fp, $fsize ));
        flock( $fp, LOCK_UN );
        fclose($fp);
        return isset($data[$pname])?$data[$pname]:false;
    }
    else return false;
}

function write_app_data( $fname, $pname, $pval ) {
  $fsize = file_exists($fname) ? filesize($fname) : 0;
  if( $fsize ) {
      $fp = fopen( $fname, "r+b" );
      flock( $fp, LOCK_EX );
      $data = unserialize(fread($fp, $fsize ));
      ftruncate($fp, 0);
      rewind($fp);
  }
  else {
      $fp = fopen( $fname, "wb" );
      flock( $fp, LOCK_EX );
      $data = array();
  }
  $data[$pname] = $pval;
  fwrite( $fp, serialize($data) );
  flock( $fp, LOCK_UN );
  fclose($fp);
}
?>
