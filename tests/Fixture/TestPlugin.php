<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package\Test\Fixture;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\AbstractPlugin;

class TestPlugin extends AbstractPlugin
{
    use DetachAggregateHandlers;

    private $fired = false;

    public function wasFired(): bool
    {
        return $this->fired;
    }

    public function attachToMessageBus(MessageBus $bus): void
    {
        $this->trackHandler($bus->attach(
            MessageBus::EVENT_DISPATCH,
            [$this, 'onInitialize'],
            MessageBus::PRIORITY_INITIALIZE
        ));
    }

    public function onInitialize(ActionEvent $event)
    {
        $this->fired = true;
    }

    public function reset()
    {
        $this->fired = false;
    }
}
