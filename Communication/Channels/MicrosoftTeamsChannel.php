<?php
namespace axenox\Notifier\Communication\Channels;

use axenox\Notifier\Communication\Messages\MicrosoftTeamsMessage;

/**
 * Sends messages to a Microsoft Teams channel via incoming webhook.
 * 
 * You can customize the message appearance using the `message_options` property of
 * a message. Design your messages using the official interactive playground here:
 * https://messagecardplayground.azurewebsites.net/. Use placeholders available
 * at the point where the notification is to be created (e.g. the `NotifyingBehavior`).
 * Once you are done, copy-paste the entire JSON to `message_options`.
 * 
 * 
 * @author andrej.kabachnik
 *
 */
class MicrosoftTeamsChannel extends SymfonyChatterChannel
{
    /**
     * 
     * @param string $default
     * @return string
     */
    protected function getMessagePrototype(string $default = MicrosoftTeamsMessage::class) : string
    {
        return parent::getMessagePrototype($default);
    }
}