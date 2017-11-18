<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package;

use Camuthig\ServiceBus\Package\Contracts\IntrospectingMessageBus;

class CommandBus extends \Prooph\ServiceBus\CommandBus implements IntrospectingMessageBus
{
    use IntrospectingMessageBusTrait;
}
