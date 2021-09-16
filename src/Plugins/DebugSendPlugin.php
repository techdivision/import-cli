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

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use TechDivision\Import\Utils\RegistryKeys;
use TechDivision\Import\Configuration\SwiftMailerConfigurationInterface;

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
     * @throws \InvalidArgumentException Is thrown if either the directory nor a artefact for the given serial is available
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

        // retrieve the SwiftMailer configuration
        $swiftMailerConfiguration = $this->getPluginConfiguration()->getSwiftMailer();

        // retrieve the question helper
        $questionHelper = $this->getHelper('question');

        // use the configured SwiftMail recipient address as default if possible
        if ($swiftMailerConfiguration instanceof SwiftMailerConfigurationInterface && $swiftMailerConfiguration->hasParam('to')) {
            $recipient = $swiftMailerConfiguration->getParam('to');
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
            $this->getOutput()->writeln('<warning>Aborting operation - debug report has NOT been sent.</warning>');
            return;
        }

        // try to load the mailer instance
        if ($mailer = $this->getSwiftMailer()) {
            // initialize the message body
            $body = sprintf('<html><head></head><body>This mail contains the debug dump for import with serial "%s"</body></html>', $serial);

            // initialize the message template
            /** @var \Swift_Message $message */
            $message = $mailer->createMessage()
                ->setSubject('Test')
                ->setFrom($swiftMailerConfiguration->getParam('from'))
                ->setTo($recipient)
                ->setBody($body, 'text/html');

            // initialize the archive file
            $archiveFile = null;

            // query whether or not the archive file is available
            if (!is_file($archiveFile = sprintf('%s/%s.zip', sys_get_temp_dir(), $serial))) {
                $this->getOutput()->writeln(sprintf('<error>Can\'t find either a directory or ZIP artefact for serial "%s"</error>', $serial));
                return;
            }

            // attach the CSV files with zipped artefacts
            $message->attach(\Swift_Attachment::fromPath($archiveFile));

            // initialize the array with the failed recipients
            $failedRecipients = array();

            // send the mail
            $recipientsAccepted = $mailer->send($message, $failedRecipients);

            // query whether or not all recipients have been accepted
            if (sizeof($failedRecipients) > 0) {
                $this->getSystemLogger()->error(sprintf('Can\'t send mail to %s', implode(', ', $failedRecipients)));
            }

            // if at least one recipient has been accepted
            if ($recipientsAccepted > 0) {
                // cast 'to' into an array if not already
                is_array($recipient) ?: $recipient = (array)$recipient;

                // remove the NOT accepted recipients
                $acceptedRecipients = array_diff($recipient, $failedRecipients);

                // log a message with the accepted receivers
                $this->getSystemLogger()->info(
                    sprintf(
                        'Mail successfully sent to %d recipient(s) (%s)',
                        $recipientsAccepted,
                        implode(', ', $acceptedRecipients)
                    )
                );
            }
        } else {
            // write a message to the console, that the mailer configuration has not been available
            $this->getOutput()->writeln('<warning>The mailer configuration is not available or mailer can not be loaded</warning>');
        }
    }
}
