<?php
$usuario = mysql_real_escape_string(htmlentities($_GET['u']));
$password = mysql_real_escape_string(htmlentities($_GET['p']));
header('Content-type: text/html; charset=utf-8');
require_once("lib/conexdb.php");
$q = mysql_query("Select * from administrador WHERE username='$usuario' AND password='$password'");

if(mysql_num_rows($q)>0)
 echo "OK";
else
 echo "ERROR";
?>
