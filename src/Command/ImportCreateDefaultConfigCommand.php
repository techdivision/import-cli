<?php
/**
 * TechDivision\Import\Cli\Command\ImportClearPidFileCommand
 *
 * PHP version 7
 *
 * @author    met@techdivision.com
 * @copyright 2023 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Cli\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TechDivision\Import\Configuration\ConfigurationInterface;
use TechDivision\Import\Utils\CommandNames;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The import command implementation.
 *
 * @author    met@techdivision.com
 * @copyright 2023 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class ImportCreateDefaultConfigCommand extends AbstractSimpleImportCommand
{
    /**
     * Configures the current command.
     *
     * @return void
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure(): void
    {
        // initialize the command with the required/optional options
        $this->setName(CommandNames::IMPORT_CREATE_CONFIG)
            ->setDescription('Create the default config file which is used by the diff command');

        // invoke the parent method
        parent::configure();
    }

    /**
     * Finally executes the simple command.
     *
     * @param ConfigurationInterface $configuration The configuration instance
     * @param InputInterface         $input         An InputInterface instance
     * @param OutputInterface        $output        An OutputInterface instance
     *
     * @return int
     */
    protected function executeSimpleCommand(
        ConfigurationInterface $configuration,
        InputInterface $input,
        OutputInterface $output
    ): int {
        $serializer = $this->createSerializer();
        $configValues = $serializer->serialize($configuration, 'json');

        // create new default json file
        $fs = new Filesystem();
        $fs->remove(ImportConfigDiffCommand::DEFAULT_FILE);
        $fs->appendToFile(ImportConfigDiffCommand::DEFAULT_FILE, $configValues);

        $output->writeln('[*] successfully created default file');
        return 0;
    }
}
