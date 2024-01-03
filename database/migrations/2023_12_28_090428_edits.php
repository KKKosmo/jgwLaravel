<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('Edits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('record_id');
            $table->timestamp('edit_timestamp');
            $table->string('summary', 100)->nullable();
            $table->string('user', 20);
            $table->foreign('record_id')->references('id')->on('main');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('edit');
    }
};
