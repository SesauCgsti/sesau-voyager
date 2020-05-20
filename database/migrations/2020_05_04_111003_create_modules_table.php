<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('data_type_id')->unsigned();
            $table->foreign('data_type_id')->references('id')->on('data_types')->onUpdate('cascade')->onDelete('cascade');
           
            $table->string('template'); 
            $table->string('name'); 
            $table->string('icon')->nullable();
            $table->string('slug')->unique();
            $table->string('module_id')->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->default(TRUE);
            $table->boolean('service')->default(TRUE);
            $table->text('description')->nullable();
          
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
        Schema::dropIfExists('modules');
    }
}
