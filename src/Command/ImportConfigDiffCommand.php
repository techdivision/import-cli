<?php
/**
 * TechDivision\Import\Cli\Command\ImportConfigDiffCommand
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

/**
 * The import command implementation.
 *
 * @author    met@techdivision.com
 * @copyright 2023 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class ImportConfigDiffCommand extends AbstractSimpleImportCommand
{
    /**
     * file where default values are saved
     *
     * @var string
     */
    public const DEFAULT_FILE = __DIR__ . '/../../config.json.default';

    /** @var string */
    public const ADD_KEY = 'added';

    /** @var string */
    public const DELETE_KEY = 'deleted';

    /** @var string */
    public const CHANGED_KEY = 'changed';

    /**
     * Configures the current command.
     *
     * @return void
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure(): void
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
        $projectValues = $serializer->serialize($configuration, 'json');
        $defaultValues = file_get_contents(self::DEFAULT_FILE);

        // compare project and default Values
        if ($defaultValues !== $projectValues) {
            $projectJsonValues = json_decode($projectValues);
            $defaultValuesJson = json_decode($defaultValues);
            $paths = $this->getDataAsFlatArray($projectJsonValues);
            $defaultPaths = $this->getDataAsFlatArray($defaultValuesJson);
            $diff = $this->getAllDiffs($defaultPaths, $paths);
            $this->writeDiffs($defaultPaths, $paths, $diff, $output);
            return 0;
        }

        $output->writeln('[*] no changes found');
        return 0;
    }

    /**
     * @param array $defaultPaths Default values as flat array
     * @param array $paths        Project values as flat array
     * @return array
     */
    public function getAllDiffs(array $defaultPaths, array $paths): array
    {
        $addedKeys = $this->getAddedKeys($defaultPaths, $paths);
        $manipulatedKeys = array_diff_assoc($defaultPaths, $paths);
        $deletedKeys = $this->getDeletedKeys($defaultPaths, $paths);
        return [
            self::ADD_KEY => $addedKeys,
            self::DELETE_KEY => $deletedKeys,
            self::CHANGED_KEY => $manipulatedKeys,
        ];
    }

    /**
     * @param array           $defaultConfig Default values as flat array
     * @param array           $projectConfig Project values as flat array
     * @param array           $diff          Array with all diffs
     * @param OutputInterface $output        OutputInterface instance
     * @return void
     */
    private function writeDiffs(
        array $defaultConfig,
        array $projectConfig,
        array $diff,
        OutputInterface $output
    ): void {
        $output->writeln('Original | Override');
        foreach ($diff[self::DELETE_KEY] as $deletedKey) {
            $output->writeln($deletedKey . ':' .
                '"' . $defaultConfig[$deletedKey] . '"' . ' | --- key was deleted ---');
        }
        foreach ($diff[self::ADD_KEY] as $addedKey) {
            $output->writeln('--- key was added --- | ' . $addedKey . ':' .
                '"' . $projectConfig[$addedKey] . '"');
        }
        foreach ($diff[self::CHANGED_KEY] as $key => $value) {
            $output->writeln($key. ':' .
                '"'.$defaultConfig[$key].'"' . ' | ' . $key . ':'. '"'.$projectConfig[$key].'"');
        }
    }

    /**
     * @param array $default Default values as flat array
     * @param array $project Project values as flat array
     * @return array
     */
    private function getDeletedKeys(array $default, array $project): array
    {
        $deletedKeys = [];
        foreach ($default as $key => $value) {
            if (!array_key_exists($key, $project)) {
                $deletedKeys[] = $key;
            }
        }
        return $deletedKeys;
    }

    /**
     * @param array $default Default values as flat array
     * @param array $project Project values as flat array
     * @return array
     */
    private function getAddedKeys(array $default, array $project): array
    {
        $addedKeys = [];
        foreach ($project as $key => $value) {
            if (!array_key_exists($key, $default)) {
                $addedKeys[] = $key;
            }
        }
        return $addedKeys;
    }

    /**
     * @param mixd   $data Data to be converted to flat array
     * @param string $key  Key of the data
     * @param string $path Path of the data
     * @param array  $arr  Array to be filled with data
     * @return array
     */
    public function getDataAsFlatArray($data, string $key = '', string $path = '', array $arr = []): array
    {
        if ($path != '') {
            $path = $path . '/';
        }
        $path = $path . $key;
        if (is_object($data) || is_array($data)) {
            foreach ($data as $key => $value) {
                $arr = $this->getDataAsFlatArray($value, $key, $path, $arr);
            }
        } else {
            $arr[$path] = $data;
        }
        return $arr;
    }
}
