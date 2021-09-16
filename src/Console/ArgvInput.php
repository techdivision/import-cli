<?php

/**
 * TechDivision\Import\Cli\Console\ArgvInput
 *
 * PHP version 7
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Cli\Console;

/**
 * Utility class containing the available visibility keys.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class ArgvInput extends \Symfony\Component\Console\Input\ArgvInput
{

    /**
     * Queries whether or not, a option value HAS been specified on command line.
     *
     * @param string $name The option name to be queried
     *
     * @return boolean TRUE if the option has been specified, else FALSE
     */
    public function hasOptionSpecified($name)
    {
        return isset($this->options[$name]);
    }
}
