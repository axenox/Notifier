<?php
namespace axenox\Notifier\Communication\Channels;

use exface\Core\CommonLogic\Communication\AbstractCommunicationChannel;
use exface\Core\CommonLogic\Communication\CommunicationAcknowledgement;
use exface\Core\Interfaces\Communication\CommunicationMessageInterface;
use exface\Core\Interfaces\Communication\CommunicationAcknowledgementInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Chatter;

class SymfonyChatterChannel extends AbstractCommunicationChannel
{
    private $messageOptionsClass = null;
    
    public function send(CommunicationMessageInterface $message) : CommunicationAcknowledgementInterface
    {
        $connection = $this->getConnection();
        // TODO Check connection type
        $transport = $connection->getTransport();
        $chatter = new Chatter($transport);
        $chatter->send($this->createChatMessage($message));
        return new CommunicationAcknowledgement($message, $this);
    }
    
    protected function createChatMessage(CommunicationMessageInterface $message) : ChatMessage
    {
        $optionsClass = $this->getMessageOptionsClass();
        if ($optionsClass) {
            $options = new $optionsClass($message->getOptionsUxon()->toArray());
        } else {
            $options = [];
        }
        $chatMsg = new ChatMessage($message->getSubject() ?? $message->getText(), $options);
        /*
        $message = new ChatMessage('DevMan chat test!', new MicrosoftTeamsOptions([
            'summary' => 'test summary',
            'text' => 'test text @Andrej'
        ]));*/
        return $chatMsg;
    }
    
    protected function getMessageOptionsClass() : string
    {
        return $this->messageOptionsClass;
    }
    
    /**
     * PHP class for chat message options
     * 
     * @uxon-property message_options_class
     * @uxon-type string
     * 
     * @param string $value
     * @return SymfonyChatterChannel
     */
    protected function setMessageOptionsClass(string $value) : SymfonyChatterChannel
    {
        $this->messageOptionsClass = $value;
        return $this;
    }
}