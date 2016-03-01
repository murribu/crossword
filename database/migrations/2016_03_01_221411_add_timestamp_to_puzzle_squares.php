<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimestampToPuzzleSquares extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('puzzle_squares', function (Blueprint $table) {
            $table->integer('timestamp_utc')->nullable()->unsigned();
        });
        Schema::create('settings', function(Blueprint $table){
            $table->increments('id');
            $table->string('slug')->unique();
            $table->integer('last_puzzle_helper_run_timestamp')->nullable()->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('puzzle_squares', function (Blueprint $table) {
            $table->dropColumn('timestamp_utc');
        });
        Schema::drop('settings');
    }
}
