<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package\Test\Fixture;

class FirstListener
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
