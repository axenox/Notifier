<?php
namespace axenox\Notifier\Interfaces;

use exface\Core\Interfaces\Communication\CommunicationMessageInterface;
use Symfony\Component\Notifier\Message\MessageInterface;

interface SymfonyMessageInterface extends CommunicationMessageInterface
{
    public function getSymfonyMessage() : MessageInterface;
}