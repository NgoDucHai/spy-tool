<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpiedBoothTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Bonanza
		Schema::create('bonanza_spied_booths', function($table)
		{
			$table->increments('id');
			$table->bigInteger('booth_id');
			$table->dateTime('booth_created_at');
			$table->dateTime('booth_updated_at');
			$table->string('spied_booth_timezone', 100);
		});

		Schema::create('bonanza_spied_booth_feeds', function($table)
		{
			$table->increments('id');
			$table->bigInteger('booth_id');
			$table->string('booth_title');
			$table->string('booth_username');
			$table->integer('transactions');
			$table->float('positive_rating');
			$table->integer('total_products');
			$table->string('member_level', 100);
			$table->dateTime('feed_last_updated_at');
			$table->string('feed_timezone', 100);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
		DB::table('bonanza_spied_booth_feeds')->delete();
		DB::table('bonanza_spied_booths')->delete();
	}

}
