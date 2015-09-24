<?php

function writeLog($message)
{
    $logFile = __DIR__."/var/log/restore.log";

	$write = sprintf("sudo -u root echo %s %s >> %s", date('Y-m-d h:i:s'), $message, $logFile);
    shell_exec($write);
	var_dump($message);
}

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

	try {
	$link = new mysqli("localhost", $user, $passwd);
	

	if (!$link) {
		//writeLog("linkFail", $file);
		throw new Exception("Failed to connect to Database");
	} else {
		//writeLog("linkSuccess", $file);
		echo "\nconnected to db\n";


		$result1 = $link->real_query("DROP DATABASE `magento-solr`;");
		if (!$result1) {
			throw new Exception($link->error);
		}
		$result2 = $link->real_query("CREATE DATABASE `magento-solr`;");

	}

	$link->close();
	
	$backup = __DIR__."/backups/".$file;

	$restoreCmd = sprintf("sudo -u root /usr/bin/mysql -u%s -p'%s' --database %s < $backup", 
				  $user, $passwd, $db, $backup);

	exec($restoreCmd, $out, $rc);
	//shell_exec($restoreCmd);
	// log if mysql dump passed or failed
	/*if ($rc !== 0) {
		writeLog("restoreSuccess", $file);
	} else {
		writeLog("restoreFail", $file);
	}*/
	} catch(Exception $e) {
		writeLog($e->getMessage());
		$link->close();
	}
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






