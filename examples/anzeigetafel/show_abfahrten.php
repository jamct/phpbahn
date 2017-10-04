<?
include('../../phpbahn.php');
include('settings.php');

$bahn = new phpbahn(SETTING_APIKEY);
$bhf = $bahn->getStation(SETTING_BAHNHOF) ;
reset($bhf);
$ibnr = key($bhf);
$bhf = array_shift($bhf);

$zuege = $bahn->getTimetable($ibnr, time()) ;

$output = "";
$output .= '<div id="header">Abfahrten für '.$bhf['name'].'<span style="float:right">'.date("H:i").'</span></div>';

$output .= "<table class='abfahrttafel'><tr><th>Zug</th><th>Geplante Abfahrt</th><th>Heutige Abfahrt</th><th>Geplantes Gleis</th><th>Heutiges Gleis</th><th>Ziel</th><th>Über</th></tr>";

//Die gefundenen Elemente werden nacheinander zu Tabellenzeilen
foreach($zuege as $zug){
    
    //Dies ist eine Abfahrttafel. Daher werden nur Elemente berücksichtigt, die eine Abfahrt enthalten:
    if(isset($zug['abfahrt'])){
          
        $ziel = array_pop($zug['abfahrt']['routeGeplant']);
        
        $naechsteHalte = array_slice($zug['abfahrt']['routeGeplant'], 0, SETTING_STOPS);
        $strecke = implode(", ",$naechsteHalte);
        
        $output .= "<tr>";    
            $output .= "<td>".$zug['zug']['klasse']." ".$zug['zug']['nummer']."</td>";
            $output .= "<td>".date("H:i", $bahn->dateToTimestamp($zug['abfahrt']['zeitGeplant']))."</td>";
        
            if(@$zug['abfahrt']['cancel'] == "cancelled"){
                $output .= "<td class='change'>FÄLLT AUS</td>"; 
            }elseif(isset($zug['abfahrt']['zeitAktuell'])){
                $output .= "<td class='change'>".date("H:i", $bahn->dateToTimestamp($zug['abfahrt']['zeitAktuell']))."</td>";
            }else{
                $output .= "<td></td>";
            }
        
            $output .= "<td>".$zug['abfahrt']['gleisGeplant']."</td>";
        
            if(isset($zug['abfahrt']['gleisAktuell'])){
                $output .= "<td class='change'>".$zug['abfahrt']['gleisAktuell']."</td>";
            }else{
                 $output .= "<td></td>";
            }
        
        
            $output .= "<td>".$ziel."</td>";
            $output .= "<td>".$strecke."</td>";
        
        $output .= "</tr>";
    }
}

$output .= "</table>";

//Ausgabe
echo $output;

?>