<?php
namespace axenox\UrlDataxenox\Notifier\DataSources;

use exface\Core\CommonLogic\DataQueries\AbstractDataQuery;
use Symfony\Component\Notifier\Message\MessageInterface;

class SymfonyNotifierMessageDataQuery extends AbstractDataQuery
{
    private $message = null;

    /**
     * Wraps a PSR-7 request in a data query, which can be used with the HttpDataConnector
     *
     * @param MessageInterface $message            
     */
    public function __construct(MessageInterface $message)
    {
        $this->message($message);
    }

    /**
     *
     * @return MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\CommonLogic\DataQueries\AbstractDataQuery::toString()
     */
    public function toString($prettify = true)
    {
        return $this->getMessage()->__toString();
    }
}