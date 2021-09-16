<?php

/**
 * TechDivision\Import\Cli\Command\ImportCreateOkFileCommand
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

use Symfony\Component\Console\Input\InputArgument;
use TechDivision\Import\Utils\CommandNames;
use TechDivision\Import\Utils\InputArgumentKeysInterface;

/**
 * The command implementation that creates a OK file from a directory with CSV files.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class ImportCreateOkFileCommand extends AbstractImportCommand
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
        $this->setName(CommandNames::IMPORT_CREATE_OK_FILE)
            ->addArgument(InputArgumentKeysInterface::SHORTCUT, InputArgument::OPTIONAL, 'The shortcut that defines the operation(s) that has to be used for the import, one of "create-ok-files" or a combination of them', 'create-ok-files')
            ->setDescription('Create\'s the OK file for the CSV files of the configured source directory');

        // invoke the parent method
        parent::configure();
    }
}
