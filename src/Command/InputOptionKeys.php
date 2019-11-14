<?php

/**
 * TechDivision\Import\Cli\Command\InputOptionKeys
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

namespace TechDivision\Import\Cli\Command;

use TechDivision\Import\Utils\InputOptionKeysInterface;

/**
 * Utility class containing the available visibility keys.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class InputOptionKeys extends \ArrayObject implements InputOptionKeysInterface
{

    /**
     * Input key for the --serial option.
     *
     * @var string
     */
    const SERIAL = 'serial';

    /**
     * The input option key for the system name to use.
     *
     * @var string
     */
    const SYSTEM_NAME = 'system-name';

    /**
     * The input option key for the path to the configuration file to use.
     *
     * @var string
     */
    const CONFIGURATION = 'configuration';

    /**
     * The input option key for the Magento installation directory.
     *
     * @var string
     */
    const INSTALLATION_DIR = 'installation-dir';

    /**
     * The input option key for the directory containing the files to be imported.
     *
     * @var string
     */
    const SOURCE_DIR = 'source-dir';

    /**
     * The input option key for the directory containing the imported files.
     *
     * @var string
     */
    const TARGET_DIR = 'target-dir';

    /**
     * The input option key for the directory containing the archived imported files.
     *
     * @var string
     */
    const ARCHIVE_DIR = 'archive-dir';

    /**
     * The input option key for the directory containing the flag to archive the imported files.
     *
     * @var string
     */
    const ARCHIVE_ARTEFACTS = 'archive-artefacts';

    /**
     * The input option key for the Magento edition, EE or CE.
     *
     * @var string
     */
    const MAGENTO_EDITION = 'magento-edition';

    /**
     * The input option key for the Magento version, e. g. 2.1.0.
     *
     * @var string
     */
    const MAGENTO_VERSION = 'magento-version';

    /**
     * The input option key for the database ID to use.
     *
     * @var string
     */
    const USE_DB_ID = 'use-db-id';

    /**
     * The input option key for the PDO DSN to use.
     *
     * @var string
     */
    const DB_PDO_DSN = 'db-pdo-dsn';

    /**
     * The input option key for the DB username to use.
     *
     * @var string
     */
    const DB_USERNAME = 'db-username';

    /**
     * The input option key for the DB password to use.
     *
     * @var string
     */
    const DB_PASSWORD = 'db-password';

    /**
     * The input option key for the DB table prefix to use.
     *
     * @var string
     */
    const DB_TABLE_PREFIX = 'db-table-prefix';

    /**
     * The input option key for the debug mode.
     *
     * @var string
     */
    const DEBUG_MODE = 'debug-mode';

    /**
     * The input option key for the log level to use.
     *
     * @var string
     */
    const LOG_LEVEL = 'log-level';

    /**
     * The input option key for the PID filename to use.
     *
     * @var string
     */
    const PID_FILENAME = 'pid-filename';

    /**
     * The input option key for the destination pathname to use.
     *
     * @var string
     */
    const DEST = 'dest';

    /**
     * The input option key for the single transaction flag.
     *
     * @var string
     */
    const SINGLE_TRANSACTION = 'single-transaction';

    /**
     * The input option key for additional params that has to be merged into the application configuration.
     *
     * @var string
     */
    const PARAMS = 'params';

    /**
     * The input option key for the path to additional params as file that has to be merged into the application configuration.
     *
     * @var string
     */
    const PARAMS_FILE = 'params-file';

    /**
     * The input option key for the flag to enable the cache functionality or not.
     *
     * @var string
     */
    const CACHE_ENABLED = 'cache-enabled';

    /**
     * The input option key for the move files prefix.
     *
     * @var string
     */
    const MOVE_FILES_PREFIX = 'move-files-prefix';

    /**
     * The input option key for the custom configuration directory.
     *
     * @var string
     */
    const CUSTOM_CONFIGURATION_DIR = 'custom-configuration-dir';

    /**
     * Construct a new input option instance.
     *
     * @param array $inputOptionKeys The array with the additional input option names
     * @link http://www.php.net/manual/en/arrayobject.construct.php
     */
    public function __construct(array $inputOptionKeys = array())
    {

        // merge the edition names with the passed ones
        $mergedInputOptionKeys = array_merge(
            array(
                InputOptionKeys::SERIAL,
                InputOptionKeys::SYSTEM_NAME,
                InputOptionKeys::CONFIGURATION,
                InputOptionKeys::INSTALLATION_DIR,
                InputOptionKeys::SOURCE_DIR,
                InputOptionKeys::TARGET_DIR,
                InputOptionKeys::ARCHIVE_DIR,
                InputOptionKeys::ARCHIVE_ARTEFACTS,
                InputOptionKeys::MAGENTO_EDITION,
                InputOptionKeys::MAGENTO_VERSION,
                InputOptionKeys::USE_DB_ID,
                InputOptionKeys::DB_PDO_DSN,
                InputOptionKeys::DB_USERNAME,
                InputOptionKeys::DB_PASSWORD,
                InputOptionKeys::DB_TABLE_PREFIX,
                InputOptionKeys::DEBUG_MODE,
                InputOptionKeys::LOG_LEVEL,
                InputOptionKeys::PID_FILENAME,
                InputOptionKeys::DEST,
                InputOptionKeys::SINGLE_TRANSACTION,
                InputOptionKeys::PARAMS,
                InputOptionKeys::PARAMS_FILE,
                InputOptionKeys::CACHE_ENABLED,
                InputOptionKeys::MOVE_FILES_PREFIX,
                InputOptionKeys::CUSTOM_CONFIGURATION_DIR,
            ),
            $inputOptionKeys
        );

        // initialize the parent class with the merged edition names
        parent::__construct($mergedInputOptionKeys);
    }

    /**
     * Query whether or not the passed input option is valid.
     *
     * @param string $inputOption The input option to query for
     *
     * @return boolean TRUE if the input option is valid, else FALSE
     */
    public function isInputOption($inputOption)
    {
        return in_array($inputOption, (array) $this);
    }
}
