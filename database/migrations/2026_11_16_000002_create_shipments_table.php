<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->string('provider', 30)->default('fake');
            $table->string('courier', 30);
            $table->string('service', 50);
            $table->string('provider_reference', 100)->nullable()->index();
            $table->string('tracking_number', 100)->nullable()->unique();
            $table->unsignedInteger('cost')->default(0);
            $table->unsignedInteger('weight_grams')->default(0);
            $table->string('status', 20)->default('PENDING');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE shipments ADD CONSTRAINT shipments_weight_non_negative CHECK (weight_grams >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
