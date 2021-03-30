<?php

namespace AuctioCore\Laminas\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\Config\Config;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var $objectManager EntityManager */
        $objectManager = $container->get(EntityManager::class);

        /** @var $config Config */
        $config = $container->get('Config');

        return new $requestedName(
            $objectManager,
            $config
        );
    }

}