<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('session.table', 'sessions'), function (Blueprint $table) {
            $table->string('session_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('ipv4')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('session_data');
            $table->timestamps();

            $table->primary(['session_id', 'ipv4']);

            $table->index('user_id');

            $table->foreign('user_id')
                ->references('user_id')
                ->on(config('laranixauth.users.table', 'users'))
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
        Schema::dropIfExists(config('session.table', 'sessions'));
    }
}
