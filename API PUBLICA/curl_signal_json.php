<?php
$mac = $_GET['mac'];
$connection =  curl_init("https://IP_SERVIDOR_INTERNO/admin/device.php?type=WISP&action=calibrate_device&mac=$mac");
curl_setopt($connection, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($connection, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($connection, CURLOPT_FOLLOWLOCATION, 1);

$result = explode("<br/>",curl_exec($connection));


$equipamiento = explode(":",$result[1]);
$equipamiento = trim($equipamiento[1]);

if($equipamiento=="ALBENTIA") {   //En el caso de ALBENTIA...

  $cliente = explode(":",$result[2]);
  $nodo = explode(":",$result[3]);
  $signal = explode(":",$result[4]);


  $var['equipamiento']=$equipamiento;
  $var['cliente']=$cliente[1];
  $var['nodo'] =$nodo[1];
  $var['signal']=$signal[1];

}


if($equipamiento=="UBIQUITI") {    //En el caso de UBIQUITI.. mostramos mas datos. Como el SSID

  $cliente = explode(":",$result[2]);
  $nodo = explode(":",$result[3]);
  $ssid = explode(":",$result[4]);
  $signal = explode(":",$result[5]);

  $var['equipamiento']=$equipamiento;
  $var['cliente']=$cliente[1];
  $var['nodo'] =$nodo[1]." / SSID: ".$ssid[1];
  $signal = explode("\n",$signal[1]);
  $var['signal']=$signal[0];

}


echo json_encode($var);
?>
