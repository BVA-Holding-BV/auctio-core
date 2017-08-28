<?php

namespace AuctioCore\Zf\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class RepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var $objectManager \Doctrine\ORM\EntityManager */
        $objectManager = $container->get(\Doctrine\ORM\EntityManager::class);

        /** @var $config \Zend\Config\Config */
        $config = $container->get('Config');

        $repository = new $requestedName(
            $objectManager,
            $config
        );

        return $repository;
    }

}