<?php

function writeLog($result, $backupFile)
{
    $logFile = __DIR__."/var/log/restore.log";

	if ($result === "restoreSuccess") {
		$str = "SUCCESS: Database restored from ".$backupFile;
	} elseif ($result === "restoreFail") {
		$str = "ERROR: mysqldump failed to import backup from ".$backupFile;
	}

	if ($result === "linkSuccess") {
		
	}

	//log db connect success/fail
	

	// log success/fail    
	$write = sprintf("sudo -u root echo %s %s >> %s", date('Y-m-d h:i:s'), $str, $logFile);
    shell_exec($write);
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
	$link = new mysqli("localhost", $user, $passwd);
	

	if (!$link) {
		//writeLog("linkFail", $file);
		echo "Not connected to DB";
	} else {
		//writeLog("linkSuccess", $file);
		echo "\nconnected to db\n";
		$result1 = $link->real_query("DROP DATABASE `magento-solr`;");
		$result2 = $link->real_query("CREATE DATABASE `magento-solr`;");
	}

	mysqli_close($link);
	
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






