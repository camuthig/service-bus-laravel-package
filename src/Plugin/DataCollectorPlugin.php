<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package\Plugin;

use Camuthig\ServiceBus\Package\Contracts\IntrospectingMessageBus;
use Camuthig\ServiceBus\Package\Exception\RuntimeException;
use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Illuminate\Contracts\Foundation\Application;
use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Messaging\DomainMessage;
use Prooph\Common\Messaging\Message;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\Plugin;
use Prooph\ServiceBus\QueryBus;
use Symfony\Component\Stopwatch\Stopwatch;

class DataCollectorPlugin extends DataCollector implements Plugin, Renderable, AssetProvider
{
    /**
     * @var string
     */
    protected $busType;

    /**
     * @var array
     */
    protected $listenerHandlers = [];

    /**
     * @var array
     */
    private $buses = [];

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app, string $busType)
    {
        $this->stopwatch        = new Stopwatch();
        $this->app              = $app;
        $this->busType          = $busType;
        $this->data['messages'] = [];
        $this->data['duration'] = [];
    }

    public function getAssets()
    {
        return $this->getVarDumper()->getAssets();
    }

    /**
     * @return array
     */
    public function getWidgets()
    {
        $name = $this->getName();
        $widget = "PhpDebugBar.Widgets.HtmlVariableListWidget";
        return array(
            "$name" => array(
                "icon" => "gear",
                "widget" => $widget,
                "map" => "$name",
                "default" => "{}"
            )
        );
    }

    public function collect()
    {
        foreach ($this->buses as $bus) {
            $busName = $bus->getName();

            switch (true) {
                case $bus instanceof QueryBus:
                    $this->data['config'][$busName] = config(sprintf('service_bus.query_buses.%s', $busName));
                    break;

                case $bus instanceof EventBus:
                    $this->data['config'][$busName] = config(sprintf('service_bus.event_buses.%s', $busName));
                    break;

                case $bus instanceof CommandBus:
                    $this->data['config'][$busName] = config(sprintf('service_bus.command_buses.%s', $busName));
                    break;

                default:
                    // Should not be able to reach this point
                    throw new RuntimeException(sprintf('Unable to get bus of type %s', get_class($bus)));
            }
        }

        $data = [];
        foreach ($this->data as $k => $v) {
            $data[$k] = $this->getVarDumper()->renderVar($v);
        }

        return $data;
    }

    public function getName(): string
    {
        return sprintf('%s Bus', ucfirst($this->busType()));
    }

    public function totalMessageCount(): int
    {
        return array_sum(array_map('count', $this->data['messages']));
    }

    public function totalBusCount(): int
    {
        return count($this->data['messages']);
    }

    public function messages(): array
    {
        return $this->data['messages'];
    }

    public function busDuration(string $busName): int
    {
        return $this->data['duration'][$busName];
    }

    public function callstack(string $busName): array
    {
        return $this->data['message_callstack'][$busName] ?? [];
    }

    public function config(string $busName): array
    {
        return $this->data['config'][$busName];
    }

    public function totalBusDuration(): int
    {
        return array_sum($this->data['duration']);
    }

    public function busType(): string
    {
        return $this->busType;
    }

    public function attachToMessageBus(MessageBus $messageBus): void
    {
        if ($messageBus instanceof QueryBus) {
            return;
        }

        $this->buses[] = $messageBus;
        if (! $messageBus instanceof IntrospectingMessageBus) {
            throw new RuntimeException(sprintf(
                'To use the DataCollector, the Bus "%s" needs to implement "%s"',
                $messageBus,
                IntrospectingMessageBus::class
            ));
        }

        // Start a timer for message handling
        $this->listenerHandlers[] = $messageBus->attach(MessageBus::EVENT_DISPATCH, function (ActionEvent $actionEvent) {
            /* @var $target IntrospectingMessageBus Is ensured above */
            $target = $actionEvent->getTarget();
            $busName = $target->getName();
            $message = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);

            if (! $message instanceof Message) {
                return;
            }

            $uuid = (string) $message->uuid();

            if (! $this->stopwatch->isStarted($busName)) {
                $this->stopwatch->start($busName);
            }

            $this->stopwatch->start($uuid);
        }, MessageBus::PRIORITY_INVOKE_HANDLER + 100);

        // Stop the timer and collect the data
        $this->listenerHandlers[] = $messageBus->attach(MessageBus::EVENT_FINALIZE, function (ActionEvent $actionEvent) {
            /* @var $messageBus IntrospectingMessageBus Is ensured above */
            $messageBus = $actionEvent->getTarget();
            $busName = $messageBus->getName();
            $message = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);

            if (! $message instanceof Message) {
                return;
            }

            $uuid = (string) $message->uuid();

            $this->data['duration'][$busName] = $this->stopwatch->lap($busName)->getDuration();
            $this->data['messages'][$busName][$uuid] = $this->createContextFromActionEvent($actionEvent);
            $this->data['messages'][$busName][$uuid]['duration'] = $this->stopwatch->stop($uuid)->getDuration();
        }, MessageBus::PRIORITY_INVOKE_HANDLER - 100);

        // Collect routing data
        $this->listenerHandlers[] = $messageBus->attach(MessageBus::EVENT_DISPATCH, function (ActionEvent $actionEvent) {
            /* @var $messageBus IntrospectingMessageBus Is ensured above */
            $messageBus = $actionEvent->getTarget();
            $message = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);
            $messageName = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME);
            $handler = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER);

            if (! $message instanceof Message) {
                return;
            }

            $log = [
                'id' => (string) $message->uuid(),
                'message' => $messageName,
                'handler' => is_object($handler) ? get_class($handler) : (string) $handler,
            ];

            foreach ($actionEvent->getParam('event-listeners', []) as $handler) {
                $this->data['message_callstack'][$messageBus->getName()][] = $log;
            }

            if ($handler !== null) {
                $this->data['message_callstack'][$messageBus->getName()][] = $log;
            }
        }, MessageBus::PRIORITY_ROUTE - 50000);
    }

    public function detachFromMessageBus(MessageBus $messageBus): void
    {
        foreach ($this->listenerHandlers as $listenerHandler) {
            $messageBus->detach($listenerHandler);
        }

        $this->listenerHandlers = [];
    }

    protected function createContextFromActionEvent(ActionEvent $event): array
    {
        /* @var $messageBus IntrospectingMessageBus Is ensured above */
        $messageBus = $event->getTarget();
        $message = $event->getParam(MessageBus::EVENT_PARAM_MESSAGE);
        $handler = $event->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER);

        return [
            'bus-name' => $messageBus->getName(),
            'message-data' => $message instanceof DomainMessage ? $message->toArray() : [],
            'message-name' => $event->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME),
            'message-handled' => $event->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLED),
            'message-handler' => is_object($handler) ? get_class($handler) : (string) $handler,
        ];
    }


}
