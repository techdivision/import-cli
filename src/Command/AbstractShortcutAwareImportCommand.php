<?php

/**
 * TechDivision\Import\Cli\Command\AbstractShortcutAwareImportCommand
 *
 * PHP version 7
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2019 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Cli\Command;

use TechDivision\Import\Utils\OperationKeys;
use Symfony\Component\Console\Input\InputArgument;
use TechDivision\Import\Utils\InputArgumentKeysInterface;

/**
 * The command implementation for shortcut aware import commands.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2019 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
abstract class AbstractShortcutAwareImportCommand extends AbstractImportCommand
{

    /**
     * Configures the current command.
     *
     * @return void
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {

        // initialize the command with the required/optional options
        $this->addArgument(InputArgumentKeysInterface::SHORTCUT, InputArgument::OPTIONAL, 'The shortcut that defines the operation(s) that has to be used for the import, one of "add-update", "replace", "delete" or "convert" or a combination of them', OperationKeys::ADD_UPDATE);

        // invoke the parent method
        parent::configure();
    }
}
