<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package\Contracts;

/**
 * Describes a bus that can introspect on itself to determine helpful logging information.
 */
interface IntrospectingMessageBus
{
    public function getName(): ?string;

    public function getType(): ?string;
}
