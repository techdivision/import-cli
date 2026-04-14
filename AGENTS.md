# AGENTS.md - import-cli

## Zweck & Verantwortung

Das `import-cli` Modul bietet **CLI Commands und Helpers** für das Pacemaker Import-System. Es ist ein **Tier 7 Modul** und dient als Basis für CLI-Anwendungen.

**Hauptverantwortung:**
- Symfony Console Integration
- CLI Commands für Import-Operationen
- Configuration Loading und Management
- Dependency Injection für CLI
- Helper-Klassen für CLI-Operationen

## Architektur & Design Patterns

### Kern-Klassen
- **ConfigurationLoader**: Lädt Konfiguration aus Dateien
- **SimpleConfigurationLoader**: Einfache Konfiguration-Loader
- **ConfigurationLoaderInterface**: Basis-Interface
- **CliCommand**: Basis-Klasse für CLI Commands

### Verwendete Patterns
- **Command Pattern**: Für CLI Commands
- **Factory Pattern**: Für Object-Erstellung
- **Dependency Injection**: Für Dependency Management

### Externe Dependencies
- **symfony/console** - Symfony Console Component
- **symfony/config** - Symfony Config Component
- **symfony/dependency-injection** - Symfony DI
- **symfony/expression-language** - Expression Language
- **ramsey/uuid** - UUID-Generierung

## Abhängigkeiten

### Externe Pakete
- **symfony/console** ^4.0|^5.0|^6.0 - Console Commands
- **symfony/config** ^4.0|^5.0|^6.0 - Configuration
- **symfony/dependency-injection** ^4.0|^5.0|^6.0 - DI Container
- **symfony/expression-language** ^4.0|^5.0|^6.0 - Expression Language
- **ramsey/uuid** ^1.0 - UUID-Generierung

### TechDivision Dependencies
- **import-app-simple** ^19.0 - Simple Application
- **import-configuration-jms** ^18.1 - JMS Configuration

### Abhängig von diesem Modul (1 Reverse Dependency)
- **import-cli-simple** - Master CLI

## Wichtige Entry Points

### Configuration Loader Klassen
```php
// Configuration Loader Interface
ConfigurationLoaderInterface::load($file): ConfigurationInterface

// Simple Configuration Loader
SimpleConfigurationLoader::load($file): ConfigurationInterface

// Configuration Loader
ConfigurationLoader::load($file): ConfigurationInterface
```

### CLI Command Klassen
```php
// CLI Command
CliCommand::execute(InputInterface $input, OutputInterface $output): int
CliCommand::configure(): void
```

## Events & Extension Points

**Keine Events** - Tier 7 CLI-Modul

## Hints für KI-Agenten

### Wichtig zu verstehen
1. **Tier 7 Modul**: CLI Framework für Import-Operationen
2. **Symfony Console**: Nutzt Symfony Console Component
3. **Configuration Loading**: Lädt Konfiguration aus Dateien
4. **Dependency Injection**: Nutzt Symfony DI Container
5. **1 Dependent**: Basis für Master CLI

### Bei Änderungen
- **CLI-Kompatibilität**: Beachte Symfony Console API
- **Configuration-Kompatibilität**: Beachte Konfiguration-Format
- **Backward Compatibility**: Alte CLI-Commands sollten noch funktionieren

## Häufige Use Cases

### CLI-Command-Beispiele
```bash
# Standard Import-Command
php bin/pacemaker import:product --config=config.xml

# Mit Custom Options
php bin/pacemaker import:product --config=config.xml --batch-size=500

# Configuration Loading
php bin/pacemaker import:customer --config=customer-config.xml
```

### Szenarien
1. **CLI-Integration**: Commands nutzen SimpleConfigurationLoader für Config-Laden
2. **Cron-Jobs**: Automatisierte Imports via Cron
3. **Manual Triggers**: Ad-hoc Imports über CLI

## Performance-Überlegungen

- **CLI-Startup**: ~200-300ms für Framework-Init
- **Configuration-Load**: ~50-100ms für Standard Config
- **Total-Overhead**: ~300-400ms vorher der Import startet
- **Configuration-Caching**: Config wird gecacht - schneller bei wiederholten Imports
- **Optimal für**: > 5000 Records (Overhead relativiert sich)

## Verwandte Module

- **import-app-simple**: Application-Layer für CLI
- **import-configuration-jms**: JMS Configuration für CLI
- **import-cli-simple**: Master CLI nutzt dieses Modul
- **import-cli** ← **diese Datei** (CLI Framework!)

## Troubleshooting & FAQ

**Q: "Configuration file not found"**
- A: Config-Pfad prüfen! Relative oder absolute Pfade möglich: `--config=/path/config.xml` oder `--config=config.xml` (relativ zu cwd)

**Q: CLI-Commands werden nicht erkannt**
- A: Composer autoload problem. Führe aus: `composer dump-autoload`

**Q: Symfony Console Command nicht geladen**
- A: Command-Klasse nicht in DI registriert. Prüfe DI-Konfiguration.

**Q: Environment-Variablen funktionieren nicht**
- A: `.env` wird nicht automatisch geladen! Setze Variablen: `export BATCH_SIZE=1000 && php bin/pacemaker import:product`

## Bekannte Einschränkungen

- **Symfony-abhängig**: Erfordert Symfony Components
- **Single-Threaded**: Nur für Single-Threaded Imports
- **Keine Multi-Threaded Support**: Nicht für parallele Imports

## Zusammenfassung

`import-cli` ist ein **Tier 7 Modul**, das CLI Commands und Helpers für das Pacemaker-System bietet. Es ist die Basis für CLI-Anwendungen und nutzt Symfony Console für Command-Management.

**Für Agenten:** Verstehe dieses Modul als **CLI Framework** mit Symfony Console Integration und Configuration Loading.
