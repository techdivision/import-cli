<?php
/**
 * TechDivision\Import\Cli\Command\ImportClearPidFileCommand
 *
 * PHP version 7
 *
 * @author
 * @copyright 2023 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Cli\Command;

use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Visitor\Factory\JsonSerializationVisitorFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TechDivision\Import\Configuration\ConfigurationInterface;
use TechDivision\Import\Utils\CommandNames;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The import command implementation.
 *
 * @author
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
    protected function configure()
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
     * @param \TechDivision\Import\Configuration\ConfigurationInterface $configuration The configuration instance
     * @param \Symfony\Component\Console\Input\InputInterface           $input         An InputInterface instance
     * @param \Symfony\Component\Console\Output\OutputInterface         $output        An OutputInterface instance
     *
     * @return void
     */
    protected function executeSimpleCommand(
        ConfigurationInterface $configuration,
        InputInterface $input,
        OutputInterface $output
    ) {
        $format = 'json';
        $builder = SerializerBuilder::create();
        $builder->addDefaultSerializationVisitors();
        $namingStrategy = new SerializedNameAnnotationStrategy(new IdenticalPropertyNamingStrategy());

        // register the visitor in the builder instance
        $visitor = new JsonSerializationVisitorFactory($namingStrategy);
        $visitor->setOptions(JSON_PRETTY_PRINT);
        $builder->setSerializationVisitor($format, $visitor);
        $serializer = $builder->build();

        // write values to file
        $configValues = $serializer->serialize($configuration, $format);
        $fs = new Filesystem();
        $fs->remove(ImportConfigDiffCommand::DEFAULT_FILE);
        $fs->appendToFile(ImportConfigDiffCommand::DEFAULT_FILE, $configValues);
        $output->writeln('[*] succesfully created default file');
        return 0;
    }
}
