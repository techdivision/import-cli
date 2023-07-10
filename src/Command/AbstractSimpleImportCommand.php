<?php

/**
 * TechDivision\Import\Cli\Command\AbstractSimpleImportCommand
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

use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Visitor\Factory\JsonSerializationVisitorFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TechDivision\Import\Configuration\ConfigurationInterface;
use TechDivision\Import\Configuration\Jms\Configuration;
use TechDivision\Import\Cli\Utils\DependencyInjectionKeys;
use TechDivision\Import\Utils\InputOptionKeysInterface;

/**
 * Abstract command implementation for simple import commands (not using Importer class).
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
abstract class AbstractSimpleImportCommand extends Command
{

    /**
     * Configures the current command.
     *
     * @return void
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {

        // configure the command
        $this->addOption(InputOptionKeysInterface::PID_FILENAME, null, InputOption::VALUE_REQUIRED, 'The explicit PID filename to use', sprintf('%s/%s', sys_get_temp_dir(), Configuration::PID_FILENAME))
             ->addOption(InputOptionKeysInterface::INSTALLATION_DIR, null, InputOption::VALUE_REQUIRED, 'The Magento installation directory to which the files has to be imported', getcwd())
             ->addOption(InputOptionKeysInterface::CONFIGURATION_DIR, null, InputOption::VALUE_OPTIONAL, 'The Magento configuration directory')
             ->addOption(InputOptionKeysInterface::SYSTEM_NAME, null, InputOption::VALUE_REQUIRED, 'Specify the system name to use', gethostname())
             ->addOption(InputOptionKeysInterface::SOURCE_DIR, null, InputOption::VALUE_REQUIRED, 'The directory that has to be watched for new files')
             ->addOption(InputOptionKeysInterface::MAGENTO_VERSION, null, InputOption::VALUE_REQUIRED, 'The Magento version to be used, e. g. "2.1.2"')
             ->addOption(InputOptionKeysInterface::MAGENTO_EDITION, null, InputOption::VALUE_REQUIRED, 'The Magento edition to be used, either one of "CE" or "EE"', 'CE')
             ->addOption(InputOptionKeysInterface::CONFIGURATION, null, InputOption::VALUE_REQUIRED, 'Specify the pathname to the configuration file to use')
             ->addOption(InputOptionKeysInterface::CUSTOM_CONFIGURATION_DIR, null, InputOption::VALUE_REQUIRED, 'The path to the custom configuration directory')
             ->addOption(InputOptionKeysInterface::LOG_LEVEL, null, InputOption::VALUE_REQUIRED, 'The log level to use')
             ->addOption(InputOptionKeysInterface::LOG_FILE, null, InputOption::VALUE_REQUIRED, 'The log file to use');
    }

    /**
     * Return's the container instance.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface The container instance
     */
    protected function getContainer()
    {
        return $this->getApplication()->getContainer();
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

        // try to load the configuration file
        $configuration = $this->getContainer()->get(DependencyInjectionKeys::CONFIGURATION_SIMPLE);

        // finally execute the simple command
        return $this->executeSimpleCommand($configuration, $input, $output);
    }

    /**
     * create json serializer for configs
     *
     * @return Serializer
     */
    protected function createSerializer(): Serializer
    {
        $format = 'json';
        $builder = SerializerBuilder::create();
        $builder->addDefaultSerializationVisitors();
        $namingStrategy = new SerializedNameAnnotationStrategy(new IdenticalPropertyNamingStrategy());

        // register the visitor in the builder instance
        $visitor = new JsonSerializationVisitorFactory($namingStrategy);
        $visitor->setOptions(JSON_PRETTY_PRINT);
        $builder->setSerializationVisitor($format, $visitor);
        return $builder->build();
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
    abstract protected function executeSimpleCommand(
        ConfigurationInterface $configuration,
        InputInterface $input,
        OutputInterface $output
    );
}
