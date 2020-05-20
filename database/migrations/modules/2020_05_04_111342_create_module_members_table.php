<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModuleMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_members', function (Blueprint $table) {
            
            $table->bigIncrements('id');

            $table->integer('module_id')->unsigned();
            $table->foreign('module_id')->references('id')->on('modules')->onUpdate('cascade')->onDelete('cascade');
           
            $table->integer('data_type_id')->unsigned();
            $table->foreign('data_type_id')->references('id')->on('data_types')->onUpdate('cascade')->onDelete('cascade');
            
            $table->boolean('main')->default(FALSE);
            $table->boolean('modal')->default(TRUE);
            $table->boolean('show')->default(TRUE);
            $table->boolean('status')->default(TRUE);
           
 
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
        Schema::dropIfExists('module_members');
    }
}
