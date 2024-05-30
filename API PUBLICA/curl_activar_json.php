<?php
$mac = $_GET['mac'];
$connection =  curl_init("https://IP_SERVIDOR_INTERNO/admin/device.php?action=enable_device&type=WISP&mac=$mac");
curl_setopt($connection, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($connection, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($connection, CURLOPT_FOLLOWLOCATION, 1);
$result = curl_exec($connection);
echo json_encode($result);
?>
