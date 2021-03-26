<?php

namespace Tests\Unit\Concerns;

use App\Constants\TransactionType;
use PHPUnit\Framework\TestCase;

class HasEnumValuesTest extends TestCase
{
    /**
     * @test
     */
    public function itCanReturnConstantValues()
    {
        $this->assertSame([
            'BASE' => 'base',
            'INCOME' => 'income',
            'OUTCOME' => 'outcome'
        ], TransactionType::supported());
    }
}
