<?php

/**
 * TechDivision\Import\Cli\Command\ImportCreateConfigurationFileCommand
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

use Jean85\PrettyVersions;
use JMS\Serializer\Visitor\Factory\JsonSerializationVisitorFactory;
use JMS\Serializer\Visitor\Factory\XmlSerializationVisitorFactory;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\XmlSerializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use TechDivision\Import\Utils\CommandNames;
use TechDivision\Import\Configuration\ConfigurationInterface;
use TechDivision\Import\Utils\InputOptionKeysInterface;

/**
 * The command implementation that creates a configuration file from one of the templates.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class ImportCreateConfigurationFileCommand extends AbstractSimpleImportCommand
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
        $this->setName(CommandNames::IMPORT_CREATE_CONFIGURATION_FILE)
             ->setDescription('Create\'s a configuration file from the given entity\'s template')
             ->addOption(InputOptionKeysInterface::DEST, null, InputOption::VALUE_REQUIRED, 'The relative/absolut pathname of the destination file');

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

        // initialie the directory where we want to export the configuration to
        $exportDir = $input->hasParameterOption(InputOptionKeysInterface::CUSTOM_CONFIGURATION_DIR) ? $input->getOption(InputOptionKeysInterface::CUSTOM_CONFIGURATION_DIR) : $configuration->getInstallationDir();

        // initialize the configuration filename
        $configurationFilename = $input->getOption(InputOptionKeysInterface::DEST);
        if ($configurationFilename === null) {
            $configurationFilename = sprintf('%s/app/etc/techdivision-import.json', $exportDir);
        }

        // extract the format from the configuration file suffix
        $format = pathinfo($configurationFilename, PATHINFO_EXTENSION);

        // initialize the serializer
        $builder = SerializerBuilder::create();
        $builder->addDefaultSerializationVisitors();

        // initialize the naming strategy
        $namingStrategy = new SerializedNameAnnotationStrategy(new IdenticalPropertyNamingStrategy());

        // create the configuration based on the given configuration file suffix
        switch ($format) {
            // initialize the JSON visitor
            case 'json':
                // try to load the JMS serializer
                $version = PrettyVersions::getVersion('jms/serializer');

                // query whether or not we're < than 2.0.0
                if (version_compare($version->getPrettyVersion(), '2.0.0', '<')) {
                    // initialize the visitor because we want to set JSON options
                    $visitor = new JsonSerializationVisitor($namingStrategy);
                    $visitor->setOptions(JSON_PRETTY_PRINT);
                } else {
                    // initialize the json visitor factory because we want to set JSON options
                    $visitor = new JsonSerializationVisitorFactory();
                }

                break;

            // initialize the XML visitor
            case 'xml':
                // try to load the JMS serializer
                $version = PrettyVersions::getVersion('jms/serializer');

                // query whether or not we're < than 2.0.0
                if (version_compare($version->getPrettyVersion(), '2.0.0', '<')) {
                    // initialize the visitor because we want to set JSON options
                    $visitor = new XmlSerializationVisitor($namingStrategy);
                } else {
                    // initialize the json visitor factory because we want to set JSON options
                    $visitor = new XmlSerializationVisitorFactory();
                }
                break;

            // throw an execption in all other cases
            default:
                throw new \Exception(sprintf('Found invalid configuration format "%s"', $format));
        }

        // register the visitor in the builder instance
        $builder->setSerializationVisitor($format, $visitor);

        // finally create the serializer instance
        $serializer = $builder->build();

        // try to write the configuration file to the actual working directory
        if (file_put_contents($configurationFilename, $serializer->serialize($configuration, $format))) {
            $output->writeln(sprintf('<info>Successfully written configuration file %s</info>', $configurationFilename));
        } else {
            $output->writeln(sprintf('<error>Can\'t write configuration file %s</error>', $configurationFilename));
        }
    }
}
