<?php

/**
 * TechDivision\Import\Cli\Command\InputArgumentKeys
 *
 * PHP version 7
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2020 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Cli\Command;

use TechDivision\Import\Utils\InputArgumentKeysInterface;

/**
 * Utility class containing the available input argument keys.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2020 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli
 * @link      http://www.techdivision.com
 */
class InputArgumentKeys extends \ArrayObject implements InputArgumentKeysInterface
{

    /**
     * Construct a new input option instance.
     *
     * @param array $inputArgumentKeys The array with the additional input option names
     * @link http://www.php.net/manual/en/arrayobject.construct.php
     */
    public function __construct(array $inputArgumentKeys = array())
    {

        // merge the input argument keys with the passed ones
        $mergedInputArgumentKeys = array_merge(
            array(
                InputArgumentKeysInterface::SHORTCUT,
                InputArgumentKeysInterface::OPERATION_NAMES,
                InputArgumentKeysInterface::ENTITY_TYPE_CODE,
                InputArgumentKeysInterface::COLUMN,
                InputArgumentKeysInterface::VALUES,
            ),
            $inputArgumentKeys
        );

        // initialize the parent class with the merged input argument keys
        parent::__construct($mergedInputArgumentKeys);
    }

    /**
     * Query whether or not the passed input argument is valid.
     *
     * @param string $inputArgument The input argument to query for
     *
     * @return boolean TRUE if the input argument is valid, else FALSE
     */
    public function isInputArgument($inputArgument)
    {
        return in_array($inputArgument, (array) $this);
    }
}
