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
        Schema::create(config('laranixauth.cage.table'), function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('level')->default(25);
            $table->string('area')->nullable();
            $table->unsignedInteger('length')->default(0);

            $table->text('reason');
            $table->text('reason_rendered')->nullable();

            $table->unsignedInteger('issuer_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('user_ipv4')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('area');

            $userTable = config('laranixauth.user.table');

            $table->foreign('user_id')
                ->references('id')
                ->on($userTable)
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('issuer_id')
                  ->references('id')
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
        $table = config('laranixauth.cage.table');

        $userKey = "{$table}_user_id_foreign";
        $issuerKey = "{$table}_issuer_id_foreign";

        Schema::table($table, function (Blueprint $table) use ($userKey, $issuerKey) {
            $table->dropForeign($userKey);
            $table->dropForeign($issuerKey);
        });

        Schema::dropIfExists($table);
    }
}
