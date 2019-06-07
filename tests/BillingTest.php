<?php namespace Ghobaty\Billing\Tests;

use Ghobaty\Billing\Plan;

class BillingTest extends TestCase
{
    /**
     * @test
     */
    public function it_reads_plans_from_config_file()
    {
        $this->assertNotEmpty(config('billing.plans'));
        $this->assertNotEmpty(Plan::all());
    }

    /**
     * @test
     */
    public function it_parses_plans_from_config_file()
    {
        config([
            'billing.plans' => [
                ['id' => 'foo'],
                ['id' => 'bar'],
            ],
        ]);

        $this->assertNotEmpty(Plan::all());

        $this->assertCount(2, Plan::all());

        $this->assertInstanceOf(Plan::class, Plan::all()->first());
        $this->assertSame('foo', Plan::all()->first()->id);

        $this->assertInstanceOf(Plan::class, Plan::all()->last());
        $this->assertSame('bar', Plan::all()->last()->id);
    }

    /**
     * @test
     */
    public function it_can_fetch_active_plans()
    {
        config([
            'billing.plans' => [
                ['id' => 'foo', 'active' => true],
                ['id' => 'bar', 'active' => false],
                ['id' => 'baz'],
            ],
        ]);

        $this->assertCount(3, Plan::all());

        $this->assertNotEmpty(Plan::active());
        $this->assertCount(1, Plan::active());
        $this->assertSame('foo', Plan::active()->first()->id);
    }

    /**
     * @test
     */
    public function it_can_fetch_paid_or_free_plans()
    {
        config([
            'billing.plans' => [
                ['id' => 'foo'],
                ['id' => 'bar', 'price' => 1234],
                ['id' => 'baz', 'price' => 0],
            ],
        ]);

        $this->assertCount(3, Plan::all());

        $this->assertNotEmpty(Plan::paid());
        $this->assertCount(1, Plan::paid());
        $this->assertSame('bar', Plan::paid()->first()->id);

        $this->assertNotEmpty(Plan::free());
        $this->assertCount(2, Plan::free());
        $this->assertSame(['foo', 'baz'], Plan::free()->pluck('id')->toArray());
    }

    /**
     * @test
     */
    public function it_can_fetch_active_and_paid_plans()
    {
        config([
            'billing.plans' => [
                ['id' => 'foo'],
                ['id' => 'bar', 'price' => 1234],
                ['id' => 'baz', 'price' => 1, 'active' => true],
                ['id' => 'qux', 'price' => 0, 'active' => true],
            ],
        ]);

        $this->assertCount(4, Plan::all());

        $this->assertNotEmpty(Plan::activeAndPaid());
        $this->assertCount(1, Plan::activeAndPaid());
        $this->assertSame('baz', Plan::activeAndPaid()->first()->id);
    }
}
