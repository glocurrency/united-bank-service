<?php

namespace GloCurrency\UnitedBank\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use GloCurrency\UnitedBank\Models\Transaction;
use GloCurrency\MiddlewareBlocks\Contracts\ModelEventInterface as MModelEventInterface;

class TransactionStateCodeChangedEvent implements MModelEventInterface
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Transaction $transaction;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function getItem(): Transaction
    {
        return $this->transaction;
    }
}
