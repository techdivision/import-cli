<?php

/**
 * TechDivision\Import\Cli\Plugins\DebugSendPlugin
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

use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use TechDivision\Import\Configuration\MailerConfigurationInterface;
use TechDivision\Import\Utils\RegistryKeys;

/**
 * Plugin that creates and sends a debug report via email.
 *
 * @author    Marcus Döllerer <m.doellerer@techdivision.com>
 * @copyright 2020 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import
 * @link      http://www.techdivision.com
 */
class DebugSendPlugin extends AbstractConsolePlugin
{

    /**
     * Process the plugin functionality.
     *
     * @return void
     * @throws InvalidArgumentException|Exception Is thrown if either the directory nor a artefact for the given serial is available
     */
    public function process()
    {

        // load the actual status
        $status = $this->getRegistryProcessor()->getAttribute(RegistryKeys::STATUS);

        // query whether or not a custom debug serial is available
        $serial = $this->getSerial();
        if (isset($status[RegistryKeys::DEBUG_SERIAL])) {
            $serial = $status[RegistryKeys::DEBUG_SERIAL];
        }

        // retrieve the mailer configuration
        $mailerConfiguration = $this->getPluginConfiguration()->getMailer();

        // retrieve the question helper
        $questionHelper = $this->getHelper('question');

        // use the configured mailer recipient address as default if possible
        if ($mailerConfiguration instanceof MailerConfigurationInterface && $mailerConfiguration->hasParam('to')) {
            $recipient = $mailerConfiguration->getParam('to');
            // ask the user for the recipient address to send the debug report to
            $recipientQuestion = new Question(
                "<question>Please enter the email address of the debug report recipient (Configured: " . $recipient . "):\n</question>",
                $recipient
            );
        } else {
            $recipientQuestion = new Question(
                "<question>Please enter the email address of the debug report recipient:\n</question>"
            );
        }

        // ask the user to confirm the configured recipient address or enter a new one
        $recipient = $questionHelper->ask($this->getInput(), $this->getOutput(), $recipientQuestion);

        // warn the user about the impact of submitting their report and ask for confirmation
        $confirmationQuestion = new ConfirmationQuestion(
            "<comment>The debug report may contain confidential information (depending on the data you were importing).\n</comment>"
            . "<question>Do you really want to send the report to " . $recipient . "? (Y/n)\n</question>"
        );

        // abort the operation if the user does not confirm with 'y' or enter
        if (!$questionHelper->ask($this->getInput(), $this->getOutput(), $confirmationQuestion)) {
            $this->getOutput()->writeln('<comment>Aborting operation - debug report has NOT been sent.</comment>');
            return;
        }

        // try to load the mailer instance
        $mailer = $this->getMailer();

        if ($mailer) {
            // initialize the message body
            $body = sprintf('<html><head></head><body>This mail contains the debug dump for import with serial "%s"</body></html>', $serial);

            // initialize the message template
            $from = $mailerConfiguration->getParam('from');
            $to = (array)$recipient;
            $email = (new Email())->subject('Test')->from($from)->to(... $to)->html($body);
            // query whether or not the archive file is available
            if (!is_file($archiveFile = sprintf('%s/%s.zip', sys_get_temp_dir(), $serial))) {
                $this->getOutput()->writeln(sprintf('<error>Can\'t find either a directory or ZIP artefact for serial "%s"</error>', $serial));
                return;
            }

            // attach the CSV files with zipped artefacts
            $email->attachFromPath($archiveFile);

            try {
                // send the mail
                $mailer->send($email);

                // cast 'to' into an array if not already
                is_array($recipient) ?: $recipient = $to;

                // log a message with the receivers
                $this->getSystemLogger()->info(
                    sprintf(
                        'Mail successfully sent to recipient(s) (%s)',
                        implode(', ', $recipient)
                    )
                );
            } catch (TransportExceptionInterface $e) {
                $this->getSystemLogger()->error(sprintf('Can\'t send mail: %s', $e->getMessage()));
            }
        } else {
            // write a message to the console, that the mailer configuration has not been available
            $this->getOutput()->writeln(
                '<comment>The mailer configuration is not available or mailer can not be loaded</comment>'
            );
        }
    }
}
