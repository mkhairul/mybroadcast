<?php

use Illuminate\Database\Migrations\Migration;

class CreatePresence extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('presence', function($table)
        {
            $table->increments('id');
            $table->longText('users');
            $table->longText('rooms');
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
		Schema::dropIfExists('presence');
	}

}