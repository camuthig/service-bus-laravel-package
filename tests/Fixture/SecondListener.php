<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package\Test\Fixture;

class SecondListener
{
    private $received = [];

    public function __invoke(TestEvent $event)
    {
        $this->received[] = $event;
    }

    public function lastEvent(): TestEvent
    {
        return end($this->received);
    }
}
