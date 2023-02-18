<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCatalogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('catalogs')) {
            Schema::create('catalogs', function (Blueprint  $table) {
                $table->increments('id');
                $table->string('cid')->nullable();
                $table->string('name')->nullable();
                $table->string('slug')->nullable();
                $table->string('url')->nullable();
                $table->string('advertiser')->nullable();
                $table->string('country')->nullable();
                $table->string('currency')->nullable();
                $table->string('crawl_page')->default(0)->nullable();
                $table->string('crawl_state')->default('processing')->nullable();
                $table->timestamp('create_time')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                $table->timestamp('update_time')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

                $table->index(['cid']);
                $table->index(['slug']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalogs');
    }
}
