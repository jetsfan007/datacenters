<?php
require 'scraperwiki.php';

$html = scraperWiki::scrape("http://www.datacentermap.com/datacenters.html");

require 'scraperwiki/simple_html_dom.php';

$dom = new simple_html_dom();
$dom->load($html);
mb_convert_encoding($dom, "ISO-8859-1", "UTF-8");

$unique_keys=array('country','area','detailarea','datacenterCity','datacenterName','postal-code','street-address','organizationName','latitude','longitude','accuracy');

foreach($dom->find("div[class='lefttext'] div[class] a") as $data){ 
    $bs = $data->find("b");
    if(count($bs)==1){
        $input = $bs[0]->plaintext;
        $country = substr($input,0,strpos($input,' ('));
        $countryhtml = scraperWiki::scrape("http://www.datacentermap.com/".strtolower($country)."/");
        $countrydom = new simple_html_dom();
        $countrydom->load($countryhtml);
        foreach($countrydom->find("div[class='lefttext'] div[class] a") as $countrydata){
            $bbs = $countrydata->find("b");
            if(count($bbs)==1){
                $countryinput = $bbs[0]->plaintext;
                //print "countryinput: ".$countryinput." ";
                $area = substr($countryinput,0,strpos($countryinput,' ('));
                if (strtolower($country)=='usa'){
                    $areahtml = scraperWiki::scrape("http://www.datacentermap.com/".strtolower($country)."/".strtolower($area)."/");
                    $areadom = new simple_html_dom();
                    $areadom->load($areahtml);
                    $address=$area.', '.$country;
                    foreach($detailareadom->find("div[class='lefttext'] div[class~='DCColumn'] a[title]") as $detailareadata){
                        $datacenter = $detailareadata->plaintext;                        
                        $datacenterurl = $detailareadata->href;
                        $datacenterhtml = scraperWiki::scrape("http://www.datacentermap.com/".strtolower($country)."/".strtolower($area)."/".strtolower($detailarea)."/".strtolower($datacenterurl));
                        $datacenterdom = new simple_html_dom();
                        $datacenterdom->load($datacenterhtml);
                        if (strcmp($detailarea,$area)!=0){
                            $address=$detailarea.', '.$address;
                        }
                        $datacenterstreet=" ";
                        $datacentercity=" ";
                        $datacenterpostal=" ";
                        $datacenterorgname=" ";
                        foreach($datacenterdom->find("div[class='adr']") as $datacenterdata){ 
                            $dc = $datacenterdata->find("span[class='locality']");
                            if (count($dc)==1){
                                $datacentercity = $dc[0]->innertext;
                                if (strcmp($datacentercity,$detailarea)!=0){
                                    $address=$datacentercity.', '.$address;
                                }
                            }
                            $dc = $datacenterdata->find("span[class='postal-code']");
                            if (count($dc)==1){
                                $datacenterpostal = $dc[0]->innertext;
                                $address=$datacenterpostal.', '.$address;
                            }
                            $dc = $datacenterdata->find("div[class='street-address']");
                            if (count($dc)==1){
                                $datacenterstreet = $dc[0]->innertext;
                                $address=$datacenterstreet.', '.$address;
                            }
                            $dc = $datacenterdata->find("span[class='organization-name']");
                            if (count($dc)==1){
                                $datacenterorgname = $dc[0]->innertext;
                                $address=$datacenterorgname.', '.$address;
                            }                                                                       

                        }
                        $locationarray = lookup(utf8_encode($address));
                        $record = array(
                            'country' => utf8_encode($country),
                            'area' => utf8_encode($area),
                            'detailarea' => utf8_encode($detailarea),
                            'datacenterCity' => utf8_encode($datacentercity),
                            'datacenterName' => utf8_encode($datacenter),
                            'postal-code' => utf8_encode($datacenterpostal),                                     
                            'street-address' => utf8_encode($datacenterstreet),
                            'organizationName' => utf8_encode($datacenterorgname),
                            'latitude' => $locationarray['lat'],
                            'longitude' => $locationarray['long'],
                            'accuracy' => utf8_encode($locationarray['location_type']),
                        );
                        scraperwiki::save_sqlite($unique_keys, $record, $table_name="data");
                    }
                }
                else {            
                    $detailarea=$area;
                    //print "datacenter: ".$datacenter.(strpos($countryinput,'(')+1)."-".(substr($countryinput, (strpos($countryinput,'(')+1),-1))." :";
                    $amount = intval(substr($countryinput, (strpos($countryinput,'(')+1),-1));
                    $detailareahtml = scraperWiki::scrape("http://www.datacentermap.com/".strtolower($country)."/".strtolower($detailarea)."/");
                    $detailareadom = new simple_html_dom();
                    $detailareadom->load($detailareahtml);
                    foreach($detailareadom->find("div[class='lefttext'] div[class~='DCColumn'] a[title]") as $detailareadata){
                        $datacenter = $detailareadata->plaintext;                        
                        $datacenterurl = $detailareadata->href;
                        $datacenterhtml = scraperWiki::scrape("http://www.datacentermap.com/".strtolower($country)."/".strtolower($detailarea)."/".strtolower($datacenterurl));
                        $datacenterdom = new simple_html_dom();
                        $datacenterdom->load($datacenterhtml);
                        $address=$area.', '.$country;
                        $datacenterstreet=" ";
                        $datacentercity=" ";
                        $datacenterpostal=" ";
                        $datacenterorgname=" ";
                        foreach($datacenterdom->find("div[class='adr']") as $datacenterdata){                                                                                   
                            $dc = $datacenterdata->find("span[class='locality']");
                            if (count($dc)==1){
                                $datacentercity = $dc[0]->innertext;
                                if (strcmp($datacentercity,$detailarea)!=0){
                                    $address=$datacentercity.', '.$address;
                                }
                            }
                            $dc = $datacenterdata->find("span[class='postal-code']");
                            if (count($dc)==1){
                                $datacenterpostal = $dc[0]->innertext;
                                $address=$datacenterpostal.', '.$address;
                            }
                            $dc = $datacenterdata->find("div[class='street-address']");
                            if (count($dc)==1){
                                $datacenterstreet = $dc[0]->innertext;
                                $address=$datacenterstreet.', '.$address;
                            }
                            $dc = $datacenterdata->find("span[class='organization-name']");
                            if (count($dc)==1){
                                $datacenterorgname = $dc[0]->innertext;
                                $address=$datacenterorgname.', '.$address;                                
                            }  
                        }
                        $locationarray = lookup(utf8_encode($address));
                        $record = array(
                            'country' => utf8_encode($country),
                            'area' => utf8_encode($area),
                            'detailarea' => utf8_encode($detailarea),
                            'datacenterCity' => utf8_encode($datacentercity),
                            'datacenterName' => utf8_encode($datacenter),
                            'postal-code' => utf8_encode($datacenterpostal),                                     
                            'street-address' => utf8_encode($datacenterstreet),
                            'organizationName' => utf8_encode($datacenterorgname),
                            'latitude' => $locationarray['lat'],
                            'longitude' => $locationarray['long'],
                            'accuracy' => utf8_encode($locationarray['location_type'])
                        );
                        //print json_encode($record) . "\n";
                        
                        scraperwiki::save_sqlite($unique_keys, $record, $table_name="data");
                    }
                }
            }
        }
    }
}


function lookup($string){
   $newsearch = explode(',',utf8_decode($string));
   //print_r(count($newsearch).' '.$newsearch[count($newsearch)-5].', '.$newsearch[count($newsearch)-4].', '.$newsearch[count($newsearch)-3].', '.$newsearch[count($newsearch)-2].', '.$newsearch[count($newsearch)-1]);
   $string = str_replace (" ", "+", urlencode($string));
   //$details_url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$string."&sensor=false";
   $details_url = "http://nominatim.openstreetmap.org/search?q=".$string."&format=json";   
//print_r($details_url);
 
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $details_url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $response = json_decode(curl_exec($ch), true);
     
   if (empty($response[0])){
        //print_r('empty, trying again.');
        $recursivesearch='';
        for ($i=count($newsearch)-1;$i>1;$i--){
            $recursivesearch+=$newsearch[$i].', ';
        }
        $recursivesearch=substr($recursivesearch,0,-2);
        //print_r('Broadeninging search from...'.array_values($newsearch).' to...'.$recursivesearch);
        $recursivesearch = str_replace (" ", "+", urlencode($recursivesearch));
        $details_url = "http://nominatim.openstreetmap.org/search?q=".$recursivesearch."&format=json";
        //print_r($details_url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $details_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = json_decode(curl_exec($ch), true);

    }
   if (empty($response[0])){
        //print_r('empty, trying again.');
        $recursivesearch='';
        for ($i=count($newsearch)-1;$i>2;$i--){
            $recursivesearch+=$newsearch[$i].', ';
        }
        //print_r('Further broadeninging search from...'.array_values($newsearch).' to...'.$recursivesearch);        
        $recursivesearch = str_replace (" ", "+", urlencode($recursivesearch));
        $details_url = "http://nominatim.openstreetmap.org/search?q=".$recursivesearch."&format=json";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $details_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = json_decode(curl_exec($ch), true);
    }
   if (empty($response[0])){ //$response[0]['lat']==null
        //print_r('empty, giving up, using '.$newsearch[count($newsearch)-1].', '.$newsearch[count($newsearch)-2]);
        $recursivesearch=$newsearch[count($newsearch)-1].', '.$newsearch[count($newsearch)-2];
        $recursivesearch = str_replace (" ", "+", urlencode($recursivesearch));
        $details_url = "http://nominatim.openstreetmap.org/search?q=".$recursivesearch."&format=json";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $details_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = json_decode(curl_exec($ch), true);
    }


   // If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST


/*
   if (($response['status'] == 'ZERO_RESULTS') && (count($newsearch)>5)) { 
        $recursivesearch=$newsearch[count($newsearch)-5].', '.$newsearch[count($newsearch)-4].', '.$newsearch[count($newsearch)-3].', '.$newsearch[count($newsearch)-2].', '.$newsearch[count($newsearch)-1];
        print_r('Broadeninging search from...'.array_values($newsearch).' to...'.$recursivesearch);
        return lookup(utf8_encode($recursivesearch));
   }
   else if (($response['status'] == 'ZERO_RESULTS') && (count($newsearch)>3)){ 
        $recursivesearch=$newsearch[count($newsearch)-1].', '.$newsearch[count($newsearch)-2].', '.$newsearch[count($newsearch)-3];
        print_r('Further broadeninging search from...'.array_values($newsearch).' to...'.$recursivesearch);
        return lookup(utf8_encode($recursivesearch));
   }
   else if (($response['status'] == 'ZERO_RESULTS') && (count($newsearch)>1)){ 
        print_r('Final broadeninging search from...'.array_values($newsearch).' to...'.$newsearch[count($newsearch)-1]);
        return lookup(utf8_encode($newsearch[count($newsearch)-1]));
   }
   else if ($response['status'] != 'OVER_QUERY_LIMIT') {
        print_r('over query limit');
        return null;
   }
   else if ($response['status'] != 'REQUEST_DENIED') {
        print_r('request Denied');
        return null;
   }
   else if ($response['status'] != 'INVALID_REQUEST') {
        print_r('invalid request');
        return null;
   }
   else if ($response['status'] != 'OK') {
        print_r('giving up location');
        return null;
   }
*/
 
   //print_r($response);
//   $geometry = $response['results'][0]['geometry'];
 
    
    //$longitude = $geometry['location']['lng'];
    //$latitude = $geometry['location']['lat'];
/*
    $array = array(
        'lat' => $geometry['location']['lat'],
        'long' => $geometry['location']['lng'],
        'location_type' => $geometry['location_type'],
    );
 */

    $array = array(
        'lat' => $response[0]['lat'],
        'long' => $response[0]['lon'],
        'location_type' => $response[0]['type'],
    );

    if (array_key_exists('lng', $response[0])){
        $array = array(
            'lat' => $response[0]['lat'],
            'long' => $response[0]['lng'],
            'location_type' => $response[0]['type'],
        );
}
   if (array_key_exists('latitude', $response[0])){
        $array = array(
            'lat' => $response[0]['latitude'],
            'long' => $response[0]['longitude'],
            'location_type' => $response[0]['type'],
        );
}

    return $array;
}
?>

