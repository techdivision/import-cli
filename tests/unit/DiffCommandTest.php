<?php
/**
 *
 * @author    met@techdivision.com
 * @copyright 2023 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Cli;

use PHPUnit\Framework\TestCase;
use TechDivision\Import\Cli\Command\ImportConfigDiffCommand;

/**
 *
 * @author    met@techdivision.com
 * @copyright 2023 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class DiffCommandTest extends TestCase
{

    /** @var ImportConfigDiffCommand */
    private ImportConfigDiffCommand $importConfigDiffCommand;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->importConfigDiffCommand = new ImportConfigDiffCommand();
    }

    /**
     * @return void
     */
    public function testPathCreation(): void
    {
        $expected = include __DIR__ . '/_files/results/diff-result.php';
        $operationsFile = __DIR__ . '/_files/test-operations.json';
        $values = file_get_contents($operationsFile);
        $jsonValues = json_decode($values);
        $result = $this->importConfigDiffCommand->getDataAsFlatArray($jsonValues);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     */
    public function testKeyAdded(): void
    {
        $operationsFile = __DIR__ . '/_files/test-operations.json';
        $operationsFileAdded = __DIR__ . '/_files/test-project-operations-add.json';
        $expected = include __DIR__ . '/_files/results/added-keys-result.php';
        $result = $this->getDiffResult($operationsFile, $operationsFileAdded);
        $this->assertEquals($expected, $result[ImportConfigDiffCommand::ADD_KEY]);
    }

    /**
     * @return void
     */
    public function testKeyDeleted(): void
    {
        $operationsFile = __DIR__ . '/_files/test-operations.json';
        $operationsFileDelete = __DIR__ . '/_files/test-project-operations-delete.json';
        $expected = include __DIR__ . '/_files/results/deleted-key-result.php';
        $result = $this->getDiffResult($operationsFile, $operationsFileDelete);
        $this->assertEquals($expected, $result[ImportConfigDiffCommand::DELETE_KEY]);
    }

    /**
     * @return void
     */
    public function testKeyChanged(): void
    {
        $operationsFile = __DIR__ . '/_files/test-operations.json';
        $operationsFileChanged = __DIR__ . '/_files/test-project-operations-changed.json';
        $expected = include __DIR__ . '/_files/results/changed-key-result.php';
        $result = $this->getDiffResult($operationsFile, $operationsFileChanged);
        $this->assertEquals($expected, $result[ImportConfigDiffCommand::CHANGED_KEY]);
    }

    /**
     * @param string $defaultFile
     * @param string $projectFile
     * @return array
     */
    private function getDiffResult(string $defaultFile, string $projectFile): array
    {
        $values = file_get_contents($defaultFile);
        $projectValues = file_get_contents($projectFile);
        $jsonValues = json_decode($values);
        $projectJsonValues = json_decode($projectValues);
        $default = $this->importConfigDiffCommand->getDataAsFlatArray($jsonValues);
        $project = $this->importConfigDiffCommand->getDataAsFlatArray($projectJsonValues);
        return $this->importConfigDiffCommand->getAllDiffs($default, $project);
    }
}
