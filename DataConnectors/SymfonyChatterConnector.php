<?php
namespace axenox\Notifier\DataConnectors;

use exface\Core\Interfaces\Communication\CommunicationMessageInterface;
use exface\Core\Interfaces\Communication\CommunicationReceiptInterface;
use exface\Core\CommonLogic\Communication\CommunicationReceipt;
use Symfony\Component\Notifier\Chatter;
use axenox\Notifier\Interfaces\SymfonyMessageInterface;
use axenox\Notifier\Communication\Messages\SymfonyChatMessage;
use exface\Core\CommonLogic\UxonObject;

/**
 * Universal adapter for Symfony chatter transports
 * 
 * Example configuration for a Microsoft Teams Channel:
 * 
 * ```
 *  {
 *    "dsn": "microsoftteams://default/webhookb...",
 *    "transport_factory_class": "\\Symfony\\Component\\Notifier\\Bridge\\MicrosoftTeams\\MicrosoftTeamsTransportFactory",
 *    "message_options_class": "\\Symfony\\Component\\Notifier\\Bridge\\MicrosoftTeams\\MicrosoftTeamsOptions"
 *  }
 * 
 * ```
 * 
 * @author andrej.kabachnik
 *
 */
class SymfonyChatterConnector extends SymfonyNotifierDsnConnector
{
    private $messageOptionsClass = null;
    
    public function communicate(CommunicationMessageInterface $message) : CommunicationReceiptInterface
    {
        if (! ($message instanceof SymfonyMessageInterface)) {
            $message = $this->castMessage($message);
        }
        $transport = $this->getTransport();
        $chatter = new Chatter($transport);
        $chatter->send($message->getSymfonyMessage($this->getMessageOptionsClass()));
        return new CommunicationReceipt($message, $this);
    }
    
    /**
     * 
     * @param CommunicationMessageInterface $nonSymfonyMessage
     * @return SymfonyMessageInterface
     */
    protected function castMessage(CommunicationMessageInterface $nonSymfonyMessage) : SymfonyMessageInterface
    {
        $msg = new SymfonyChatMessage(new UxonObject([
            'text' => $nonSymfonyMessage->getText()
        ]));
        
        if ($val = $nonSymfonyMessage->getSubject()) {
            $msg->setSubject($val);
        }
        
        return $msg;
    }
    
    /**
     *
     * @return string|NULL
     */
    protected function getMessageOptionsClass() : ?string
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
     * @return SymfonyChatMessage
     */
    protected function setMessageOptionsClass(string $value) : SymfonyChatterConnector
    {
        $this->messageOptionsClass = $value;
        return $this;
    }
}