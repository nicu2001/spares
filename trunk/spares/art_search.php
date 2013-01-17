<?

session_start();

// Ajax request legality check
require_once('config.php');
require_once('functions.php');

if( !check_ajax_referer() ) {
    header( 'Location: index.php' );
    exit(0);
}

$errmess = '';

while( true ){

// Проверка корректности протокола

    if( !isset($_REQUEST['oper']) || ( $oper = strip_tags(trim($_REQUEST['oper']))) != 'search_articles' ||
        !isset($_REQUEST['country_list']) || !is_numeric($_REQUEST['country_list']) || 
        !isset($_REQUEST['art_code']) || $_REQUEST['art_code'] == '' || !isset($_REQUEST['doing']) )
    {
        $errmess = "Нарушение протокола";
        break;
    }

    $ctm = $_REQUEST['country_list']+2;
    $_SESSION['COU_ID'] = $ctm;
    
    $art = $_REQUEST['art_code'];
    
    try {
        $db = new PDO("mysql:host=".$dbserv.";dbname=".$dbname.";charset=utf8",$dbuser,$dbpass);
    } catch (PDOException $e) {
        $errmess =  "Dictionary DataBase connection error";
        break;
    }

    try {
        $stock_db = new PDO("mysql:host=".$stock_serv.
                ";dbname=".$stock_base.";charset=utf8",$stock_user,$stock_pass);
    } catch (PDOException $e) {
        $errmess =  "Stock DataBase connection error";
        break;
    }

	$db->exec('SET CHARACTER SET utf8');
	
/*
SELECT distinct art_id, ga_id, ga_tex.tex_text AS gades, ga_assembly_tex.tex_text 
       AS ga_assembly, art_sup_id, arl_kind, arl_display_nr, art_replacement 
FROM tof_art_lookup
JOIN tof_articles ON art_id = arl_art_id AND substr(art_ctm,225,1)=1
JOIN tof_link_art_ga ON lag_art_id = art_id
JOIN tof_generic_articles ON ga_id = lag_ga_id AND ((ga_universal = 0 AND ga_id = ga_nr) OR ga_universal = 1)
JOIN tof_designations ga_des ON ga_des.des_id = ga_des_id AND ga_des.des_lng_id = 16
JOIN tof_des_texts ga_tex ON ga_tex.tex_id = ga_des.des_tex_id
LEFT OUTER JOIN tof_designations ga_assembly_des ON ga_assembly_des.des_id = ga_des_id_assembly AND ga_assembly_des.des_lng_id = 16
LEFT OUTER JOIN tof_des_texts ga_assembly_tex ON ga_assembly_tex.tex_id =  ga_assembly_des.des_tex_id
WHERE arl_search_number = 5221 AND arl_kind IN ('1','2','3','4','5') AND substr(arl_ctm,225,1)=1
*/    
$req =  "SELECT distinct art_id, ga_id, ga_tex.tex_text AS gades, ga_assembly_tex.tex_text ".
        "AS ga_assembly, art_sup_id, arl_kind, arl_display_nr, art_replacement ".
"FROM tof_art_lookup ".
"JOIN tof_articles ON art_id = arl_art_id AND substr(art_ctm,".$ctm.",1)=1 ".
"JOIN tof_link_art_ga ON lag_art_id = art_id ".
"JOIN tof_generic_articles ON ga_id = lag_ga_id AND ((ga_universal = 0 AND ga_id = ga_nr) OR ga_universal = 1) ".
"JOIN tof_designations ga_des ON ga_des.des_id = ga_des_id AND ga_des.des_lng_id = 16 ".
"JOIN tof_des_texts ga_tex ON ga_tex.tex_id = ga_des.des_tex_id ".
"LEFT OUTER JOIN tof_designations ga_assembly_des ON ga_assembly_des.des_id = ga_des_id_assembly AND ga_assembly_des.des_lng_id = 16 ".
"LEFT OUTER JOIN tof_des_texts ga_assembly_tex ON ga_assembly_tex.tex_id =  ga_assembly_des.des_tex_id ".
"WHERE arl_search_number = '".$art."' AND arl_kind IN ('1','2','3','4','5') AND substr(arl_ctm,".$ctm.",1)=1";    
    
    $recset = $db->query($req);

    $data = array();
    while ($row = $recset->fetch(PDO::FETCH_ASSOC)){
        $data[] = array(
            'arl_kind'=>$row['arl_kind'],
            'art_id'=>$row['art_id'],
            'ga_id'=>$row['ga_id'],
            'gen_name'=>htmlspecialchars($row['gades'].' ('.$row['ga_assembly'].')'),
            'art_number'=>'',
            'sup_brand'=>'',
            'art_name'=>'',
            'cri_text'=>'',
            'photo'=>'',
            'stock_id'=>'',
            'price'=>'',
            'store'=>''
        );
    }
    $recset->closeCursor();

    $total = count($data);
    if(!$total) break;
    
    $ars = array();
    for( $i = 0; $i < $total; $i++ ) {
        $ars[] = $data[$i]['art_id'];        
    }

/*
SELECT art_id, art_article_nr, sup_brand,
    nick_text.tex_text as nick_art_name, full_text.tex_text as full_art_name
FROM tof_articles
LEFT JOIN tof_suppliers ON sup_id = art_sup_id
LEFT JOIN tof_designations AS nick_des
ON nick_des.des_id = art_des_id AND nick_des.des_lng_id = 255
LEFT JOIN tof_designations AS full_des
ON full_des.des_id = art_complete_des_id AND full_des.des_lng_id = 16
LEFT JOIN tof_des_texts AS nick_text ON nick_text.tex_id = nick_des.des_tex_id
LEFT JOIN tof_des_texts AS full_text ON full_text.tex_id = full_des.des_tex_id
WHERE art_id in (1260795,1260796,1587106,2311816,1260795,1260796,1260795,1587106,2311816)
*/    
$req = "SELECT art_id, art_article_nr, sup_brand ".
"FROM tof_articles ".
"LEFT JOIN tof_suppliers ON sup_id = art_sup_id ".
"WHERE art_id in (".implode(",", $ars).")";
    
    $recset = $db->query($req);

    while ($row = $recset->fetch(PDO::FETCH_ASSOC)){
        for($i=0; $i<$total; $i++) {
            if( $data[$i]['art_id'] == $row['art_id'] ) {
                $data[$i]['art_number'] = $row['art_article_nr'];
                $data[$i]['sup_brand'] = ucwords(strtolower(htmlspecialchars($row['sup_brand'])));
//                $f_art_name = htmlspecialchars($row['full_art_name'].
//                        (is_null($row['nick_art_name']) ? '' : " ".$row['nick_art_name'] ));
//                $data[$i]['art_name'] = $f_art_name;
            }
        }
    }
    $recset->closeCursor();

$req = "SELECT acr_art_id AS article_id, IF(cri_tex.tex_text IS NULL,'',cri_tex.tex_text) AS crit_designation, ".
"IF( acr_value <> value_tex.tex_text && acr_value = '', value_tex.tex_text, acr_value ) AS crit_value, ".
"IF( unit_tex.tex_text = '' || ( unit_tex.tex_text IS NULL ),'',unit_tex.tex_text ) AS crit_unit, ".
"cri_id, acr_sort AS sort, cri_is_interval, cri_successor, acr_ga_id AS ga_id ".
"FROM tof_article_criteria ".
"JOIN tof_criteria ON acr_cri_id = cri_id ".
"LEFT OUTER JOIN tof_designations AS cri_des ".
    "ON cri_des.des_id = cri_short_des_id AND cri_des.des_lng_id = 16 ".
"LEFT OUTER JOIN tof_des_texts AS cri_tex ON cri_tex.tex_id = cri_des.des_tex_id ".
"LEFT OUTER JOIN tof_designations AS value_des ".
    "ON value_des.des_id = acr_kv_des_id AND value_des.des_lng_id = 16 ".
"LEFT OUTER JOIN tof_des_texts AS value_tex ON value_tex.tex_id = value_des.des_tex_id ".
"LEFT OUTER JOIN tof_designations AS unit_des ".
    "ON unit_des.des_id = cri_unit_des_id AND unit_des.des_lng_id = 16 ".
"LEFT OUTER JOIN tof_des_texts AS unit_tex ON unit_tex.tex_id = unit_des.des_tex_id ".
"WHERE acr_art_id IN (".implode(",",$ars).") AND ".
    "SUBSTR(acr_ctm, ".$ctm.", 1) = 1 AND acr_display in (1) ".
"ORDER BY article_id,ga_id,sort";
    
    $recset = $db->query($req);

    $aid = $gid = $m = -1;
    $cri = array();
    while ($row = $recset->fetch(PDO::FETCH_ASSOC)){
        if( $row['article_id'] != $aid || $row['ga_id'] != $gid ) {
            if( isset($cri[$m]) && isset($param)) {
                $fulltext = "";
                foreach ( $param as $value ) {
                    $semi = ( $value['crit_name'] != "" && $value['crit_val'] != "" ) ? ": " : "";
                    $fulltext = $fulltext.$value['crit_name'].$semi.$value['crit_val'].". ";
                }
                $cri[$m]['cri_text'] = $fulltext;    
                unset($param);
            }
            $m++;
            $aid = $row['article_id'];
            $gid = $row['ga_id'];
            $cri[$m] = array('art_id'=>$aid, 'ga_id'=>$gid, 'cri_text'=>"");
        }
        $crid = $row['cri_id'];
        if( !isset( $param[$crid] ) ) {
            $param[$crid]['crit_name'] = $row['crit_designation'];
            $param[$crid]['crit_val'] = $row['crit_value'].$row['crit_unit'];
        }
        else 
            $param[$crid]['crit_val'] = $param[$crid]['crit_val'].", ".
                        $row['crit_value'].$row['crit_unit'];
    }
    if( isset($cri[$m]) && isset($param)) {
        $fulltext = "";
        foreach ( $param as $value ) {
            $semi = ( $value['crit_name'] != "" && $value['crit_val'] != "" ) ? ": " : "";
            $fulltext = $fulltext.$value['crit_name'].$semi.$value['crit_val'].". ";
        }
        $cri[$m]['cri_text'] = $fulltext;    
        unset($param);
    }

    for($i=0; $i<$total; $i++) {
        for( $m=0; $m<count($cri); $m++) {
            if( $cri[$m]['art_id'] == $data[$i]['art_id'] && $cri[$m]['ga_id'] == $data[$i]['ga_id']) {
                $data[$i]['cri_text'] = htmlspecialchars($cri[$m]['cri_text']);
                break;
            }
        }
    }
    $recset->closeCursor();

$req = "SELECT lga_art_id, gra_tab_nr, gra_grd_id, doc_extension ".
"FROM  tof_link_gra_art ".
"JOIN tof_graphics ON gra_id = lga_gra_id AND gra_doc_type != 2 ".
"JOIN tof_doc_types ON doc_type = gra_doc_type ".
"WHERE lga_art_id in (".implode(",", $ars).")";

    
    $recset = $db->query($req);

    while ($row = $recset->fetch(PDO::FETCH_ASSOC)){
        for($i=0; $i<$total; $i++) {
            if( $data[$i]['art_id'] == $row['lga_art_id'] ) {
                if( !is_null($row['gra_tab_nr']) && 
                    !is_null($row['gra_grd_id']) &&
                    !is_null($row['doc_extension'])
                )
                $data[$i]['photo'] = $row['gra_tab_nr'].'/'.$row['gra_grd_id'].'.'.strtolower($row['doc_extension']);
            }
        }
    }
    $recset->closeCursor();

// Stock search
    $stock_ids = array();
    for( $i=0; $i<$total; $i++) {
        $tval = str_canonify($data[$i]['sup_brand']).str_canonify($data[$i]['art_number']);
        $data[$i]['stock_id'] = $tval;
        $stock_ids[] = "'".$tval."'";
    }

    $req = "SELECT * FROM stock WHERE owner_id = ".$stock_owner_id.
                                " AND stock_id IN ( ".implode(',',$stock_ids)." )";

    $recset = $stock_db->query($req);
    unset( $stock_ids );
    
    while( $row = $recset->fetch(PDO::FETCH_ASSOC) ) {
        for( $i=0; $i<$total; $i++) {
            if( $data[$i]['stock_id'] == $row['stock_id'] ) {
                $data[$i]['price'] = $row['price'];
                $data[$i]['store'] = $row['store'];
            }
        }
    }
    $recset->closeCursor();

    break;
}
if( $errmess != '' ) {
    echo_json_answer( 1, $errmess );
}
else {
    $json_arr = array( 
        'errcode'=>0, 
        'errmess'=>"Ok",
        'data'=>$data 
    );
    header("Content-type: text/script;charset=utf-8");
    echo json_encode( $json_arr );   
}    
exit(0);
?>
