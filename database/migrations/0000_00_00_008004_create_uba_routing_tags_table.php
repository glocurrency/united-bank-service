<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUbaRoutingTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uba_routing_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tag');
            $table->char('country_code', 3);
            $table->string('transaction_type');
            $table->timestamps(6);
            $table->softDeletes('deleted_at', 6);

            $table->unique(['country_code', 'transaction_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uba_routing_tags');
    }
}
