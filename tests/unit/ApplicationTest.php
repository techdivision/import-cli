<?php

/**
 * TechDivision\Import\Cli\ApplicationTest
 *
 * PHP version 7
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Cli;

use PHPUnit\Framework\TestCase;
use TechDivision\Import\Cli\Utils\DependencyInjectionKeys;

/**
 * Test class for the symfony application implementation.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class ApplicationTest extends TestCase
{

    /**
     * The application that has to be tested.
     *
     * @var \TechDivision\Import\Cli\Application
     */
    protected $application;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {

        // mock the container instance
        $mockContainer = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
                              ->setMethods(get_class_methods('Symfony\Component\DependencyInjection\ContainerInterface'))
                              ->getMock();

        // mock the methods
        $mockContainer->expects($this->any())
                      ->method('getParameter')
                      ->withConsecutive(
                          array(DependencyInjectionKeys::CONFIGURATION_BASE_DIR),
                          array(DependencyInjectionKeys::APPLICATION_VERSION_FILE),
                          array(DependencyInjectionKeys::APPLICATION_NAME)
                      )
                      ->willReturnOnConsecutiveCalls(
                          __DIR__ . DIRECTORY_SEPARATOR . '_files',
                          '.semver',
                          'Test Tool'
                      );

        // create an instance of the application
        $this->application = new Application($mockContainer);
    }

    /**
     * Test the getContainer() method.
     *
     * @return void
     */
    public function testGetContainer()
    {
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerInterface', $this->application->getContainer());
    }
}
