# Version 12.0.0

## Bugfixes

* None

## Features

* Add #PAC-89: Add debug email command + DebugSendPlugin
* Add functionality to also parse JSON configuration in extension libraries
* Add #PAC-57: "--empty-attribute-value-constant" for deleting dedicated attribute values via import by setting a configurable value.

# Version 11.0.1

## Bugfixes

* Fixed invalid Magento edition/version mapping

## Features

* None

# Version 11.0.0

## Bugfixes

* Fixed techdivision/import-cli-simple#250

## Features

* None

# Version 10.0.3

## Bugfixes

* Fixed #PAC-151: Directive `additional-vendor-dirs` not applied

## Features

* None

# Version 10.0.2

## Bugfixes

* None

## Features

* Update console application name

# Version 10.0.1

## Bugfixes

* None

## Features

* Make composer dependencies more generic

# Version 10.0.0

## Bugfixes

* Fixed techdivision/import-cli#2
* Fixed techdivision/import-cli-simple#242

## Features

* Add #PAC-85
* Add techdivision/import-cli-simple#244
* Add functionality to also parse configuration files that'll be available in configured additional vendor directory
* Update to versions of techdivision/import 16.*, techdivision/import-configuration-jms 16.* and techdivision/import-app-simple 16.*

# Version 9.0.4

## Bugfixes

* None

## Features

* Add extracted DI configuration import.configuration.manager from techdivision/import to make it overwritable

# Version 9.0.3

## Bugfixes

* None

## Features

* Extract dev autoloading
* Add command to import URL rewrites

# Version 9.0.2

## Bugfixes

* None

## Features

* Remove mandatory autoload.php from additional vendor directory

# Version 9.0.1

## Bugfixes

* None

## Features

* Switch to latest techdivision/import-configuration-jms version

# Version 9.0.0

## Bugfixes

* Also use DB port configuration from Magento env.php if given

## Features

* None

# Version 8.0.0

## Bugfixes

* Fixed techdivision/import-cli-simple#224

## Features

* Add converter libraries to default libraries
* Add techdivision/import#162
* Add techdivision/import#163
* Add techdivision/import-cli-simple#216
* Add techdivision/import-configuration-jms#25
* Update to versions of techdivision/import 15.*, techdivision/import-configuration-jms 15.* and techdivision/import-app-simple 16.*

# Version 7.0.6

## Bugfixes

* None

## Features

* Make composer dependencies compatible with Magento 2.2.x

# Version 7.0.3

## Bugfixes

* Fixed invalid composer dependencies

## Features

* None

# Version 7.0.2

## Bugfixes

* None

## Features

* Fixed issue with InvalidArgumentException - The "magento-version" option does not exist - when invoking simple commands like import:create:ok-file

# Version 7.0.1

## Bugfixes

* None

## Features

* Make error message for missing library directory more speakable

# Version 7.0.0

## Bugfixes

* Fixed techdivision/import-cli#1

## Features

* None

# Version 6.0.0

## Bugfixes

* None

## Features

* Update to versions of techdivision/import 14.*, techdivision/import-configuration-jms 14.* and techdivision/import-app-simple 15.*

# Version 5.0.0

## Bugfixes

* None

## Features

* Update to versions of techdivision/import 13.*, techdivision/import-configuration-jms 13.* and techdivision/import-app-simple 14.*

# Version 4.0.2

## Bugfixes

* Fixed invalid comparision for Magento Edition + Version in SimpleConfigurationLoader

## Features

* None

# Version 4.0.1

## Bugfixes

* Add check if version specific configuration file really exists or not to

## Features

* None

# Version 4.0.0

## Bugfixes

* None

## Features

* Add functionality for version specific configuration and Symfony DI configuration files

# Version 3.0.1

## Bugfixes

* Use DependencyInjectionKeys::CONFIGURATION_BASE_DIR to load path for .semver file

## Features

* None

# Version 3.0.0

## Bugfixes

* None

## Features

* Add new DI configuration keys
* Make default configuration file and default configuration file mappings configurable via Symfony DI

# Version 2.0.0

## Bugfixes

* None

## Features

* Replace member variable with default library names with value from DI configuration

# Version 1.1.1

## Bugfixes

* Bugfix invalid DI configuration names

## Features

* None

# Version 1.1.0

## Bugfixes

* None

## Features

* Add constant DependencyInjectionKeys::CONFIGURATION_BASE_DIR

# Version 1.0.2

## Bugfixes

* None

## Features

* Add PHPUnit test for application implementation

# Version 1.0.1

## Bugfixes

* None

## Features

* Make application more generic

# Version 1.0.0

## Bugfixes

* None

## Features

* Initial Release