<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package;

use Camuthig\ServiceBus\Package\Contracts\IntrospectingMessageBus;

class EventBus extends \Prooph\ServiceBus\EventBus implements IntrospectingMessageBus
{
    use IntrospectingMessageBusTrait;
}
