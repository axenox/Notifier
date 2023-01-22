<?php
namespace axenox\Notifier\DataConnectors;

use exface\Core\CommonLogic\AbstractDataConnectorWithoutTransactions;
use exface\Core\Interfaces\DataSources\DataQueryInterface;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;
use Symfony\Component\Notifier\Transport\TransportFactoryInterface;
use axenox\UrlDataxenox\Notifier\DataSources\SymfonyNotifierMessageDataQuery;
use exface\Core\Exceptions\DataSources\DataConnectionQueryTypeError;
use exface\Core\Interfaces\Communication\CommunicationMessageInterface;
use exface\Core\Interfaces\Communication\CommunicationReceiptInterface;
use exface\Core\Interfaces\Communication\CommunicationConnectionInterface;
use exface\Core\CommonLogic\Communication\CommunicationReceipt;
use axenox\Notifier\Interfaces\SymfonyMessageInterface;
use axenox\Notifier\Communication\Messages\SymfonyChatMessage;
use exface\Core\Exceptions\Communication\CommunicationNotSentError;
use axenox\Notifier\Communication\Recipients\SymfonyDsnRecipient;

class SymfonyNotifierDsnConnector extends AbstractDataConnectorWithoutTransactions implements CommunicationConnectionInterface
{
    private $dsn = null;
    
    private $transportFactoryClass = null;
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\CommonLogic\AbstractDataConnector::performConnect()
     */
    protected function performConnect()
    {
        return;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\CommonLogic\AbstractDataConnector::performQuery()
     */
    protected function performQuery(DataQueryInterface $query)
    {
        if (! $query instanceof SymfonyNotifierMessageDataQuery) {
            throw new DataConnectionQueryTypeError($this, 'Invalid query type for connector "' . $this->getAliasWithNamespace() . '": expecting "SymfonyNotifierMessageDataQuery", received "' . get_class($query) . '"!');
        }
        $this->getTransport()->send($query->getMessage());
        return $query;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\CommonLogic\AbstractDataConnector::performDisconnect()
     */
    protected function performDisconnect()
    {
        return;
    }
    
    /**
     * 
     * @return string
     */
    protected function getDsnString() : ?string
    {
        return $this->dsn;
    }
    
    /**
     * The DSN for the transport to be used. If not set, each message will need to have its own address/DSN
     * 
     * @uxon-property dsn
     * @uxon-type string
     * 
     * @param string $value
     * @return SymfonyNotifierDsnConnector
     */
    public function setDsn(string $value) : SymfonyNotifierDsnConnector
    {
        $this->dsn = $value;
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    protected function getTransportFactoryClass() : string
    {
        return $this->transportFactoryClass;
    }
    
    /**
     * 
     * @return TransportFactoryInterface
     */
    protected function getTransportFactory() : TransportFactoryInterface
    {
        $class = $this->getTransportFactoryClass();
        return new $class();
    }
    
    /**
     * The PHP class of the transport factory to be used
     * 
     * @uxon-property transport_factory_class
     * @uxon-type string
     * @uxon-required true
     * 
     * @see \Symfony\Component\Notifier\Transport\TransportFactoryInterface
     * 
     * @param string $value
     * @return SymfonyNotifierDsnConnector
     */
    public function setTransportFactoryClass(string $value) : SymfonyNotifierDsnConnector
    {
        $this->transportFactoryClass = $value;
        return $this;
    }
    
    /**
     * 
     * @return TransportInterface
     */
    protected function getTransport(Dsn $dsn) : TransportInterface
    {
        return $this->getTransportFactory()->create($dsn);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Interfaces\Communication\CommunicationConnectionInterface::communicate()
     */
    public function communicate(CommunicationMessageInterface $message) : CommunicationReceiptInterface
    {
        if (! ($message instanceof SymfonyMessageInterface)) {
            throw new CommunicationNotSentError($message, 'Invalid message class "' . get_class($message) . '" - expecting SymfonyMessageInterface', null, null, $this);
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
            $transport->send($message->getSymfonyMessage());
        }
        
        return new CommunicationReceipt($message, $this);
    }
}