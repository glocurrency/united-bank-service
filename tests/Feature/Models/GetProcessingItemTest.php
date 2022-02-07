<?php

namespace GloCurrency\UnitedBank\Tests\Feature\Models\Transaction;

use Illuminate\Support\Facades\Event;
use GloCurrency\UnitedBank\Tests\Fixtures\ProcessingItemFixture;
use GloCurrency\UnitedBank\Tests\FeatureTestCase;
use GloCurrency\UnitedBank\Models\Transaction;
use GloCurrency\UnitedBank\Events\TransactionCreatedEvent;

class GetProcessingItemTest extends FeatureTestCase
{
    /** @test */
    public function it_can_get_processing_item(): void
    {
        Event::fake([
            TransactionCreatedEvent::class,
        ]);

        $processingItem = ProcessingItemFixture::factory()->create();

        $ubaTransaction = Transaction::factory()->create([
            'processing_item_id' => $processingItem->id,
        ]);

        $this->assertSame($processingItem->id, $ubaTransaction->fresh()->processingItem->id);
    }
}
