<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tercero_contactos', function (Blueprint $table) {
            $table->id('IdContacto');
            $table->unsignedBigInteger('IdTercero');
            $table->string('NombreContacto', 100);
            $table->string('Cargo', 100)->nullable();
            $table->string('Email', 100)->nullable();
            $table->string('Telefono', 20)->nullable();
            $table->enum('TipoContacto', ['Principal', 'Facturación', 'Cobros', 'Soporte', 'Otro'])->default('Otro');
            $table->boolean('Estado')->default(true);
            $table->timestamps();
        });

        DB::statement('
            ALTER TABLE `tercero_contactos` 
            ADD CONSTRAINT `tercero_contactos_idtercero_foreign` 
            FOREIGN KEY (`IdTercero`) 
            REFERENCES `catalogoterceros` (`IdTercero`) 
            ON DELETE CASCADE
        ');
    }

    public function down()
    {
        DB::statement('ALTER TABLE `tercero_contactos` DROP FOREIGN KEY `tercero_contactos_idtercero_foreign`');
        Schema::dropIfExists('tercero_contactos');
    }
};