<?php

/**
 * TechDivision\Import\Cli\Plugins\AbstractConsolePlugin
 *
 * PHP version 7
 *
 * @author    Marcus Döllerer <m.doellerer@techdivision.com>
 * @copyright 2020 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Cli\Plugins;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TechDivision\Import\ApplicationInterface;
use TechDivision\Import\Plugins\AbstractPlugin;

/**
 * Abstract console plugin implementation containing access to console commands and helpers.
 *
 * @author    Marcus Döllerer <m.doellerer@techdivision.com>
 * @copyright 2020 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli
 * @link      http://www.techdivision.com
 */
abstract class AbstractConsolePlugin extends AbstractPlugin
{

    /**
     * The M2IF console application instance.
     *
     * @var \Symfony\Component\Console\Application
     */
    protected $cliApplication;

    /**
     * The console input instance.
     *
     * @var InputInterface
     */
    protected $input;

    /**
     * The console output instance.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * The helper set.
     *
     * @var HelperSet
     */
    protected $helperSet;

    /**
     * The constructor to initialize the plugin with.
     *
     * @param \TechDivision\Import\ApplicationInterface $application The application instance
     *
     * @throws \Exception
     */
    public function __construct(ApplicationInterface $application)
    {

        // inject the cli application
        $cliApplication = $application->getContainer()->get('application');
        if (!$cliApplication instanceof Application) {
            throw new \Exception('No console application configured, please check your configuration.');
        }
        $this->setCliApplication($cliApplication);

        // set the console input
        $input = $application->getContainer()->get('input');
        if (!$input instanceof InputInterface) {
            throw new \Exception('No console input configured, please check your configuration.');
        }
        $this->setInput($input);

        // set the console output
        $output = $application->getContainer()->get('output');
        if (!$output instanceof OutputInterface) {
            throw new \Exception('No console output configured, please check your configuration.');
        }
        $this->setOutput($output);

        // inject the helper set
        $helperSet = $this->getCliApplication()->getHelperSet();
        if (!$helperSet instanceof HelperSet) {
            throw new LogicException('No HelperSet is defined.');
        }
        $this->setHelperSet($helperSet);

        parent::__construct($application);
    }

    /**
     * Return's the console application instance.
     *
     * @return \Symfony\Component\Console\Application The console application instance
     */
    public function getCliApplication()
    {
        return $this->cliApplication;
    }

    /**
     * Set's the console application instance.
     *
     * @param \Symfony\Component\Console\Application $cliApplication The console application instance
     *
     * @return void
     */
    public function setCliApplication(Application $cliApplication)
    {
        $this->cliApplication = $cliApplication;
    }

    /**
     * Return's the console input instance.
     *
     * @return \Symfony\Component\Console\Input\InputInterface The console input instance
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Set's the console input instance.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input The console input instance
     *
     * @return void
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * Return's the console output instance.
     *
     * @return \Symfony\Component\Console\Output\OutputInterface The console output instance
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Set's the console output instance.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output The console output instance
     *
     * @return void
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Return's the helper set instance.
     *
     * @return \Symfony\Component\Console\Helper\HelperSet The helper set instance
     */
    public function getHelperSet()
    {
        return $this->helperSet;
    }

    /**
     * Set's the helper set instance.
     *
     * @param \Symfony\Component\Console\Helper\HelperSet $helperSet The helper set instance
     *
     * @return void
     */
    public function setHelperSet(HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;
    }

    /**
     * Retrieve a helper by name.
     *
     * @param string $name The name of the helper to retrieve
     *
     * @return \Symfony\Component\Console\Helper\HelperInterface The helper instance
     */
    public function getHelper($name)
    {
        return $this->getHelperSet()->get($name);
    }
}
