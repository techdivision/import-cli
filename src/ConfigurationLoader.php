<?php

/**
 * TechDivision\Import\Cli\ConfigurationLoader
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

use Psr\Log\LogLevel;
use Ramsey\Uuid\Uuid;
use TechDivision\Import\ConfigurationInterface;
use TechDivision\Import\Configuration\Jms\Configuration\Database;
use TechDivision\Import\Cli\Command\InputArgumentKeys;
use TechDivision\Import\Cli\Command\InputOptionKeys;
use TechDivision\Import\Cli\Utils\DependencyInjectionKeys;
use TechDivision\Import\Cli\Utils\MagentoConfigurationKeys;

/**
 * The configuration loader implementation.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class ConfigurationLoader extends SimpleConfigurationLoader
{

    /**
     * Factory implementation to create a new initialized configuration instance.
     *
     * If command line options are specified, they will always override the
     * values found in the configuration file.
     *
     * @return \TechDivision\Import\ConfigurationInterface The configuration instance
     * @throws \Exception Is thrown, if the specified configuration file doesn't exist or the mandatory arguments/options to run the requested operation are not available
     */
    public function load()
    {

        // load the configuration instance
        $instance = parent::load();

        // set the serial that has been specified as command line option (or the default value)
        $instance->setSerial($this->input->getOption(InputOptionKeys::SERIAL));

        // query whether or not operation names has been specified as command line
        // option, if yes override the value from the configuration file
        if ($operationNames = $this->input->getArgument(InputArgumentKeys::OPERATION_NAMES)) {
            // append the names of the operations we want to execute to the configuration
            foreach ($operationNames as $operationName) {
                $instance->addOperationName($operationName);
            }
        }

        // query whether or not a directory containing the imported files has been specified as command line
        // option, if yes override the value from the configuration file
        if ($targetDir = $this->input->getOption(InputOptionKeys::TARGET_DIR)) {
            $instance->setTargetDir($targetDir);
        }

        // query whether or not a directory containing the archived imported files has been specified as command line
        // option, if yes override the value from the configuration file
        if ($archiveDir = $this->input->getOption(InputOptionKeys::ARCHIVE_DIR)) {
            $instance->setArchiveDir($archiveDir);
        }

        // query whether or not the log level has been specified as command line
        // option, if yes override the value from the configuration file
        if ($logLevel = $this->input->getOption(InputOptionKeys::LOG_LEVEL)) {
            $instance->setLogLevel($logLevel);
        }

        // query whether or not a prefix for the move files subject has been specified as command line
        // option, if yes override the value from the configuration file
        if ($moveFilesPrefix = $this->input->getOption(InputOptionKeys::MOVE_FILES_PREFIX)) {
            $instance->setMoveFilesPrefix($moveFilesPrefix);
        }

        // query whether or not the debug mode has been specified as command line
        // option, if yes override the value from the configuration file
        if ($this->input->hasOptionSpecified(InputOptionKeys::ARCHIVE_ARTEFACTS)) {
            $instance->setArchiveArtefacts($instance->mapBoolean($this->input->getOption(InputOptionKeys::ARCHIVE_ARTEFACTS)));
        }

        // query whether or not the debug mode has been specified as command line
        // option, if yes override the value from the configuration file
        if ($this->input->hasOptionSpecified(InputOptionKeys::DEBUG_MODE)) {
            $instance->setDebugMode($instance->mapBoolean($this->input->getOption(InputOptionKeys::DEBUG_MODE)));
        }

        // query whether or not the single transaction flag has been specified as command line
        // option, if yes override the value from the configuration file
        if ($this->input->hasOptionSpecified(InputOptionKeys::SINGLE_TRANSACTION)) {
            $instance->setSingleTransaction($instance->mapBoolean($this->input->getOption(InputOptionKeys::SINGLE_TRANSACTION)));
        }

        // query whether or not the cache flag has been specified as command line
        // option, if yes override the value from the configuration file
        if ($this->input->hasOptionSpecified(InputOptionKeys::CACHE_ENABLED)) {
            $instance->setCacheEnabled($instance->mapBoolean($this->input->getOption(InputOptionKeys::CACHE_ENABLED)));
        }

        // query whether or not the move files flag has been specified as command line
        // option, if yes override the value from the configuration file
        if ($this->input->hasOptionSpecified(InputOptionKeys::MOVE_FILES)) {
            $instance->setMoveFiles($instance->mapBoolean($this->input->getOption(InputOptionKeys::MOVE_FILES)));
        }

        // query whether or not the configurationfiles flag has been specified as command line
        // option, if yes override the value from the configuration file
        if ($this->input->hasOptionSpecified(InputOptionKeys::COMPILE)) {
            $instance->setCompile($instance->mapBoolean($this->input->getOption(InputOptionKeys::COMPILE)));
        }

        // query whether or not we've an valid Magento root directory specified
        if ($this->isMagentoRootDir($installationDir = $instance->getInstallationDir())) {
            // if yes, add the database configuration
            $instance->addDatabase($this->getMagentoDbConnection($installationDir));

            // add the source directory if NOT specified in the configuration file
            if (($sourceDir = $instance->getSourceDir()) === null) {
                $instance->setSourceDir($sourceDir = sprintf('%s/var/importexport', $installationDir));
            }

            // add the target directory if NOT specified in the configuration file
            if ($instance->getTargetDir() === null) {
                $instance->setTargetDir($sourceDir);
            }
        }

        // query whether or not a DB ID has been specified as command line
        // option, if yes override the value from the configuration file
        if ($useDbId = $this->input->getOption(InputOptionKeys::USE_DB_ID)) {
            $instance->setUseDbId($useDbId);
        } else {
            // query whether or not a PDO DSN has been specified as command line
            // option, if yes override the value from the configuration file
            if ($dsn = $this->input->getOption(InputOptionKeys::DB_PDO_DSN)) {
                // first REMOVE all other database configurations
                $instance->clearDatabases();

                // add the database configuration
                $instance->addDatabase(
                    $this->newDatabaseConfiguration(
                        $dsn,
                        $this->input->getOption(InputOptionKeys::DB_USERNAME),
                        $this->input->getOption(InputOptionKeys::DB_PASSWORD)
                    )
                );
            }
        }

        // query whether or not a DB ID has been specified as command line
        // option, if yes override the value from the configuration file
        if ($tablePrefix = $this->input->getOption(InputOptionKeys::DB_TABLE_PREFIX)) {
            $instance->getDatabase()->setTablePrefix($tablePrefix);
        }

        // extend the plugins with the main configuration instance
        /** @var \TechDivision\Import\Cli\Configuration\Subject $subject */
        foreach ($instance->getPlugins() as $plugin) {
            // set the configuration instance on the plugin
            $plugin->setConfiguration($instance);

            // query whether or not the plugin has subjects configured
            if ($subjects = $plugin->getSubjects()) {
                // extend the plugin's subjects with the main configuration instance
                /** @var \TechDivision\Import\Cli\Configuration\Subject $subject */
                foreach ($subjects as $subject) {
                    // set the configuration instance on the subject
                    $subject->setConfiguration($instance);
                }
            }
        }

        // query whether or not the debug mode is enabled and log level
        // has NOT been overwritten with a commandline option
        if ($instance->isDebugMode() && !$this->input->getOption(InputOptionKeys::LOG_LEVEL)) {
            // set debug log level, if log level has NOT been overwritten on command line
            $instance->setLogLevel(LogLevel::DEBUG);
        }

        // prepend the array with the Magento Edition specific core libraries
        $instance->setExtensionLibraries(
            array_merge(
                $this->getDefaultLibraries($instance->getMagentoEdition()),
                $instance->getExtensionLibraries()
            )
        );

        // load the extension libraries, if configured
        $this->libraryLoader->load($instance);

        // register the configured aliases in the DI container, this MUST
        // happen after the libraries have been loaded, else it would not
        // be possible to override existing aliases
        $this->initializeAliases($instance);

        // return the initialized configuration instance
        return $instance;
    }

    /**
     * Return's the requested Magento DB connction data.
     *
     * @param string $dir            The path to the Magento root directory
     * @param string $connectionName The connection name to return the data for
     *
     * @return array The connection data
     * @throws \Exception Is thrown, if the requested DB connection is not available
     */
    protected function getMagentoDbConnection($dir, $connectionName = 'default')
    {

        // load the magento environment
        $env = require $this->getMagentoEnv($dir);

        // query whether or not, the requested connection is available
        if (isset($env[MagentoConfigurationKeys::DB][MagentoConfigurationKeys::CONNECTION][$connectionName])) {
            // load the databaase connection
            $db = $env[MagentoConfigurationKeys::DB];
            // load the connection data
            $connection = $db[MagentoConfigurationKeys::CONNECTION][$connectionName];

            // create and return a new database configuration
            return $this->newDatabaseConfiguration(
                $this->newDsn($connection[MagentoConfigurationKeys::HOST], $connection[MagentoConfigurationKeys::DBNAME]),
                $connection[MagentoConfigurationKeys::USERNAME],
                $connection[MagentoConfigurationKeys::PASSWORD],
                false,
                null,
                isset($db[MagentoConfigurationKeys::TABLE_PREFIX]) ? $db[MagentoConfigurationKeys::TABLE_PREFIX] : null
            );
        }

        // throw an execption if not
        throw new \Exception(sprintf('Requested Magento DB connection "%s" not found in Magento "%s"', $connectionName, $dir));
    }

    /**
     * Create's and return's a new database configuration instance, initialized with
     * the passed values.
     *
     * @param string      $dsn         The DSN to use
     * @param string      $username    The username to  use
     * @param string|null $password    The passed to use
     * @param boolean     $default     TRUE if this should be the default connection
     * @param string      $id          The ID to use
     * @param string      $tablePrefix The table prefix to use
     *
     * @return \TechDivision\Import\Configuration\Jms\Configuration\Database The database configuration instance
     */
    protected function newDatabaseConfiguration($dsn, $username = 'root', $password = null, $default = true, $id = null, $tablePrefix = null)
    {

        // initialize a new database configuration
        $database = new Database();
        $database->setDsn($dsn);
        $database->setDefault($default);
        $database->setUsername($username);

        // query whether or not an ID has been passed
        if ($id === null) {
            $id = Uuid::uuid4()->__toString();
        }

        // set the ID
        $database->setId($id);

        // query whether or not a password has been passed
        if ($password) {
            $database->setPassword($password);
        }

        // query whether or not a table prefix has been passed
        if ($tablePrefix) {
            $database->setTablePrefix($tablePrefix);
        }

        // return the database configuration
        return $database;
    }

    /**
     * Create's and return's a new DSN from the passed values.
     *
     * @param string $host    The host to use
     * @param string $dbName  The database name to use
     * @param string $charset The charset to use
     *
     * @return string The DSN
     */
    protected function newDsn($host, $dbName, $charset = 'utf8')
    {
        return sprintf('mysql:host=%s;dbname=%s;charset=%s', $host, $dbName, $charset);
    }

    /**
     * Return's the Magento Edition specific default libraries. Supported Magento Editions are CE or EE.
     *
     * @param string $magentoEdition The Magento Edition to return the libraries for
     *
     * @return array The Magento Edition specific default libraries
     * @throws \Exception Is thrown, if the passed Magento Edition is NOT supported
     */
    protected function getDefaultLibraries($magentoEdition)
    {

        // load the default libraries from the configuration
        $defaultLibraries = $this->getContainer()->getParameter(DependencyInjectionKeys::APPLICATION_DEFAULT_LIBRARIES);

        // query whether or not, default libraries for the passed edition are available
        if (isset($defaultLibraries[$edition = strtolower($magentoEdition)])) {
            return $defaultLibraries[$edition];
        }

        // throw an exception, if the passed edition is not supported
        throw new \Exception(
            sprintf(
                'Default libraries for Magento \'%s\' not supported (MUST be one of CE or EE)',
                $magentoEdition
            )
        );
    }

    /**
     * Registers the configured aliases in the DI container.
     *
     * @param \TechDivision\Import\ConfigurationInterface $configuration The configuration with the aliases to register
     *
     * @return void
     */
    protected function initializeAliases(ConfigurationInterface $configuration)
    {

        // load the DI aliases
        $aliases = $configuration->getAliases();

        // register the DI aliases
        foreach ($aliases as $alias) {
            $this->getContainer()->setAlias($alias->getId(), $alias->getTarget());
        }
    }
}
