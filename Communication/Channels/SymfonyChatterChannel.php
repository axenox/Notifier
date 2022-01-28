<?php
namespace axenox\Notifier\Communication\Channels;

use exface\Core\CommonLogic\Communication\AbstractCommunicationChannel;
use exface\Core\Interfaces\Communication\CommunicationReceiptInterface;
use exface\Core\Interfaces\Communication\EnvelopeInterface;
use exface\Core\CommonLogic\UxonObject;
use axenox\Notifier\Communication\Messages\MicrosoftTeamsMessage;
use axenox\Notifier\Communication\Messages\SymfonyChatMessage;

class SymfonyChatterChannel extends AbstractCommunicationChannel
{
    private $messageClass = null;
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Interfaces\Communication\CommunicationChannelInterface::send($envelope)
     */
    public function send(EnvelopeInterface $envelope) : CommunicationReceiptInterface
    {
        $connection = $this->getConnection();
        
        return $connection->communicate(
            $this->createMessage(
                $this->getMessageDefaults()->extend($envelope->getPayloadUxon())
            ),
            $envelope->getRecipients()
        );
    }
    
    /**
     * 
     * @param UxonObject $payloadUxon
     * @return MicrosoftTeamsMessage
     */
    protected function createMessage(UxonObject $payloadUxon) : SymfonyChatMessage
    {
        $class = $this->getMessagePrototype();
        return new $class($this->getMessageDefaults()->extend($payloadUxon));
    }
    
    /**
     * 
     * @param string $default
     * @return string
     */
    protected function getMessagePrototype(string $default = SymfonyChatMessage::class) : string
    {
        return $this->messageClass ?? $default;
    }
    
    /**
     * The PHP class to use for messages in this channel
     * 
     * @uxon-property message_prototype
     * @uxon-type string
     * @uxon-template \axenox\Notifier\Communication\Messages\SymfonyChatMessage
     * @uxon-default \axenox\Notifier\Communication\Messages\SymfonyChatMessage
     * 
     * @param string $value
     * @return SymfonyChatterChannel
     */
    public function setMessagePrototype(string $value) : SymfonyChatterChannel
    {
        $this->messageClass = $value;
        return $this;
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\Core\Interfaces\iCanBeConvertedToUxon::exportUxonObject()
     */
    public function exportUxonObject()
    {
        return new UxonObject();
    }
}