#!/usr/bin/php -q
<?php

function writeLog($str)
{
    $logFile = __DIR__."/var/log/restore.log";

    $write = sprintf("sudo -u root echo %s %s >> %s", date('Y-m-d h:i:s'), $str, $logFile);
    shell_exec($write);
}

writeLog("test");

function echoHelp()
{
    echo "\n";
    echo "Usage: php restoredb.php [-restore] [user] [password] [database] [file]\n";
    echo "                         [-list]\n";
    echo "                         [-help]\n";
    echo "\n";
    echo "Restore -- Import .sql file from backups directory.\n";
    echo "List   -- List backup files in the backups directory.\n";
    echo "Help   -- Print help screen.\n";
    echo "\n";
    echo "*NOTE*: SQL files must be placed in a \"backups\" directory.\n";
    echo "        Only give the filename, not the full path.\n";
    echo "\n";
}

function listBackupsDir()
{
    $files = scandir(__DIR__."/backups");
	
	if (count($files) <= 2) {
		$noFiles = true;
	} else {
		$noFiles = false;
	}

    echo "\n";
    echo "Available Backup Files:\n";

    foreach ($files as $file) {
        $fileParts = pathinfo($file);

        if ($fileParts['extension'] == 'sql') {
            echo $file . "\n";
        }
    }

    if ($noFiles) {
        echo "There are no available .sql files.\n";
    }

    echo "\n";
}

function restoreDb($user, $passwd, $db, $file) {
	echo "\n";
	echo "User: ".$user."\n";
	echo "Password: ".$passwd."\n";
	echo "Database: ".$db."\n";
	echo "Backup: ".$file."\n";
	echo "\n";
	
	$backup = __DIR__."/backups/".$file;	

	$restoreCmd = sprintf("sudo -u root /usr/bin/mysqldump -u%s -p'%s' --database %s < $backup", 
				  $user, $passwd, $db, $backup);

	shell_exec($restoreCmd);

}


// make var dir if it doesn't exist
if (!file_exists(__DIR__."/var")) {
        $makeVar = sprintf("sudo -u root mkdir %s%s", __DIR__, "/var");
        shell_exec($makeVar);
}

// make log dir inside var if it does not exist
if (!file_exists(__DIR__."/var/log")) {
        $makeLog = sprintf("sudo -u root mkdir %s%s", __DIR__, "/var/log");
        shell_exec($makeLog);
}

// make backups dir if it does not exist
if (!file_exists(__DIR__."/backups")) {
    $makeBackups = sprintf("sudo -u root mkdir %s%s", __DIR__, "/backups");
    shell_exec($makeBackups);
}

// argv logic
if (!isset($argv[1])) {
        echoHelp();
}

if (isset($argv[1])) {
	if ($argv[1] === "-help") {
		    echoHelp();
	}
}

if (isset($argv[1])) {
	if ($argv[1] === "-list") {
		    listBackupsDir();
	}
}

if (isset($argv[1])) {
	if ($argv[1] === "-restore") {
		restoreDb($argv[2], $argv[3], $argv[4], $argv[5]);
	}
}

if (isset($argv[1])) {
	if ($argv[1]!=="-help"&&$argv[1]!=="-list"&&$argv[1]!=="-restore") {
		echoHelp();
	}
}






