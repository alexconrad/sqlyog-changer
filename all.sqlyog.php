<?php
define('CONNECTION_NAME', 'Name=');
define('SSHPWD', 'SshPwd=');
define('MYSQL_PASSWORD', 'Password=');

$homepath = $_SERVER['USERPROFILE'];

$newEncodedPass = '';
if (isset($_SERVER['argv'][1])) {
    $newEncodedPass = $_SERVER['argv'][1];
}

if (!isset($_SERVER['argv'][2])) {
    die("Specify a second parameter as ALL to confirm that\nyou want to change ALL the lines that start with SshPwd= and Password= with your new password");
}


if (empty($newEncodedPass)) {
    die("\tSpecify new encrypted password to be set.\n\tEncode your pass using https://github.com/gkralik/sqlyog-decode-pwd/ \n");
}

$sqlyog_ini_location = $homepath.DIRECTORY_SEPARATOR.'AppData'.DIRECTORY_SEPARATOR.'Roaming'.DIRECTORY_SEPARATOR.'SQLyog'.DIRECTORY_SEPARATOR.'sqlyog.ini';

//parse_ini_file($sqlyog_ini_location);//errors with
//PHP Warning:  syntax error, unexpected $end, expecting TC_DOLLAR_CURLY or TC_QUOTED_STRING or '"' in XXXsqlyog.ini on line XXX
$data = file_get_contents($sqlyog_ini_location);
if (!$data) {
    die("Cannot read ".$sqlyog_ini_location."\n");
}

$backup_filename = $sqlyog_ini_location.'.backup.'.date("YmdHis");
$backup = file_put_contents($backup_filename, $data);
if (!$backup) {
    die("Cannot backup ".$sqlyog_ini_location." TO ".$backup_filename."\n");
}

echo "BACKUP ".$backup_filename."\n";

$lines = explode(PHP_EOL, $data);
$newLines = $lines;

foreach ($lines as $key=>$line) {
    if (substr($line,0, strlen(CONNECTION_NAME)) == CONNECTION_NAME) {
        echo $line.PHP_EOL;
    }

    if (substr($line,0, strlen(SSHPWD)) == SSHPWD) {
        $newLine = SSHPWD.$newEncodedPass;
        echo $line." >> ".$newLine.PHP_EOL;
        $lines[$key] = $newLine;
    }
    if (substr($line,0, strlen(MYSQL_PASSWORD)) == MYSQL_PASSWORD) {
        $newLine = MYSQL_PASSWORD.$newEncodedPass;
        echo $line." >> ".$newLine.PHP_EOL;
        $lines[$key] = $newLine;
    }
}

$wrote = file_put_contents($sqlyog_ini_location, implode(PHP_EOL, $lines));
if (!$wrote) {
    die("Cannot write to ".$sqlyog_ini_location."\n");
}

echo "Finished.\n";
