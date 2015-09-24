<?php

function writeLog($message)
{
    $logFile = __DIR__."/var/log/restore.log";

	$write = sprintf("sudo -u root echo %s %s >> %s", date('Y-m-d h:i:s'), $message, $logFile);
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

	try {
		$link = new mysqli("localhost", $user, $passwd);
	
		if (!$link) {
			throw new Exception("ERROR: Failed to connect to Database");
		} else {
			writeLog("SUCCESS: Connected to Database");
			$dropDb = $link->real_query("DROP DATABASE `$db`;");

			if (!$dropDb) {
				throw new Exception($link->error);
			}

			$createDb = $link->real_query("CREATE DATABASE `$db`;");
			
			if (!$createDb) {
				throw new Exception($link->error);
			}
		}

		$link->close();
		$backup = __DIR__."/backups/".$file;

		$restoreCmd = sprintf("sudo -u root /usr/bin/mysql -u%s -p'%s' --database %s < $backup", 
					  $user, $passwd, $db, $backup);
		
		exec($restoreCmd, $out, $rc);

		if ($rc !== 0) {
			throw new Exception("ERROR: Failed to restore Database: ".$db);
		} else {
			writeLog("SUCCESS: Database restore completed on Database: ".$db);
		}
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
		if (count($argv) === 6){
			restoreDb($argv[2], $argv[3], $argv[4], $argv[5]);
		} else {
			echoHelp();
		}
	} 
}

if (isset($argv[1])) {
	if ($argv[1]!=="-help"&&$argv[1]!=="-list"&&$argv[1]!=="-restore") {
		echoHelp();
	}
}






