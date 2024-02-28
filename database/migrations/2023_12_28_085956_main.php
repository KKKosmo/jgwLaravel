<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('main', function (Blueprint $table) {
            $table->id();
            $table->timestamp('dateInserted');
            $table->string('name', 50);
            $table->integer('pax');
            $table->integer('vehicle');
            $table->boolean('pets');
            $table->boolean('videoke');
            $table->decimal('partial_payment', 6, 2);
            $table->decimal('full_payment', 6, 2);
            $table->boolean('paid');
            $table->date('checkIn');
            $table->date('checkOut');
            $table->string('room', 20);
            $table->string('user', 20);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('main');
    }
};
