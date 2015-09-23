#!/usr/bin/php -q
<?php

function writeLog($str)
{
    $logFile = __DIR__."/var/log/restore.log";

    $write = sprintf("sudo -u root echo %s %s >> %s", date('Y-m-d h:i:s'), $str, $logFile);
    shell_exec($write);
    var_dump($logFile);
}

function echoHelp()
{
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

function listBackupsDir()
{
    $files = scandir(__DIR__."/backups");
    $noFiles = false;

    echo "\n";
    echo "Available Backup Files:\n";

    foreach ($files as $file) {
        $fileParts = pathinfo($file);

        if ($fileParts['extension'] == 'sql') {
            echo $file . "\n";
        } else {
            $noFiles = true;
        }
    }

    if ($noFiles) {
        echo "There are no available .sql files.\n";
    }

    echo "\n";
}


# make var dir if it doesn't exist
if (!file_exists(__DIR__."/var")) {
        $makeVar = sprintf("sudo -u root mkdir %s%s", __DIR__, "/var");
        shell_exec($makeVar);
}

# make log dir inside var if it does not exist
if (!file_exists(__DIR__."/var/log")) {
        $makeLog = sprintf("sudo -u root mkdir %s%s", __DIR__, "/var/log");
        shell_exec($makeLog);
}

# make backups dir if it does not exist
if (!file_exists(__DIR__."/backups")) {
    $makeBackups = sprintf("sudo -u root mkdir %s%s", __DIR__, "/backups");
    shell_exec($makeBackups);
}

if (count($argv) > 5) {
        echoHelp();
}

if ($argv[1] === "-help") {
        echoHelp();
}

if ($argv[1] === "-list") {
        listBackupsDir();
}