<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('history', function (Blueprint $table) {
			$table->increments('id');
			$table->timestamps();
			
			$table->string('user_type')->nullable();
			$table->integer('user_id')->nullable();
			
			$table->string('model_type')->nullable();
			$table->integer('model_id')->nullable();
			
			$table->string('type')->nullable();
			$table->text('data')->nullable();
			$table->text('extra')->nullable();
			
			$table->index('model_id');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::drop('history');
    }
}
