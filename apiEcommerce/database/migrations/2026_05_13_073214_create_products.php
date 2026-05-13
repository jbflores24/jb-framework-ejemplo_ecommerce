<?php

declare(strict_types=1);

use Jb\Database\Blueprint;
use Jb\Database\Connection;
use Jb\Database\Migration;

return new class (Connection::getInstance()) extends Migration {
    /**
     * Run the migration.
     */
    public function up(): void
    {
        $this->create('products', function (Blueprint $table): void {
            $table->id();
            $table->integer('category_id');
            $table->string('sku', 80)->unique();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->integer('precio_centavos');
            $table->integer('stock')->default(0);
            $table->string('imagen_url', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        $this->drop('products');
    }
};
