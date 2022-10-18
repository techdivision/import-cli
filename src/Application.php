<?php

/**
 * TechDivision\Import\Cli\Application
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

use TechDivision\Import\Cli\Utils\DependencyInjectionKeys;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * The M2IF - Console Tool implementation.
 *
 * This is a example console tool implementation that should give developers an impression
 * on how the M2IF could be used to implement their own Magento 2 importer.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class Application extends \Symfony\Component\Console\Application implements ContainerAwareInterface
{

    /**
     * Regex to read the actual version number from the .semver file.
     *
     * @var string
     */
    const REGEX = "/^\-\-\-\n:major:\s(0|[1-9]\d*)\n:minor:\s(0|[1-9]\d*)\n:patch:\s(0|[1-9]\d*)\n:special:\s'([a-zA-z0-9]*\.?(?:0|[1-9]\d*)?)'\n:metadata:\s'((?:0|[1-9]\d*)?(?:\.[a-zA-z0-9\.]*)?)'/";

    /**
     * The DI container instance.
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * The constructor to initialize the instance.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container The DI container instance
     */
    public function __construct(ContainerInterface $container)
    {

        // set the DI container instance
        $this->setContainer($container);

        // initialize the variables for the elements of the version string
        $major = 1;
        $minor = 0;
        $patch = 0;
        $special = null;
        $metadata = null;

        // load the base directory (necessary to locate the base .semver file)
        $baseDir = $container->getParameter(DependencyInjectionKeys::CONFIGURATION_BASE_DIR);

        // parse the file with the semantic versioning data
        extract($this->parse($baseDir . DIRECTORY_SEPARATOR . $container->getParameter(DependencyInjectionKeys::APPLICATION_VERSION_FILE)));

        // invoke the parent constructor
        parent::__construct(
            $container->getParameter(DependencyInjectionKeys::APPLICATION_NAME),
            sprintf('%d.%d.%d', $major, $minor, $patch) . ($special ? sprintf('-%s%s', $special, $metadata) : null)
        );
    }

    /**
     * Sets the DI container instance.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface|null $container The DI container instance
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Return's the DI container instance.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface|null The DI container instance
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Parse and return the version number from the application's .semver file.
     *
     * @param string $semverFile The path to the semver file
     *
     * @return array The array with the version information
     * @throws \Exception Is thrown, if the .semver file is not available or invalid
     */
    protected function parse($semverFile)
    {

        // load the content of the semver file
        $output = file_get_contents($semverFile);

        // initialize the array with the matches
        $matches = array();

        // extract the version information
        if (!preg_match_all(self::REGEX, $output, $matches)) {
            throw new \Exception('Bad semver file.');
        }

        // prepare and return the version number
        list(, $major, $minor, $patch, $special, $metadata) = array_map('current', $matches);
        return compact('major', 'minor', 'patch', 'special', 'metadata');
    }
}
