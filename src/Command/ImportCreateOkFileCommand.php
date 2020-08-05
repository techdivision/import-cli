<?php

/**
 * TechDivision\Import\Cli\Command\ImportCreateOkFileCommand
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

use Symfony\Component\Console\Input\InputArgument;
use TechDivision\Import\Utils\CommandNames;
use TechDivision\Import\Utils\InputArgumentKeysInterface;

/**
 * The command implementation that creates a OK file from a directory with CSV files.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
