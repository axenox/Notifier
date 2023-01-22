<?php
namespace axenox\Notifier\DataConnectors;

use exface\Core\Interfaces\Communication\CommunicationMessageInterface;
use exface\Core\Interfaces\Communication\CommunicationReceiptInterface;
use exface\Core\CommonLogic\Communication\CommunicationReceipt;
use Symfony\Component\Notifier\Chatter;
use axenox\Notifier\Interfaces\SymfonyMessageInterface;
use axenox\Notifier\Communication\Messages\SymfonyChatMessage;
use exface\Core\CommonLogic\UxonObject;
use exface\Core\Exceptions\Communication\CommunicationNotSentError;
use axenox\Notifier\Interfaces\DsnRecipientInterface;
use axenox\Notifier\Communication\Recipients\SymfonyDsnRecipient;

/**
 * Universal adapter for Symfony chatter transports
 * 
 * ## Example configuration
 * 
 * ### Microsoft Teams Channel
 * 
 * Use a channel with message type `MicrosoftTeamsMessage` and the following connector configuration
 * 
 * ```
 *  {
 *    "dsn": "microsoftteams://default/webhookb...",
 *    "transport_factory_class": "\\Symfony\\Component\\Notifier\\Bridge\\MicrosoftTeams\\MicrosoftTeamsTransportFactory"
 *  }
 * 
 * ```
 * 
 * Or you could also use a generic channel with `SymfonyChatMessage` as message type and a more detailed
 * connector config. This generic channel can then be configured to be a Teams channel, a Slack channel, 
 * etc. depending on the used connection. The connection config for MS Teams would look like this:
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
    
    /**
     * 
     * {@inheritDoc}
     * @see \axenox\Notifier\DataConnectors\SymfonyNotifierDsnConnector::communicate()
     */
    public function communicate(CommunicationMessageInterface $message) : CommunicationReceiptInterface
    {
        if (! ($message instanceof SymfonyMessageInterface)) {
            $message = $this->castMessage($message);
        }
        
        $dsns = [];
        if ($this->getDsnString() !== null) {
            $dsns[] = (new SymfonyDsnRecipient($this->getDsnString()))->getDsn();
        } else {
            foreach ($message->getRecipients() as $recipient) {
                if ($recipient instanceof DsnRecipientInterface) {
                    $dsns[] = $recipient->getDsn();
                }
            }
        }
        
        if (empty($dsns)) {
            throw new CommunicationNotSentError($message, 'Cannot determine DSN for communication connection ' . $this->getAliasWithNamespace() . ': either the connection or the message must have a DSN!', null, null, $this);
        }
        
        foreach ($dsns as $dsn) {
            $transport = $this->getTransport($dsn);
            $chatter = new Chatter($transport);
            $chatter->send($message->getSymfonyMessage($this->getMessageOptionsClass()));
        }
        
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