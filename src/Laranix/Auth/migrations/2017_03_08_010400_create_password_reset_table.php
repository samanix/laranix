<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreatePasswordResetTable extends Migration
{
    /**
     * Run the migrations
     */
    public function up()
    {
        Schema::create(config('laranixauth.password.table'), function (Blueprint $table) {
            $table->unsignedInteger('user_id')->unique();
            $table->string('email');
            $table->string('token');
            $table->timestamps();

            $table->index('token');
            $table->unique('email');

            $table->foreign('user_id')
                ->references('id')
                ->on(config('laranixauth.user.table'))
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse migrations
     */
    public function down()
    {
        $table = config('laranixauth.password.table');

        $userKey = "{$table}_user_id_foreign";

        Schema::table($table, function (Blueprint $table) use ($userKey) {
            $table->dropForeign($userKey);
        });

        Schema::dropIfExists($table);
    }
}
