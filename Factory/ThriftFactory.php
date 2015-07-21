<?php
/**
 * This file is part of the SmartGecko(c) business platform.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SmartGecko\ThriftBundle\Factory;

use Thrift\ClassLoader\ThriftClassLoader;

class ThriftFactory
{
    /**
     * @var array
     */
    protected $services;

    /**
     * Inject dependencies
     * @param array $services
     */
    public function __construct(array $services)
    {
        $this->services = $services;
    }

    /**
     * Initialize loader
     * @param array $namespaces
     */
    public function initLoader(array $namespaces)
    {
        $loader = new ThriftClassLoader();

        foreach ($namespaces as $prefix => $dir) {
            $loader->registerNamespace($prefix, $dir);
            $loader->registerDefinition($prefix, $dir);
        }

        $loader->register();
    }

    /**
     * Return a processor instance
     * @param string $service
     * @param mixed $handler
     * @return Object
     */
    public function getProcessorInstance($service, $handler)
    {
        $classe = sprintf('%s\%sProcessor', $this->services[$service]['namespace'], $this->services[$service]['className']);
        return new $classe($handler);
    }
    /**
     * Return a client instance
     * @param string $service
     * @param \Thrift\Protocol\TProtocol $protocol
     * @return Object
     */
    public function getClientInstance($service, $protocol)
    {
        $classe = sprintf('%s\%sClient', $this->services[$service]['namespace'], $this->services[$service]['className']);
        return new $classe($protocol);
    }
}