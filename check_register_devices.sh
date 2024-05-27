#!/bin/bash


 CHECK_BEFORE_REGISTER() {

  local FECHA=`date +"%d/%m/%Y - %R"`
  local TIMESTAMP=`date '+%s'`

  QUERY_toCHECK=`mysql -u asteriskuser -ppassword -e "Select MAC from asteriskcdr.provisioning_wisp where REG='' AND to_CHECK='1'" -ss -N 2>&1 | grep -v "Warning: Using a password" | tr "\n" ","`
  local FECHA=`date +"%d/%m/%Y - %R"`

  echo "QUERY_TOCHECK vale: $QUERY_toCHECK"
  MACS_num=`echo ${QUERY_toCHECK} | tr -cd ',' | wc -c`

  echo "MACS_num vale: $MACS_num"
  let VUELTA=0
  let POS=1



   while [ $VUELTA -lt ${MACS_num} ]; do
     
     MAC=`echo ${QUERY_toCHECK} | cut -d "," -f${POS}`
      
      #Borramos MAC de DHCP LEASES y IP-ARP
      # BUSQUEDA_IP=`sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER_DHCP} "/ip dhcp-server lease get [find where mac-address=${MAC}]"`
     
     #  if [[ "$BUSQUEDA_IP" == "" ]]; then   
       echo "IP QUE SE VA A ELIMINAR ANTES DE REGISTRAR....: ${IPDEL}"
       sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER_DHCP} "/ip dhcp-server lease remove [find where mac-address=${MAC}]"
       sleep 1
       IPDEL=`sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER_DHCP} "/ip arp print where mac-address=${MAC}" | cut -d " " -f4 | egrep -Eo '172\.16\.[0-9]{1,3}\.[0-9]{1,3}'`
       #Aqui hacer ping..Si responde, no eliminar
       sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER_DHCP} "/ip firewall address-list remove [find where address=${IPDEL} ]"
       sleep 1
       sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER_DHCP} "/ip arp remove [find where mac-address=${MAC}]"
       sleep 1
       sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER_DHCP} "/ip arp remove [find where mac-address=${MAC}]"
       echo "BORRADO ANTIGUA MAC-IP.... Hecho"
       sleep 1
    #   fi
      

    # Despues del PRE_REGISTER marcamos el to_CHECK a 0
     echo "MAC VALE: ${MAC}"
     mysql -u asteriskuser -ppassword -e "Update asteriskcdr.provisioning_wisp SET to_CHECK='0' where MAC='${MAC}'" -ss -N 2>&1 | grep -v "Warning: Using a password"
     let VUELTA=VUELTA+1

   done


 }




 CHECK_REGISTER() {

    local FECHA=`date +"%d/%m/%Y - %R"`
    local TIMESTAMP=`date '+%s'`

    QUERY_toREGISTER=`mysql -u asteriskuser -ppassword -e "Select MAC from asteriskcdr.provisioning_wisp where REG='' AND to_CHECK='0' AND to_REG='1'" -ss -N 2>&1 | grep -v "Warning: Using a password" | tr "\n" ","`
    local FECHA=`date +"%d/%m/%Y - %R"`

    REGISTER_MACS=`echo ${QUERY_toREGISTER} | tr -cd ',' | wc -c`

    let VUELTA=0
    let POS=0
  

  while [ $VUELTA -lt ${REGISTER_MACS} ]; do

      let POS=VUELTA+1
      MAC=`echo ${QUERY_toREGISTER} | cut -d "," -f${POS}`

      
      echo "COMPROBANDO MAC... $MAC "

      #Borramos MAC de DHCP LEASES y IP-ARP
      CLIENTE=`mysql -u asteriskuser -ppassword -e "Select CLIENTE from asteriskcdr.provisioning_wisp where MAC='${MAC}'" -ss -N 2>&1 | grep -v "Warning: Using a password"`
      DNI=`mysql -u asteriskuser -ppassword -e "Select DNI from asteriskcdr.provisioning_wisp where MAC='${MAC}'" -ss -N 2>&1 | grep -v "Warning: Using a password"`
      SPEED=`mysql -u asteriskuser -ppassword -e "Select SPEED from asteriskcdr.provisioning_wisp where MAC='${MAC}'" -ss -N 2>&1 | grep -v "Warning: Using a password"`
      IP_ROUTER_DHCP=`mysql -u asteriskuser -ppassword -e "Select IP_ROUTER_DHCP from asteriskcdr.provisioning_wisp where MAC='${MAC}'" -ss -N 2>&1 | grep -v "Warning: Using a password"`
      IP_ROUTER=`mysql -u asteriskuser -ppassword -e "Select IP_ROUTER from asteriskcdr.provisioning_wisp where MAC='${MAC}'" -ss -N 2>&1 | grep -v "Warning: Using a password"`
      


      IP_CLIENT=`sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER_DHCP} "/ip dhcp-server lease print where mac-address=${MAC}" | egrep -Eo '172\.16\.[0-9]{1,3}\.[0-9]{1,3}'`
      
      TELEFONO=`mysql -u asteriskuser -ppassword -e "Select TELEFONO from asteriskcdr.provisioning_wisp where MAC='${MAC}'" -ss -N 2>&1 | grep -v "Warning: Using a password"`

      LOOP=0

      echo "Cliente: $CLIENTE dni: $DNI speed: $SPEED ip_router_dhcp: $IP_ROUTER_DHCP ip_router: $IP_ROUTER ip_cliente: $IP_CLIENT tlf: $TELEFONO"

    sleep 5


    while [[ "$IP_CLIENT" == "" ]] ; do

       if [ $LOOP -ge 30 ] # MAXIMO 20 VUELTAS para encontrar la IP
         then
           break
       fi
      
       IP_CLIENT=`sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER_DHCP} "/ip dhcp-server lease print where mac-address=${MAC}" | egrep -Eo '172\.16\.[0-9]{1,3}\.[0-9]{1,3}'`
      let LOOP=LOOP+1
       sleep 2

    done
 

   if [[ "$IP_CLIENT" != "" ]]; then

       # Marcamos la IP en DHCP
         sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER_DHCP} "/ip dhcp-server lease set [find where mac-address=${MAC} ] comment=\"${CLIENTE}\""
         sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER_DHCP} "/ip dhcp-server lease make-static [find where mac-address=${MAC} ]"

      # Agregar a AddressList segun perfil
        if [[ "$SPEED" == "30" ]]; then
          sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER} "/ip firewall address-list add list=30Mb_profile address=${IP_CLIENT} comment=\"${CLIENTE}\""
        fi

        if [[ "$SPEED" == "50" ]]; then
          sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER} "/ip firewall address-list add list=50Mb_profile address=${IP_CLIENT} comment=\"$CLIENTE\""
         fi

         if [[ "$SPEED" == "100" ]]; then
           sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER} "/ip firewall address-list add list=100Mb_profile address=${IP_CLIENT} comment=\"$CLIENTE\""
         fi

	  if [[ "$SPEED" == "150" ]]; then
           sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER} "/ip firewall address-list add list=150Mb_profile address=${IP_CLIENT} comment=\"$CLIENTE\""
         fi

	  if [[ "$SPEED" == "300" ]]; then
           sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER} "/ip firewall address-list add list=300Mb_profile address=${IP_CLIENT} comment=\"$CLIENTE\""
         fi

	  if [[ "$SPEED" == "600" ]]; then
           sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER} "/ip firewall address-list add list=600Mb_profile address=${IP_CLIENT} comment=\"$CLIENTE\""
         fi

	  if [[ "$SPEED" == "1000" ]]; then
           sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER} "/ip firewall address-list add list=1000Mb_profile address=${IP_CLIENT} comment=\"$CLIENTE\""
         fi





        echo "DENTRO DE IF...SE HA ENCONTRADO IP"
        mysql -u asteriskuser -ppassword -e "Update asteriskcdr.provisioning_wisp SET IP='${IP_CLIENT}' where MAC='${MAC}'" -ss -N 2>&1 | grep -v "Warning: Using a password"
        mysql -u asteriskuser -ppassword -e "Update asteriskcdr.provisioning_wisp SET FECHA='${FECHA}' where MAC='${MAC}'" -ss -N 2>&1 | grep -v "Warning: Using a password"
        mysql -u asteriskuser -ppassword -e "Update asteriskcdr.provisioning_wisp SET TIMESTAMP='${TIMESTAMP}' where MAC='${MAC}'" -ss -N 2>&1 | grep -v "Warning: Using a password"
	mysql -u asteriskuser -ppassword -e "Update asteriskcdr.provisioning_wisp SET REG='1' where MAC='${MAC}'" -ss -N 2>&1 | grep -v "Warning: Using a password"
         echo "(${FECHA}) CLIENTE: ${CLIENTE} CON DNI: ${DNI} IP: ${IP_CLIENT} AGREGADO con exito" >> log_register_wisp.txt
         ERROR=0

         break 

    else
 
       echo "NO SE HA ENCONTRADO IP"
       ERROR=1
   
    fi




     let VUELTA=VUELTA+1
      sleep 5

   done


  
      if [[ "$ERROR" == "1" ]]; then

         ERRORFILE="error_"`echo ${MAC} | tr ":" "-"`
         echo "ERRORFILE VALE: ${ERRORFILE}"

	 if [ ! -f ${ERRORFILE} ]; then
              
	      curl -X POST -H 'Content-Type: application/json' -H 'Accept: application/json' -d '{"api_key":"6b17b320a7f840a780c8de793477fdb5","report_url":"","concat":1,"messages":[{"from":"SOURCENET","to":"34'${TELEFONO}'","text":"ERROR_ACTIVACION_AIRE_MAC:'${MAC}':_NO_SE_HA_OBTENIDO_IP","send_at":""}]}' https://api.gateway360.com/api/3.0/sms/send 2>/dev/null &
             echo "(${FECHA}) CLIENTE: ${CLIENTE} CON DNI: ${DNI} ERROR AL OBTENER IP" >> log_register_wisp.txt
	     touch ${ERRORFILE}
	     echo "FICHERO DE ERROR CREADO"
         fi

       else

         ERRORFILE=`echo ${MAC} | tr ":" "-"`
	 rm -r -f ${ERRORFILE}

	 if [[ "${TELEFONO}" != "" ]]; then
	    curl -X POST -H 'Content-Type: application/json' -H 'Accept: application/json' -d '{"api_key":"6b17b320a7f840a780c8de793477fdb5","report_url":"","concat":1,"messages":[{"from":"SOURCENET","to":"34'${TELEFONO}'","text":"ACTIVACION_AIRE_MAC:'${MAC}':_ACTIVADO_CON_EXITO","send_at":""}]}' https://api.gateway360.com/api/3.0/sms/send 2>/dev/null &
	 fi


	echo "(${FECHA}) CLIENTE: ${CLIENTE} CON DNI: ${DNI} IP: ${IP} AGREGADO con exito" >> log_register_wisp.txt

      fi

 } 







  CHECK_DELETE() {



   QUERY_DELETE=`mysql -u asteriskuser -ppassword -e "Select IP from asteriskcdr.provisioning_wisp where DEL='1'" -ss -N 2>&1 | grep -v "Warning: Using a password" | tr "\n" ","` 
   QUERY_DELETE_TIMESTAMP=`mysql -u asteriskuser -ppassword -e "Select MAC from asteriskcdr.provisioning_wisp where REG='' AND TIMESTAMP>=172800" -ss -N 2>&1 | grep -v "Warning: Using a password" | tr "\n" ","`

   local FECHA=`date +"%d/%m/%Y"`
   local TIMESTAMP=`date '+%s'`

   DELETE_IPS=`echo ${QUERY_DELETE} | tr -cd ',' | wc -c`
   DELETE_TIMESTAMP=`echo ${QUERY_DELETE_TIMESTAMP} | tr -cd ',' | wc -c`
   

   #echo "Fecha vale: ${FECHA}"
   #echo  "Query delete vale: ${QUERY_DELETE}"
   #echo "Delete ips vale: ${DELETE_IPS}"

   let VUELTA=0
   let POS=0
  

  while [ $VUELTA -lt ${DELETE_IPS} ]; do

     let POS=VUELTA+1
     IP=`echo ${QUERY_DELETE} | cut -d "," -f${POS}`

     IP_ROUTER_DHCP=`mysql -u asteriskuser -ppassword -e "Select IP_ROUTER_DHCP from asteriskcdr.provisioning_wisp where MAC='${IP}' AND DEL='1'" -ss -N 2>&1 | grep -v "Warning: Using a password"`
     IP_ROUTER=`mysql -u asteriskuser -ppassword -e "Select IP_ROUTER from asteriskcdr.provisioning_wisp where MAC='${IP}' AND DEL='1'" -ss -N 2>&1 | grep -v "Warning: Using a password"`

     CLIENTE=`mysql -u asteriskuser -ppassword -e "Select CLIENTE from asteriskcdr.provisioning_wisp where IP='${IP}' AND DEL='1'" -ss -N 2>&1 | grep -v "Warning: Using a password"`
     MAC=`mysql -u asteriskuser -ppassword -e "Select MAC from asteriskcdr.provisioning_wisp where IP='${IP}' AND DEL='1'" -ss -N 2>&1 | grep -v "Warning: Using a password"`  
     TELEFONO=`mysql -u asteriskuser -ppassword -e "Select TELEFONO from asteriskcdr.provisioning_wisp where MAC='${MAC}'" -ss -N 2>&1 | grep -v "Warning: Using a password"`

    sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER} "/ip firewall address-list remove [find where comment=\"${CLIENTE}\"]"
 
   # Remove DHCP LEASES
     sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER_DHCP} "/ip dhcp-server lease remove [find where comment=\"${CLIENTE}\"]"
     sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER_DHCP} "/ip dhcp-server lease remove [find where address=${IP} ]"
     sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER_DHCP} "/ip dhcp-server lease remove [find where mac-address=${MAC} ]"

   # Remove IP - ARP
    sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER_DHCP} "/ip arp remove [find where mac-address=${MAC}]"
    sshpass -p "password" ssh -o StrictHostKeyChecking=no -n ssh@${IP_ROUTER_DHCP} "/ip arp remove [find where mac-address=${MAC}]"

     mysql -u asteriskuser -ppassword -e "Delete FROM asteriskcdr.provisioning_wisp where IP='$IP' AND DEL='1'" -ss -N 2>&1 | grep -v "Warning: Using a password"

     echo "(${FECHA}) CLIENTE: ${CLIENTE} IP: ${IP} ELIMINADO CON EXITO" >> log_unregister_devices.txt

   

     curl "http://sms1.gateway360.com/api/push/?V=HTTPV3&UN=prueba2250&PWD=123456&R=2&SA=SOURCENET&DA=34${TELEFONO}&M=DISPOSITIVO_CLIENTE:_${CLIENTE}_ELIMINADO_CON_EXITO&DC=SMS&DR=1&UR=772349&STA=Alpha" 2>/dev/null &
    

    let VUELTA=VUELTA+1
    sleep 5

  done


   echo "Delete timestamp vale: ${DELETE_TIMESTAMP}"

   while [ $VUELTA -lt ${DELETE_TIMESTAMP} ]; do
    
    let POS=VUELTA+1
    MAC=`echo ${QUERY_DELETE_TIMESTAMP} | cut -d "," -f${POS}` 
    mysql -u asteriskuser -ppassword -e "Delete FROM asteriskcdr.provisioning_wisp where MAC='$MAC' AND REG=''" -ss -N 2>&1 | grep -v "Warning: Using a password"
    echo ".....Eliminando ${MAC} por exceso de tiempo......"

   done


 }
##MAIN
 echo "....Chequeamos para REGISTRAR...."
 sleep 5
 CHECK_REGISTER
 echo ".....FIN....."
 sleep 5
 echo ".....Chequeamos para ELIMINAR....."
 CHECK_DELETE
 echo "......FIN......"
 ###
