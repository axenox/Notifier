<?php
namespace axenox\Notifier\Interfaces;

use Symfony\Component\Notifier\Transport\Dsn;
use exface\Core\Interfaces\Communication\RecipientInterface;

interface DsnRecipientInterface extends RecipientInterface
{
    /**
     * 
     * @return Dsn
     */
    public function getDsn() : Dsn;
}