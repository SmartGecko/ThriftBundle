<?php
/**
 * This file is part of the SmartGecko(c) business platform.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SmartGecko\ThriftBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use SmartGecko\ThriftBundle\Compiler\Compiler;

class CompileCommand extends ContainerAwareCommand
{
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('thrift:compile')
            ->setDescription('Compile Thrift IDL definitions');
        $this->addArgument('service', InputArgument::REQUIRED, 'Service name');
        $this->addOption('namespace', null, InputOption::VALUE_REQUIRED, 'Namespace prefix');
        $this->addOption('path', null, InputOption::VALUE_REQUIRED, 'Thrift exec path');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $compiler = new Compiler();

        if (($path = $input->getOption('path'))) {
            $compiler->setCompilerPath($path);
        }

        $service = $input->getArgument('service');
        $configs = $this->getContainer()->getParameter('thrift.config.services');

        // Get config
        if (isset($configs[$service])) {
            $config = $configs[$service];
        } else {
            $output->writeln(sprintf('<error>Unknown service %s</error>', $service));

            return 1;
        }

        $definitionPath = $config['path'].'/'.$config['idl'].'.thrift';
        // Get definition path
        $directory = $this->getContainer()->get('kernel')->getRootDir().'/../gen-php';

        //Set Path
        $compiler->setOutputDirectory($directory);

        //Add namespace prefix if needed
        if ($input->getOption('namespace')) {
            $compiler->setNamespacePrefix($input->getOption('namespace'));
        }

        $return = $compiler->compile($definitionPath);

        // log command
        $formattedLine =  $this->getHelper('formatter')->formatSection(
            'Running Thrift Compiler',
            $compiler->getCommand()
        );
        $output->writeln($formattedLine);

        //Error
        if (1 === $return) {
            $output->writeln(sprintf('<error>%s</error>', implode("\n", $compiler->getLastOutput())));
        } else {
            $output->writeln(sprintf('<info>%s</info>', implode("\n", $compiler->getLastOutput())));
        }
    }
}