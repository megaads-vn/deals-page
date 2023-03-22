<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableDeals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('deals')) {
            Schema::table('deals', function(Blueprint $table) {
                $table->enum('crawl_image', ['done', 'processing'])->default('processing');
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
        if (Schema::hasTable('deals')) {
            Schema::table('deals', function(Blueprint $table) {
                $table->dropColumn('crawl_image');
            });
        }
    }
}
