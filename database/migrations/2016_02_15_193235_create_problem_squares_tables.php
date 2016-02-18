<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProblemSquaresTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('puzzle_helper_squares', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('puzzle_id')->unsigned();
            $table->foreign('puzzle_id')->references('id')->on('puzzles');
            $table->integer('row');
            $table->integer('col');
            $table->integer('created_timestamp_utc')->nullable()->unsigned();
            $table->integer('updated_timestamp_utc')->nullable()->unsigned();
        });
        Schema::create('puzzle_helper_square_options', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('puzzle_helper_square_id')->unsigned();
            $table->foreign('puzzle_helper_square_id')->references('id')->on('puzzle_helper_squares');
            $table->string('letter',1);
            $table->decimal('score',6,3);
            $table->integer('created_timestamp_utc')->nullable()->unsigned();
            $table->integer('updated_timestamp_utc')->nullable()->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('puzzle_helper_square_options');
        Schema::drop('puzzle_helper_squares');
    }
}
