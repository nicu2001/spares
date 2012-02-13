<?php
// Экспорт одной таблицы базы данных
// Сделан на основе export-my-stp.php

class Tools {
static function ansi2oem( $str ) { 
	return iconv('CP1251', 'CP866', $str ); 
}
static function utf2oem( $str ) { 
	return iconv('UTF-8', 'CP866', $str ); 
}
static function utf2ansi( $str ) { 
    return iconv('UTF-8', 'CP1251', $str ); 
}
} // End of Tools

class Tecdoc {
var $mysql_serv = "localhost";
var $mysql_login = "root";
var $mysql_pass = "";
var $mysql_db = "test";
var $odb = "";

function mysqlConnect() {
// charset=CP1251 - Windows, charset=utf8 - Unix 
	$this->odb = new PDO("mysql:host=".$this->mysql_serv.";dbname=".$this->mysql_db.";charset=CP1251",
							$this->mysql_login,$this->mysql_pass);
}

function AddRecord($wr_type, $name) {

	$mysql_query = "INSERT INTO `test_text` ( wr_type, name ) VALUES ( ?, ? )";
	$prep = $this->odb->prepare( $mysql_query );
	if( $prep === false ) {
		die("Prepare statement.\n");
	}
	
	if( $prep->execute(array($wr_type,$name)) === false ) {
		die( "Write Error.\n" );
	}
		
}

} //End Class

$a = "Предподготовка";
$b = "Метод blink возвращает строку, состоящую из примитивного значения строкового объекта, ".
"заключенного в теги <BLINK>…</BLINK>. Проверки на то, не была ли исходная строка уже заключена ".
"в эти теги, не делается. Этот метод используется совместно с методами document.write и ".
"document.writeln для отображения текста мигающим шрифтом. Указанные теги не входят в стандарт ".
"HTML и поддерживаются только обозревателями Netscape и WebTV. Например, оператор ".
"document.write(\"Мой текст\".blink()) выведет на экран обозревателя строку Мой текст.";

$tecdoc = new Tecdoc();
$tecdoc->mysqlConnect(); //Коннект к базе MySQL
$tecdoc->AddRecord(Tools::utf2ansi($a), Tools::utf2ansi($b));
?>
