<?php
/* Variables */
/* token a recuper via l'api user */
$token = 'fa6ee7f7108b27cf0c1ee01ae6243e0b17ra3';
$torrentPath = "/home/Downloads/Torrents/";
$folderwatch = "/home/Downloads/FolderWatch/";

#get list of torrent files in downloading folder
$allFiles = array_diff(scandir('/home/Downloads/Torrents/'), [".", ".."]); 
#if there is more than one file, start uploading files to alldebrid
foreach ($allFiles as $id => $filename) {
    if(strpos($filename,".torrent")>0){
        echo "Start sending torrent ! ";
        /* renaming file for replace any special character */
        $chaine = preg_replace("#[^a-zA-Z0-9]#", "_", $filename);
        /* As letter '.' has been removed, recreate file extension */
        $chaine = str_replace("_torrent",".torrent",$chaine);
        /* move original file with new name */
        exec('mv "/home/Downloads/Torrents/'.$filename.'" "/home/Downloads/Torrents/'.$chaine.'"');
        /* start sending torrent request by Curl */
        $torrent = new CURLFile($torrentPath.$chaine, 'application/x-bittorrent');
        $addTorrent = curl_init('https://api.alldebrid.com/magnet/upload/file?token='.$token);
        curl_setopt($addTorrent, CURLOPT_POST, true);
        curl_setopt($addTorrent, CURLOPT_POSTFIELDS, ['files[]' => $torrent]);
        curl_setopt($addTorrent, CURLOPT_RETURNTRANSFER, true);
        $resultAddTorrent = curl_exec($addTorrent);
        $uploadStatus = json_decode($resultAddTorrent, true);
        if($uploadStatus['success']){
            echo "added correctly\n";
            exec("mv /home/Downloads/Torrents/".$chaine." /home/Downloads/Torrents/success/".$chaine);
        } else {
            exec("mv /home/Downloads/Torrents/".$chaine." /home/Downloads/Torrents/error/".$chaine);
        }
    }
}

#start getting links from alldebrid
$torrentList = 'https://api.alldebrid.com/user/torrents?token='.$token;
$torrentStatus = getHttpRequest($torrentList);
if($torrentStatus['success']){
    echo "List recieved\n";
    foreach ($torrentStatus['torrents'] as $key => $torrent) {
        if($torrent['statusCode']==4){
            echo "Torrent finished, start creating crawjob for ".$torrent["filename"]."\n";
            $crawljobFile = fopen($folderwatch.$torrent["filename"].".crawljob", 'a+');
            foreach ($torrent['link'] as $key => $link) {
                fputs($crawljobFile,"->NEW ENTRY<- \n");
                fputs($crawljobFile,"enabled=TRUE \n");
                fputs($crawljobFile,'text="'.$link.'" \n');
                fputs($crawljobFile,"packageName=".$torrent["filename"]." \n\n");
            }
            exec('chmod 777 "'.$folderwatch.$torrent["filename"].'".crawljob');
            /* On supprime le fichier de la liste de alldebrid */
            
            $url2 = 'https://alldebrid.fr/torrent/?action=remove&id='.$torrent['id'];
            $deleteTorrent = 'https://api.alldebrid.com/magnet/delete?token='.$token.'&id='.$torrent['id'];
            $deleteTorrentStatus = getHttpRequest($deleteTorrent);
            if($deleteTorrentStatus['success'])
                echo "torrent: ".$torrent['filename']." deleted successfully\n";
        }
    }
} else {
    echo "cannot get access to alldebrid...\n";
}

/* Functions */

/**
 * Function getHttpRequest
 * 
 * @input : $url    - url to ask
 * 
 * @return : $json  - result of httprequest 
 */
function getHttpRequest($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $result = curl_exec($ch);
    $json = json_decode($result, true);
    return $json;
}
?>