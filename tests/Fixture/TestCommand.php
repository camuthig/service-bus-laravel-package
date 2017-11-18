<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package\Test\Fixture;

use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\Message;

class TestCommand extends Command implements Message
{
    private $payload = [];

    public function __construct()
    {
        $this->init();
    }

    protected function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }

    public function payload(): array
    {
        return $this->payload;
    }
}
