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
use TechDivision\Import\Configuration\ConfigurationInterface;
use TechDivision\Import\Configuration\Jms\Configuration\Database;
use TechDivision\Import\Utils\InputOptionKeysInterface;
use TechDivision\Import\Utils\InputArgumentKeysInterface;
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
     * @return \TechDivision\Import\Configuration\ConfigurationInterface The configuration instance
     * @throws \Exception Is thrown, if the specified configuration file doesn't exist or the mandatory arguments/options to run the requested operation are not available
     */
    public function load()
    {

        // load the configuration instance
        $instance = parent::load();

        // query whether or not a shortcut has been specified as command line
        // option, if yes override the value from the configuration file
        if ($this->input->hasArgument(InputArgumentKeysInterface::SHORTCUT)) {
            $instance->setShortcut($this->input->getArgument(InputArgumentKeysInterface::SHORTCUT));
        }

        // query whether or not operation names has been specified as command line
        // option, if yes override the value from the configuration file
        if ($this->input->hasArgument(InputArgumentKeysInterface::OPERATION_NAMES)) {
            // load the operation names from the commandline
            $operationNames = $this->input->getArgument(InputArgumentKeysInterface::OPERATION_NAMES);
            // append the names of the operations we want to execute to the configuration
            foreach ($operationNames as $operationName) {
                $instance->addOperationName($operationName);
            }
        }

        // query whether or not we've an valid Magento root directory specified
        if ($this->isMagentoRootDir($installationDir = $instance->getInstallationDir())) {
            // if yes, add the database configuration
            $instance->addDatabase($this->getMagentoDbConnection($installationDir));
        }

        // query whether or not a DB ID has been specified as command line
        // option, if yes override the value from the configuration file
        if ($useDbId = $this->input->getOption(InputOptionKeysInterface::USE_DB_ID)) {
            $instance->setUseDbId($useDbId);
        } else {
            // query whether or not a PDO DSN has been specified as command line
            // option, if yes override the value from the configuration file
            if ($dsn = $this->input->getOption(InputOptionKeysInterface::DB_PDO_DSN)) {
                // first REMOVE all other database configurations
                $instance->clearDatabases();

                // add the database configuration
                $instance->addDatabase(
                    $this->newDatabaseConfiguration(
                        $dsn,
                        $this->input->getOption(InputOptionKeysInterface::DB_USERNAME),
                        $this->input->getOption(InputOptionKeysInterface::DB_PASSWORD)
                    )
                );
            }
        }

        // query whether or not a DB ID has been specified as command line
        // option, if yes override the value from the configuration file
        if ($tablePrefix = $this->input->getOption(InputOptionKeysInterface::DB_TABLE_PREFIX)) {
            $instance->getDatabase()->setTablePrefix($tablePrefix);
        }

        // query whether or not the debug mode is enabled and log level
        // has NOT been overwritten with a commandline option
        if ($instance->isDebugMode() && !$this->input->getOption(InputOptionKeysInterface::LOG_LEVEL)) {
            // set debug log level, if log level has NOT been overwritten on command line
            $instance->setLogLevel(LogLevel::DEBUG);
        }

        // prepend the array with the Magento Edition specific core libraries
        $instance->setExtensionLibraries(
            array_merge(
                $this->getDefaultLibrariesByMagentoEdition($instance->getMagentoEdition()),
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

            // try to load port and table prefix (they're optional)
            $port = isset($connection[MagentoConfigurationKeys::PORT]) ? $connection[MagentoConfigurationKeys::PORT] : 3306;
            $tablePrefix = isset($db[MagentoConfigurationKeys::TABLE_PREFIX]) ? $db[MagentoConfigurationKeys::TABLE_PREFIX] : null;

            // create and return a new database configuration
            return $this->newDatabaseConfiguration(
                $this->newDsn($connection[MagentoConfigurationKeys::HOST], $port, $connection[MagentoConfigurationKeys::DBNAME]),
                $connection[MagentoConfigurationKeys::USERNAME],
                $connection[MagentoConfigurationKeys::PASSWORD],
                false,
                null,
                $tablePrefix
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
     * @param string  $host    The host to use
     * @param integer $port    The port to use
     * @param string  $dbName  The database name to use
     * @param string  $charset The charset to use
     *
     * @return string The DSN
     */
    protected function newDsn($host, $port = 3306, $dbName = 'magento', $charset = 'utf8')
    {
        return sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $dbName, $charset);
    }

    /**
     * Return's the Magento Edition specific default libraries. Supported Magento Editions are CE or EE.
     *
     * @param string $magentoEdition The Magento Edition to return the libraries for
     *
     * @return array The Magento Edition specific default libraries
     * @throws \Exception Is thrown, if the passed Magento Edition is NOT supported
     */
    protected function getDefaultLibrariesByMagentoEdition($magentoEdition)
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
     * @param \TechDivision\Import\Configuration\ConfigurationInterface $configuration The configuration with the aliases to register
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
