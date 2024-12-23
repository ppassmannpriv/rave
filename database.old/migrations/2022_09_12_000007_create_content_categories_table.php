<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentCategoriesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('content_categories') === false) {
            Schema::create('content_categories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->nullable();
                $table->string('slug')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }
}
