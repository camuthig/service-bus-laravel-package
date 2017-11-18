<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package\Test\Fixture;

class TestHandler
{
    private $received = [];

    public function __invoke(TestCommand $command)
    {
        $this->received[] = $command;
    }

    public function lastCommand(): TestCommand
    {
        return end($this->received);
    }
}
