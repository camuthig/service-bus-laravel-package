<?php

declare(strict_types=1);

namespace DeliveryCenter\Infrastructure\Prooph\ServiceBus;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Prooph\Common\Messaging\Message;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;

class ProophJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /**
     * @var Message
     */
    private $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message;

        if (property_exists($message, 'connection')) {
            $this->connection = $message->connection;
        }

        if (property_exists($message, 'queue')) {
            $this->connection = $message->connection;
        }

        if (property_exists($message, 'delay')) {
            $this->delay = $message->delay;
        }

        if (property_exists($message, 'chainConnection')) {
            $this->chainConnection = $message->chainConnection;
        }

        if (property_exists($message, 'chainQueue')) {
            $this->chainQueue = $message->chainQueue;
        }

        if (property_exists($message, 'chained')) {
            $this->chained = $message->chained;
        }
    }

    /**
     * Execute the job.
     *
     * @param CommandBus $commandBus
     * @param EventBus   $eventBus
     *
     * @return void
     */
    public function handle(CommandBus $commandBus, EventBus $eventBus)
    {
        // @TODO Handle the errors here gracefully in some way
        if ($this->message->messageType() === Message::TYPE_COMMAND) {
            $commandBus->dispatch($this->message);
        } elseif ($this->message->messageType() === Message::TYPE_EVENT) {
            $eventBus->dispatch($this->message);
        }
    }
}