<?
require_once('config.php');

$db = new PDO("mysql:host=".$dbserv.";dbname=".$dbname.";charset=utf8",$dbuser,$dbpass);
$result = $db->query(
    "SELECT cou_id, cou_cc, cou_iso2, tex_text FROM tof_countries ".
    "JOIN tof_designations ON des_id = cou_des_id AND des_lng_id=16 ".
    "LEFT JOIN tof_des_texts ON tex_id = des_tex_id ".
    "ORDER BY  `tof_des_texts`.`tex_text` ASC"
);

$data = array();
while ($row = $result->fetch(PDO::FETCH_ASSOC)){
     $data[] = array(
            'cou_id'=>$row['cou_id'],
            'cou_cc'=>$row['cou_cc'],
            'cou_iso2'=>$row['cou_iso2'],
            'cou_text'=>$row['tex_text'],
        );
}
$result->closeCursor();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Expires" content="Sun, 7 May 2000 12:04:32 GMT">
<title>Поиск запчастей</title>
<link rel="stylesheet" type="text/css" media="screen" href="css/cust-smooth/jquery-ui-1.8.7.custom.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/ipdepo.css" mce_href="css/ipdepo.css" />
<script type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.7.custom.min.js"></script>
<script type="text/javascript" src="js/jqmodal/jqModal.js"></script>
<script type="text/javascript" src="js/jquery.form.js"></script>
<script type="text/javascript" src="onready.js"></script>
</head>
<body>
<div id="main_panel" class="main_panel">
    <div class="search_panel ui-widget ui-widget-header ui-corner-all">
        <form id="SearchForm" action="">
<!--			<fieldset><legend>Условия поиска</legend> -->
                <table>
                    <tr><td style="display:none;"><label>Страна поставки</label></td>
                    <td><label>Код артикля</label></td><td></td></tr>
                    <tr>
                        <td  style="display:none;"><select id="country_list" name="country_list">
<?
for($i=0; $i<count($data); $i++) {
    if( $data[$i]['cou_cc'] == '###') continue;
    $sel = ( $data[$i]['cou_id'] == $country_code ) ? "selected" : "";
    echo '<option '.$sel.' value="'.$data[$i]['cou_id'].'">'.$data[$i]['cou_text']."</option>\n";
}
?>
                        </select></td>
                        <td><input id="art_code" name="art_code" type="text" value=""></td>
                        <td><button id="doing" name="doing" type="submit">Поиск</button></td>                        
                    </tr>
                </table>
        </form>
    </div>
    <div class="result_panel">
        <div id="wait_img"><img src="img/black-013-loading-p.gif"></div>
        <div id="data_tab" class="ui-widget"></div>
    </div>
</body>
</html>
