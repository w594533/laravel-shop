<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no', 100);
            $table->enum('type', ['reduction', 'discount']);
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('offer', 10, 2)->default(0);
            $table->integer('total')->default(1)->comment('可供使用的数量');
            $table->interget('used')->default(0)->comment('已使用');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupon_codes');
    }
}
