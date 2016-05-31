<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CaptchaRecord extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('captcha_records', function(Blueprint $table){
            $table->increments('id');
            $table->uuid('uuid')->unique()->index();
            $table->string('captcha_string');
            $table->string('imageUrl');
            $table->enum('status',['new', 'used'])->default('new');
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
        Schema::drop('captcha_records');
    }
}
