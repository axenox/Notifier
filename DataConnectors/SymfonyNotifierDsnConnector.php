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
    
    protected function getDsnString() : string
    {
        return $this->dsn;
    }
    
    protected function getDsn() : Dsn
    {
        return new Dsn($this->getDsnString());
    }
    
    /**
     * The DSN for the transport to be used
     * 
     * @uxon-property transport_dsn
     * @uxon-type string
     * @uxon-required true
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
    protected function getTransport() : TransportInterface
    {
        return $this->getTransportFactory()->create($this->getDsn());
    }
    
    public function communicate(CommunicationMessageInterface $message, array $recipients) : CommunicationReceiptInterface
    {
        $transport = $this->getTransport();
        $transport->send($message->getSymfonyMessage());
        return new CommunicationReceipt($message);
    }
}