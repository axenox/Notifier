<?php
namespace axenox\Notifier\Communication\Messages;

use Symfony\Component\Notifier\Bridge\MicrosoftTeams\MicrosoftTeamsOptions;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;
use exface\Core\CommonLogic\Communication\AbstractMessage;
use axenox\Notifier\Interfaces\SymfonyMessageInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageInterface;
use exface\Core\CommonLogic\UxonObject;
use exface\Core\Interfaces\Communication\CommunicationMessageInterface;
use exface\Core\DataTypes\StringDataType;
use axenox\Notifier\Communication\Recipients\SymfonyDsnRecipient;
use exface\Core\Interfaces\Communication\RecipientInterface;

/**
 * Special message type for Microsoft message cards
 * 
 * You can control the message appearance using the `card` property of
 * the message. Design your messages using the official interactive playground here:
 * 
 * https://messagecardplayground.azurewebsites.net/
 * 
 * Use placeholders available at the point where the notification is to be created 
 * (e.g. the `NotifyingBehavior`). Once you are done, copy-paste the entire JSON to 
 * `card`.
 * 
 * Example based on the core object `exface.Core.MONITOR_ERROR`:
 * 
 * ```
 * {
 *   "channel_webhook_url": "http://...",
 *   "card": {
 *     "@type": "MessageCard",
 *     "@context": "https://schema.org/extensions",
 *     "summary": "[#~data:MESSAGE#]",
 *     "title": "[#~data:ERROR_LEVEL#] error for user [#~data:USER__USERNAME#]",
 *     "sections": [{
 *         "text": "[#~data:MESSAGE#]",
 *         "facts": [
 *           {"name": "Log-ID:", "value": "[#~data:LOG_ID#]"},
 *           {"name": "User:", "value": "[#~data:USER__USERNAME#]"}
 *         ]
 *       }
 *     ]
 *   }
 * }
 * 
 * ```
 * 
 * @author andrej.kabachnik
 *
 */
class MicrosoftTeamsMessage extends AbstractMessage implements SymfonyMessageInterface
{
    private $card = null;
    
    public function getSymfonyMessage(string $optionsClass = null) : MessageInterface
    {
        $chatMsg = new ChatMessage(($this->hasCard() ? '' : $this->getText()), $this->getSymfonyMessageOptions($optionsClass));
        return $chatMsg;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \axenox\Notifier\Communication\Messages\SymfonyChatMessage::getSymfonyMessageOptions()
     */
    protected function getSymfonyMessageOptions(string $optionsClass = null) : ?MessageOptionsInterface
    {
        if ($optionsClass !== null && $optionsClass !== '\\' . MicrosoftTeamsOptions::class) {
            return new $optionsClass();
        }
        return new MicrosoftTeamsOptions($this->getCard()->toArray());
    }
    
    /**
     * 
     * @return bool
     */
    protected function hasCard() : bool
    {
        return $this->card !== null;
    }
    
    /**
     * 
     * @return UxonObject
     */
    protected function getCard() : UxonObject
    {
        return $this->card ?? new UxonObject();
    }
    
    /**
     * Definition of a Microsoft message card.
     * 
     * Use the official playground to edit the card visually:
     * 
     * https://messagecardplayground.azurewebsites.net/
     * 
     * ## Example
     * 
     * ```
     * {
     *   "channel_webhook_url": "http://...",
     *   "card": {
     *     "@type": "MessageCard",
     *     "@context": "https://schema.org/extensions",
     *     "summary": "[#~data:MESSAGE#]",
     *     "title": "[#~data:ERROR_LEVEL#] error for user [#~data:USER__USERNAME#]",
     *     "sections": [{
     *         "text": "[#~data:MESSAGE#]",
     *         "facts": [
     *           {"name": "Log-ID:", "value": "[#~data:LOG_ID#]"},
     *           {"name": "User:", "value": "[#~data:USER__USERNAME#]"}
     *         ]
     *       }
     *     ]
     *   }
     * }
     * 
     * ```
     * 
     * @uxon-property card
     * @uxon-type object
     * @uxon-template {"@type": "MessageCard", "@context": "https://schema.org/extensions", "summary": "", "title": ""}
     * 
     * @param UxonObject $value
     * @return MicrosoftTeamsMessage
     */
    public function setCard(UxonObject $value) : MicrosoftTeamsMessage
    {
        $this->card = $value;
        return $this;
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\Core\Interfaces\Communication\CommunicationMessageInterface::getText()
     */
    public function getText(): string
    {
        return $this->card->getProperty('title') ?? $this->card->getProperty('summary') ?? $this->card->getProperty('text') ?? '';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Interfaces\Communication\CommunicationMessageInterface::setText()
     */
    public function setText(string $value) : CommunicationMessageInterface
    {
        if ($this->card === null) {
            $this->card = new UxonObject();
        }
        
        if (StringDataType::stripLineBreaks($value) === $value) {
            $this->card->setProperty('title', $value);
        } else {
            $this->card->setProperty('text', $value);
        }
        
        return $this;
    }
    
    /**
     * Custom channel URL for communication connections, that do not have a fixed DSN
     * 
     * @uxon-property teams_webhook_url
     * @uxon-type string
     * 
     * @param string $value
     * @return MicrosoftTeamsMessage
     */
    protected function setTeamsWebhookUrl(string $value) : MicrosoftTeamsMessage
    {
        $this->addRecipient($this->parseRcipientAddress($value));
        return $this;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Interfaces\iCanBeConvertedToUxon::exportUxonObject()
     */
    public function exportUxonObject()
    {
        $uxon = parent::exportUxonObject();
        if ($this->card !== null) {
            $uxon->setProperty('card', $this->dard);
        }
        return $uxon;
    }
    
    /**
     * 
     * {@inheritdoc}
     * @see AbstractMessage::parseRcipientAddress()
     */
    protected function parseRcipientAddress(string $address) : ?RecipientInterface
    {
        if (StringDataType::startsWith($address, 'https://', false)) {
            return new SymfonyDsnRecipient('microsoftteams://' . StringDataType::substringAfter($address, 'https://', $address));
        }
        if (null === $recipient = parent::parseRcipientAddress($address)) {
            try {
                $recipient = new SymfonyDsnRecipient($address);
            } catch (\Throwable $e) {
                return null;
            }
        }
        return $recipient;
    }
}