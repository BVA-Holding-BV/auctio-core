<?php

namespace AuctioCore\Zf\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // Set repository-name (replace service into repository
        $repositoryName = str_replace("\Service\\", "\Repository\\", $requestedName);
        $repositoryName = preg_replace("~Service(?!.*Service)~", "Repository", $repositoryName);

        /** @var $repository */
        $repository = $container->get($repositoryName);

        /** @var $service */
        $service = new $requestedName(
            $repository
        );

        return $service;
    }

}