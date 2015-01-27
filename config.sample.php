<?php
/**
 * Config Email2DB
 */

return array(

   // Database conection config
  'db' => array(
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'username'  => 'root',
    'password'  => 'password',
    'port'      => '3306'
    ),

  // Daemon config 
  'daemon' => array(
    'usePEAR'               => true,
    'usePEARLogInstance'    => false,
    'authorName'            => 'Author Name',
    'authorEmail'           => '<email>',
    'appName'               => 'email2db',
    'appDescription'        => 'Email2DB application daemon',
    'appDir'                => './',
    'appExecutable'         => 'email2db.php',
    'logVerbosity'          => 6,
    'logLocation'           => './email2db.log',
    'logPhpErrors'          => true,
    'logFilePosition'       => true,
    'logLinePosition'       => true,
    'appRunAsUID'           => '1000',
    'appRunAsGID'           => '1000',
    'appPidLocation'        => 'email2db.pid',
    'appDieOnIdentityCrisis'=> true,
    'sysMaxExecutionTime'   => 0,
    'sysMaxInputTime'       => 0,
    'sysMemoryLimit'        => '128M',
    )
  );