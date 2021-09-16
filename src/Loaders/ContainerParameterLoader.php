<?php

/**
 * TechDivision\Import\Cli\Loaders\ContainerParameterLoader
 *
 * PHP version 7
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2019 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Cli\Loaders;

use TechDivision\Import\Loaders\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generic loader implementation for container parameters.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2019 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli
 * @link      http://www.techdivision.com
 */
class ContainerParameterLoader implements LoaderInterface
{

    /**
     * The container instance.
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * The parameter name to load the value with.
     *
     * @var string
     */
    protected $parameterName;

    /**
     * Initializes the loader with the container and the parameter name to load the value for.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container     The container instance
     * @param string                                                    $parameterName The container parameter name to return the value for
     */
    public function __construct(ContainerInterface $container, string $parameterName)
    {
        $this->container = $container;
        $this->parameterName = $parameterName;
    }

    /**
     * Return's the container instance.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface The container instance
     */
    protected function getContainer() : ContainerInterface
    {
        return $this->container;
    }

    /**
     * Return's the parameter name to load the value for.
     *
     * @return string The parameter name
     */
    protected function getParameterName() : string
    {
        return $this->parameterName;
    }

    /**
     * Loads and returns the parameter value from the container configuration.
     *
     * @return mixed The parameter value from the container configuration
     * @see \Symfony\Component\DependencyInjection\ContainerInterface::getParameter()
     */
    public function load()
    {
        return $this->getContainer()->getParameter($this->getParameterName());
    }
}
