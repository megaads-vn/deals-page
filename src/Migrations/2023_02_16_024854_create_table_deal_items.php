<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDealItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::hasTable('deals') ) {
            Schema::create('deals', function (Blueprint $table) {
               $table->increments('id');
               $table->string('title')->nullable();
               $table->string('slug')->nullable();
               $table->string('search_slug')->nullable();
               $table->float('price')->nullable()->default(0);
               $table->float('sale_price')->nullable()->default(0);
               $table->float('discount')->nullable()->default(0);
               $table->string('image')->nullable();
               $table->string('content')->nullable();
               $table->integer('store_id')->nullable();
               $table->bigInteger('clicks')->nullable();
               $table->timestamp('expire_time')->nullable();
               $table->string('affiliate_link', 1000)->nullable();
               $table->string('origin_link', 1000)->nullable();
               $table->integer('sorder')->nullable();
               $table->integer('sorder_in_category')->nullable();
               $table->bigInteger('views')->nullable();
               $table->integer('vote_up')->nullable();
               $table->integer('vote_down')->nullable();
               $table->string('meta_title')->nullable();
               $table->string('meta_description')->nullable();
               $table->string('meta_keywords')->nullable();
               $table->string('currency')->nullable();
               $table->string('crawl_id')->nullable();
               $table->string('category_id')->nullable();
               $table->string('mpn')->nullable();
               $table->string('sku')->nullable();
               $table->tinyInteger('in_stock')->nullable()->default(1);
               $table->enum('status', ['active','pending','delete','future','unreliable'])->default('active');
               $table->string('type', 20)->default('DEAL')->nullable();
               $table->string('code', 50)->nullable();
               $table->timestamp('publish_time')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
               $table->timestamp('create_time')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
               $table->timestamp('update_time')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
               $table->integer('creator_id')->nullable();
               $table->string('creator_name')->nullable();
               $table->integer('modifier_id')->nullable();
               $table->string('modifier_name')->nullable();

                $table->index(['expire_time']);
                $table->index(['sorder']);
                $table->index(['discount']);
                $table->index(['publish_time']);
            });

            DB::statement(
                'ALTER TABLE `deals` ADD INDEX `search_slug` (`search_slug`) using BTREE;');
            DB::statement('ALTER TABLE `deals` ADD INDEX `search_status` (`status`) using BTREE;');
            DB::statement('ALTER TABLE `deals` ADD INDEX `search_storeId` (`store_id`) using BTREE;');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deals');
    }
}
