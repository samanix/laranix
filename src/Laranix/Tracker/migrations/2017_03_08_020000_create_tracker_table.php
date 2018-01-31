<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTrackerTable extends Migration
{
    /**
     * Run the migrations
     */
    public function up()
    {
        Schema::create(config('tracker.table'), function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('ipv4');
            $table->string('user_agent')->nullable();
            $table->string('request_method', 20)->nullable();
            $table->string('request_url', 600)->nullable();

            $table->string('type');
            $table->unsignedInteger('type_id')->nullable();
            $table->unsignedInteger('item_id')->nullable();
            $table->unsignedTinyInteger('level')->default(0);
            $table->unsignedTinyInteger('trackable_type');

            $table->text('data')->nullable();
            $table->text('data_rendered')->nullable();

            $table->timestamps();

            $table->index('ipv4');
            $table->index(['type', 'type_id'], 'tracker_type_key');
            $table->index('item_id');

            $table->foreign('user_id')
                  ->references('id')
                  ->on(config('laranixauth.user.table'))
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse migrations
     */
    public function down()
    {
        $table = config('tracker.table');

        $userKey = "{$table}_user_id_foreign";

        Schema::table($table, function (Blueprint $table) use ($userKey) {
            $table->dropForeign($userKey);
        });

        Schema::dropIfExists($table);
    }
}
