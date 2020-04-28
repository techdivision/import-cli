<?php

/**
 * TechDivision\Import\Cli\Command\ImportCreateConfigurationFileCommand
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

use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\XmlSerializationVisitor;
use JMS\Serializer\YamlSerializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use TechDivision\Import\Utils\CommandNames;
use TechDivision\Import\Configuration\ConfigurationInterface;

/**
 * The command implementation that creates a configuration file from one of the templates.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
             ->addOption(InputOptionKeys::DEST, null, InputOption::VALUE_REQUIRED, 'The relative/absolut pathname of the destination file');

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
        $exportDir = $input->hasParameterOption(InputOptionKeys::CUSTOM_CONFIGURATION_DIR) ? $input->getOption(InputOptionKeys::CUSTOM_CONFIGURATION_DIR) : $configuration->getInstallationDir();

        // initialize the configuration filename
        $configurationFilename = $input->getOption(InputOptionKeys::DEST);
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
                // initialize the visitor because we want to set JSON options
                $visitor = new JsonSerializationVisitor($namingStrategy);
                $visitor->setOptions(JSON_PRETTY_PRINT);
                break;

            // initialize the XML visitor
            case 'xml':
                $visitor = new XmlSerializationVisitor($namingStrategy);
                break;

            // initialize the YAML visitor
            case 'yml':
            case 'yaml':
                $visitor = new YamlSerializationVisitor($namingStrategy);
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
