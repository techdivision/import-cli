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
use TechDivision\Import\ConfigurationFactoryInterface;
use TechDivision\Import\Cli\Command\InputOptionKeys;
use TechDivision\Import\Cli\Configuration\LibraryLoader;
use TechDivision\Import\Cli\Utils\DependencyInjectionKeys;
use TechDivision\Import\Cli\Utils\MagentoConfigurationKeys;
use TechDivision\Import\Utils\CommandNames;
use TechDivision\Import\Utils\Mappings\CommandNameToEntityTypeCode;

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
     * @var \TechDivision\Import\ConfigurationFactoryInterface
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
     * Initializes the configuration loader.
     *
     * @param \Symfony\Component\Console\Input\InputInterface                 $input                        The input instance
     * @param \Symfony\Component\DependencyInjection\ContainerInterface       $container                    The container instance
     * @param \TechDivision\Import\Cli\Configuration\LibraryLoader            $libraryLoader                The configuration loader instance
     * @param \TechDivision\Import\ConfigurationFactoryInterface              $configurationFactory         The configuration factory instance
     * @param \TechDivision\Import\Utils\CommandNames                         $commandNames                 The available command names
     * @param \TechDivision\Import\Utils\Mappings\CommandNameToEntityTypeCode $commandNameToEntityTypeCodes The mapping of the command names to the entity type codes
     */
    public function __construct(
        InputInterface $input,
        ContainerInterface $container,
        LibraryLoader $libraryLoader,
        ConfigurationFactoryInterface $configurationFactory,
        CommandNames $commandNames,
        CommandNameToEntityTypeCode $commandNameToEntityTypeCodes
    ) {

        // set the passed instances
        $this->input = $input;
        $this->container = $container;
        $this->libraryLoader = $libraryLoader;
        $this->configurationFactory = $configurationFactory;
        $this->commandNames = $commandNames;
        $this->commandNameToEntityTypeCode = $commandNameToEntityTypeCodes;
    }

    /**
     * Factory implementation to create a new initialized configuration instance.
     *
     * If command line options are specified, they will always override the
     * values found in the configuration file.
     *
     * @return \TechDivision\Import\ConfigurationInterface The configuration instance
     */
    public function load()
    {

        // initially try to create the configuration instance
        // $instance = $this->createInstance();
        $instance = $this->createInstanceFromDirectories();

        // we have to set the entity type code at least
        $instance->setEntityTypeCode($this->getEntityTypeCode());

        // query whether or not a system name has been specified as command line option, if yes override the value from the configuration file
        if (($this->input->hasOptionSpecified(InputOptionKeys::SYSTEM_NAME) && $this->input->getOption(InputOptionKeys::SYSTEM_NAME)) || $instance->getSystemName() === null) {
            $instance->setSystemName($this->input->getOption(InputOptionKeys::SYSTEM_NAME));
        }

        // query whether or not a PID filename has been specified as command line option, if yes override the value from the configuration file
        if (($this->input->hasOptionSpecified(InputOptionKeys::PID_FILENAME) && $this->input->getOption(InputOptionKeys::PID_FILENAME)) || $instance->getPidFilename() === null) {
            $instance->setPidFilename($this->input->getOption(InputOptionKeys::PID_FILENAME));
        }

        // query whether or not a Magento installation directory has been specified as command line option, if yes override the value from the configuration file
        if (($this->input->hasOptionSpecified(InputOptionKeys::INSTALLATION_DIR) && $this->input->getOption(InputOptionKeys::INSTALLATION_DIR)) || $instance->getInstallationDir() === null) {
            $instance->setInstallationDir($this->input->getOption(InputOptionKeys::INSTALLATION_DIR));
        }

        // query whether or not a Magento Edition has been specified as command line option, if yes override the value from the configuration file
        if (($this->input->hasOptionSpecified(InputOptionKeys::MAGENTO_EDITION) && $this->input->getOption(InputOptionKeys::MAGENTO_EDITION)) || $instance->getMagentoEdition() === null) {
            $instance->setMagentoEdition($this->input->getOption(InputOptionKeys::MAGENTO_EDITION));
        }

        // query whether or not a Magento Version has been specified as command line option, if yes override the value from the configuration file
        if (($this->input->hasOptionSpecified(InputOptionKeys::MAGENTO_VERSION) && $this->input->getOption(InputOptionKeys::MAGENTO_VERSION)) || $instance->getMagentoVersion() === null) {
            $instance->setMagentoVersion($this->input->getOption(InputOptionKeys::MAGENTO_VERSION));
        }

        // query whether or not a directory for the source files has been specified as command line option, if yes override the value from the configuration file
        if (($this->input->hasOptionSpecified(InputOptionKeys::SOURCE_DIR) && $this->input->getOption(InputOptionKeys::SOURCE_DIR)) || $instance->getSourceDir() === null) {
            $instance->setSourceDir($this->input->getOption(InputOptionKeys::SOURCE_DIR));
        }

        // return the initialized configuration instance
        return $instance;
    }

    protected function createInstanceFromDirectories()
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

        // load the actual vendor directory and entity type code
        $directories = array(
            implode(DIRECTORY_SEPARATOR, array($vendorDir, 'techdivision', 'import-configuration-jms', 'etc')),
            implode(DIRECTORY_SEPARATOR, array(getcwd(), 'etc'))
        );

        return $this->configurationFactory->factoryFromDirectories($directories);
    }

    /**
     * This method create the configuration instance from the configuration file
     * defined by the commandline args and options.
     *
     * @return \TechDivision\Import\ConfigurationInterface The configuration instance loaded from the configuration file
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
        if ($configuration = $this->input->getOption(InputOptionKeys::CONFIGURATION)) {
            // load the configuration from the file with the given filename
            return $this->createConfiguration($configuration);
        } elseif (($magentoEdition = $this->input->getOption(InputOptionKeys::MAGENTO_EDITION)) && ($magentoVersion = $this->input->getOption(InputOptionKeys::MAGENTO_VERSION))) {
            // use the Magento Edition that has been specified as option
            $instance = $this->createConfiguration($this->getDefaultConfiguration($magentoEdition, $magentoVersion, $this->getEntityTypeCode()));

            // override the Magento Edition/Version
            $instance->setMagentoEdition($magentoEdition);
            $instance->setMagentoVersion($magentoVersion);

            // return the configuration intance
            return $instance;
        }

        // finally, query whether or not the installation directory is a valid Magento root directory
        if (!$this->isMagentoRootDir($installationDir = $this->input->getOption(InputOptionKeys::INSTALLATION_DIR))) {
            throw new \Exception(
                sprintf(
                    'Directory "%s" specified with option "--installation-dir" is not a valid Magento root directory',
                    $installationDir
                )
            );
        }

        // load the Magento Edition from the Composer configuration file
        $metadata = $this->getEditionMapping($installationDir);

        // extract edition & version from the metadata
        $magentoEdition = $metadata[SimpleConfigurationLoader::EDITION];
        $magentoVersion = $metadata[SimpleConfigurationLoader::VERSION];

        // use the Magento Edition that has been detected by the installation directory
        $instance = $this->createConfiguration($this->getDefaultConfiguration($magentoEdition, $magentoVersion, $this->getEntityTypeCode()));

        // override the Magento Edition/Version
        $instance->setMagentoEdition($magentoEdition);
        $instance->setMagentoVersion($magentoVersion);

        // return the configuration intance
        return $instance;
    }

    /**
     * Create and return a new configuration instance from the passed configuration filename
     * after merging additional specified params from the commandline.
     *
     * @param string $filename The configuration filename to use
     *
     * @return \TechDivision\Import\ConfigurationInterface The configuration instance
     */
    protected function createConfiguration($filename)
    {

        // initialize the params specified with the --params parameter
        $params = null;

        // try to load the params from the commandline
        if ($this->input->hasOptionSpecified(InputOptionKeys::PARAMS) && $this->input->getOption(InputOptionKeys::PARAMS)) {
            $params = $this->input->getOption(InputOptionKeys::PARAMS);
        }

        // initialize the params file specified with the --params-file parameter
        $paramsFile = null;

        // try to load the path of the params file from the commandline
        if ($this->input->hasOptionSpecified(InputOptionKeys::PARAMS_FILE) && $this->input->getOption(InputOptionKeys::PARAMS_FILE)) {
            $paramsFile = $this->input->getOption(InputOptionKeys::PARAMS_FILE);
        }

        // create the configuration and return it
        return $this->configurationFactory->factory($filename, pathinfo($filename, PATHINFO_EXTENSION), $params, $paramsFile);
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
     * Return's the default configuration for the passed Magento Edition/Version and the actual entity type.
     *
     * @param string $magentoEdition The Magento Edition to return the configuration for
     * @param string $magentoVersion The Magento Version to return the configuration for
     * @param string $entityTypeCode The entity type code to use
     *
     * @return string The path to the default configuration
     */
    protected function getDefaultConfiguration($magentoEdition, $magentoVersion, $entityTypeCode)
    {

        // load the components the filename has to be concatenated with
        $vendorDir = $this->getVendorDir();
        $libraryDir = $this->getDefaultConfigurationLibrary($magentoEdition, $entityTypeCode);
        $filename = $this->getDefaultConfigurationFile($entityTypeCode);

        // load the directories that equals the versions custom configuration files are available for
        $versions = glob(sprintf('%s/%s/etc/*', $vendorDir, $libraryDir), GLOB_ONLYDIR);

        // sort the directories descending by their version
        usort($versions, 'version_compare');
        krsort($versions);

        // explode the Magento version
        $explodedMagentoVersion = explode('.', $magentoVersion);

        // initialize the proposed filename with the default file in the library's root directory
        $proposedfilename = $filename;

        // iterate over the magento versions, try to find the matching configuration file
        for ($i = sizeof($explodedMagentoVersion); $i > 0; $i--) {
            foreach ($versions as $version) {
                // create a version number
                $level = implode('.', array_slice($explodedMagentoVersion, 0, $i));
                // try to match the version number against the directory
                if (version_compare($versionBasname = basename($version), $level, '<=') && is_file(sprintf('%s/%s.json', $version, $filename))) {
                    // we found the apropriate version directory and stop here
                    $proposedfilename = sprintf('%s/%s', $versionBasname, $filename);
                    break 2;
                }
            }
        }

        // return the default configuration file
        return sprintf('%s/%s/etc/%s.json', $vendorDir, $libraryDir, $proposedfilename);
    }

    /**
     * Return's the name of the default configuration file.
     *
     * @param string $entityTypeCode The entity type code to return the default configuration file for
     *
     * @return string The name of the entity type's default configuration file
     * @throws \Exception
     */
    protected function getDefaultConfigurationFile($entityTypeCode)
    {

        // load the default configuration file mappings from the configuration
        $defaultConfigurationFileMappings = $this->getContainer()->getParameter(DependencyInjectionKeys::APPLICATION_DEFAULT_CONFIGURATION_FILE_MAPPINGS);

        // query whether or not a default configuration file for the passed entity type code exists
        if (isset($defaultConfigurationFileMappings[$entityTypeCode])) {
            return $defaultConfigurationFileMappings[$entityTypeCode];
        }

        // throw an exception, if no default configuration file for the passed entity type is available
        throw new \Exception(
            sprintf(
                'Can\'t find a default configuration file for entity Type Code \'%s\' (MUST be one of catalog_product, catalog_product_price, catalog_product_inventory, catalog_category or eav_attribute)',
                $entityTypeCode
            )
        );
    }

    /**
     * Return's the Magento Edition and entity type's specific default library that contains
     * the configuration file.
     *
     * @param string $magentoEdition The Magento Edition to return the default library for
     * @param string $entityTypeCode The entity type code to return the default library file for
     *
     * @return string The name of the library that contains the default configuration file for the passed Magento Edition and entity type code
     * @throws \Exception Is thrown, if no default configuration for the passed entity type code is available
     */
    protected function getDefaultConfigurationLibrary($magentoEdition, $entityTypeCode)
    {

        // load the default configuration file mappings from the configuration
        $defaultConfigurations = $this->getContainer()->getParameter(DependencyInjectionKeys::APPLICATION_DEFAULT_CONFIGURATIONS);

        // query whether or not, a default configuration file for the passed entity type is available
        if (isset($defaultConfigurations[$edition = strtolower($magentoEdition)])) {
            if (isset($defaultConfigurations[$edition][$entityTypeCode])) {
                return $defaultConfigurations[$edition][$entityTypeCode];
            }

            // throw an exception, if the passed entity type is not supported
            throw new \Exception(
                sprintf(
                    'Entity Type Code \'%s\' not supported by entity type code \'%s\' (MUST be one of catalog_product, catalog_category or eav_attribute)',
                    $edition,
                    $entityTypeCode
                )
            );
        }

        // throw an exception, if the passed edition is not supported
        throw new \Exception(
            sprintf(
                'Default configuration for Magento \'%s\' not supported (MUST be one of CE or EE)',
                $magentoEdition
            )
        );
    }
}
