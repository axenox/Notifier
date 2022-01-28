<?php
namespace axenox\Notifier\Communication\Messages;

use Symfony\Component\Notifier\Bridge\MicrosoftTeams\MicrosoftTeamsOptions;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;

class MicrosoftTeamsMessage extends SymfonyChatMessage
{
    /**
     * 
     * {@inheritDoc}
     * @see \axenox\Notifier\Communication\Messages\SymfonyChatMessage::getSymfonyMessageOptions()
     */
    protected function getSymfonyMessageOptions(string $optionsClass = null) : ?MessageOptionsInterface
    {
        if ($optionsClass !== null && $optionsClass !== '\\' . MicrosoftTeamsOptions::class) {
            return parent::getSymfonyMessageOptions($optionsClass);
        }
        $baseOptions = [
            'summary' => $this->getSubject(),
            'text' => $this->getText()
        ];
        return new MicrosoftTeamsOptions(array_merge($baseOptions, $this->getMessageOptions()));
    }
}