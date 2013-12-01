<?php

use Composer\Autoload\ClassLoader;

use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once 'Composer/ClassLoader.php';

/**
 * Civi bridge to Doctrine ORM
 */
class Doctrine {
  static $entityManager;

  static function getEntityManager() {
    if (!self::$entityManager) {
      self::initializeDoctrine();
    }
    return self::$entityManager;
  }

  static protected function initializeDoctrine() {
    global $civicrm_root;

    require_once 'DB.php';

    $namespaces_map = array(
      'Symfony\\Component\\Console\\' => array(__DIR__ . '/SymfonyComponents/Console'),
      'Doctrine\\ORM\\' => array(__DIR__ . '/Doctrine/orm/lib'),
      'Doctrine\\DBAL\\' => array(__DIR__ . '/Doctrine/dbal/lib'),
      'Doctrine\\Common\\Lexer\\' => array(__DIR__ . '/Doctrine/lexer/lib'),
      'Doctrine\\Common\\Inflector\\' => array(__DIR__ . '/Doctrine/inflector/lib'),
      'Doctrine\\Common\\Collections\\' => array(__DIR__ . '/Doctrine/collections/lib'),
      'Doctrine\\Common\\Cache\\' => array(__DIR__ . '/Doctrine/cache/lib'),
      'Doctrine\\Common\\Annotations\\' => array(__DIR__ . '/Doctrine/annotations/lib'),
      'Doctrine\\Common\\' => array(__DIR__ . '/Doctrine/common/lib'),
    );

    $classloader = new ClassLoader();
    foreach ($namespaces_map as $namespace => $path) {
      $classloader->set($namespace, $path);
    }
    $classloader->register(true);

    $daoPath = $civicrm_root . "/CRM";
    $config = Setup::createAnnotationMetadataConfiguration(array($daoPath));

    $cachePath = CRM_Utils_File::baseFilePath() . "/doctrine-cache";
    $config->setMetadataCacheImpl(new FilesystemCache($cachePath));

    $civiDb = DB::parseDSN(CRM_Core_Config::singleton()->dsn);
    $conn = array(
        'driver' => 'pdo_mysql',
        'user' => $civiDb['username'],
        'password' => $civiDb['password'],
        'dbname' => $civiDb['database'],
        'host' => $civiDb['hostspec'],
        'port' => $civiDb['port'],
    );
    self::$entityManager = EntityManager::create($conn, $config);
  }
}
