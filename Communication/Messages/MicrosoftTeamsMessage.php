<?php
namespace axenox\Notifier\Communication\Messages;

use Symfony\Component\Notifier\Bridge\MicrosoftTeams\MicrosoftTeamsOptions;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;
use exface\Core\CommonLogic\Communication\AbstractMessage;
use axenox\Notifier\Interfaces\SymfonyMessageInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageInterface;
use exface\Core\CommonLogic\UxonObject;

/**
 * Special message type for Microsoft message cards
 * 
 * You can control the message appearance using the `card` property of
 * the message. Design your messages using the official interactive playground here:
 * https://messagecardplayground.azurewebsites.net/. Use placeholders available
 * at the point where the notification is to be created (e.g. the `NotifyingBehavior`).
 * Once you are done, copy-paste the entire JSON to `card`.
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
     * Definition of a Microsoft message card 
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
}