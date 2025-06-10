<?php

namespace Powernic\Bot\Framework\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Finder\Finder;

class EntityManagerFactory
{
    public static function create(string $url, string $proxyDir): EntityManager
    {
        $config = self::getConfiguration();
        $config->setProxyDir($proxyDir);
        $config->setAutoGenerateProxyClasses(true);
        $dbParams = ['url' => $url];
        $connection = DriverManager::getConnection($dbParams, $config);
        return new EntityManager($connection, $config);
    }

    protected static function getConfiguration(): Configuration
    {
        $isDevMode = true;
        $paths = self::getEntityPaths();
        return ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode);
    }

    /**
     * @return array<int, string>
     */
    private static function getEntityPaths(): array
    {
        $finder = new Finder();
        $finder->in(__DIR__ . "/../../")->directories()->name('Entity');
        $paths = [];
        foreach ($finder as $dir) {
            $paths[] = $dir->getPath();
        }

        return $paths;
    }
}
