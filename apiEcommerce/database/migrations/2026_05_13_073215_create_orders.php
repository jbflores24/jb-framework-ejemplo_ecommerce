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
        $this->create('orders', function (Blueprint $table): void {
            $table->id();
            $table->integer('customer_id');
            $table->string('status', 30)->default('created');
            $table->string('currency', 3)->default('MXN');
            $table->integer('total_centavos')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        $this->drop('orders');
    }
};
