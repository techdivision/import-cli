<?php

/**
 * TechDivision\Import\Cli\SimpleConfigurationLoader
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

namespace TechDivision\Import\Cli;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TechDivision\Import\Cli\Configuration\LibraryLoader;
use TechDivision\Import\Cli\Utils\DependencyInjectionKeys;
use TechDivision\Import\Cli\Utils\MagentoConfigurationKeys;
use TechDivision\Import\Utils\CommandNames;
use TechDivision\Import\Utils\Mappings\CommandNameToEntityTypeCode;
use TechDivision\Import\ConsoleOptionLoaderInterface;
use TechDivision\Import\Utils\InputOptionKeysInterface;
use TechDivision\Import\Configuration\ConfigurationFactoryInterface;

/**
 * The configuration loader implementation.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class SimpleConfigurationLoader implements ConfigurationLoaderInterface
{

    /**
     * The key for the Magento Edition in the metadata extracted from the Composer configuration.
     *
     * @var string
     */
    const EDITION = 'edition';

    /**
     * The key for the Magento Version in the metadata extracted from the Composer configuration.
     *
     * @var string
     */
    const VERSION = 'version';

    /**
     * The container instance.
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * The actual input instance.
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * The library loader instance.
     *
     * @param \TechDivision\Import\Cli\LibraryLoader
     */
    protected $libraryLoader;

    /**
     * The configuration factory instance.
     *
     * @var \TechDivision\Import\Configuration\ConfigurationFactoryInterface
     */
    protected $configurationFactory;

    /**
     * The available command names.
     *
     * @var \TechDivision\Import\Utils\CommandNames
     */
    protected $commandNames;

    /**
     * The mapping of the command names to the entity type codes
     *
     * @var \TechDivision\Import\Utils\Mappings\CommandNameToEntityTypeCode
     */
    protected $commandNameToEntityTypeCode;

    /**
     * The console option loader instance.
     *
     * @var \TechDivision\Import\ConsoleOptionLoaderInterface
     */
    protected $consoleOptionLoader;

    /**
     * Initializes the configuration loader.
     *
     * @param \Symfony\Component\Console\Input\InputInterface                  $input                        The input instance
     * @param \Symfony\Component\DependencyInjection\ContainerInterface        $container                    The container instance
     * @param \TechDivision\Import\Cli\Configuration\LibraryLoader             $libraryLoader                The configuration loader instance
     * @param \TechDivision\Import\Configuration\ConfigurationFactoryInterface $configurationFactory         The configuration factory instance
     * @param \TechDivision\Import\Utils\CommandNames                          $commandNames                 The available command names
     * @param \TechDivision\Import\Utils\Mappings\CommandNameToEntityTypeCode  $commandNameToEntityTypeCodes The mapping of the command names to the entity type codes
     * @param \TechDivision\Import\ConsoleOptionLoaderInterface                $consoleOptionLoader          The console option loader instance
     */
    public function __construct(
        InputInterface $input,
        ContainerInterface $container,
        LibraryLoader $libraryLoader,
        ConfigurationFactoryInterface $configurationFactory,
        CommandNames $commandNames,
        CommandNameToEntityTypeCode $commandNameToEntityTypeCodes,
        ConsoleOptionLoaderInterface $consoleOptionLoader
    ) {

        // set the passed instances
        $this->input = $input;
        $this->container = $container;
        $this->libraryLoader = $libraryLoader;
        $this->configurationFactory = $configurationFactory;
        $this->commandNames = $commandNames;
        $this->commandNameToEntityTypeCode = $commandNameToEntityTypeCodes;
        $this->consoleOptionLoader = $consoleOptionLoader;
    }

    /**
     * Factory implementation to create a new initialized configuration instance.
     *
     * If command line options are specified, they will always override the
     * values found in the configuration file.
     *
     * @return \TechDivision\Import\Configuration\ConfigurationInterface The configuration instance
     */
    public function load()
    {

        // initially try to create the configuration instance
        $instance = $this->createInstance();

        // we have to set the entity type code at least
        $instance->setEntityTypeCode($this->getEntityTypeCode());

        // load and merge the console options
        $this->getConsoleOptionLoader()->load($instance);

        // return the initialized configuration instance
        return $instance;
    }

    /**
     * This method create the configuration instance from the configuration file
     * defined by the commandline args and options.
     *
     * @return \TechDivision\Import\Configuration\ConfigurationInterface The configuration instance loaded from the configuration file
     * @throws \Exception Is thrown, if the specified configuration file doesn't exist or the mandatory arguments/options to run the requested operation are not available
     */
    protected function createInstance()
    {

        // load the actual vendor directory and entity type code
        $vendorDir = $this->getVendorDir();

        // the path of the JMS serializer directory, relative to the vendor directory
        $jmsDir = DIRECTORY_SEPARATOR . 'jms' . DIRECTORY_SEPARATOR . 'serializer' . DIRECTORY_SEPARATOR . 'src';

        // try to find the path to the JMS Serializer annotations
        if (!file_exists($annotationDir = $vendorDir . DIRECTORY_SEPARATOR . $jmsDir)) {
            // stop processing, if the JMS annotations can't be found
            throw new \Exception(
                sprintf(
                    'The jms/serializer libarary can not be found in one of "%s"',
                    implode(', ', $vendorDir)
                )
            );
        }

        // register the autoloader for the JMS serializer annotations
        \Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
            'JMS\Serializer\Annotation',
            $annotationDir
        );

        // query whether or not, a configuration file has been specified
        if ($configuration = $this->input->getOption(InputOptionKeysInterface::CONFIGURATION)) {
            // load the configuration from the file with the given filename
            $instance = $this->createConfiguration($configuration);
            // set the actual command name in the configuration
            $instance->setCommandName($this->input->getFirstArgument());
            // return the instance
            return $instance;
        } elseif (($magentoEdition = $this->input->getOption(InputOptionKeysInterface::MAGENTO_EDITION)) && ($magentoVersion = $this->input->getOption(InputOptionKeysInterface::MAGENTO_VERSION))) {
            // use the Magento Edition that has been specified as option
            $instance = $this->createConfiguration();

            // override the Magento Edition/Version
            $instance->setMagentoEdition($magentoEdition);
            $instance->setMagentoVersion($magentoVersion);

            // set the actual command name in the configuration
            $instance->setCommandName($this->input->getFirstArgument());

            // return the instance
            return $instance;
        }

        // finally, query whether or not the installation directory is a valid Magento root directory
        if (!$this->isMagentoRootDir($installationDir = $this->input->getOption(InputOptionKeysInterface::INSTALLATION_DIR))) {
            throw new \Exception(
                sprintf(
                    'Directory "%s" is not a valid Magento root directory, please use option "--installation-dir" to specify it',
                    $installationDir
                )
            );
        }

        // use the Magento Edition that has been detected by the installation directory
        $instance = $this->createConfiguration();

        // load the Magento Edition from the Composer configuration file
        $metadata = $this->getEditionMapping($installationDir);

        // extract edition & version from the metadata
        $magentoEdition = $metadata[SimpleConfigurationLoader::EDITION];
        $magentoVersion = $metadata[SimpleConfigurationLoader::VERSION];

        // override the Magento Edition/Version
        $instance->setMagentoEdition($magentoEdition);
        $instance->setMagentoVersion($magentoVersion);

        // set the actual command name in the configuration
        $instance->setCommandName($this->input->getFirstArgument());

        // return the instance
        return $instance;
    }

    /**
     * Create and return a new configuration instance from the passed configuration filename
     * after merging additional specified params from the commandline.
     *
     * @param string|null $filename The configuration filename to use
     *
     * @return \TechDivision\Import\Configuration\ConfigurationInterface The configuration instance
     */
    protected function createConfiguration($filename = null)
    {

        // initialize the params specified with the --params parameter
        $params = null;

        // try to load the params from the commandline
        if ($this->input->hasOptionSpecified(InputOptionKeysInterface::PARAMS) && $this->input->getOption(InputOptionKeysInterface::PARAMS)) {
            $params = $this->input->getOption(InputOptionKeysInterface::PARAMS);
        }

        // initialize the params file specified with the --params-file parameter
        $paramsFile = null;

        // try to load the path of the params file from the commandline
        if ($this->input->hasOptionSpecified(InputOptionKeysInterface::PARAMS_FILE) && $this->input->getOption(InputOptionKeysInterface::PARAMS_FILE)) {
            $paramsFile = $this->input->getOption(InputOptionKeysInterface::PARAMS_FILE);
        }

        // if a filename has been passed, try to load the configuration from the file
        if (is_file($filename)) {
            return $this->configurationFactory->factory($filename, pathinfo($filename, PATHINFO_EXTENSION), $params, $paramsFile);
        }

        // initialize the array for the directories
        $directories = array();

        // set the default file format
        $format = 'json';

        // load the actual vendor directory and entity type code
        $vendorDir = $this->getVendorDir();

        // load the default configuration directory from the DI configuration
        $defaultConfigurationDir = $this->getContainer()->getParameter(DependencyInjectionKeys::APPLICATION_DEFAULT_CONFIGURATION_DIR);

        // load the directories that has to be parsed for configuration files1
        foreach ($this->getDefaultLibraries() as $defaultLibrary) {
            // initialize the directory name
            $directory = implode(
                DIRECTORY_SEPARATOR,
                array_merge(
                    array($vendorDir),
                    explode('/', $defaultLibrary),
                    explode('/', $defaultConfigurationDir)
                )
            );

            // query whether or not the directory is available1
            if (is_dir($directory)) {
                $directories[] = $directory;
            }
        }

        // load the assumed installation directory
        $installationDir = $this->input->getOption(InputOptionKeysInterface::INSTALLATION_DIR);

        // initialize the default custom configuration directory
        $customConfigurationDir = implode(
            DIRECTORY_SEPARATOR,
            array_merge(
                array($installationDir),
                explode('/', $this->getContainer()->getParameter(DependencyInjectionKeys::APPLICATION_CUSTOM_CONFIGURATION_DIR))
            )
        );

        // query whether or not a custom configuration directory has been speified, if yes override the default one
        if ($this->input->hasOptionSpecified(InputOptionKeysInterface::CUSTOM_CONFIGURATION_DIR) && $this->input->getOption(InputOptionKeysInterface::CUSTOM_CONFIGURATION_DIR)) {
            $customConfigurationDir = $this->input->getOption(InputOptionKeysInterface::CUSTOM_CONFIGURATION_DIR);
        }

        // specify the default directory for custom configuration files
        if (is_dir($customConfigurationDir)) {
            $directories[] = $customConfigurationDir;
        }

        // load and return the configuration from the files found in the passed directories
        $instance = $this->configurationFactory->factoryFromDirectories($installationDir, $defaultConfigurationDir, $directories, $format, $params, $paramsFile);

        // query whether or not we've an valid Magento root directory specified
        if ($this->isMagentoRootDir($installationDir)) {
            // add the source directory if NOT specified in the configuration file
            if (($sourceDir = $instance->getSourceDir()) === null) {
                $instance->setSourceDir($sourceDir = sprintf('%s/var/importexport', $installationDir));
            }

            // add the target directory if NOT specified in the configuration file
            if ($instance->getTargetDir() === null) {
                $instance->setTargetDir($sourceDir);
            }
        }

        return $instance;
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
     * The console option loader instance.
     *
     * @return \TechDivision\Import\ConsoleOptionLoaderInterface The instance
     */
    protected function getConsoleOptionLoader()
    {
        return $this->consoleOptionLoader;
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
     * Return's the actual command name.
     *
     * @return string The actual command name
     */
    protected function getCommandName()
    {
        return $this->input->getArgument('command');
    }

    /**
     * Return's the command's entity type code.
     *
     * @return string The command's entity type code
     * @throws \Exception Is thrown, if the command name can not be mapped
     */
    protected function getEntityTypeCode()
    {

        // try to map the command name to a entity type code
        if (array_key_exists($commandName = $this->getCommandName(), (array) $this->commandNameToEntityTypeCode)) {
            return $this->commandNameToEntityTypeCode[$commandName];
        }

        // throw an exception if not possible
        throw new \Exception(sprintf('Can\'t map command name %s to a entity type', $commandName));
    }

    /**
     * Returns the mapped Magento Edition from the passed Magento installation.
     *
     * @param string $installationDir The Magento installation directory
     *
     * @return array The array with the mapped Magento Edition (either CE or EE) + the Version
     * @throws \Exception Is thrown, if the passed installation directory doesn't contain a valid Magento installation
     */
    protected function getEditionMapping($installationDir)
    {

        // load the default edition mappings from the configuration
        $editionMappings = $this->getContainer()->getParameter(DependencyInjectionKeys::APPLICATION_EDITION_MAPPINGS);

        // load the composer file from the Magento root directory
        $composer = json_decode(file_get_contents($composerFile = sprintf('%s/composer.json', $installationDir)), true);

        // try to load and explode the Magento Edition identifier from the Composer name
        $explodedEdition = explode('/', $composer[MagentoConfigurationKeys::COMPOSER_EDITION_NAME_ATTRIBUTE]);

        // try to load and explode the Magento Edition from the Composer configuration
        if (!isset($editionMappings[$possibleEdition = end($explodedEdition)])) {
            throw new \Exception(
                sprintf(
                    '"%s" detected in "%s" is not a valid Magento Edition, please set Magento Edition with the "--magento-edition" option',
                    $possibleEdition,
                    $composerFile
                )
            );
        }

        // try to load and explode the Magento Version from the Composer configuration
        if (!isset($composer[MagentoConfigurationKeys::COMPOSER_EDITION_VERSION_ATTRIBUTE])) {
            throw new \Exception(
                sprintf(
                    'Can\'t detect a version in "%s", please set Magento Version with the "--magento-version" option',
                    $composerFile
                )
            );
        }

        // return the array with the Magento Version/Edition data
        return array(
            SimpleConfigurationLoader::VERSION => $composer[MagentoConfigurationKeys::COMPOSER_EDITION_VERSION_ATTRIBUTE],
            SimpleConfigurationLoader::EDITION => $editionMappings[$possibleEdition]
        );
    }


    /**
     * Return's the application's default libraries.
     *
     * @return array The default libraries
     */
    protected function getDefaultLibraries()
    {

        // load the default libraries from the configuration
        $defaultLibraries = $this->getContainer()->getParameter(DependencyInjectionKeys::APPLICATION_DEFAULT_LIBRARIES);

        // initialize the array for the libraries
        $libraries = array();

        // append each library only ONCE
        foreach ($defaultLibraries as $libraries) {
            foreach ($libraries as $library) {
                if (in_array($library, $libraries)) {
                    continue;
                }
                // append the library
                $libraries[] = $library;
            }
        }

        // return the array with the libraries
        return $libraries;
    }

    /**
     * Query whether or not, the passed directory is a Magento root directory.
     *
     * @param string $dir The directory to query
     *
     * @return boolean TRUE if the directory is a Magento root directory, else FALSE
     */
    protected function isMagentoRootDir($dir)
    {
        return is_file($this->getMagentoEnv($dir));
    }

    /**
     * Return's the path to the Magento file with the environment configuration.
     *
     * @param string $dir The path to the Magento root directory
     *
     * @return string The path to the Magento file with the environment configuration
     */
    protected function getMagentoEnv($dir)
    {
        return sprintf('%s/app/etc/env.php', $dir);
    }
}
