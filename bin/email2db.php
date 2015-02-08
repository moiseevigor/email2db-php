#!/usr/bin/php
<?php
/**
 * email2db executable
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

error_reporting(E_ALL);

$config = require_once('config.php');

// composer autoloader
require_once('vendor/autoload.php');

// main instance
$email2db = new Email2DB();

// define the file permissions to 644
umask(0022);

// get user info from passwd
$userinfo = posix_getpwuid(posix_getuid());

// Daemon options
$options = array_merge($config['daemon'], array(
  "authorName"  => "Igor Moseev",
  "authorEmail" => "moiseev.igor@gmail.com",
  "appDir"      => $userinfo["dir"] . "/",
  "appRunAsUID" => $userinfo["uid"],
  "appRunAsGID" => $userinfo["gid"],
  "logLocation"           => "/var/log/email2db.log",
  "appPidLocation"        => "/var/run/email2db/email2db.pid"
  ));
System_Daemon::setOptions($options);

// Spawn Deamon!
System_Daemon::start();

// daemon GREAT cycle
while(true)
{
  if($email2db->manage()) {
    sleep(11);
  } else {
    sleep(600);
  }

} // END WHILE

// Stopping daemon
System_Daemon::stop();
