<?php

/**
 * TechDivision\Import\Cli\Command\ImportCommandTrait
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

use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TechDivision\Import\Utils\InputOptionKeysInterface;
use TechDivision\Import\Configuration\Jms\Configuration;
use TechDivision\Import\Cli\Utils\DependencyInjectionKeys;

/**
 * The abstract import command implementation.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
abstract class AbstractImportCommand extends Command
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
        $this->addOption(InputOptionKeysInterface::SERIAL, null, InputOption::VALUE_REQUIRED, 'The unique identifier of this import process', Uuid::uuid4()->__toString())
             ->addOption(InputOptionKeysInterface::INSTALLATION_DIR, null, InputOption::VALUE_REQUIRED, 'The Magento installation directory to which the files has to be imported', getcwd())
             ->addOption(InputOptionKeysInterface::SYSTEM_NAME, null, InputOption::VALUE_REQUIRED, 'Specify the system name to use', gethostname())
             ->addOption(InputOptionKeysInterface::PID_FILENAME, null, InputOption::VALUE_REQUIRED, 'The explicit PID filename to use', sprintf('%s/%s', sys_get_temp_dir(), Configuration::PID_FILENAME))
             ->addOption(InputOptionKeysInterface::CACHE_ENABLED, null, InputOption::VALUE_REQUIRED, 'Whether or not the cache functionality for the import should be enabled', false)
             ->addOption(InputOptionKeysInterface::RENDER_VALIDATION_ISSUES, null, InputOption::VALUE_REQUIRED, 'The number of validation issues that has to be rendered on the CLI', 100)
             ->addOption(InputOptionKeysInterface::MAGENTO_EDITION, null, InputOption::VALUE_REQUIRED, 'The Magento edition to be used, either one of "CE" or "EE" (will be autodetected if possible and not specified)')
             ->addOption(InputOptionKeysInterface::MAGENTO_VERSION, null, InputOption::VALUE_REQUIRED, 'The Magento version to be used, e. g. "2.1.2"')
             ->addOption(InputOptionKeysInterface::CONFIGURATION, null, InputOption::VALUE_REQUIRED, 'Specify the pathname to the configuration file to use')
             ->addOption(InputOptionKeysInterface::CUSTOM_CONFIGURATION_DIR, null, InputOption::VALUE_REQUIRED, 'The path to the custom configuration directory')
             ->addOption(InputOptionKeysInterface::SOURCE_DIR, null, InputOption::VALUE_REQUIRED, 'The directory that has to be watched for new files')
             ->addOption(InputOptionKeysInterface::TARGET_DIR, null, InputOption::VALUE_REQUIRED, 'The target directory with the files that has been imported')
             ->addOption(InputOptionKeysInterface::ARCHIVE_DIR, null, InputOption::VALUE_REQUIRED, 'The directory the imported files will be archived in')
             ->addOption(InputOptionKeysInterface::ARCHIVE_ARTEFACTS, null, InputOption::VALUE_REQUIRED, 'Whether or not files should be archived')
             ->addOption(InputOptionKeysInterface::CLEAR_ARTEFACTS, null, InputOption::VALUE_REQUIRED, 'Whether or not artefacts should be cleared')
             ->addOption(InputOptionKeysInterface::USE_DB_ID, null, InputOption::VALUE_REQUIRED, 'The explicit database ID used for the actual import process')
             ->addOption(InputOptionKeysInterface::DB_PDO_DSN, null, InputOption::VALUE_REQUIRED, 'The DSN used to connect to the Magento database where the data has to be imported, e. g. mysql:host=127.0.0.1;dbname=magento;charset=utf8')
             ->addOption(InputOptionKeysInterface::DB_USERNAME, null, InputOption::VALUE_REQUIRED, 'The username used to connect to the Magento database')
             ->addOption(InputOptionKeysInterface::DB_PASSWORD, null, InputOption::VALUE_REQUIRED, 'The password used to connect to the Magento database')
             ->addOption(InputOptionKeysInterface::DB_TABLE_PREFIX, null, InputOption::VALUE_REQUIRED, 'The table prefix used by the Magento database')
             ->addOption(InputOptionKeysInterface::LOG_LEVEL, null, InputOption::VALUE_REQUIRED, 'The log level to use')
             ->addOption(InputOptionKeysInterface::DEBUG_MODE, null, InputOption::VALUE_REQUIRED, 'Whether or not debug mode should be used')
             ->addOption(InputOptionKeysInterface::SINGLE_TRANSACTION, null, InputOption::VALUE_REQUIRED, 'Whether or not the import should be wrapped within a single transaction')
             ->addOption(InputOptionKeysInterface::PARAMS, null, InputOption::VALUE_REQUIRED, 'Additional options passed as a string (MUST have the same format as the used configuration file has)')
             ->addOption(InputOptionKeysInterface::PARAMS_FILE, null, InputOption::VALUE_REQUIRED, 'Additional options passed as pathname')
             ->addOption(InputOptionKeysInterface::MOVE_FILES_PREFIX, null, InputOption::VALUE_REQUIRED, 'Prefix of the files to move (defaults to the prefix of the first of the first plugin subject)')
             ->addOption(InputOptionKeysInterface::EMPTY_ATTRIBUTE_VALUE_CONSTANT, null, InputOption::VALUE_REQUIRED, 'Value to define empty value to remove EAV attributes values in categories, products and customers')
             ->addOption(InputOptionKeysInterface::STRICT_MODE, null, InputOption::VALUE_REQUIRED, 'Whether or not strict mode should be used')
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

        // initialize the configuration instance
        $this->getContainer()->get(DependencyInjectionKeys::CONFIGURATION);

        // execute the appliation instance and return the exit code
        return $this->getContainer()->get(DependencyInjectionKeys::SIMPLE)->process($input->getOption(InputOptionKeysInterface::SERIAL));
    }
}
