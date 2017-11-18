<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package\Plugin;

use Illuminate\Contracts\Foundation\Application;
use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\AbstractPlugin;

/**
 * A plugin based on the ServiceLocatorPlugin from Prooph that leverages the Laravel Application and `make` to support
 * Laravel autowiring.
 */
class ResolverPlugin extends AbstractPlugin
{
    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function attachToMessageBus(MessageBus $messageBus): void
    {
        $this->listenerHandlers[] = $messageBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                $messageHandlerAlias = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER);

                if (is_string($messageHandlerAlias) && $this->app->make($messageHandlerAlias)) {
                    $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, $this->app->make($messageHandlerAlias));
                }

                // for event bus only
                $currentEventListeners = $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, []);
                $newEventListeners = [];

                foreach ($currentEventListeners as $key => $eventListenerAlias) {
                    $newEventListeners[$key] = $this->app->make($eventListenerAlias);
                }

                // merge array whilst preserving numeric keys and giving priority to newEventListeners
                $actionEvent->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, $newEventListeners + $currentEventListeners);
            },
            MessageBus::PRIORITY_LOCATE_HANDLER
        );
    }
}
