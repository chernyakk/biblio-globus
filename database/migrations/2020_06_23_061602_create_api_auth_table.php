<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiAuthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_auth', function (Blueprint $table) {
            $table->string('username', 255)->unique();
            $table->string('password');
            $table->longText('a1')->nullable();
            $table->longText('z1')->nullable();
            $table->longText('l')->nullable();
            $table->string('email')->nullable();

            $table->foreign('email')
                ->references('email')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_auth', static function ($table) {
            $table->dropForeign('api_auth_email_foreign');
        });

        Schema::dropIfExists('api_auth');
    }
}
