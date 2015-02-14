<?php
/**
 * tests email2db
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

$config = require_once('config/config.php');

// composer autoloader
require_once('vendor/autoload.php');
require_once('src/Email2DB.php');

// main instance
$email2db = new Email2DB($config);

//$email2db->parseEmail('email.eml');
//die;


foreach (glob("../flanker/tests/fixtures/messages/*.eml") as $filename) {
  $email2db->parseEmail($filename);
}
foreach (glob("../official-library-php-email-parser/tests/emails/*") as $filename) {
  $email2db->parseEmail($filename);
}
foreach (glob("vendor/exorus/php-mime-mail-parser/test/mails/m*") as $filename) {
  $email2db->parseEmail($filename);
}
die();