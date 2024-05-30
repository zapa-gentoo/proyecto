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

class Anfit{

	private $_ch;
	private $_baseurl;
	private $_timeout;
	private $_username;
	private $_password;
	private $_ip;
	private $_mac;

	private $_signal1;
	private $_signal2;

       
	public function __construct($ip, $user, $password, $https = true, $port = '443', $timeout = 5){
	
		$this->_ch	        = curl_init();
		$this->_timeout		= $timeout;
		$this->_username	= $user;
		$this->_password	= $password;
		$this->_ip		= $ip;
		$this->_mac             = $mac;
		$this->_baseurl         = "http://$ip/";
		//$this->_baseurl	= ($https) ? 'https://'.$ip.':'.$port.'/login.cgi?uri=' : 'http://'.$ip.':'.$port.'/login.cgi?uri=';
	}

	private function query($page, $timeout = false){
		if(!$timeout){
			$timeout = $this->_timeout;
		}


       
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';

        $this->_ch = curl_init();
        curl_setopt($this->_ch, CURLOPT_URL,$this->_baseurl.$page);
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER,true);

       
	curl_setopt($this->_ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($this->_ch, CURLOPT_POST, 1);
	curl_setopt($this->_ch, CURLOPT_POSTFIELDS, "challenge=&username=".$this->_username."&password=".$this->_password."&save=Login&submit-url=/admin/login.asp");
        curl_setopt($this->_ch, CURLOPT_USERAGENT, $agent);
	curl_setopt($this->_ch, CURLOPT_COOKIEFILE, '/tmp/cookieanfit-'.$this->_ip);
        curl_setopt($this->_ch, CURLOPT_COOKIEJAR, '/tmp/cookieanfit-'.$this->_ip);
	$result = curl_exec($this->_ch);
	return($result);
	
	}




	private function login(){
		$exec	= $this->query('/boaform/admin/formLogin');
		if($exec){
			return true;
		} else {
			return false;
		}
	}

	public function stations($array = false){
		if($this->login()){
			$result	= $this->query('/sta.cgi');
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

    public function status_new($array = false){
        if($this->login()){
            $result = $this->query('/status-new.cgi');
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

    public function ifstats($array = false){
        if($this->login()){
            $result = $this->query('/ifstats.cgi');
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

    public function iflist($array = false){
        if($this->login()){
            $result = $this->query('/iflist.cgi');
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

    public function brmacs($array = false){
        if($this->login()){
            $result = $this->query('/brmacs.cgi?brmacs=y');
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

    public function station_kick($mac, $interface, $array = false){
        if($this->login()){
            $result = $this->query('/stakick.cgi?staid='.$mac.'&staif='.$interface);
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

    public function spectrum($timeout = 10, $array = false){
        if($this->login()){
            $result = $this->query('/survey.json.cgi', $timeout);
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


    public function getSignalPon($ruta) {

        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->_baseurl.$ruta);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);


        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookieanfit-'.$this->_ip);
        $result = curl_exec($ch);
        
	//echo "Result vale: $result <br/>";
	 if( preg_match_all( '(\-[0-9][0-9]+\.[0-9][0-9][0-9][0-9][0-9][0-9])',$result, $matches)) {
           $rx=$matches[0][0];
           //var_dump($matches);
         }

	return($rx);


    }




    public function getThroughputTX() {

      if($this->login()){
        
	sleep(2);           // Pausamos despues del login para evitar que la subida nos de elevada
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

            $signal1 = $this->getSignalPon("/status_pon.asp");
	    $signal2 = $signal1;
            $mac = strtoupper(trim($this->_mac));
	    

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
      return($this->_signal1);

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
