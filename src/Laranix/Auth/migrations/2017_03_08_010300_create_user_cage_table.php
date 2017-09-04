<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateUserCageTable extends Migration
{
    /**
     * Run the migrations
     */
    public function up()
    {
        Schema::create(config('laranixauth.cage.table', 'user_cage'), function (Blueprint $table) {
            $table->increments('cage_id');
            $table->unsignedTinyInteger('cage_level')->default(25);
            $table->string('cage_area')->nullable();
            $table->unsignedInteger('cage_time')->default(0);

            $table->text('cage_reason');
            $table->text('cage_reason_rendered')->nullable();

            $table->unsignedInteger('issuer_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('user_ipv4')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('cage_area');

            $userTable = config('laranixauth.users.table', 'users');

            $table->foreign('user_id')
                ->references('user_id')
                ->on($userTable)
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('issuer_id')
                  ->references('user_id')
                  ->on($userTable)
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse migrations
     */
    public function down()
    {
        $table = config('laranixauth.cage.table', 'user_cage');

        $userKey = "{$table}_user_id_foreign";
        $issuerKey = "{$table}_issuer_id_foreign";

        Schema::table($table, function (Blueprint $table) use ($userKey, $issuerKey) {
            $table->dropForeign($userKey);
            $table->dropForeign($issuerKey);
        });

        Schema::dropIfExists($table);
    }
}
