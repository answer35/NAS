<?php

/****************************************
/* index.php
/* Utilité :    Recupere la liste des torrents en cours et termines depuis alldebrid, examine le resultat et le torrent est termine
/*              envoi a jdownloader le fichier puis supprime de alldebrid
/* Usage : php5 /var/www/ScriptHome/index.php
/* Auteur : BFAM
/* Version : 1.1.3
/* Mis a jour le : 21/03/2017 
/****************************************/


/*********************************************
/*
/* Configuration des variables d'environement
/*
/********************************************/
// go debrid
$godebrid = "/root/go/bin/";
// jdownloader
$jdownloader = "/opt/jdownloader/JDownloader.jar";
// folderwatch pour les multi-paquets
$folderwatch = "/home/Downloads/FolderWatch/";
// alldebrid uid cookie a obtenir avec l'url : https://alldebrid.com/api.php?action=info_user&login=<USERNAME>&pw=<PASSWORD>&format=json
$cookie = "48c60c6129f7203ff9205580"; 


/*********************************************
/*
/* Debut du script
/*
/********************************************/


function get_string_between($string, $start, $end)
  {
  $string = " " . $string;
  $ini = strpos($string, $start);
  if ($ini == 0) return "";
  $ini+= strlen($start);
  $len = strpos($string, $end, $ini) - $ini;
  return substr($string, $ini, $len);
  }

$url1 = 'http://www.alldebrid.com/api/torrent.php?json=true';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  "Cookie: uid=" . $cookie
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
$result = curl_exec($ch);
$json = json_decode($result, true);

        
/* on a maintenant un tableau de DL, on va ve©rifier si le dl est termine©, si oui, on va recupe©rer l$ */
foreach($json as $index => $dlJson){
    /* Reactribution des index vers du texte pour plus de lisibilité */
    $dl['Status'] = $dlJson[4];
    $dl['ID'] = $dlJson[1];
    $dl["Links"] = getLinks($dlJson[10]);
    $dl['Filename'] = $dlJson[3];
    /* On verifie que le status du download est finished */ 
    if($dl['Status']=="finished" || $dl['Status']=="Finished"){
        /* On recupere la / les URL(s) obtenues */
	echo $dl["Links"];
        $urlExplode = json_decode($dl["Links"]);
	var_dump($urlExplode);
	$countedUrl = 0;
	foreach ($urlExplode as $key => $value) {
	    $countedUrl++;
	}
        /* On verifie le nombre d'url obtenues */
        echo "explode = ".$countedUrl.'<br>\n';
        if($countedUrl>=1){
            $dl["Filename"] = str_replace("<span class='torrent_filename'>","",$dl['Filename']);
            $dl["Filename"] = str_replace("</span>","",$dl['Filename']);
            // il y a plusieurs paquets, on va creer un fichier crawjob pour y mettre les infos.
            $logFile = fopen($folderwatch.$dl["Filename"].".crawljob", 'a+');
	    $packageName = ''; 
            foreach ($urlExplode as $url => $name) {
		if($packageName == '')
                	$packageName = str_replace(" ","_",$name);
                fputs($logFile,"->NEW ENTRY<- \n");
                fputs($logFile,"enabled=TRUE \n");
                fputs($logFile,"text=".$url." \n");
                fputs($logFile,"packageName=".$packageName." \n\n");
            }
        } else {
	    $urlExplode = explode(",;,",$dl["Links"]);
	    var_dump($urlExplode);
	    foreach ($urlExplode as $name => $url) {
                echo "dl num : $index est termine! son URL est $url et son nom est $name et son id est ".$dl['ID']." \n";
		/* Envoi du lien a jdownloader */
		exec("java -jar ".$jdownloader." -a ".$url);
	    }
        }
        /* On supprime le fichier de la liste de alldebrid */
        $url2 = 'https://alldebrid.fr/torrent/?action=remove&id='.$dl['ID'];
          
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $url2);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, array(
          "Cookie: uid=" . $cookie
        ));
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch2, CURLOPT_HEADER, 0);
        curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, 1);
        $result2 = curl_exec($ch2);
    } else {
        /* Download toujours en cours de DL */
        echo "pas terminé \n";
    }
}


function getLinks($linkToClear){
    //echo $linkToClear;
    $dom = new DOMDocument();
    @$dom->loadHTML($linkToClear);
    foreach($dom->getElementsByTagName('a') as $link) {
        $ddlink = $link->getAttribute('value');
        //echo $ddlink;
    }
    return $ddlink;
}
?>
