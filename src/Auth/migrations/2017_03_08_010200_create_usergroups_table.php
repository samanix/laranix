<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateUserGroupsTable extends Migration
{
    /**
     * Run the migrations
     */
    public function up()
    {
        Schema::create(config('laranixauth.usergroups.table', 'usergroups'), function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedSmallInteger('group_id');
            $table->unsignedTinyInteger('is_primary')->default(0);
            $table->unsignedTinyInteger('is_hidden')->default(0);

            $table->timestamps();

            $table->primary(['user_id', 'group_id']);

            $table->foreign('user_id')
                ->references('user_id')
                ->on(config('laranixauth.users.table', 'users'))
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('group_id')
                ->references('group_id')
                ->on(config('laranixauth.groups.table', 'groups'))
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse migrations
     */
    public function down()
    {
        $table = config('laranixauth.usergroups.table', 'usergroups');

        $userKey = "{$table}_user_id_foreign";
        $groupKey = "{$table}_group_id_foreign";

        Schema::table($table, function (Blueprint $table) use ($userKey, $groupKey) {
            $table->dropForeign($userKey);
            $table->dropForeign($groupKey);
        });

        Schema::dropIfExists($table);
    }
}
