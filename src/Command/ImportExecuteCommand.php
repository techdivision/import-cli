<?php

/**
 * TechDivision\Import\Cli\Command\ImportExecuteOperationsCommand
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

use TechDivision\Import\Utils\CommandNames;
use Symfony\Component\Console\Input\InputArgument;
use TechDivision\Import\Utils\InputArgumentKeysInterface;

/**
 * The import command implementation.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2019 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class ImportExecuteCommand extends AbstractImportCommand
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
        $this->setName(CommandNames::IMPORT_EXECUTE)
             ->setDescription('Executes the operations passed as argument')
             ->addArgument(InputArgumentKeysInterface::OPERATION_NAMES, InputArgument::IS_ARRAY|InputArgument::OPTIONAL, 'The operation(s) that has to be executed');

        // invoke the parent method
        parent::configure();
    }
}
