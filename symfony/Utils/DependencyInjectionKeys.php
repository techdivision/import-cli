<?php

/**
 * TechDivision\Import\Cli\Utils\DependencyInjectionKeys
 *
 * PHP version 7
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Cli\Utils;

/**
 * A utility class for the DI service keys.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli
 * @link      http://www.techdivision.com
 */
class DependencyInjectionKeys extends \TechDivision\Import\App\Utils\DependencyInjectionKeys
{

    /**
     * The key for the input instance.
     *
     * @var string
     */
    const INPUT = 'input';

    /**
     * The key for the output instance.
     *
     * @var string
     */
    const OUTPUT = 'output';

    /**
     * The key for the connection.
     *
     * @var string
     */
    const CONNECTION = 'connection';

    /**
     * The key for the loggers.
     *
     * @var string
     */
    const LOGGERS = 'loggers';

    /**
     * The key for the simple configuration instance.
     *
     * @var string
     */
    const CONFIGURATION_SIMPLE = 'configuration.simple';

    /**
     * The key for the configuration factory instance.
     *
     * @var string
     */
    const CONFIGURATION_FACTORY = 'import_cli.configuration.factory';

    /**
     * The key for the base directory.
     *
     * @var string
     */
    const CONFIGURATION_BASE_DIR = 'import_cli.configuration.base.dir';

    /**
     * The key for the library loader.
     *
     * @var string
     */
    const LIBRARY_LOADER = 'import_cli.library.loader';

    /**
     * The key for the application name.
     *
     * @var string
     */
    const APPLICATION_NAME = 'application.name';

    /**
     * The key for the application version file.
     *
     * @var string
     */
    const APPLICATION_VERSION_FILE = 'application.version.file';

    /**
     * The key for the application Magento 2 Edition mappings.
     *
     * @var string
     */
    const APPLICATION_EDITION_MAPPINGS = 'application.edition.mappings';

    /**
     * The key for the array with the default application libraries.
     *
     * @var string
     */
    const APPLICATION_DEFAULT_LIBRARIES = 'application.default.libraries';

    /**
     * The key for the DI parameter that contains the default configuration directory.
     *
     * @var string
     */
    const APPLICATION_DEFAULT_CONFIGURATION_DIR = 'application.default.configuration.dir';

    /**
     * The key for the DI parameter that contains the custom configuration directory.
     *
     * @var string
     */
    const APPLICATION_CUSTOM_CONFIGURATION_DIR = 'application.custom.configuration.dir';
}
