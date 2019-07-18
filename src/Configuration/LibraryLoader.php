<?php

/**
 * TechDivision\Import\Cli\Configuration\LibraryLoader
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Cli\Configuration;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use TechDivision\Import\ConfigurationInterface;
use TechDivision\Import\Cli\Utils\DependencyInjectionKeys;

/**
 * The library loader implementation.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class LibraryLoader
{

    /**
     * The container instance.
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * Initializes the configuration loader.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container The container instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Return's the DI container instance.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface The DI container instance
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * Return's the absolute path to the actual vendor directory.
     *
     * @return string The absolute path to the actual vendor directory
     * @throws \Exception Is thrown, if none of the possible vendor directories can be found
     */
    protected function getVendorDir()
    {
        return $this->getContainer()->getParameter(DependencyInjectionKeys::CONFIGURATION_VENDOR_DIR);
    }

    /**
     * Load's the external libraries registered in the passed configuration.
     *
     * @param \TechDivision\Import\ConfigurationInterface $configuration The configuration instance
     *
     * @return void
     */
    public function load(ConfigurationInterface $configuration)
    {

        // load the Magento Version from the configuration
        $magentoVersion = $configuration->getMagentoVersion();

        // initialize the default loader and load the DI configuration for the this library
        $defaultLoader = new XmlFileLoader($this->getContainer(), new FileLocator($vendorDir = $this->getVendorDir()));

        // load the DI configuration for all the extension libraries
        foreach ($configuration->getExtensionLibraries() as $library) {
            $this->loadConfiguration($defaultLoader, $magentoVersion, sprintf('%s/%s', $vendorDir, $library));
        }

        // register autoloaders for additional vendor directories
        $customLoader = new XmlFileLoader($this->getContainer(), new FileLocator());
        foreach ($configuration->getAdditionalVendorDirs() as $additionalVendorDir) {
            // load the vendor directory's auto loader
            if (file_exists($autoLoader = $additionalVendorDir->getVendorDir() . '/autoload.php')) {
                require $autoLoader;
            } else {
                throw new \Exception(
                    sprintf(
                        'Can\'t find autoloader in configured additional vendor directory "%s"',
                        $additionalVendorDir->getVendorDir()
                    )
                );
            }

            // try to load the DI configuration for the configured extension libraries
            foreach ($additionalVendorDir->getLibraries() as $library) {
                $this->loadConfiguration($customLoader, $magentoVersion, sprintf('%s/%s', $additionalVendorDir->getVendorDir(), $library));
            }
        }
    }

    /**
     * Loads the version specific Symfony DI configuration.
     *
     * @param \Symfony\Component\Config\Loader\LoaderInterface $loader         The Symfony DI loader instance
     * @param string                                           $magentoVersion The Magento Version to load the configuration for
     * @param string                                           $libraryDir     The library directory
     *
     * @return void
     */
    protected function loadConfiguration(LoaderInterface $loader, $magentoVersion, $libraryDir)
    {

        // load the default Symfony Di configuration
        if (file_exists($diConfiguration = sprintf('%s/symfony/Resources/config/services.xml', $libraryDir))) {
            // load the DI configuration
            $loader->load($diConfiguration);
        } else {
            throw new \Exception(
                sprintf(
                    'Can\'t load default DI configuration "%s"',
                    $diConfiguration
                )
            );
        }

        // load the directories that equals the versions custom configuration files are available for
        $versions = glob(sprintf('%s/symfony/Resources/config/*', $libraryDir), GLOB_ONLYDIR);

        // sort the directories descending by their version
        usort($versions, 'version_compare');
        krsort($versions);

        // override DI configuration with version specifc data
        foreach ($versions as $version) {
            if (version_compare(basename($version), $magentoVersion, '<=')) {
                if (file_exists($diConfiguration = sprintf('%s/services.xml', $version))) {
                    // load the version specific DI configuration
                    $loader->load($diConfiguration);
                }
            }
        }
    }
}
