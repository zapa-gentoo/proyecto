<?php
 header('Content-type: text/html; charset=utf-8'); 
 require_once("lib/conexdb_facturacion.php");
 require_once("lib/ubiquiti.class.php");
 require_once("lib/albentia.class.php");
 require_once("lib/anfit.class.php");

 $action=htmlentities($_GET['action']);
?>
<?php


  function view_signal($mac,$CLIENTE,$VENDOR,$IP,$NODO,$IP_NODO,$IP_ROUTER_DHCP) {

   
    $fecha=date("d-m-Y");

    switch($VENDOR) {


      case "EPON_ANFIT":
       $array = "";
       $anfit = new Anfit($IP, 'admin', 'admin',false,'80',3);
       $signal1=$anfit->getSignal();
       
       if($signal1=="") {       //Si con los primeros datos no conseguimos señal, probamos con los segundos
        $anfit = new Anfit($IP, 'admin', 'admin', false, '80', 3);
        //$array_status=$anfit->status(true);
        $signal1=$anfit->getSignal();
       }
    
     
      if($signal1!="")
        echo "Fecha: $fecha <br/> Equipamiento: $VENDOR <br/> Cliente: $CLIENTE <br/> Nodo: $NODO <br/> Señal: $signal1 db";
       else 
        echo "Fecha: $fecha <br/> Cliente: $CLIENTE Nodo: $NODO <br/> Imposible obtener señal. ¿CPE no conectado a repetidor?<br/>";
       
      break;




      case "UBIQUITILTU":
       
       $array="";
       $ubiquiti = new Ubiquiti($IP, 'admin', 'admin123--', false, '8888', 3);
       $array=$ubiquiti->signal(true);

       $hostname = $array["host"]["hostname"];
       //$signal1 = $array["wireless"]["sta"][0]["signal"];
       //$signal2 = $array["wireless"]["sta"][0]["remote"]["signal"];

       $signal1 = ($array['rx_pwr']['0']);
       $signal2 = ($array['rx_pwr']['1']);
       

       //$lastip = $array["interfaces"]["2"]["status"]["ipaddr"];


      if($signal1=="" && $signal2=="")

        $ubiquiti = new Ubiquiti($IP, 'admin', 'admin123--', false, '8888', 3, "new");
	$array=$ubiquiti->signal(true);
	$hostname = $array["host"]["hostname"];
	$signal1 = ($array['rx_pwr']['0']);
        $signal2 = ($array['rx_pwr']['1']);

	if($signal1=="" && $signal2=="")
          echo "Fecha: $fecha <br/> Cliente: $CLIENTE Nodo: $NODO <br/> Imposible obtener señal. ¿CPE no conectado a repetidor?<br/>";
        else
          echo "Fecha: $fecha <br/> Equipamiento: $VENDOR <br/> Cliente: $CLIENTE <br/> Nodo: $NODO <br/> Señal: $signal1 / $signal2";


      break;

    
      case "UBIQUITI":
       
       $array="";
       $ubiquiti = new Ubiquiti($IP, 'admin', 'admin', false, '8888', 3);
       $array_status=$ubiquiti->status(true);
       $array=$ubiquiti->signal(true);
       
       
       if(sizeof($array)<=0) {
        $ubiquiti = new Ubiquiti($IP, 'admin', 'admin123--', false, '8888', 3);
        $array_status=$ubiquiti->status(true);
	$array=$ubiquiti->signal(true);
       }

       $hostname = $array["host"]["hostname"];
       $essid = $array_status["wireless"]["essid"];
       //$signal1 = $array["wireless"]["sta"][0]["signal"];
       //$signal2 = $array["wireless"]["sta"][0]["remote"]["signal"];
       
       $signal1 = ($array['chainrssi']['0'])-95;
       $signal2 = ($array['chainrssi']['1'])-95;
       $signalglobal = $array['signal'];
       
       if(sizeof($array)<=0) { //Algunas versiones de ubiquiti ac



       }



       //$lastip = $array["interfaces"]["2"]["status"]["ipaddr"]; 
      
      
      
      if($signal1==-95 && $signal2==-95)
        echo "Fecha: $fecha <br/> Cliente: $CLIENTE <br/> Nodo: $NODO <br/> Imposible obtener señal. ¿CPE no conectado a repetidor?<br/>";
       else
        echo "Fecha: $fecha <br/> Equipamiento: $VENDOR <br/> Cliente: $CLIENTE <br/> Nodo: $NODO <br/> SSID: $essid <br/> Señal: $signal1 / $signal2";
          
       //echo "wireless: ".print_r($array["wireless"]);
       //echo "array entero: ".print_r($array);

      break;
      

      case "ALBENTIA":
       
       
       $signal = "";
       $albentia = new Albentia($IP_NODO,$mac,'wmax', 'wmax', false, '80',3);
       $signal = $albentia->getSignal();

       if($signal=="") {
        $albentia = new Albentia($IP_NODO,$mac,'wmax', 'wmax', false, '80',3);
	$signal = $albentia->getSignal();
       }
        
       if($signal=="") {
         echo "Fecha: $fecha <br/> Cliente: $CLIENTE <br/> Nodo: $NODO <br/> Imposible obtener señal. ¿CPE no conectado a repetidor?<br/>"; 
       } else { 
         echo "Fecha: $fecha <br/> Equipamiento: $VENDOR <br/> Cliente: $CLIENTE <br/> Nodo: $NODO <br/> Señal: $signal <br/>";
       }

      break;


    }


  }






  function obtain_base_albentia($nodo) {

     $ip="";

     $c = mysql_query("Select * from nodos_wisp where NODO_PROVISIONING='$nodo'");
     $array = mysql_fetch_array($c);
     return($array['IP_NODO']);
    

   }







  function obtain_ip_router($nodo,$dhcp) {

   $c = mysql_query("Select * from nodos_wisp where NODO_PROVISIONING='$nodo'");
   $array = mysql_fetch_array($c);
   return($array['IP_ROUTER']);

  }





  function list_repeaters() {
 
    $c = mysql_query("Select * from nodos_wisp");

    if(mysql_num_rows($c)>0) {

      while($array = mysql_fetch_array($c)) {
       $nodo_provisioning = $array['NODO_PROVISIONING'];
       $nodo_name = $array['NODO'];
       echo "<option value='$nodo_provisioning'>$nodo_name</option>";
      }

   }

  }






   function obtain_file_cfg($nodo,$speed) {

     $c = mysql_query("Select * from provisioning_wisp WHERE NODO_PROVISIONING='$nodo'");
     $array = mysql_fetch_array($c);
     return($array['CFG_CLIENTES']);
    
    }

      



switch($action) {

     case "edit":

       $mac = htmlentities($_GET['mac']);
       $c = mysql_query("Select * from provisioning_wisp where MAC='$mac'");
       $array = mysql_fetch_array($c);
       $id = $array['ID'];
       $cliente = $array['CLIENTE'];
       $dnicliente = $array['DNI'];
       $maccliente = $array['MAC'];
       $ipcliente = $array['IP'];
       $speedcliente = $array['SPEED'];

      if($_POST['enviado']=="YES") {

       $cliente = htmlentities($_POST['cliente_post']);
       $dni = htmlentities($_POST['dnicliente_post']);
       $mac = htmlentities($_POST['maccliente_post']);
       $ip = htmlentities($_POST['ipcliente_post']);
       $speed = htmlentities($_POST['speedcliente_post']);

       mysql_query("Update provisioning_wisp set CLIENTE='$cliente',DNI='$dni',IP='$ip',SPEED='$speed' where MAC='$mac'") or die ("ERROR Mysql query update");
       echo '<script>alert("Registro modificado con exxito");location.href="device.php?action=dir_device";</script>';

      } else {

      ?>
       <a href="device.php?action=dir_device"><-Atras</a>
        <hr/>
	 <h2>Modificar datos equipo MAC: <?php echo $mac?> </h2>
	 <hr/>
        <form method="post" name="form" action="device.php?action=edit&mac=<?php echo $mac?>">
         <table border="0">

          <tr>
           <td>Cliente:</td>
           <td><input type="text" name="cliente_post" value="<?php echo $cliente?>"/></td>
         </tr>

          <tr>
           <td>DNI Cliente:</td>
           <td><input type="text" name="dnicliente_post" value="<?php echo $dnicliente?>"/></td>
         </tr>

	 <tr>
	   <td>MAC:</td>
	   <td><input type="text" name="maccliente_post" value="<?php echo $maccliente?>"/></td>
	 </tr>
      
        <tr>
          <td>IP:</td>
          <td><input type="text" name="ipcliente_post" value="<?php echo $ipcliente?>"/></td>
        </tr>


	 <tr>
	   <td>Velocidad</td>
	   <td><input type="text" name="speedcliente_post" value="<?php echo $speedcliente?>" size="2"/>Mb</td>
	 </tr>

	 <tr>
	   <td>
	     <td><input type="submit" value="Modificar"></td>
	   </td>
	 </tr>

         <input type="hidden" name="enviado" value="YES"/>
       </form>
   <?php
     }
 


     break;


     case "add_ssid":
    
    $mac = htmlentities($_GET['mac']);
    $ip = htmlentities($_GET['ip']);
    $dni = htmlentities($_GET['dni']);
    
    if($_POST['enviado']=="SI") {

      $ssid = htmlentities($_POST['ssid']);
      $comment = htmlentities($_POST['comment']);

      if($ssid!="") 
       mysql_query("Update provisioning_wisp set SSID='$ssid' where MAC='$mac'") or die ("ERROR MYSQL_QUERY");
      if($comment!="")
       mysql_query("Update provisioning_wisp set COMMENT='$comment' where MAC='$mac'") or die ("ERROR MYSQL QUERY");
      
       echo '<script>alert("SSID y COMENTARIO asignados con exito!");location.href="device.php?action=dir_device";</script>';
    } else {
      $c = mysql_query("Select * from provisioning_wisp where MAC='$mac'");
      if(mysql_num_rows($c)>0) {
       $array = mysql_fetch_array($c);
       $ssid_arr=$array['SSID'];
       $comment_arr=$array['COMMENT'];
      }
     ?>
     <html>
     <head>
      <title>Asignar SSID</title>
     </head>
     <body>
     <a href="device.php?action=dir_device">Atras</a>
       <form method="post" name="form" action="device.php?action=add_ssid&mac=<?php echo $mac?>&ip=<?php echo $ip ?>&dni=<?php echo $dni ?>">
         <input type="hidden" name="enviado" value="SI"/>
	 <hr/>
	  <h2>Asignación SSID para dispositivo con MAC: <?php echo $mac ?> </h2>
	 <hr/>
          <table border="0"> 
          <tr>
	    <td>SSID</td>
	    <td> <input type="text" name="ssid" maxlength="20" size="10"  value="<?php echo $ssid_arr ?>" /> </td>
	  </tr>

	   <tr>
	    <td>COMENTARIO</td>
	    <td> <textarea name="comment" value="<?php echo $comment_arr ?>"> </textarea> </td>
	  </tr>

	    <tr>
              <input type="hidden" name="enviado" value="SI"/>
	      <td></td>
	      <td><input type="submit" value="OK"></td>
	    </tr>

	  </table>

       </form>
     </body>
     </html>
   <?php
    }
   

   break;


   case "del_device":

    $mac = htmlentities($_GET['mac']);
    $ip = htmlentities($_GET['ip']);
    $dni = htmlentities($_GET['dni']);

    ?>
     <html>
     <head>
     <script>
       var msg = confirm("¿ Estas seguro que deseas eliminar el dispositivo con MAC: <?php echo $mac?> ?");  
       if(msg == true) {
        <?php $c = mysql_query("Update provisioning_wisp set DEL='1' where MAC='$mac'") or die ("ERROR MYSQL QUERY UPDATE"); ?>
         alert("Dispositivo emplazado para ser eliminado");location.href="WISP/device.php?action=dir_device";
       } else {
         alert("Eliminacion de dispositivo cancelada");
         location.href="WISP/device.php?action=dir_device";
        }
     </script> 
     </head>
    <?php
   break;



  case "dir_device":
   
    if($_POST['enviado'] == "YES") {

      

    } else {
  ?>
   <html>
   <head>
     <title>Listado de dispositivos registrados</title>
   <link rel="stylesheet" href="../../style.css">
   </head>
   <body>
     <hr/>
      <a href="device.php?action=add_device">Provision Wisp</a> | <a href="device.php?action=dir_device">Listar registrados</a> | <a href="../../averias.php?action=dir">Listado de averias</a>
    <hr/>
     <h2>Listado de dispositivos registrados</h2>
   <table border="1" align="center"> 
    <tr>
     <th>Nodo</th>
     <th>IP Nodo</th>
     <th>Nombre</th>
     <th>DNI</th>
     <th>MAC</th>
     <th>SSID</th>
     <th>IP Antena</th>
     <th>Router</th>
     <th>Modelo Antena</th>
     <th>Instalador</th>
     <th>Eliminar</th>
    </tr>
   <?php
      $c = mysql_query("Select * from provisioning_wisp");

    while($array = mysql_fetch_array($c)) {

      $nodo = $array['NODO'];
      $ip_nodo = $array['IP_NODO'];
   
      $cliente = $array['CLIENTE'];
      $dni = $array['DNI'];
      $mac = $array['MAC'];
      $ssid = $array['SSID'];
      $ip = $array['IP'];
      $vendor = $array['VENDOR'];
      $instalador = $array['INSTALADOR'];
      $cto = '<a href="device.php?action=add_ssid&sn='.$sn.'&ip='.$ip.'&dni='.$dni.'">'.$cdoquery.'-'.$ctoquery.'</a>';
      $eliminar = '<a href="device.php?action=del_device&mac='.$mac.'&ip='.$ip.'&dni='.$dni.'">X</a>';
   ?>
     <tr>
       <td><?php echo $nodo ?></td>
       <td><a href="http://<?php echo $ip_nodo?>"><?php echo $ip_nodo ?></a></td>
       <td><a href="device.php?action=edit&mac=<?php echo $mac?>"> <? echo $cliente ?> </a></td>
       <td><a href="device.php?action=edit&mac=<?php echo $mac?>"> <? echo $dni ?> </a></td>
       <td><a href="device.php?action=edit&mac=<?php echo $mac?>"><? echo $mac ?> </a></td>
       <td><a href="device.php?action=add_ssid&mac=<?php echo $mac?>"><?php echo $ssid?></a> </td>
       <td><a href="http://<?php echo $ip?>"><? echo $ip ?> </a> </td>
       <td><?php echo '<a href="http://'.$ip.':8080">'.$ip.':8080</a>'?>
       <td><? echo $vendor ?></td>
       <td> <? echo $instalador ?> </td>
       <td> <? echo $eliminar ?> </td>
     </tr>

    <?php
      }
    ?>
   </table>
   </body>
   </html>


  <?php
    }




  break;


  case "add_device":

   if($_POST['enviado'] == "YES") {

     $nodo = htmlentities($_POST['nodo']);
     $ip_nodo="";

     $cliente = $_POST['cliente'];
     $dnicliente = $_POST['dnicliente'];
     $modelo = $_POST['modelo'];
     $sn1 = strtoupper(bin2hex($_POST['sn1']));
     $sn2 = strtoupper($_POST['sn2']);
     $sn = $sn1.$sn2;
     $m1 = strtoupper($_POST['m1']);
     $m2 = strtoupper($_POST['m2']);
     $m3 = strtoupper($_POST['m3']);
     $m4 = strtoupper($_POST['m4']);
     $m5 = strtoupper($_POST['m5']);
     $m6 = strtoupper($_POST['m6']);
     $telefono = $_POST['telefono'];
     $velocidad = $_POST['velocidad'];
     $instalador = $_POST['instalador'];
     $movilinstalador = $_POST['movilinstalador'];
     
     $mac = $m1.":".$m2.":".$m3.":".$m4.":".$m5.":".$m6;
    

     if($velocidad!="" && $telefono=="NO") {	//Solo internet
      $servicio = "INTERNET";
     }

     if($velocidad!="" && $telefono=="SI") {   //Internet y telefono
      $servicio = "INTERNET+TELEFONO";
     }
  
     if($telefono=="SI" && $velocidad=="") {    //Solo telefono
       $servicio = "TELEFONO";
     }

     if($modelo=="UBIQUITI") {
      $ipdefault="192.168.1.20";
      $ip_router=obtain_ip_router($nodo,false);
      $ip_router_dhcp=obtain_ip_router($nodo,true);
     }

     if($modelo=="UBIQUITILTU") {
      $ipdefault="192.168.1.20";
      $ip_router=obtain_ip_router($nodo,false);
      $ip_router_dhcp=obtain_ip_router($nodo,true);
     }

     if($modelo=="ALBENTIA") {
       $ipdefault="192.168.0.128";
       $base_albentia=obtain_base_albentia($nodo);
       $ip_router=obtain_ip_router($nodo,false);
       $ip_router_dhcp=obtain_ip_router($nodo,true);
     }

     if($modelo=="EPON_ANFIT") {
      $ipdefault="192.168.18.1";
      $ip_router=obtain_ip_router($nodo,false);
      $ip_router_dhcp=obtain_ip_router($nodo,true);
     }


    $c = mysql_query("Select MAC from provisioning_wisp where MAC='$mac'");
    if(mysql_num_rows($c)<=0) {
       $config_file=obtain_file_cfg($nodo,$velocidad);
       $ssid_availables=obtain_ssid($modelo,$nodo);

      mysql_query("Insert into provisioning_wisp(to_CHECK,NODO,MAC,IP_ROUTER,IP_ROUTER_DHCP,IP_NODO,VENDOR,SERVICE,SPEED,CLIENTE,DNI,INSTALADOR,TELEFONO,CFG_FILE)values('1','$nodo','$mac','$ip_router','$ip_router_dhcp','$base_albentia','$modelo','$servicio','$velocidad','$cliente','$dnicliente','$instalador','$movilinstalador','$config_file')") or die ("ERROR Mysql Insert ".mysql_error());
     echo '<script>alert("Dispositivo agregado. Ahora descargue el archivo de configuracion");</script>';
     echo '<div align="center"><b>Cliente:</b> '.$cliente.'<br/> <b>DNI:</b> '.$dnicliente.'<br/><b>Fabricante:</b> '.$modelo.'<br/> <b>Ip fabrica:</b> '.$ipdefault.'<br/>';
     echo $base_albentia;
     echo '<br/><br/><a download href="cfg/'.$config_file.'">DESCARGAR CONFIGURACION</a></div>';
     echo '<hr/>';
     echo  '<h2>SSID Disponibles:</h2>';
     echo '<hr/><br/><br/>';
       echo $ssid_availables;
     echo '<br/><br/>';
     die();
    } else
      echo '<script>alert("ERROR. Dispositivo ya existente");location.href="device.php?action=add_device";</script>';
    
   }



   ?>
    <html>
    <head>
     <title>Alta de Dispositivo WISP</title>
    </head>
    <body>
    <a href="http://192.168.5.1/administrator/tecnico/WISP/device.php?action=dir_device">Atras</a>
     <hr/>
      <h2>Alta de Dispositivo WISP</h2>
     <hr/>

   <form method="post" name="form" action="device.php?action=add_device">
    <table border="0">
    <tr>
     <td>Nodo:</td>
     <td> <select name="nodo">
           <?php list_repeaters(); ?>
        </select>
     </td>
    </tr>

    <tr>
      <td>Cliente:</td>
      <td><input type="text" name="cliente" value=""/></td>
   </tr>

    <tr>
     <td>DNI Cliente:</td>
     <td><input type="text" name="dnicliente" value=""/></td>
    </tr>

    <tr>
      <td>Fabricante/Modelo</td>
      <td>
       <select name="modelo">
         <option value="EPON_ANFIT">ANFIT</option>
         <option value="UBIQUITILTU">UBIQUITI_LTU</option>
         <option value="UBIQUITI">UBIQUITI</option>
	 <option value="ALBENTIA">ALBENTIA</option>
       </select>
     </td>
    </tr>

    <tr>
      <td>MAC:</td>
      <td>
        <input type="text" name="m1" size="1"/>:
	<input type="text" name="m2" size="1"/>:
        <input type="text" name="m3" size="1"/>:
	<input type="text" name="m4" size="1"/>:
	<input type="text" name="m5" size="1"/>:
	<input type="text" name="m6" size="1"/>
      </td>
    </tr>

    <tr>
     <td>Telefono fijo:</td>
     <td>
      <select name="telefono"> 
       <option value="NO">NO</option>
       <option value="SI">SI</option>
     </td>  

    <tr>
      <td>Servicio:</td>
      <td>
        <select name="velocidad">
         <option value="30">Internet 30Mb</option>
  	 <option value="50">Internet 50Mb</option>
	 <option value="100">Internet 100Mb</option>
	 <option value="150">Fibra 150Mb</option>
	 <option value="300">Fibra 300Mb</option>
	 <option value="600">Fibra 600Mb</option>
	 <option value="1000">Fibra 1000Mb</option>
	 <option value="">SOLO FIJO</option>
       </select>
      </td>
    </tr>
   
     <tr>
      <td>Instalador:</td>
      <td><input type="text" name="instalador" value=""/></td>
     </tr>

     <tr>
      <td>Tlf Instalador:</td>
      <td><input type="text" name="movilinstalador" value=""/></td>
     </tr>
    
      <input type="hidden" name="enviado" value="YES"/>
     </table>
     <td></td>
     <td><input type="submit" value="Registrar"/></td>

    </form>
    </body>
    </html>

  <?


    break;
  

    case "enable_device":

     $type = htmlentities($_GET['type']);
      
    if($type=="WISP") {

      $mac = htmlentities($_GET['mac']);
       
      $c = mysql_query("Select * from provisioning_wisp WHERE MAC='$mac' AND REG=''");
       if(mysql_num_rows($c)>0) {
         mysql_query("Update provisioning_wisp SET to_REG='1' WHERE MAC='$mac'") or die ("ERROR MYSQL QUERY ".mysql_error());
         echo '<div align="center">Dispositivo con MAC: <b>'.$mac.'</b> emplazado para ACTIVAR.<br/>(Recibirá confirmacion por SMS)<br/>';
	 die();
       } else {
         echo "<div align='center'>Error al procesar la activacion del dispositivo. MAC NO REGISTRADA o YA ACTIVADA<br/>";
       }
     }

    break;

    
    case "calibrate_device":
     
     $type = htmlentities($_GET['type']);
     if($type=="WISP") {

       $mac = htmlentities($_GET['mac']);
       $c = mysql_query("Select * from provisioning_wisp WHERE MAC='$mac' AND REG='1'");


       if(mysql_num_rows($c)>0) {
         
	 $array = mysql_fetch_array($c);
	 $NOMBRE = $array['CLIENTE'];
         $IP = $array['IP'];
	 $NODO = $array['NODO'];
	 $IP_NODO = $array['IP_NODO'];
	 $IP_ROUTER_DHCP = $array['IP_ROUTER_DHCP'];
         $VENDOR = $array['VENDOR'];
	 $SPEED = $array['SPEED']; 

	 view_signal($mac,$NOMBRE,$VENDOR,$IP,$NODO,$IP_NODO,$IP_ROUTER_DHCP);
        
       }

      


     }

    break;


    case "add_wisp":

    break;

    case "del_wisp":

    break;

}

function obtain_ssid($modelo,$nodo) {
 
   if($modelo=="UBIQUITI") {
     
     $ssid="";


     switch($nodo) {

       case "LOCAL_ubiquiti":
         $ssid="[S1-] -> Dirección Monovar Venta de blai <br/>
	        [S1-1] -> Dirección Monovar Venta de blai <br/>
		[S1-2] -> Dirección Monovar Venta de blai <br/>
		[S1-3] -> Dirección Surtidor-Vifama <br/>
		[S1-3_1] -> Dirección Surtidor-Vifama <br/>
		[S1-3_2] -> Dirección Surtido-Vifama <br/>
		[S1-4] -> Dirección Eco Ciudad <br/>
		[S1-4_1] -> Dirección Eco Ciudad <br/>
		[S1-4_2] -> Dirección Eco Ciudad <br/>";
       break;

       case "GABRIEL_ubiquiti":
         $ssid="[S3-PP] -> Dirección Monovar (Vifama) <br/>
	        [S3-PP-2] -> Dirección Monovar (Vifama) <br/>
		[S3-BB] -> Dirección plaza de toros <br/>
		[S3-BB_2] -> Dirección plaza de toros  <br/>
		[S3-EP-1] -> Dirección Elda/Petrer <br/>";

       break;

       case "BILAIRE_ubiquiti":
         $ssid="[S4-C] -> Direccion Casas del Señor,Chinorlet,pedanias..<br/>
	        [S4-S] -> Direccion Salinas<br/>
		[S4-M] -> Direccion Monovar<br/>
		[S4-M2] -> Direccion Monovar<br/>
		[S4-M3] -> Direccion Monovar<br/>";
       break;

       case "SBARBARA_ubiquiti":
         $ssid="[S1111-1] -> Dirección Monovar <br/>
	        [S1111-2] -> Dirección Monovar <br/>
		[S1111-7] -> Dirección Monovar <br/>
		[S1111-5] -> Dirección Monovar (Parte Izquierda) (Torre del reloj) <br/>
		[S1111-6] -> Dirección Monovar (Parte derecha) (Colegio Ricardo Leal) <br/>
		[S1111-3] -> Dirección campos (La pedrera...) <br/>
		[S1111-3_2] -> Dirección campos (La pedrera...) <br/>
		[S1111-3_3] -> Dirección campos (La Pedrera...) <br/>
		[S1111-4] -> Dirección Elda/Petrer <br/>
		[S1111-4-2] -> Dirección Elda/Petrer <br/>
		[S1111-4-3] -> (LTU) Dirección Elda/Petrer<br/>";
       break;

       case "CASTILLOPETRER_ubiquiti":
         $ssid="";
       break;

       case "SALINAS_ubiquiti":
         $ssid="[S1-1] -> Dirección carretera Monovar <br/>
	        [S1-2] -> Dirección bar Trivial <br/>
		[S1-3] -> Dirección poligono industrial <br/>
		[S1-4] -> Dirección camara <br/>";
       break;

       case "SAX_ubiquitiltu_camara":
       
         $ssid="[####S_PUEBLO] -> Direccion pueblo de SAX <br/>
	        [####S_CAMPO]  -> Dirección campos derecha <br/>";


       break;

     }

     return($ssid);
     

   }

   

   

}

?>


</body>
</html>
