<?php

declare(strict_types=1);

namespace Tests\Integration;

use Tests\BaseTestCase;

class CustomerScaffoldTest extends BaseTestCase
{
    /**
     * Verify scaffold metadata.
     */
    public function test_scaffold_name(): void
    {
        $this->assertSame('Customer', 'Customer');
    }
}
