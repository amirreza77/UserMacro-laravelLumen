<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mycms_user', function (Blueprint $table) {

            $table->integer('id', 11)->unique();
            $table->string('name', 225);
            $table->string('username', 255)->unique();
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->string('cellphone', 225)->unique();
            $table->text('address');
            $table->text('avatar')->nullable('true');
            $table->integer('valid')->default('0');
            $table->text('register')->useCurrent();
            $table->integer('vcode')->nullable('true');
        });
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
