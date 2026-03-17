<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asientodetalletercero_direccion', function (Blueprint $table) {
            $table->id('IdRelacion');
            $table->unsignedBigInteger('IdDetalleTercero');
            $table->unsignedBigInteger('IdDireccion');
            $table->timestamps();

            $table->unique(['IdDetalleTercero', 'IdDireccion'], 'uk_detalle_direccion');
        });

        // Foreign key a asientodetalletercero
        DB::statement('
            ALTER TABLE `asientodetalletercero_direccion` 
            ADD CONSTRAINT `asientodetalletercero_direccion_iddetalletecero_foreign` 
            FOREIGN KEY (`IdDetalleTercero`) 
            REFERENCES `asientodetalletercero` (`IdDetalleTercero`) 
            ON DELETE CASCADE
        ');

        // Foreign key a tercero_direcciones
        DB::statement('
            ALTER TABLE `asientodetalletercero_direccion` 
            ADD CONSTRAINT `asientodetalletercero_direccion_iddireccion_foreign` 
            FOREIGN KEY (`IdDireccion`) 
            REFERENCES `tercero_direcciones` (`IdDireccion`) 
            ON DELETE CASCADE
        ');
    }

    public function down()
    {
        DB::statement('ALTER TABLE `asientodetalletercero_direccion` DROP FOREIGN KEY `asientodetalletercero_direccion_iddetalletecero_foreign`');
        DB::statement('ALTER TABLE `asientodetalletercero_direccion` DROP FOREIGN KEY `asientodetalletercero_direccion_iddireccion_foreign`');
        Schema::dropIfExists('asientodetalletercero_direccion');
    }
};