<?php

use Illuminate\Database\Migrations\Migration;

class CreateChat extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('chat', function($table)
        {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('room_id', 255);
            $table->longText('topics');
            $table->longText('message');
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
		Schema::dropIfExists('chat');
	}

}