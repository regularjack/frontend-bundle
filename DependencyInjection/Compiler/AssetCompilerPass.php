<?php

namespace Rj\FrontendBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AssetCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $packages = [];
        $registeredPackages = $this->getRegisteredPackages($container);

        foreach ($this->getTaggedPackages($container) as $id => $tags) {
            if (empty($tags) || !isset($tags[0]['alias'])) {
                throw new \LogicException(
                    "The tag for the service with id '$id' must define an 'alias' attribute"
                );
            }

            $packageName = $tags[0]['alias'];

            if (isset($registeredPackages[$packageName])) {
                throw new \LogicException(
                    "A package named '$packageName' has already been registered"
                );
            }

            if (isset($packages[$packageName])) {
                throw new \LogicException(
                    "Multiple packages were found with alias '$packageName'. Package alias' must be unique"
                );
            }

            $packages[$packageName] = $id;
        }

        $this->addPackages($packages, $container);

        if ($container->hasDefinition($this->namespaceService('package.fallback'))) {
            $this->setDefaultPackage($container);
        }
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return Definition[]
     */
    private function getTaggedPackages(ContainerBuilder $container)
    {
        return $container->findTaggedServiceIds($this->namespaceService('package.asset'));
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return Definition
     */
    private function getPackagesService(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('assets.packages')) {
            throw new \LogicException('The Asset component is not registered in the container');
        }

        return $container->getDefinition('assets.packages');
    }

    /**
     * @param Definition[]     $packages
     * @param ContainerBuilder $container
     */
    private function addPackages($packages, ContainerBuilder $container)
    {
        $packagesService = $this->getPackagesService($container);

        foreach ($packages as $name => $id) {
            $packagesService->addMethodCall(
                'addPackage',
                [$name, new Reference($id)]
            );
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    private function setDefaultPackage(ContainerBuilder $container)
    {
        $packagesService = $this->getPackagesService($container);
        $defaultPackage = $this->getRegisteredDefaultPackage($container);
        $fallbackPackageId = $this->namespaceService('package.fallback');

        $container->getDefinition($fallbackPackageId)->addMethodCall('setFallback', [$defaultPackage]);

        $packagesService->replaceArgument(0, new Reference($fallbackPackageId));
    }

    /**
     * Retrieve packages that have already been registered.
     *
     * @param ContainerBuilder $container
     *
     * @return array with the packages' name as keys
     */
    private function getRegisteredPackages(ContainerBuilder $container)
    {
        $arguments = $this->getPackagesService($container)->getArguments();

        if (!isset($arguments[1]) || count($arguments[1]) < 2) {
            return [];
        }

        $argPackages = $arguments[1];

        $packages = [];
        $argCount = count($argPackages);
        for ($i = 0; $i < $argCount; ++$i) {
            $packages[$argPackages[$i]] = $argPackages[++$i];
        }

        return $packages;
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return Definition|null
     */
    private function getRegisteredDefaultPackage(ContainerBuilder $container)
    {
        $arguments = $this->getPackagesService($container)->getArguments();

        if (!isset($arguments[0])) {
            return null;
        }

        return $arguments[0];
    }

    /**
     * @param string $id
     *
     * @return string
     */
    private function namespaceService($id)
    {
        return "rj_frontend.$id";
    }
}
