<?php

/**
 * TechDivision\Import\Cli\Command\ImportDebugCommand
 *
 * PHP version 7
 *
 * @author    Marcus Döllerer <m.doellerer@techdivision.com>
 * @copyright 2020 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli
 * @link      https://www.techdivision.com
 */

namespace TechDivision\Import\Cli\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use TechDivision\Import\Utils\CommandNames;
use TechDivision\Import\Utils\InputArgumentKeysInterface;
use TechDivision\Import\Utils\InputOptionKeysInterface;

/**
 * Command implementation that provides debugging functionality.
 *
 * @author    Marcus Döllerer <m.doellerer@techdivision.com>
 * @copyright 2020 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli
 * @link      https://www.techdivision.com
 */
class ImportDebugCommand extends AbstractImportCommand
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
        $this->setName(CommandNames::IMPORT_DEBUG)
             ->addArgument(InputArgumentKeysInterface::SHORTCUT, InputArgument::OPTIONAL, 'The shortcut that defines the operation(s) that has to be used for debugging the import, one of "send" or a combination of them', 'send')
             ->addOption(InputOptionKeysInterface::RENDER_DEBUG_SERIALS, null, InputOption::VALUE_OPTIONAL, 'The number of debug serials rendered on the console', 10)
             ->setDescription('Creates a debug dump and mails it to the specified recipient');

        // invoke the parent method
        parent::configure();
    }
}
