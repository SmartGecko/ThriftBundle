<?php
/**
 * This file is part of the SmartGecko(c) business platform.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SmartGecko\ThriftBundle\Controller;

use Thrift\Transport\TBufferedTransport;
use Thrift\Transport\TMemoryBuffer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Debug\ErrorHandler;

ErrorHandler::register();

/**
 * Description of ThriftController
 *
 * @author david
 */
class ThriftController extends Controller
{

    public function serverAction($service)
    {
        $servers = $this->container->getParameter('thrift.config.servers');
        $config = $servers[$service];

        $processor = $this->container->get('thrift.factory')->getProcessorInstance(
            $config['service'],
            $this->container->get($config['handler'])
        );

        $buffer = new TMemoryBuffer($this->getRequest()->getContent());

        $transport = new TBufferedTransport($buffer);
        $protocol = new $config['service_config']['protocol']($transport, true, true);
        $transport->open();
        $processor->process($protocol, $protocol);
        $transport->close();

        $response = new Response($buffer->getBuffer());
        $response->headers->set('Content-Type', 'application/x-thrift');

        return $response;
    }
}