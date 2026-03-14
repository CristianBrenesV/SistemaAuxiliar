<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tercero_direcciones', function (Blueprint $table) {
            $table->id('IdDireccion');
            $table->unsignedBigInteger('IdTercero');
            $table->string('Alias', 100);
            $table->string('Provincia', 50);
            $table->string('Canton', 50);
            $table->string('Distrito', 50);
            $table->text('DireccionExacta');
            $table->boolean('EsPrincipal')->default(false);
            $table->boolean('Estado')->default(true);
            $table->timestamps();

            // NO crear la foreign key aquí, la crearemos después con DB::statement
            $table->index(['IdTercero', 'EsPrincipal']);
        });

        // Crear la foreign key usando SQL directo (esto funciona con tablas existentes)
        DB::statement('
            ALTER TABLE `tercero_direcciones` 
            ADD CONSTRAINT `tercero_direcciones_idtercero_foreign` 
            FOREIGN KEY (`IdTercero`) 
            REFERENCES `catalogoterceros` (`IdTercero`) 
            ON DELETE CASCADE
        ');
    }

    public function down()
    {
        // Eliminar la foreign key primero
        DB::statement('ALTER TABLE `tercero_direcciones` DROP FOREIGN KEY `tercero_direcciones_idtercero_foreign`');
        Schema::dropIfExists('tercero_direcciones');
    }
};