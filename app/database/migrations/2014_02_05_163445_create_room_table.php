<?php

use Illuminate\Database\Migrations\Migration;

class CreateRoomTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('room', function($table)
        {
            $table->string('id', 255)->unique();
            $table->text('name');
            $table->text('created_by');
            $table->dateTime('created_at');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('room');
	}

}