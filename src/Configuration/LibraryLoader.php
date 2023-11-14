<?php

/**
 * TechDivision\Import\Cli\Configuration\LibraryLoader
 *
 * PHP version 7
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Cli\Configuration;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use TechDivision\Import\Configuration\ConfigurationInterface;
use TechDivision\Import\Cli\Utils\DependencyInjectionKeys;

/**
 * The library loader implementation.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
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
     */
    protected function getVendorDir()
    {
        return $this->getContainer()->getParameter(DependencyInjectionKeys::CONFIGURATION_VENDOR_DIR);
    }

    /**
     * Return's the relative path to the custom configuration directory.
     *
     * @return string The relative path to the custom configuration directory
     */
    protected function getCustomConfigurationDir()
    {
        return $this->getContainer()->getParameter(DependencyInjectionKeys::APPLICATION_CUSTOM_CONFIGURATION_DIR);
    }

    /**
     * Return's the relative path to the custom configuration public directory.
     *
     * @return string The relative path to the custom configuration public directory
     */
    protected function getCustomConfigurationPublicDir()
    {
        return $this->getContainer()->getParameter(DependencyInjectionKeys::APPLICATION_CUSTOM_CONFIGURATION_PUBLIC_DIR);
    }

    /**
     * Load's the external libraries registered in the passed configuration.
     *
     * @param \TechDivision\Import\Configuration\ConfigurationInterface $configuration The configuration instance
     *
     * @return void
     */
    public function load(ConfigurationInterface $configuration)
    {

        // load the DI container, vendor and custom configuration
        // directory as well as the Magento version
        $container = $this->getContainer();
        $vendorDir = $this->getVendorDir();
        $magentoVersion = $configuration->getMagentoVersion();
        $customConfigurationDir = $this->getCustomConfigurationDir();

        // initialize the default loader and load the DI configuration for the this library
        $defaultLoader = new XmlFileLoader($container, new FileLocator($vendorDir));

        // load the DI configuration for all the extension libraries
        foreach ($configuration->getExtensionLibraries() as $library) {
            $this->loadConfiguration($defaultLoader, $magentoVersion, sprintf('%s/%s', $vendorDir, $library));
        }

        // register autoloaders for additional vendor directories
        $customLoader = new XmlFileLoader($container, new FileLocator());
        foreach ($configuration->getAdditionalVendorDirs() as $additionalVendorDir) {
            // try to load the vendor directory's auto loader, if available. Otherwise we assume
            // that the vendor directory uses an autoloader that has already been loaded, e. g. in
            // case of Magento which has app/code registered as vendor directory what we want to use
            if (file_exists($autoLoader = $additionalVendorDir->getVendorDir() . '/autoload.php')) {
                require $autoLoader;
            }

            // try to load the DI configuration for the configured extension libraries
            foreach ($additionalVendorDir->getLibraries() as $library) {
                // concatenate the directory for the library
                $libDir = sprintf('%s/%s', $additionalVendorDir->getVendorDir(), $library);
                // prepend the installation directory, if the vendor is relative to it
                if ($additionalVendorDir->isRelative()) {
                    $libDir = sprintf('%s/%s', $configuration->getInstallationDir(), $libDir);
                }

                // create the canonicalized absolute pathname and try to load the configuration
                if ($libraryDir = realpath($libDir)) {
                    $this->loadConfiguration($customLoader, $magentoVersion, $libraryDir);
                } else {
                    throw new \Exception(sprintf('Can\'t find find library directory "%s"', $libDir));
                }
            }
        }

        // initialize the project specific configuration loader for the DI configuration
        $projectLoader = new XmlFileLoader($container, new FileLocator(getcwd()));

        // finally load the project specific custom library configuration which overwrites the default one
        $this->loadConfiguration($projectLoader, $magentoVersion, $customConfigurationDir);
    }

    /**
     * Loads the version specific Symfony DI configuration.
     *
     * @param \Symfony\Component\Config\Loader\LoaderInterface $loader         The Symfony DI loader instance
     * @param string                                           $magentoVersion The Magento Version to load the configuration for
     * @param string                                           $libraryDir     The library directory
     *
     * @return boolean TRUE if the configuration file has been loaded, else FALSE
     */
    protected function loadConfiguration(LoaderInterface $loader, $magentoVersion, $libraryDir)
    {

        // load the default Symfony Di configuration
        if (file_exists($diConfiguration = sprintf('%s/symfony/Resources/config/services.xml', $libraryDir))) {
            // load the DI configuration
            $loader->load($diConfiguration);
            // initialize the array for the directories with the
            // available versions that'll override the defaults
            $versions = array();
            // load the directories that equals the versions custom configuration files are available for
            $iterator = new \DirectoryIterator(sprintf('%s/symfony/Resources/config', $libraryDir));
            // iterate over the subdirectories
            while ($iterator->valid()) {
                // query whether or not we've a directory, if yes we
                // assume it'll contain additional version information
                if ($iterator->isDir() && !$iterator->isDot()) {
                    $versions[] = $iterator->current()->getPathname();
                }
                // continue reading the directry content
                $iterator->next();
            }

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

            // return TRUE if the configuration has been loaded
            return true;
        }

        // return FALSE if the configuration file has NOT been available
        return false;
    }
}
