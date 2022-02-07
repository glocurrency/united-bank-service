<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUbaTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uba_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('transaction_id')->unique()->index();
            $table->uuid('processing_item_id')->index();

            $table->string('state_code');
            $table->longText('state_code_reason')->nullable();

            $table->string('status_code')->nullable();
            $table->longText('status_code_description')->nullable();
            $table->string('error_code')->nullable();
            $table->longText('error_code_description')->nullable();

            $table->string('reference')->unique();
            $table->string('uba_destination_id')->nullable()->unique()->index();
            $table->string('uba_request_id')->nullable();
            $table->string('destination_swift_code');
            $table->string('destination_account_number');
            $table->string('source_swift_code');
            $table->string('sender_name');
            $table->string('recipient_name')->nullable();

            $table->string('routing_tag');
            $table->string('description')->nullable();

            $table->unsignedDouble('amount');
            $table->char('country_code', 3);
            $table->char('currency_code', 3);

            $table->timestamps(6);
            $table->softDeletes('deleted_at', 6);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uba_transactions');
    }
}
