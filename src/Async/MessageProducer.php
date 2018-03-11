<?php

declare(strict_types=1);

namespace DeliveryCenter\Infrastructure\Prooph\ServiceBus;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Prooph\Common\Messaging\Message;
use Prooph\ServiceBus\Async\MessageProducer as MessageProducerCOntract;
use React\Promise\Deferred;
use RuntimeException;

class MessageProducer implements MessageProducerContract
{
    /**
     * @var QueueingDispatcher
     */
    private $dispatcher;

    public function __construct(QueueingDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Message producers need to be invokable.
     *
     * A producer MUST be able to handle a message async without returning a response.
     * A producer MAY also support future response by resolving the passed $deferred.
     *
     * Note: A $deferred is only passed by a QueryBus but in this case the $deferred
     *       MUST either be resolved/rejected OR the message producer
     *       MUST throw a Prooph\ServiceBus\Exception\RuntimeException if it cannot
     *       handle the $deferred
     */
    public function __invoke(Message $message, Deferred $deferred = null): void
    {
        if (null !== $deferred) {
            throw new RuntimeException(__CLASS__ . ' cannot handle query messages which require future responses.');
        }


        $this->dispatcher->dispatchToQueue(new ProophJob($message));
    }
}