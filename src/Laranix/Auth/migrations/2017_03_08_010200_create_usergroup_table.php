<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateUserGroupTable extends Migration
{
    /**
     * Run the migrations
     */
    public function up()
    {
        Schema::create(config('laranixauth.usergroup.table'), function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedSmallInteger('group_id');
            $table->unsignedTinyInteger('primary')->default(0);
            $table->unsignedTinyInteger('hidden')->default(0);

            $table->timestamps();

            $table->primary(['user_id', 'group_id']);

            $table->foreign('user_id')
                ->references('id')
                ->on(config('laranixauth.user.table'))
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('group_id')
                ->references('id')
                ->on(config('laranixauth.group.table'))
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse migrations
     */
    public function down()
    {
        $table = config('laranixauth.usergroup.table');

        $userKey = "{$table}_user_id_foreign";
        $groupKey = "{$table}_group_id_foreign";

        Schema::table($table, function (Blueprint $table) use ($userKey, $groupKey) {
            $table->dropForeign($userKey);
            $table->dropForeign($groupKey);
        });

        Schema::dropIfExists($table);
    }
}
