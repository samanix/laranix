<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations
     */
    public function up()
    {
        Schema::create(config('laranixauth.user.table'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('username', 64)->unique();
            $table->string('avatar')->nullable();
            $table->string('first_name', 64);
            $table->string('last_name', 64);
            $table->string('password');
            $table->string('company', 64)->nullable();
            $table->string('timezone')->default('UTC');
            $table->unsignedTinyInteger('account_status')->default(0);
            $table->rememberToken();
            $table->string('api_token')->nullable();
            $table->timestamp('last_login')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();
        });
    }

    /**
     * Reverse migrations
     */
    public function down()
    {
        Schema::dropIfExists(config('laranixauth.user.table'));
    }
}
