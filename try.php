<?php
/* Variables */
/* token a recuper via l'api user */
$token = 'fa6ee7f7108b27cf0c1ee01ae6243e0b17ra3';

$file_name_with_full_path = '/home/test.torrent';
$cFile = curl_file_create($file_name_with_full_path);
var_dump($cFile);
$data = array('magnet'=> urlencode('magnet:?xt=urn:btih:1a2b6c93e94691ab06d126faf2188181f783ac08&'));

$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.real-debrid.com/rest/1.0/torrents/addMagnet");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', "Authorization: Bearer NOF72YM4XHQOJ35UVBIQ3F7PFQE25XOD4I5V7MZY5RWBZUH2BNQA"));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$file_name_with_full_path);
    $result = curl_exec($ch);
    $json = json_decode($result, true);
    var_dump($json)

?>