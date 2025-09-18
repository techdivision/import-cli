<?php

/**
 * TechDivision\Import\Cli\Plugins\DebugCreatePlugin
 *
 * PHP version 7
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2020 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Cli\Plugins;

use TechDivision\Import\Utils\RegistryKeys;
use TechDivision\Import\ApplicationInterface;
use TechDivision\Import\Utils\DebugUtilInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use TechDivision\Import\Utils\InputOptionKeysInterface;

/**
 * Plugin that creates and sends a debug report via email.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2020 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import
 * @link      http://www.techdivision.com
 */
class DebugCreatePlugin extends AbstractConsolePlugin
{

    /**
     * The debug util instance.
     *
     * @var \TechDivision\Import\Utils\DebugUtilInterface
     */
    private $debugUtil;

    /**
     * The constructor to initialize the plugin with.
     *
     * @param \TechDivision\Import\ApplicationInterface     $application The application instance
     * @param \TechDivision\Import\Utils\DebugUtilInterface $debugUtil   The debug util instance
     */
    public function __construct(ApplicationInterface $application, DebugUtilInterface $debugUtil)
    {

        // set the debug utility
        $this->debugUtil = $debugUtil;

        // pass the application to the parent constructor
        parent::__construct($application);
    }


    /**
     * Return's the debug util instance.
     *
     * @return \TechDivision\Import\Utils\DebugUtilInterface The debug util instance
     */
    private function getDebugUtil() : DebugUtilInterface
    {
        return $this->debugUtil;
    }

    /**
     * Process the plugin functionality.
     *
     * @return void
     * @throws \InvalidArgumentException Is thrown if either the directory nor a artefact for the given serial is available
     */
    public function process()
    {
        if ($this->getConfiguration()->isConfigOutput()) {
            $configurationFiles = $this->getConfigurationFiles();
            $this->getSystemLogger()->info(
                print_r($configurationFiles, true)
            );
            return;
        }

        // load the actual status
        $status = $this->getRegistryProcessor()->getAttribute(RegistryKeys::STATUS);

        // query whether or not the configured source directory is available
        if (isset($status[RegistryKeys::SOURCE_DIRECTORY])) {
            $sourceDir = $status[RegistryKeys::SOURCE_DIRECTORY];
        } else {
            throw new \Exception('Source directory is not available!');
        }

        // try to load the archive directory
        $archiveDir = $this->getConfiguration()->getArchiveDir();

        // try to initialize a default archive directory by concatenating 'archive' to the target directory
        if ($archiveDir === null) {
            $archiveDir = sprintf('var/import_history');
        }

        // retrieve the question helper
        $questionHelper = $this->getHelper('question');

        // initialize the array for the available serials
        $availableSerials = array();

        // load the directories from the actual working directory (broken imports, etc.)
        foreach (glob(sprintf('%s/*', $sourceDir), GLOB_ONLYDIR) as $possibleImportDir) {
            $availableSerials[basename($possibleImportDir)] = filemtime($possibleImportDir);
        }

        // load the ZIP artefacts from the archive directory
        foreach (glob(sprintf('%s/*.zip', $archiveDir)) as $possibleArtefact) {
            $availableSerials[ basename($possibleArtefact, '.zip')] = filemtime($possibleArtefact);
        }

        // sort the available serials by modification time
        uasort($availableSerials, function ($a, $b) {
            // return zero, if the passed values are equal
            if ($a == $b) {
                return 0;
            }
            // otherwise return -1 or 1
            return ($a > $b) ? -1 : 1;
        });

        // finally create the array with the available serials to render on the console
        $availableSerials = array_slice(array_keys($availableSerials), 0, $this->getInput()->getOption(InputOptionKeysInterface::RENDER_DEBUG_SERIALS));

        // if no serials are available, abort gracefully instead of asking an empty \Symfony\Component\Console\Question\ChoiceQuestion
        if (empty($availableSerials)) {
            $this->getOutput()->writeln(
                '<info>No debug artefacts or import directories found to create a debug dump for. Aborting.</info>'
            );
            return;
        }

        // this is, when the import:debug send command has been invoked
        // WITHOUT the --serial=<UUID> parameter or an invalid serial
        if (!in_array($serial = $this->getSerial(), $availableSerials, true)) {
            // create the question instance to choose the serial
            $chooseSerialQuestion = new ChoiceQuestion('Please select the serial to create the debug artefact for', $availableSerials);
            $chooseSerialQuestion->setErrorMessage('Selected serial "%s" is invalid.');

            // abort the operation if the user does not confirm with 'y' or enter
            if (!$serial = $questionHelper->ask($this->getInput(), $this->getOutput(), $chooseSerialQuestion)) {
                $this->getOutput()->writeln('<info>Aborting operation - debug report has NOT been sent.</info>');
                return;
            }
        }

        // update the registry with the (probably new) serial and the source directory
        $this->getRegistryProcessor()->mergeAttributesRecursive(
            RegistryKeys::STATUS,
            array(
                RegistryKeys::DEBUG_SERIAL     => $serial,
                RegistryKeys::SOURCE_DIRECTORY => sprintf('%s/%s', $sourceDir, $serial)
            )
        );

        // prepare the debug dump
        $this->getDebugUtil()->extractArchive($serial);
        $this->getDebugUtil()->prepareDump($serial);
        $dumpFilename = $this->getDebugUtil()->createDump($serial);

        // write a success message to the console
        $this->getOutput()->writeln(sprintf('<info>Successfully created debug dump "%s"</info>', $dumpFilename));
    }

    /**
     * @return array
     */
    public function getConfigurationFiles()
    {
        return $this->getConfiguration()->getConfigurationFiles();
    }
}
