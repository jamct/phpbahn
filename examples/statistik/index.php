<?
include('../../phpbahn.php');

include('settings.php');

//Vorbereitung des Abrufs
$bahn = new phpbahn(SETTING_APIKEY);
$bhf = $bahn->getStation(SETTING_BAHNHOF) ;
reset($bhf);
$ibnr = key($bhf);
$bhf = array_shift($bhf);

$zuege = $bahn->getTimetable($ibnr, time() );

if(!count($zuege)){
	echo "keine Verbindungen";
	
}

$datei = fopen('./data/statistik.csv', 'a');

foreach($zuege as $zug){
	$zugname = $zug['zug']['klasse'].$zug['zug']['nummer'];
	
	if(in_array($zugname, SETTING_LINES) AND isset($zug['abfahrt']) ){
		if(!isset($zug['abfahrt']['zeitGeplant'])){
			$verspaetung = 0;
		}else{
			$verspaetung = $bahn->dateToTimestamp($zug['abfahrt']['zeitAktuell'])-$bahn->dateToTimestamp($zug['abfahrt']['zeitGeplant']);
		}
		fputcsv($datei, array(time(), $zugname, $verspaetung));
		fwrite($datei, PHP_EOL);
		
	}
}
