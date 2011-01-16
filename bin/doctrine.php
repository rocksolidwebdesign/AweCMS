<?php
// CLI Location Self Awareness
// ****************************************************************
$zend_relative_root = 'bin';

$DS = '/..'; $dir_up = $DS;
$dir_count = count(explode('/', $zend_relative_root));
for ($x = 1; $x < $dir_count; $x++) {
    $dir_up .= $DS;
}

// Zend Framework Configuration
// ****************************************************************
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . "$dir_up/application"));
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Add library to include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

// Load Zend Application Config
// ****************************************************************
require_once APPLICATION_PATH . '/configs/config.php';
$conn_settings = $zfConfArr['doctrine']['connection'];
$entities_path = $zfConfArr['doctrine']['settings']['entities_path'];
$proxies_path  = $zfConfArr['doctrine']['settings']['proxies_path'];
$log_path      = $zfConfArr['doctrine']['settings']['log_path'];

// Setup Autoloading
// ****************************************************************
$required_libs = array(
    'Awe'      => '',
    'Doctrine' => '',
    'Symfony'  => 'Doctrine',
    'Entities' => $entities_path,
    'Proxies'  => $proxies_path,
);

require_once 'Doctrine/Common/ClassLoader.php';
foreach ($required_libs as $name => $path) {
    if ($path) {
        $classLoader = new \Doctrine\Common\ClassLoader($name, $path); 
    } else {
        $classLoader = new \Doctrine\Common\ClassLoader($name);
    }
    $classLoader->register();
}

// Setup the Entity Manager
// ****************************************************************
$config = new \Doctrine\ORM\Configuration();
$config->setSQLLogger(new Doctrine\DBAL\Logging\EchoSQLLogger);
$config->setQueryCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
$config->setMetadataDriverImpl($config->newDefaultAnnotationDriver(array($entities_path)));
$config->setProxyDir($proxies_path.'/Proxies');
$config->setProxyNamespace('Proxies');
$em = \Doctrine\ORM\EntityManager::create($conn_settings, $config);

// Command Line Setup
// ****************************************************************
$cli = new \Symfony\Component\Console\Application('Doctrine Command Line Interface', Doctrine\ORM\Version::VERSION);

// Helper Set
$helperSet = $cli->getHelperSet();
$helperSet->set(new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()), 'db');
$helperSet->set(new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em), 'em');
$cli->setHelperSet($helperSet);

// CLI Config
$cli->setCatchExceptions(true);
$cli->addCommands(array(
    // DBAL Commands
    new \Doctrine\DBAL\Tools\Console\Command\RunSqlCommand(),
    new \Doctrine\DBAL\Tools\Console\Command\ImportCommand(),

    // ORM Commands
    new \Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand(),
    new \Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand(),
    new \Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand(),
    new \Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand(),
    new \Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand(),
    new \Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand(),
    new \Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand(),
    new \Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand(),
    new \Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand(),
    new \Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand(),
    new \Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand(),
    new \Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand(),
    new \Doctrine\ORM\Tools\Console\Command\RunDqlCommand(),
    new \Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand(),
));

$cli->run();
