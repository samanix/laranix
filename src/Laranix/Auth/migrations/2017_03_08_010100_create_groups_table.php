<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations
     */
    public function up()
    {
        Schema::create(config('laranixauth.groups.table', 'groups'), function (Blueprint $table) {
            $table->smallIncrements('group_id');
            $table->string('group_name')->unique();
            $table->string('group_color')->nullable();
            $table->string('group_icon')->nullable();
            $table->unsignedTinyInteger('group_level')->default(0);

            if (config('laranixauth.groups.use_json_column', true)) {
                $table->json('group_flags');
            } else {
                $table->text('group_flags');
            }

            $table->unsignedTinyInteger('is_hidden')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse migrations
     */
    public function down()
    {
        Schema::dropIfExists(config('laranixauth.groups.table', 'groups'));
    }
}
