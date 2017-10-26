<?
class phpbahn {
	
	private $apikey;
	private $apis;
		
	function __construct($apikey){
		$this->apikey = $apikey;
        
        if(strlen($apikey) < 5){
           trigger_error("Fehler: Kein API-Key angegeben.", 256);
            return false;
        }
        
		$this->apis = array("timetables" => array("url"=> "https://api.deutschebahn.com/timetables/v1/", "return" => "xml" ), "fahrplan-plus"=> array("url"=> "https://api.deutschebahn.com/fahrplan-plus/v1/", "return"=>"json" ));
		
	}
	
	private function bahnCurl($request, $api ){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->apis[$api]['url'].$request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	
		//echo $this->apis[$api]['url'].$request;
	
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$headers = array();
	
		$headers[] = "Authorization: Bearer ".$this->apikey;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);

		if (curl_errno($ch)) {
			trigger_error('Fehler:' . curl_error($ch));
            return false;
		}
		curl_close ($ch);
		
		if($this->apis[$api]['return'] == "xml" ){
			$xml = simplexml_load_string($result, "SimpleXMLElement", LIBXML_NOCDATA);
			$json = json_encode($xml);
			$array = json_decode($json,TRUE);
		}elseif($this->apis[$api]['return'] == "json" ){
			$array = json_decode($result,TRUE);
			
		}
		
			
		return $array;
		
		
	} 
	
	
	public function getStation($stationName){
		
		$request = "location/".rawurlencode($stationName);
		
		$result = $this->bahnCurl($request, "fahrplan-plus" );
		if(count($result) ){
            
            if(isset($result['error'])){
            trigger_error("Fehler: ".$result['error']['description'], 256);
                return;
              
            }
            
            
			foreach($result as $res){
				$i = $res['id'];
				unset($res['id']);
				$stationen[$i]= $res;
			}
		}else{
			return false;
		}
		
		return $stationen;
	
	}
	
	public function getTimetable($stationID, $time = 0){
		
        if($time == 0){
        $time = time();
        }
        
		$date = date("ymd", $time);
		$hour = date ("H", $time);
		
		$requestAbweichung = "fchg/".$stationID;
		$abweichungen = $this->bahnCurl($requestAbweichung, "timetables" );
		
		$sortAbweichung = array();
		
		//print_r($abweichungen);
		foreach($abweichungen['s'] as $abw){
			$sortAbweichung[$abw['@attributes']['id']] = $abw;		
		}
		//print_r($sortAbweichung);
		
		
		$requestFahrten = 'plan/'.$stationID.'/'.$date.'/'.$hour;
		$fahrten = $this->bahnCurl($requestFahrten, "timetables");
		
		$sortFahrten = array();
		foreach($fahrten['s'] as $fahrt){
			$id = $fahrt['@attributes']['id'];
			unset($fahrt['@attributes']);
            
            $fahrt['zug']['typ'] = $fahrt['tl']['@attributes']['t'];
            $fahrt['zug']['owner'] = $fahrt['tl']['@attributes']['o'];
            $fahrt['zug']['klasse'] = $fahrt['tl']['@attributes']['c'];
            $fahrt['zug']['nummer'] = $fahrt['tl']['@attributes']['n'];
                    
            unset($fahrt['tl']);
            
             $cancelstates = array("a"=>"added", "c"=>"cancelled", "p"=>"planned");
            
            if(isset($fahrt['ar'])){
               
                if(isset($fahrt['ar']['@attributes']['l'])){
                    $fahrt['ankunft']['line'] = $fahrt['ar']['@attributes']['l'];
                }
                
                $fahrt['ankunft']['zeitGeplant'] = $fahrt['ar']['@attributes']['pt'];
                if(isset($sortAbweichung[$id]['ar']['@attributes']['ct'])){
                    $fahrt['ankunft']['zeitAktuell'] = $sortAbweichung[$id]['ar']['@attributes']['ct'];
                }
                
                $fahrt['ankunft']['gleisGeplant'] = $fahrt['ar']['@attributes']['pp'];
                if(isset($sortAbweichung[$id]['ar']['@attributes']['cp'])){
                    $fahrt['ankunft']['gleisAktuell'] = $sortAbweichung[$id]['ar']['@attributes']['cp'];
                }
                
                $fahrt['ankunft']['routeGeplant'] = explode("|",$fahrt['ar']['@attributes']['ppth']);
                if(isset($sortAbweichung[$id]['ar']['@attributes']['cpth'])){
                    $fahrt['ankunft']['routeAktuell'] = explode("|",$sortAbweichung[$id]['ar']['@attributes']['cpth']);
                }
                
                if(isset($sortAbweichung[$id]['ar']['@attributes']['cs'])){
                    $fahrt['ankunft']['cancel'] = $cancelstates[$sortAbweichung[$id]['ar']['@attributes']['cs']];
                }
                
            }
            
			
            if(isset($fahrt['dp'])){
                
                 if(isset($fahrt['dp']['@attributes']['l'])){
                    $fahrt['abfahrt']['line'] = $fahrt['dp']['@attributes']['l'];
                }
                                
                $fahrt['abfahrt']['zeitGeplant'] = $fahrt['dp']['@attributes']['pt'];
                 if(isset($sortAbweichung[$id]['dp']['@attributes']['ct'])){
                    $fahrt['abfahrt']['zeitAktuell'] = $sortAbweichung[$id]['dp']['@attributes']['ct'];
                }
                
                $fahrt['abfahrt']['gleisGeplant'] = $fahrt['dp']['@attributes']['pp'];
                 if(isset($sortAbweichung[$id]['dp']['@attributes']['cp'])){
                    $fahrt['abfahrt']['gleisAktuell'] = $sortAbweichung[$id]['dp']['@attributes']['cp'];
                }
                
                $fahrt['abfahrt']['routeGeplant'] = explode("|",$fahrt['dp']['@attributes']['ppth']);
                if(isset($sortAbweichung[$id]['dp']['@attributes']['cpth'])){
                    $fahrt['abfahrt']['routeAktuell'] = explode("|",$sortAbweichung[$id]['dp']['@attributes']['cpth']);
                }
                
            
                 if(isset($sortAbweichung[$id]['dp']['@attributes']['cs'])){
                    $fahrt['abfahrt']['cancel'] = $cancelstates[$sortAbweichung[$id]['dp']['@attributes']['cs']];
                }
                
            }
            
            
            
            
            unset($fahrt['ar']);
            unset($fahrt['dp']);
            
            $sortFahrten[$id] = $fahrt;
            
		}
		
		return $sortFahrten;
	
		
	}
    
    public function dateToTimestamp($bahndatum){
        $date = DateTime::createFromFormat('ymdHi', $bahndatum);
        return date_timestamp_get($date);
        
        
    }
    
	
}
