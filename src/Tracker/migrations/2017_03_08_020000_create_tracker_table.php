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
        Schema::create(config('tracker.table', 'tracker'), function (Blueprint $table) {
            $table->increments('tracker_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('ipv4');
            $table->string('user_agent')->nullable();
            $table->string('request_method', 20)->nullable();
            $table->string('request_url', 600)->nullable();

            $table->string('tracker_type');
            $table->unsignedInteger('tracker_type_id')->nullable();
            $table->unsignedInteger('tracker_item_id')->nullable();
            $table->unsignedTinyInteger('flag_level')->default(0);
            $table->unsignedTinyInteger('trackable_type');

            $table->text('tracker_data')->nullable();
            $table->text('tracker_data_rendered')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('ipv4');
            $table->index(['tracker_type', 'tracker_type_id'], 'tracker_type_key');
            $table->index('tracker_item_id');

            $table->foreign('user_id')
                  ->references('user_id')
                  ->on(config('laranixauth.users.table', 'users'))
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse migrations
     */
    public function down()
    {
        $table = config('tracker.table', 'tracker');

        $userKey = "{$table}_user_id_foreign";

        Schema::table($table, function (Blueprint $table) use ($userKey) {
            $table->dropForeign($userKey);
        });

        Schema::dropIfExists($table);
    }
}
