<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Command Buses
    |
    | Each entry will define a different command bus in the application. It can
    | be retried with `ServiceBus::commandBus('index')`. The default bus will
    | be bound to the the CommandBus class and facade.
    |
    | Each command bus can configure:
    | - message_factory: Defaults to FQCNMessageFactory, if not provided.
    | - action_event_emitter: Defaults to ProophActionEventEmitter, if not provided.
    | - plugins: A list of plugins to add to the bus.
    | - router: Configuration for the router plugin of the bus.
    |
    | Router configurations:
    | - type: The service ID or FQCN of the router. Defaults to CommandRouter if
    |         not provided.
    | - routes: A list of messageName => handler
    |
    |--------------------------------------------------------------------------
    */
    'command_buses' => [
        'default' => [
            'message_factory' => \Prooph\Common\Messaging\FQCNMessageFactory::class,
            'action_event_emitter' => \Prooph\Common\Event\ProophActionEventEmitter::class,
            'plugins'         => [
                \Camuthig\ServiceBus\Package\Test\Fixture\TestPlugin::class,
            ],
            'router' => [
                'type' => \Prooph\ServiceBus\Plugin\Router\CommandRouter::class,
                'routes' => [
                    \Camuthig\ServiceBus\Package\Test\Fixture\TestCommand::class => \Camuthig\ServiceBus\Package\Test\Fixture\TestHandler::class,
                ]
            ]
        ],
        'secondary' => [
            'message_factory' => \Prooph\Common\Messaging\FQCNMessageFactory::class,
            'action_event_emitter' => \Prooph\Common\Event\ProophActionEventEmitter::class,
            'plugins'         => [
                \Camuthig\ServiceBus\Package\Test\Fixture\TestPlugin::class,
            ],
            'router' => [
                'type' => \Prooph\ServiceBus\Plugin\Router\CommandRouter::class,
                'routes' => [
                    \Camuthig\ServiceBus\Package\Test\Fixture\TestCommand::class => \Camuthig\ServiceBus\Package\Test\Fixture\TestHandler::class,
                ]
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Buses
    |
    | Each entry will define a different command bus in the application. It can
    | be retried with `ServiceBus::eventBus('index')`. The default bus will
    | be bound to the the EventBus class and facade.
    |
    | Each command bus can configure:
    | - message_factory: Defaults to FQCNMessageFactory, if not provided.
    | - action_event_emitter: Defaults to ProophActionEventEmitter, if not provided.
    | - plugins: A list of plugins to add to the bus.
    | - router: Configuration for the router plugin of the bus.
    |
    | Router configurations:
    | - type: The service ID or FQCN of the router. Defaults to EventRouter if
    |         not provided.
    | - routes: A list of messageName => [ listener1, listener2 ]
    |
    |--------------------------------------------------------------------------
    */
    'event_buses'   => [
        'default' => [
            'message_factory' => \Prooph\Common\Messaging\FQCNMessageFactory::class,
            'action_event_emitter' => \Prooph\Common\Event\ProophActionEventEmitter::class,
            'plugins'         => [
                \Camuthig\ServiceBus\Package\Test\Fixture\TestPlugin::class,
            ],
            'router' => [
                'type' => \Prooph\ServiceBus\Plugin\Router\EventRouter::class,
                'routes' => [
                    \Camuthig\ServiceBus\Package\Test\Fixture\TestEvent::class => [
                        \Camuthig\ServiceBus\Package\Test\Fixture\FirstListener::class,
                        \Camuthig\ServiceBus\Package\Test\Fixture\SecondListener::class,
                    ]
                ]
            ]
        ],
        'secondary' => [
            'message_factory' => \Prooph\Common\Messaging\FQCNMessageFactory::class,
            'action_event_emitter' => \Prooph\Common\Event\ProophActionEventEmitter::class,
            'plugins'         => [
                \Camuthig\ServiceBus\Package\Test\Fixture\TestPlugin::class,
            ],
            'router' => [
                'type' => \Prooph\ServiceBus\Plugin\Router\EventRouter::class,
                'routes' => [
                    \Camuthig\ServiceBus\Package\Test\Fixture\TestEvent::class => [
                        \Camuthig\ServiceBus\Package\Test\Fixture\FirstListener::class,
                        \Camuthig\ServiceBus\Package\Test\Fixture\SecondListener::class,
                    ]
                ]
            ]
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Query Buses
    |
    | Each entry will define a different command bus in the application. It can
    | be retried with `ServiceBus::queryBus('index')`. The default bus will
    | be bound to the the QueryBus class and facade.
    |
    | Each command bus can configure:
    | - message_factory: Defaults to FQCNMessageFactory, if not provided.
    | - action_event_emitter: Defaults to ProophActionEventEmitter, if not provided.
    | - plugins: A list of plugins to add to the bus.
    | - router: Configuration for the router plugin of the bus.
    |
    | Router configurations:
    | - type: The service ID or FQCN of the router. Defaults to QueryRouter if
    |         not provided.
    | - routes: A list of messageName => handler
    |
    |--------------------------------------------------------------------------
    */
    'query_buses'   => [
        'default' => [
            'message_factory' => \Prooph\Common\Messaging\FQCNMessageFactory::class,
            'action_event_emitter' => \Prooph\Common\Event\ProophActionEventEmitter::class,
            'plugins'         => [
                \Camuthig\ServiceBus\Package\Test\Fixture\TestPlugin::class,
            ],
            'router' => [
                'type' => \Prooph\ServiceBus\Plugin\Router\QueryRouter::class,
                'routes' => [
                    \Camuthig\ServiceBus\Package\Test\Fixture\TestQuery::class => \Camuthig\ServiceBus\Package\Test\Fixture\TestFinder::class
                ]
            ]
        ],
        'secondary' => [
            'message_factory' => \Prooph\Common\Messaging\FQCNMessageFactory::class,
            'action_event_emitter' => \Prooph\Common\Event\ProophActionEventEmitter::class,
            'plugins'         => [
                \Camuthig\ServiceBus\Package\Test\Fixture\TestPlugin::class,
            ],
            'router' => [
                'type' => \Prooph\ServiceBus\Plugin\Router\QueryRouter::class,
                'routes' => [
                    \Camuthig\ServiceBus\Package\Test\Fixture\TestQuery::class => \Camuthig\ServiceBus\Package\Test\Fixture\TestFinder::class
                ]
            ]
        ]
    ],
];
