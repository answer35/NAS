<?php

/**
 * check configuration file create it if needed and prompt for values
 * 
 * @param null
 * @return null
 */
function checkConfig() {
    if (file_exists("alldebrid.ini")){
        echo "file exist\n";
        $iniFile = parse_ini_file("alldebrid.ini", true);
        /* Check token */
        if (isset($iniFile['Logins']["token"]) && $iniFile['Logins']['token']!=""){
            echo "token is :".$iniFile['Logins']["token"]."\n";
        } 
        else {
            getToken($iniFile);
        }
        $iniFile = parse_ini_file("alldebrid.ini", true);
        /* Check torrent path */
        if (isset($iniFile['Paths']["torrentFolder"]) && $iniFile['Paths']['torrentFolder']!=""){
            echo "torrent folder is :".$iniFile['Paths']["torrentFolder"]."\n";
        } 
        else {
            getPath($iniFile,"torrentFolder","torrent folder");
        }
        $iniFile = parse_ini_file("alldebrid.ini", true);
        /* Check folder watch path */
        if (isset($iniFile['Paths']["folderWatch"]) && $iniFile['Paths']['folderWatch']!=""){
            echo "folder Watch is :".$iniFile['Paths']["folderWatch"]."\n";
        } 
        else {
            getPath($iniFile,"folderWatch","folder watch for jdownloader");
        }
    } else {
        /* ini file isn't present, create it */
        exec("touch alldebrid.ini");
        $iniFile = fopen("alldebrid.ini", 'a+');
        fputs($iniFile,"[Logins]\n");
        fputs($iniFile,"token = ''\n");
        fputs($iniFile,"\n");
        fputs($iniFile,"[Paths]\n");
        fputs($iniFile,"torrentFolder = ''\n");
        fputs($iniFile,"folderWatch = ''\n");
        fclose($iniFile);
        checkConfig();
    }
}

/**
 * create a pin code for alldebrid token
 * then ask user to go to alldebrid to valid pin
 * then get token to fill configuration file
 * 
 * @param array $iniFile    array of parsed ini file
 * @return null
 */
function getToken($iniFile) {
    echo "token not existing, check pin code\n";
    echo "try to get pin code from alldebrid\n";
    $apiEndpoint = "https://api.alldebrid.com/pin/get?agent=debridToJdown";
    $pinInfo = getHttpRequest($apiEndpoint);
    echo "please visit ".$pinInfo['user_url']." when logged in alldebrid: \n";
    $answer = false;
    while (!$answer) {
        echo "\nAre you sure you want to do this?  Type 'yes' to continue or 'quit' to leave: ";
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        if(trim($line) == 'quit'){
            echo "ABORTING!\n";
            exit;
        } elseif (trim($line)=='yes') {
            $checkUrl = $pinInfo['check_url'];
            $tokenInfo = getHttpRequest($checkUrl);
            if($tokenInfo["success"]){
                echo "\n\n\n SUCCESS !!!! your token is :\n\n";
                echo $tokenInfo["token"]."\n\n";
                $iniFile['Logins']["token"] = $tokenInfo["token"];
                echo "Set value in alldebrid.ini file...";
                write_ini_file('alldebrid.ini', $iniFile);
                fclose($handle);
                echo "\n"; 
                echo "Thank you, continuing...\n";
                $answer = true;
            } else {
                echo "error during request, please visit link bellow or restart this script...\n\n";
                echo$pinInfo['user_url']."\n";
            }
            
        }
    }
}

/**
 * create a pin code for alldebrid token
 * then ask user to go to alldebrid to valid pin
 * then get token to fill configuration file
 * 
 * @param array $iniFile    array of parsed ini file
 * @return null
 */
function getPath($iniFile, $attribut, $comment) {
    $answer = false;
    while (!$answer) {
        echo "\nPlease enter path for ".$comment." or type 'quit' to leave: \n";
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        if(trim($line) == 'quit'){
            echo "ABORTING!\n";
            exit;
        } elseif (is_dir(trim($line))) {
            $iniFile['Paths'][$attribut] = trim($line);
            echo "Set value in alldebrid.ini file...";
            if(substr(trim($line), -1) != "/")
                $iniFile['Paths'][$attribut] .= "/";
            write_ini_file('alldebrid.ini', $iniFile);
            fclose($handle);
            echo "\n"; 
            echo "Thank you, continuing...\n";
            $answer = true;           
        } else {
            echo trim($line)." is not a valid path... retry! \n";
        }
    }
}

/**
 * send Curl request to specified URL
 * 
 * @param string $url
 * @return array $json response from site
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

/**
 * Write an ini configuration file
 * 
 * @param string $file
 * @param array  $array
 * @return bool
 */
function write_ini_file($file, $array = []) {
    // check first argument is string
    if (!is_string($file)) {
        throw new \InvalidArgumentException('Function argument 1 must be a string.');
    }

    // check second argument is array
    if (!is_array($array)) {
        throw new \InvalidArgumentException('Function argument 2 must be an array.');
    }

    // process array
    $data = array();
    foreach ($array as $key => $val) {
        if (is_array($val)) {
            $data[] = "[$key]";
            foreach ($val as $skey => $sval) {
                if (is_array($sval)) {
                    foreach ($sval as $_skey => $_sval) {
                        if (is_numeric($_skey)) {
                            $data[] = $skey.'[] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                        } else {
                            $data[] = $skey.'['.$_skey.'] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                        }
                    }
                } else {
                    $data[] = $skey.' = '.(is_numeric($sval) ? $sval : (ctype_upper($sval) ? $sval : '"'.$sval.'"'));
                }
            }
        } else {
            $data[] = $key.' = '.(is_numeric($val) ? $val : (ctype_upper($val) ? $val : '"'.$val.'"'));
        }
        // empty line
        $data[] = null;
    }

    // open file pointer, init flock options
    $fp = fopen($file, 'w');
    $retries = 0;
    $max_retries = 100;

    if (!$fp) {
        return false;
    }

    // loop until get lock, or reach max retries
    do {
        if ($retries > 0) {
            usleep(rand(1, 5000));
        }
        $retries += 1;
    } while (!flock($fp, LOCK_EX) && $retries <= $max_retries);

    // couldn't get the lock
    if ($retries == $max_retries) {
        return false;
    }

    // got lock, write data
    fwrite($fp, implode(PHP_EOL, $data).PHP_EOL);

    // release lock
    flock($fp, LOCK_UN);
    fclose($fp);

    return true;
}