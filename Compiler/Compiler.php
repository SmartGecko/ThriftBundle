<?php
/**
 * This file is part of the SmartGecko(c) business platform.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SmartGecko\ThriftBundle\Compiler;


class Compiler
{
    /**
     * Thrift Executable name
     * @var string
     */
    private $compiler = 'thrift';
    /**
     * Thrift Executable path
     * @var string
     */
    private $compilerPath = '/usr/local/bin/';
    /**
     * Output directory.
     * @var string
     */
    private $outputDirectory;
    /**
     * Base compiler options
     * @var array
     */
    private $options = ['oop' => null, 'server' => null, 'validate' => null];
    /**
     * Last compiler output
     * @var string
     */
    private $lastOutput;

    /**
     * @var string
     */
    private $command;

    /**
     * Return Thrift path
     * @return string
     */
    protected function getCompilerPath()
    {
        return $this->compilerPath.$this->compiler;
    }

    /**
     * Set exec path
     * @param string $path
     * @return bool
     */
    public function setCompilerPath($path)
    {
        if ('/' !== substr($path, -1)) {
            $path .= '/';
        }
        $this->compilerPath = $path;

        return $this->assertCompiler();
    }

    /**
     * Check if Thrift exec is installed
     *
     * @throws \RuntimeException
     * @return boolean
     */
    protected function assertCompiler()
    {
        if (!file_exists($this->getCompilerPath())) {
            throw new \RuntimeException('Unable to find Thrift executable');
        }

        return true;
    }

    /**
     * Set model path and create it if needed
     * @param string $path
     */
    public function setOutputDirectory($path)
    {
        if (!is_null($path) && !file_exists($path)) {
            mkdir($path);
        }
        $this->outputDirectory = $path;
    }

    /**
     * Set namespace prefix
     * @param string $namespace
     */
    public function setNamespacePrefix($namespace)
    {
        $this->options['nsglobal'] = escapeshellarg($namespace);
    }


    /**
     * Compile the thrift options
     * @return string
     */
    protected function compileOptions()
    {
        $return = [];

        foreach ($this->options as $option => $value) {
            $return[] = $option.(!empty($value) ? '='.$value : '');
        }

        return implode(',', $return);
    }

    /**
     * Compile the Thrift definition
     * @param string $definition
     * @throws \RuntimeException
     * @return boolean
     */
    public function compile($definition)
    {
        // Check if definition file exists
        if (!file_exists($definition)) {
            throw new \RuntimeException(sprintf('Unable to find Thrift definition at path "%s"', $definition));
        }

        //Reset output
        $this->lastOutput = null;
        $this->command = sprintf(
            '%s -r -v --gen php:%s --out %s %s 2>&1',
            $this->getCompilerPath(),
            $this->compileOptions(),
            $this->outputDirectory,
            $definition
        );

        exec($this->command, $this->lastOutput, $return);

        return (0 === $return) ? true : false;
    }

    /**
     * Returns the executed command.
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Return the last compiler output
     * @return string
     */
    public function getLastOutput()
    {
        return $this->lastOutput;
    }
}