<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('identifier');
            $table->String('type')->nullable();
            $table->Integer('duration')->default(1);
            $table->decimal('price',8,2)->default(0);
            $table->decimal('tax',8,2)->default(0);
            $table->decimal('total_price',8,2)->default(0);
            $table->string('currency')->default('inr');
            $table->longText('permission_ids')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('status')->default(1);
            $table->integer('max_appointment')->default(0);
            $table->integer('max_branch')->default(0);
            $table->integer('max_service')->default(0);
            $table->integer('max_staff')->default(0);
            $table->integer('max_customer')->default(0);
            $table->boolean('is_free_plan')->default(0);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('plan');
    }
};
