#!/usr/bin/php
<?
/**
 * mail2db executable
 */

// error_reporting(E_ALL ^ E_NOTICE);
error_reporting(E_ALL);

require_once('MDB2.php');                           // PEAR standard DB handler class
require_once('Log.php');                            // PEAR Log
require_once('Mail.php');                           // PEAR Mail
require_once('Mail/mime.php');                      // PEAR mail mime class, for sending attachements and others
require_once("System/Daemon.php");                  // PEAR Daemon class

require_once("/usr/local/faxfacile/system/scripts/common/class.Sys.php");
require_once("/usr/local/faxfacile/system/scripts/mail2db/class.Mail2DB.php");

$mail2db = new Mail2DB();

// define the file permissions to 644
umask(0022);

// get user info from passwd
$userinfo = posix_getpwnam("mail2faxsystem");

// Daemon options
$options = array(
    "usePEAR"               => true,
    "usePEARLogInstance"    => false,
    "authorName"            => "Igor Moseev",
    "authorEmail"           => "moiseev.igor@gmail.com",
    "appName"               => "mail2db",
    "appDescription"        => "Mail2DB application daemon",
    "appDir"                => $userinfo["dir"] . "/",
    "appExecutable"         => "/usr/local/faxfacile/system/scripts/mail2db/mail2db.php",
    "logVerbosity"          => 6,
    "logLocation"           => "/var/log/mail2db.log",
    "logPhpErrors"          => true,
    "logFilePosition"       => true,
    "logLinePosition"       => true,
    "appRunAsUID"           => $userinfo["uid"],
    "appRunAsGID"           => $userinfo["gid"],
    "appPidLocation"        => "/var/run/mail2db/mail2db.pid",
    "appDieOnIdentityCrisis"=> true,
    "sysMaxExecutionTime"   => 0,
    "sysMaxInputTime"       => 0,
    "sysMemoryLimit"        => "128M",
);
System_Daemon::setOptions($options);

// Spawn Deamon!
System_Daemon::start();

// daemon GREAT cycle
while(true)
{
    if($mail2db->manage()) {
        sleep(11);
    } else {
        sleep(600);
    }

} // END WHILE

// Stopping daemon
System_Daemon::stop();
