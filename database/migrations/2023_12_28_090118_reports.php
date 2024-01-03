<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('Reports', function (Blueprint $table) {
            $table->id();
            $table->timestamp('dateInserted');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('report');
    }
};
