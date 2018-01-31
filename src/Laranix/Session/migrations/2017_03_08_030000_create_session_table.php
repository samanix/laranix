<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('session.table'), function (Blueprint $table) {
            $table->string('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('ipv4')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('data');
            $table->timestamps();

            $table->primary(['id', 'ipv4']);

            $table->foreign('user_id')
                ->references('id')
                ->on(config('laranixauth.user.table'))
                ->onUpdate('set null')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('session.table'));
    }
}
