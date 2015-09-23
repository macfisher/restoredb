#!/usr/bin/php -q
<?php

/**
 * FEATURES
 *
 * List DB backup files in backups dir
 */
$projectRoot = "/usr/local/bin/restoreAcademyDB/";

function writeLog($str) {
        $logFile = "/usr/local/bin/restoreAcademyDB/var/log/restore.log";

        $write = sprintf("sudo -u root echo %s %s >> %s", date('Y-m-d h:i:s'), $str, $logFile);
        shell_exec($write);
}

function echoHelp() {
        echo "\n";
        echo "Usage: restoredb [-import] [user] [password] [file to import]\n";
        echo "                 [-list]\n";
        echo "                 [-help]\n";
        echo "\n";
        echo "Import -- Import file from backups stored in backups directory.\n";
        echo "List   -- List backup files in the backups directory.\n";
        echo "Help   -- Print help screen.\n";
        echo "\n";
        echo "*NOTE*: Backup files must be placed in /usr/local/bin/restoreAcademyDB/backups\n";
        echo "        Only give the filename, not the full path.\n";
        echo "\n";
}

function listBackupsDir() {
        $files = scandir("/usr/local/bin/restoreAcademyDB/backups");

        echo "\n";
        echo "Available Backup Files:\n";

        foreach ($files as $file) {
                $fileParts = pathinfo($file);

                if ($fileParts['extension'] !== 'sql' || $file !== '.' || $file !== '..') {
                        echo $file."\n";
                        echo $fileParts;
                }
        }

        echo "\n";
}

# make var dir if it doesn't exist
if (!file_exists($projectRoot."var")) {
        $makeVar = sprintf("sudo -u root mkdir %s%s", $projectRoot, "var");
        shell_exec($makeVar);
}

# make log dir inside var if it does not exist
if (!file_exists($projectRoot."/var/log")) {
        $makeLog = sprintf("sudo -u root mkdir %s%s", $projectRoot, "var/log");
        shell_exec($makeLog);
}

var_dump($argv);

if (count($argv) > 5) {
        echoHelp();
}

if ($argv[1] === "-help") {
        echoHelp();
}

if ($argv[1] === "-list") {
        listBackupsDir();
}
