<?php

/**
 * TechDivision\Import\Cli\Command\DebugSendCommand
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 7
 *
 * @author    Marcus Döllerer <m.doellerer@techdivision.com>
 * @copyright 2020 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-cli
 * @link      https://www.techdivision.com
 */

namespace TechDivision\Import\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;
use TechDivision\Import\Utils\CommandNames;
use TechDivision\Import\Utils\InputArgumentKeysInterface;

/**
 * Command implementation that creates and sends a debug report via email.
 *
 * @author    Marcus Döllerer <m.doellerer@techdivision.com>
 * @copyright 2020 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-cli
 * @link      https://www.techdivision.com
 */
class DebugSendCommand extends AbstractImportCommand
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
        $this->setName(CommandNames::DEBUG_SEND)
            ->addArgument(InputArgumentKeysInterface::SHORTCUT, InputArgument::OPTIONAL, 'The shortcut that defines the operation(s) that has to be used for the import, one of "debug-send" or a combination of them', 'debug-send')
            ->setDescription('Creates a debug dump and mails it to the specified recipient');

        // invoke the parent method
        parent::configure();
    }
}
