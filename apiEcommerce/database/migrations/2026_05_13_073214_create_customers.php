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
        $this->create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 150);
            $table->string('email', 180)->unique();
            $table->string('telefono', 40)->nullable();
            $table->text('direccion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        $this->drop('customers');
    }
};
