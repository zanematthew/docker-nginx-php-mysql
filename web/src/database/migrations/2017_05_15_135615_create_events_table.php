<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {

            // Standard
            $table->increments('id');
            $table->timestamps();

            // Specific
            $table->string('title');
            $table->string('type');
            $table->string('url')->nullable();
            $table->string('fee')->nullable();
            $table->time('registration_start_time')->nullable();
            $table->time('registration_end_time')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('flyer_uri')->nullable();
            $table->string('event_schedule_uri')->nullable();
            $table->string('hotel_uri')->nullable();
            $table->string('usabmx_track_id')->nullable();
            $table->string('usabmx_id')->nullable();

            // Relationship
            $table->integer('venue_id')->unsigned();
            $table->foreign('venue_id')->references('id')->on('venues');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
