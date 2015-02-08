<?php

require_once "vendor/autoload.php";

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$config = require_once('config/config.php');

$paths = array("db/schema");
$isDevMode = true;

$configDoctrine = Setup::createYAMLMetadataConfiguration($paths, $isDevMode);
$entityManager = EntityManager::create($config['db'], $configDoctrine);

