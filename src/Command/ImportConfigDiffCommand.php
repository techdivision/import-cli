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

use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Visitor\Factory\JsonSerializationVisitorFactory;
use JMS\Serializer\XmlSerializationVisitor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TechDivision\Import\Configuration\ConfigurationInterface;
use TechDivision\Import\Utils\CommandNames;
use TechDivision\Import\Configuration\Jms\Configuration;
use TechDivision\Import\Utils\InputOptionKeysInterface;

/**
 * The import command implementation.
 *
 * @author
 * @copyright 2023 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class ImportConfigDiffCommand extends AbstractSimpleImportCommand
{
    /**
     * project config Values
     *
     * @var array
     */
    private $paths = [];

    /**
     * default config Values
     *
     * @var array
     */
    private $defaultPaths = [];

    /**
     * file where default values are saved
     *
     * @var string
     */
    const DEFAULT_FILE = __DIR__ . '/../../config.json.default';

    /**
     * Configures the current command.
     *
     * @return void
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        // initialize the command with the required/optional options
        $this->setName(CommandNames::IMPORT_CONFIG_DIFF)
            ->setDescription('Shows Diffs between default configuration values and project config');

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

        // create serializer
        $namingStrategy = new SerializedNameAnnotationStrategy(new IdenticalPropertyNamingStrategy());
        $visitor = new JsonSerializationVisitorFactory($namingStrategy);
        $visitor->setOptions(JSON_PRETTY_PRINT);
        $builder->setSerializationVisitor($format, $visitor);
        $serializer = $builder->build();

        // write default values to output
        $projectValues = $serializer->serialize($configuration, $format);
        $defaultValues = file_get_contents(self::DEFAULT_FILE);

        if ($defaultValues !== $projectValues) {
            $projectJsonValues = json_decode($projectValues);
            $defaultValuesJson = json_decode($defaultValues);
            $this->callGetPath($projectJsonValues, '', true);
            $this->callGetPath($defaultValuesJson, '', false);
            $this->showDiffs($output);
            return 0;
        }

        $output->writeln('[*] no changes found');
        return 0;
    }

    /**
     * @return void
     */
    private function showDiffs(OutputInterface $output) {
        $output->writeln('Original | Override');
        foreach ($this->paths as $key => $value) {
            if ($this->defaultPaths[$key] !== $value) {
                $output->writeln($key . ':' .
                    '"'.$this->defaultPaths[$key].'"' . '|' . $key . ':'. '"'.$value.'"');
            }
        }
    }

    /**
     * @param $value
     * @param $path
     * @param $projectValues
     * @return void
     */
    private function callGetPath($value, $path, $projectValues) {
        foreach ($value as $key => $nvalue) {
            $this->getPath($key, $nvalue, $path, $projectValues);
        }
    }

    /**
     * @param $key
     * @param $value
     * @param $path
     * @param $paths
     * @return void
     */
    private function getPath($key, $value, $path, $projectValues) {
        $path = $path . '/' . $key;
        if (is_object($value) || is_array($value)) {
            $this->callGetPath($value, $path, $projectValues);
        }

        if (is_string($value)) {
            if ($projectValues) {
                $this->paths[$path] = $value;
            } else {
                $this->defaultPaths[$path] = $value;
            }
        }
    }
}
