<?php

namespace GloCurrency\UnitedBank\Tests\Feature\Models\Transaction;

use Illuminate\Support\Facades\Event;
use GloCurrency\UnitedBank\Tests\FeatureTestCase;
use GloCurrency\UnitedBank\Models\Transaction;
use GloCurrency\UnitedBank\Events\TransactionUpdatedEvent;

class UpdateTransactionTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            TransactionCreatedEvent::class,
        ]);
    }

    /** @test */
    public function fire_event_when_it_updated(): void
    {
        $transaction = Transaction::factory()->create([
            'state_code_reason' => 'abc',
        ]);

        Event::fake();

        $transaction->state_code_reason = 'xyz';
        $transaction->save();

        Event::assertDispatched(TransactionUpdatedEvent::class);
    }
}
