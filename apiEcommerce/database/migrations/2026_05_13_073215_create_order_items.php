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
        $this->create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->integer('order_id');
            $table->integer('product_id');
            $table->integer('cantidad');
            $table->integer('precio_unitario_centavos');
            $table->integer('subtotal_centavos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        $this->drop('order_items');
    }
};
