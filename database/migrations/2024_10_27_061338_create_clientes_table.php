<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
        public function up()
        {
            Schema::create('clientes', function (Blueprint $table) {
                $table->id();
                $table->string('nome_completo');
                $table->string('cpf')->unique();
                $table->date('data_nascimento');
                $table->string('rua');
                $table->string('bairro');
                $table->string('cidade');
                $table->string('estado');
                $table->string('complemento')->nullable();
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
        Schema::dropIfExists('clientes');
    }
}
