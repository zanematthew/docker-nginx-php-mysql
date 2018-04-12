<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVenuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            // Specific fields
            $table->string('name');
            $table->string('district');
            $table->string('usabmx_id');
            $table->string('website')->nullable();
            $table->string('image_uri')->nullable();
            $table->text('description')->nullable();
            $table->string('street_address')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->string('email')->nullable();
            $table->string('primary_contact')->nullable();
            $table->string('phone_number')->nullable();

            // Relationship
            $table->integer('city_id')->unsigned();
            $table->foreign('city_id')->references('id')->on('cities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('venues');
    }
}
