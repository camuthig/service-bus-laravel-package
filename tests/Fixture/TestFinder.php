<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package\Test\Fixture;

use Prooph\Common\Messaging\Query;

class TestFinder
{
    /**
     * @var Query[]
     */
    private $queries = [];

    public function __invoke(TestQuery $query)
    {
        $this->queries[] = $query;
    }

    public function lastQuery(): Query
    {
        return end($this->queries);
    }
}
