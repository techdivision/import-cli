<?php

/**
 * TechDivision\Import\Cli\Command\ImportConvertValueCommand
 *
 * PHP version 7
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2019 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Cli\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use TechDivision\Import\Utils\CommandNames;
use TechDivision\Import\Utils\InputArgumentKeysInterface;
use TechDivision\Import\Serializer\Csv\ValueCsvSerializer;
use TechDivision\Import\Configuration\ConfigurationInterface;

/**
 * The command to simulate converting a file.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2019 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class ImportConvertValueCommand extends AbstractSimpleImportCommand
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
        $this->setName(CommandNames::IMPORT_CONVERT_VALUE)
             ->setDescription('Converts the value to the format expected by the given column')
             ->addArgument(InputArgumentKeysInterface::ENTITY_TYPE_CODE, InputArgument::REQUIRED, 'The entity type code to use, e. g. catalog_product')
             ->addArgument(InputArgumentKeysInterface::COLUMN, InputArgument::REQUIRED, 'The column name to convert the value for')
             ->addArgument(InputArgumentKeysInterface::VALUES, InputArgument::REQUIRED|InputArgument::IS_ARRAY, 'The value to convert');

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

        // initialize the default CSV serializer
        $serializer = new ValueCsvSerializer();
        $serializer->init($configuration);

        // initialize the array for the values that has to be serialized
        $serialize = array();

        // load the values that has to be serialized
        $values = $input->getArgument(InputArgumentKeysInterface::VALUES);

        // simulate custom column handling
        switch ($input->getArgument(InputArgumentKeysInterface::COLUMN)) {
            case 'categories':
                // serialize the categories and use the default delimiter
                $serialize[] = $serializer->serialize($values);
                break;

            case 'path':
                // categories use a slash (/) as delimiter for the first level of serialization
                $delimiter = '/';
                // load the enclosure from the configuration
                $enclosure = $configuration->getEnclosure();
                // iterate over the values and serialize them
                for ($i = 0; $i < sizeof($values); $i++) {
                    // serialize the value and use a slash (/) as delimiter
                    $val = $serializer->serialize(array($values[$i]), $delimiter);

                    // clean-up (means to remove surrounding + double quotes)
                    // because we've no delimiter within the value
                    if (strstr($val, $delimiter) === false) {
                        $val = preg_replace("/^(\'(.*)\'|${enclosure}(.*)${enclosure})$/", '$2$3', $val);
                        $val = str_replace('""', '"', $val);
                    }

                    // append the cleaned value
                    $values[$i] = $val;
                }

                // implode the category's to get the complete path
                $serialize[] = implode($delimiter, $values);
                break;

            default:
                break;
        }

        // second serialization that simulates the framework parsing the CSV file
        $output->write($serializer->serialize($serialize));
    }
}
