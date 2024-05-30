<?php

/*
 * Usage example:
  Albentia(ipbase,usuario,password,false,port80,timeout5);

 * $ubiquiti   = new Ubiquiti('192.168.1.2', 'ubnt', 'ubnt', true, '443', 3);
 * print_r($ubiquiti->stations(true)); true => array | false | json
 * print_r($ubiquiti->status(true)); true => array | false | json
 * print_r($ubiquiti->status_new(true)); true => array | false | json
 * print_r($ubiquiti->ifstats(true)); true => array | false | json
 * print_r($ubiquiti->iflist(true)); true => array | false | json
 * print_r($ubiquiti->brmacs(true)); true => array | false | json
 * print_r($ubiquiti->spectrum(10, false));  true => array | false | json
 * print_r($ubiquiti->signal(true)); true => array | false | json
 * print_r($ubiquiti->air_view(true)); true => array | false | json
 * print_r($ubiquiti->station_kick('AA:BB:CC:DD:EE:FF', 'ath0', true)); true => array | false | json
 */

class Albentia{

	private $_ch;
	private $_baseurl;
	private $_timeout;
	private $_username;
	private $_password;
	private $_ip;
	private $_mac;

	private $_signal1;
	private $_signal2;

       
	public function __construct($ip,$mac, $user, $password, $https = true, $port = '443', $timeout = 3){
		$this->_ch	        = curl_init();
		$this->_timeout		= $timeout;
		$this->_username	= $user;
		$this->_password	= $password;
		$this->_ip		= $ip;
		$this->_mac             = $mac;
		$this->_baseurl         = "http://$ip:$port/";
		//$this->_baseurl	= ($https) ? 'https://'.$ip.':'.$port.'/login.cgi?uri=' : 'http://'.$ip.':'.$port.'/login.cgi?uri=';
	}

	private function query($page, $timeout = false){
		if(!$timeout){
			$timeout = $this->_timeout;
		}

        $this->_ch = curl_init();
        curl_setopt($this->_ch, CURLOPT_URL,$this->_baseurl.$page);
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($this->_ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($this->_ch, CURLOPT_USERPWD, "$this->_username:$this->_password");
	curl_setopt($this->_ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->_ch, CURLOPT_COOKIEFILE, '/tmp/cookiealbentia-'.$this->_ip);
        curl_setopt($this->_ch, CURLOPT_COOKIEJAR, '/tmp/cookiealbentia-'.$this->_ip);
	$result = curl_exec($this->_ch);
	return($result);
	
	}

	private function login(){
		$exec	= $this->query('/');
		if($exec){
			return true;
		} else {
			return false;
		}
	}





    public function getthroughputRX() {

    
      if($this->login()){

        sleep(1);           // Pausamos despues del login para evitar que la subida nos de elevada
        $result = $this->query('/cpe/refresh_top/refresh');
        $rx="";

        if( preg_match_all( '/([0-9]+\.[0-9]+)Kbps|([0-9]+\.[0-9]+)Mbps/',$result, $matches)) {
           $rx=$matches[0][1];
           //var_dump($matches);
         }

         return($rx);  //Retornamos bajada
      
      }


    }






    public function getThroughputTX() {

      if($this->login()){
        
	sleep(1);           // Pausamos despues del login para evitar que la subida nos de elevada
        $result = $this->query('/cpe/refresh_top/refresh');
        $tx="";


	if( preg_match_all( '/([0-9]+\.[0-9]+)Kbps|([0-9]+\.[0-9]+)Mbps/',$result, $matches)) {
	   $tx=$matches[0][0];
	   //var_dump($matches);
	 }

	 return($tx);  //Retornamos subida

      }


    }



    public function stats(){


        if($this->login()){

            $csvfile = $this->query('/gui/stats.cgi/download');
	    $array=str_getcsv($csvfile, "\n");
           

	    //$mac = "NO\n".strtoupper(trim($this->_mac));

            $mac = strtoupper(trim($this->_mac));
	    

	    for($i=0;$i<sizeof($array);$i++) {

	       $linea=explode(",",$array[$i]);
              
	        if($mac == $linea[0]) {
                  $v=0;
                  $nombre=$linea[$v+3];
                  $signal1=$linea[$v+12];
                  $signal2=$linea[$v+11];
                  $ip=$array[$v+19];
                break;
	      }

		/*
               
	      if($mac == $array[$i]) {
	        echo "match MAC VALE: $mac <br/>";
	        $nombre=$array[$i+3];
                $signal1=$array[$i+12];
		echo "Signal1 vale: $signal1 <br/>";
		$signal2=$array[$i+11];
		echo "Signal2 vale: $signal2 <br/>";
		$ip=$array[$i+19];
		break;
	      }
	      */
	        
	    }

	    $this->setSignal($signal1,$signal2);
	    $this->setIp($ip);
	   	       
        }

   }

    
    public function setIp($ip) {
     $this->_ip = $ip;
    }

    public function getIp() {

      $this->stats();
      return($this->_ip);

    }


    public function setSignal($signal1,$signal2) {
   
      $this->_signal1 = $signal1;
      $this->_signal2 = $signal2;

    }
    


    public function getSignal() {
   
      $this->stats();
      return($this->_signal2." / ".$this->_signal1);

    }



    public function air_view($array = false){
        if($this->login()){
            $result = $this->query('/air-view.cgi');
            if($array){
                $result = json_decode($result, true);
                return ($result);
            } else {
                return $result;
            }
        } else {
            return false;
        }
    }

	public function __destruct(){
		curl_close($this->_ch);
	}

}

?>
