<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package;

use Camuthig\ServiceBus\Package\Contracts\IntrospectingMessageBus;

class QueryBus extends \Prooph\ServiceBus\QueryBus implements IntrospectingMessageBus
{
    use IntrospectingMessageBusTrait;
}
