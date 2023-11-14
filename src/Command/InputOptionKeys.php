<?php

/**
 * TechDivision\Import\Cli\Command\InputOptionKeys
 *
 * PHP version 7
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Cli\Command;

use TechDivision\Import\Utils\InputOptionKeysInterface;

/**
 * Utility class containing the available input options.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class InputOptionKeys extends \ArrayObject implements InputOptionKeysInterface
{

    /**
     * Construct a new input option instance.
     *
     * @param array $inputOptionKeys The array with the additional input option names
     * @link http://www.php.net/manual/en/arrayobject.construct.php
     */
    public function __construct(array $inputOptionKeys = array())
    {

        // merge the input options with the passed ones
        $mergedInputOptionKeys = array_merge(
            array(
                InputOptionKeysInterface::SERIAL,
                InputOptionKeysInterface::SYSTEM_NAME,
                InputOptionKeysInterface::CONFIGURATION,
                InputOptionKeysInterface::INSTALLATION_DIR,
                InputOptionKeysInterface::CONFIGURATION_DIR,
                InputOptionKeysInterface::SOURCE_DIR,
                InputOptionKeysInterface::TARGET_DIR,
                InputOptionKeysInterface::ARCHIVE_DIR,
                InputOptionKeysInterface::ARCHIVE_ARTEFACTS,
                InputOptionKeysInterface::CLEAR_ARTEFACTS,
                InputOptionKeysInterface::MAGENTO_EDITION,
                InputOptionKeysInterface::MAGENTO_VERSION,
                InputOptionKeysInterface::USE_DB_ID,
                InputOptionKeysInterface::DB_PDO_DSN,
                InputOptionKeysInterface::DB_USERNAME,
                InputOptionKeysInterface::DB_PASSWORD,
                InputOptionKeysInterface::DB_TABLE_PREFIX,
                InputOptionKeysInterface::DEBUG_MODE,
                InputOptionKeysInterface::LOG_LEVEL,
                InputOptionKeysInterface::PID_FILENAME,
                InputOptionKeysInterface::DEST,
                InputOptionKeysInterface::SINGLE_TRANSACTION,
                InputOptionKeysInterface::PARAMS,
                InputOptionKeysInterface::PARAMS_FILE,
                InputOptionKeysInterface::CACHE_ENABLED,
                InputOptionKeysInterface::MOVE_FILES_PREFIX,
                InputOptionKeysInterface::CUSTOM_CONFIGURATION_DIR,
                InputOptionKeysInterface::CUSTOM_CONFIGURATION_PUBLIC_DIR,
                InputOptionKeysInterface::RENDER_VALIDATION_ISSUES,
                InputOptionKeysInterface::EMPTY_ATTRIBUTE_VALUE_CONSTANT,
                InputOptionKeysInterface::STRICT_MODE,
                InputOptionKeysInterface::CONFIG_OUTPUT,
                InputOptionKeysInterface::LOG_FILE

            ),
            $inputOptionKeys
        );

        // initialize the parent class with the merged input options
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
