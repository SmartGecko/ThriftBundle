<?php
/**
 * This file is part of the SmartGecko(c) business platform.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SmartGecko\ThriftBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SmartGeckoThriftExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('thrift.config.compiler.path', $config['compiler']['path']);
        $container->setParameter('thrift.config.services', $config['services']);
        $container->setParameter('thrift.config.servers', $config['servers']);

        // Register clients
        foreach ($config['clients'] as $name => $client) {
            $this->loadClient($name, $client, $container, $config['testMode']);
        }

        // Register autoloader
        $this->registerLoader($config['services'], $container);
    }

    /**
     * Create client service
     * @param string $name
     * @param array $client
     * @param ContainerBuilder $container
     * @param boolean $testMode
     */
    protected function loadClient($name, array $client, ContainerBuilder $container, $testMode = false)
    {
        $clientDef = new Definition(
            $container->getParameter(
                $testMode ? 'thrift.client.test.class' : 'thrift.client.class'
            )
        );
        $clientDef->addArgument(new Reference('thrift.factory'));
        $clientDef->addArgument($client);
        $container->setDefinition(
            sprintf('thrift.client.%s', $name),
            $clientDef
        );
    }

    /**
     * Register Thrift AutoLoader
     * @param array $services
     * @param ContainerBuilder $container
     */
    protected function registerLoader($services, ContainerBuilder $container)
    {
        $namespaces = [];

        foreach ($services as $service) {
            preg_match('#^([^\\\]+)\\\#', $service['namespace'], $m);
            if (false === array_key_exists($m[1], $namespaces)) {
                $namespaces[$m[1]] = $container->getParameter('kernel.cache_dir');
            }
        }
        if (0 < count($namespaces)) {
            $container->getDefinition('thrift.factory')
                ->addMethodCall('initLoader', array($namespaces));
        }
    }
}