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

use TechDivision\Import\Utils\CommandNames;
use TechDivision\Import\ConfigurationInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TechDivision\Import\Cli\Utils\DependencyInjectionKeys;

/**
 * The command implementation that creates a OK file from a directory with CSV files.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class ImportCreateOkFileCommand extends AbstractSimpleImportCommand
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
             ->setDescription('Create\'s the OK file for the CSV files of the configured source directory');

        // invoke the parent method
        parent::configure();
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input  An InputInterface instance
     * @param \Symfony\Component\Console\Output\OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     * @throws \LogicException When this abstract method is not implemented
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // initialize the configuration instance
        $configuration = $this->getContainer()->get(DependencyInjectionKeys::CONFIGURATION_SIMPLE);

        // finally execute the simple command
        return $this->executeSimpleCommand($configuration, $input, $output);
    }


    /**
     * Finally executes the simple command.
     *
     * @param \TechDivision\Import\ConfigurationInterface       $configuration The configuration instance
     * @param \Symfony\Component\Console\Input\InputInterface   $input         An InputInterface instance
     * @param \Symfony\Component\Console\Output\OutputInterface $output        An OutputInterface instance
     *
     * @return void
     */
    protected function executeSimpleCommand(
        ConfigurationInterface $configuration,
        InputInterface $input,
        OutputInterface $output
    ) {

        // load the source directory, ALWAYS remove the directory separator, if appended
        $sourceDir = rtrim($configuration->getSourceDir(), DIRECTORY_SEPARATOR);

        // load the array with the unique prefixes
        $prefixes = $configuration->getPrefixes();

        // sort the prefixes
        usort($prefixes, function ($a, $b) {
            return strcmp($a, $b);
        });

        // initialize the counter for the CSV files
        $csvFilesFound = 0;

        // iterate over the prefixes and create the .ok files
        foreach ($prefixes as $prefix) {
            // load the CSVfiles from the source directory
            $csvFiles = glob(sprintf('%s/%s_*.csv', $sourceDir, $prefix));
            // raise the counter for the CSV files we've found
            $csvFilesFound += sizeof($csvFiles);
            // query whether or not any CSV files are available
            if (sizeof($csvFiles) > 0) {
                // prepare the OK file's content
                $okfileContent = '';
                foreach ($csvFiles as $filename) {
                    $okfileContent .= basename($filename) . PHP_EOL;
                }

                // prepare the OK file's name
                $okFilename = sprintf('%s/%s.ok', $sourceDir, $prefix);

                // write the OK file
                if (file_put_contents($okFilename, $okfileContent)) {
                    // write a message to the console
                    $output->writeln(sprintf('<info>Successfully written OK file %s</info>', $okFilename));
                } else {
                    // write a message to the console
                    $output->writeln(sprintf('<error>Can\'t write OK file %s</error>', $okFilename));
                }
            }
        }

        // query whether or not we've found any CSV files
        if ($csvFilesFound === 0) {
            // write a message to the console, if we can't find any CSV files
            $output->writeln(sprintf('<error>Can\'t find any CSV files in source directory %s</error>', $sourceDir));
            // return 1 to signal an error
            return 1;
        }

        // return 0 to signal success
        return 0;
    }
}
