<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hands', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('round_id');
            $table->string('hand1_cards');
            $table->string('hand1');
            $table->string('hand2_cards');
            $table->string('hand2');
            $table->unsignedTinyInteger('result');
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
        Schema::dropIfExists('hands');
    }
}
