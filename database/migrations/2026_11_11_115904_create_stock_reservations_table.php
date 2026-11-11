<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->unsignedInteger('qty');
            $table->timestamp('expires_at');
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['expires_at', 'status']);
            $table->index('order_id');
            $table->index('variant_id');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE stock_reservations ADD CONSTRAINT stock_reservations_qty_positive CHECK (qty > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
