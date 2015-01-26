#!/usr/bin/php
<?php
/**
 * mail2db executable
 *
 *   Copyright (C) 2011 Igor Moiseev
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// error_reporting(E_ALL ^ E_NOTICE);
error_reporting(E_ALL ^ E_STRICT);

// composer autoloader
require_once('vendor/autoload.php');
require_once('src/Sys.php');
require_once('src/Email2DB.php');

$mail2db = new Email2DB();

// define the file permissions to 644
umask(0022);

// get user info from passwd
$userinfo = posix_getpwuid(posix_getuid());

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
