<?php

/**
 * TechDivision\Import\Cli\Utils\MagentoConfigurationKeys
 *
 * PHP version 7
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Cli\Utils;

/**
 * Utility class containing the necessary Magento configuration keys.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-cli-simple
 * @link      http://www.techdivision.com
 */
class MagentoConfigurationKeys
{

    /**
     * This is a utility class, so protect it against direct
     * instantiation.
     */
    private function __construct()
    {
    }

    /**
     * This is a utility class, so protect it against cloning.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * The key for the DB environment in the app/etc/env.php file.
     *
     * @var string
     */
    const DB = 'db';

    /**
     * The key for the DB connection in the app/etc/env.php file.
     *
     * @var string
     */
    const CONNECTION = 'connection';

    /**
     * The key for the DB host in the app/etc/env.php file.
     *
     * @var string
     */
    const HOST = 'host';

    /**
     * The key for the DB port in the app/etc/env.php file.
     *
     * @var string
     */
    const PORT = 'port';

    /**
     * The key for the DB name in the app/etc/env.php file.
     *
     * @var string
     */
    const DBNAME = 'dbname';

    /**
     * The key for the DB username in the app/etc/env.php file.
     *
     * @var string
     */
    const USERNAME = 'username';

    /**
     * The key for the DB password in the app/etc/env.php file.
     *
     * @var string
     */
    const PASSWORD = 'password';

    /**
     * The attribute with packages in the composer.json file.
     *
     * @var string
     */
    const COMPOSER_PACKAGES = 'packages';

    /**
     * The attribute with the Magento Edition name in the composer.json file.
     *
     * @var string
     */
    const COMPOSER_EDITION_NAME_ATTRIBUTE = 'name';

    /**
     * The attribute with the Magento Edition version in the composer.json file.
     *
     * @var string
     */
    const COMPOSER_EDITION_VERSION_ATTRIBUTE = 'version';

    /**
     * The key for the DB table prefix in the app/etc/env.php file.
     *
     * @var string
     */
    const TABLE_PREFIX = 'table_prefix';
}
