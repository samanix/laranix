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
            $table->smallIncrements('id');
            $table->string('name')->unique();
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedTinyInteger('level')->default(0);

            if (config('laranixauth.groups.use_json_column', true)) {
                $table->json('flags');
            } else {
                $table->text('flags');
            }

            $table->unsignedTinyInteger('hidden')->default(0);

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
