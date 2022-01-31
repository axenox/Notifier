<?php
namespace axenox\Notifier\Communication\Messages;

use exface\Core\CommonLogic\Communication\AbstractMessage;
use axenox\Notifier\Interfaces\SymfonyMessageInterface;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use exface\Core\CommonLogic\UxonObject;

class SymfonyChatMessage extends AbstractMessage implements SymfonyMessageInterface
{
    private $messageOptions = null;
    
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