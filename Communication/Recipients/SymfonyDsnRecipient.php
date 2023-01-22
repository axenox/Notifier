<?php
namespace axenox\Notifier\Communication\Recipients;

use axenox\Notifier\Interfaces\DsnRecipientInterface;
use Symfony\Component\Notifier\Transport\Dsn;

class SymfonyDsnRecipient implements DsnRecipientInterface
{
    private $dsnString = null;
    
    /**
     * 
     * @param string $dsn
     */
    public function __construct(string $dsn)
    {
        $this->dsnString = trim($dsn);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \axenox\Notifier\Interfaces\DsnRecipientInterface::getDsn()
     */
    public function getDsn(): Dsn
    {
        return new Dsn($this->dsnString);
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\Core\Interfaces\Communication\RecipientInterface::__toString()
     */
    public function __toString(): string
    {
        return $this->dsnString;
    }
}