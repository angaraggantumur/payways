<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('billable_id')->unsigned()->nullable();
            $table->string('billable_type')->nullable();
            $table->string('gateway', 10);
            $table->decimal('amount', 10, 2);
            $table->integer('currency');
            $table->text('description', 160)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->enum('response_status', ['pending', 'approved', 'declined', 'cancelled'])->default('pending');
            $table->string('response_code')->nullable();
            $table->string('response_message')->nullable();
            $table->jsonb('response_data')->nullable();
            $table->string('reference')->nullable()->unique(); // Reference is generated by the gateway server, Used for Khan,
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transactions');
    }
}
