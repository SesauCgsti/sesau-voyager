<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('data_type_id')->unsigned()->nullable();
            $table->foreign('data_type_id')->references('id')->on('data_types')->onUpdate('cascade')->onDelete('set null');
          
            $table->string('template')->nullable();
            $table->string('name'); 
            $table->date('initial_date')->nullable(); //initial_date
            $table->string('responsible')->nullable();            
            $table->string('developer')->nullable();
            $table->string('folder')->nullable();
            $table->string('link')->nullable();
            $table->string('icone')->nullable();
            $table->string('color')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->string('image')->nullable();
            $table->boolean('production')->default(FALSE);
            $table->boolean('status')->default(TRUE);  
            $table->text('abstract')->nullable();
            $table->text('description')->nullable();
            $table->text('note')->nullable();
            
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
                  
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
        Schema::dropIfExists('services');
    }
}
