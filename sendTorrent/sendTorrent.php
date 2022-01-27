<?php
include 'functions.php';
checkConfig();
$iniFile = parse_ini_file("alldebrid.ini", true);
$token = $iniFile['Logins']['token'];
$torrentPath = $iniFile['Paths']['torrentFolder'];
$folderwatch = $iniFile['Paths']['folderWatch'];

#get list of torrent files in downloading folder
$allFiles = array_diff(scandir($torrentPath), [".", ".."]); 
#if there is more than one file, start uploading files to alldebrid
foreach ($allFiles as $id => $filename) {
    if(strpos($filename,".torrent")>0){
        echo "Start sending torrent ! ";
        /* renaming file for replace any special character */
        $chaine = preg_replace("#[^a-zA-Z0-9]#", "_", $filename);
        /* As letter '.' has been removed, recreate file extension */
        $chaine = str_replace("_torrent",".torrent",$chaine);
        /* move original file with new name */
        exec('mv "'.$torrentPath.$filename.'" "'.$torrentPath.$chaine.'"');
        /* start sending torrent request by Curl */
        $torrent = new CURLFile($torrentPath.$chaine, 'application/x-bittorrent');
        $addTorrent = curl_init('https://api.alldebrid.com/v4/magnet/upload/file?agent=debridToJdown&apikey='.$token);
        curl_setopt($addTorrent, CURLOPT_POST, true);
        curl_setopt($addTorrent, CURLOPT_POSTFIELDS, ['files[]' => $torrent]);
        curl_setopt($addTorrent, CURLOPT_RETURNTRANSFER, true);
        $resultAddTorrent = curl_exec($addTorrent);
        $uploadStatus = json_decode($resultAddTorrent, true);
        if($uploadStatus['status'] == 'success'){
            if (!isset($uploadStatus['data']['files'][0]['error'])){
                echo "added correctly\n";
                exec("mv ".$torrentPath.$chaine." ".$torrentPath."success/".$chaine);
            } else {
                echo "Sent but in error in Alldebrid ...\n";
                exec("mv ".$torrentPath.$chaine." ".$torrentPath."error/".$chaine);
            }
            
        } else {
            echo "Torrent not sent, please check manually ... \n";
            exec("mv ".$torrentPath.$chaine." ".$torrentPath."error/".$chaine);
        }
    }
}

#start getting links from alldebrid
$torrentList = 'https://api.alldebrid.com/v4/magnet/status?agent=debridToJdown&apikey='.$token;
$torrentStatus = getHttpRequest($torrentList);
var_dump($torrentStatus);
if(isset($torrentStatus["status"]) && $torrentStatus["status"] == "success"){
    echo "List recieved\n";
    foreach ($torrentStatus['data']['magnets'] as $key => $torrent) {
        if($torrent['statusCode']==4){
            if (!file_exists($folderwatch.'tmp/')) {
                mkdir($folderwatch.'tmp/', 0777, true);
            }
            echo "Torrent finished, start creating crawjob for ".$torrent["filename"]."\n";
            $crawljobFile = fopen($folderwatch.'tmp/'.$torrent["filename"].".crawljob", 'a+');
            foreach ($torrent['links'] as $key => $link) {
                fputs($crawljobFile,"->NEW ENTRY<- \n");
                fputs($crawljobFile,"enabled=TRUE \n");
                fputs($crawljobFile,"text=\"".$link['link']."\" \n");
                fputs($crawljobFile,"packageName=\"".$torrent["filename"]."\" \n\n");
            }
            exec('chmod 777 "'.$folderwatch.'tmp/'.$torrent["filename"].".crawljob");
            exec('mv "'.$folderwatch.'tmp/'.$torrent["filename"].'.crawljob" "'.$folderwatch.$torrent["filename"].'.crawljob"');
            /* On supprime le fichier de la liste de alldebrid */
            /*$deleteTorrent = 'https://api.alldebrid.com/v4/magnet/delete?agent=debridToJdown&apikey='.$token.'&id='.$torrent['id'];
            $deleteTorrentStatus = getHttpRequest($deleteTorrent);
            if($deleteTorrentStatus['status'] == 'success')
                echo "torrent: ".$torrent['filename']." deleted successfully\n";*/
        }
    }
} else {
    echo "cannot get access to alldebrid...\n";
}

?>