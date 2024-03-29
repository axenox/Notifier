<?php
namespace axenox\Notifier\Communication\Messages;

use exface\Core\Communication\TextMessage;
use axenox\Notifier\Interfaces\SymfonyMessageInterface;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use exface\Core\CommonLogic\UxonObject;

/**
 * Generic message type usable for all Symfony notifier chat transports
 * 
 * Use a communication connection with `SymfonyChatterConnector` prototype and make sure
 * the configuration of the connection includes a valid `message_options_class`.
 * 
 * Now set the `message_options` of each message sent through this connections according
 * to the documentation of the Symfony notifier transport used. See the Symfony docs
 * for details: https://symfony.com/doc/current/notifier/chatters.html. * 
 * 
 * @author Andrej Kabachnik
 *
 */
class SymfonyChatMessage extends TextMessage implements SymfonyMessageInterface
{
    private $messageOptions = null;
    
    /**
     * 
     * {@inheritDoc}
     * @see \axenox\Notifier\Interfaces\SymfonyMessageInterface::getSymfonyMessage()
     */
    public function getSymfonyMessage(string $optionsClass = null) : MessageInterface
    {
        $chatMsg = new ChatMessage($this->getSubject() ?? $this->getText(), $this->getSymfonyMessageOptions($optionsClass));
        return $chatMsg;
    }
    
    /**
     * 
     * @return MessageOptionsInterface|NULL
     */
    protected function getSymfonyMessageOptions(string $optionsClass = null) : ?MessageOptionsInterface
    {
        if ($optionsClass) {
            $options = new $optionsClass($this->getMessageOptions());
        } else {
            $options = null;
        }
        return $options;
    }
    
    protected function getMessageOptions() : array
    {
        return $this->messageOptions ?? [];
    }
    
    /**
     * Custom Symfony message options specific to the transport used in the connection of the channel.
     * 
     * @uxon-property message_options
     * @uxon-type object
     * @uxon-template {"": ""}
     * 
     * @param UxonObject $value
     * @return SymfonyChatMessage
     */
    public function setMessageOptions(UxonObject $value) : SymfonyChatMessage
    {
        $this->messageOptions = $value->toArray();
        return $this;
    }
}