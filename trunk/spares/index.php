<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Expires" content="Sun, 7 May 2000 12:04:32 GMT">
<title>Поиск запчастей</title>
<link rel="stylesheet" type="text/css" media="screen" href="css/cust-smooth/jquery-ui-1.8.7.custom.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/spares.css" mce_href="css/spares.css" />
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
			<div>Код артикля</div>
            <div>
				<input id="art_code" name="art_code" type="text" value="">
				<button id="doing" name="doing" type="submit">Поиск</button>
            </div>
        </form>
    </div>
    <div class="result_panel">
        <div id="wait_img"><img src="img/black-013-loading-p.gif"></div>
        <div id="data_tab" class="ui-widget"></div>
    </div>
</body>
</html>
