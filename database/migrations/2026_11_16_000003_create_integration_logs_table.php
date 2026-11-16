<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 30);
            $table->string('type', 20);
            $table->string('event', 50);
            $table->string('external_id', 100)->nullable();
            $table->string('status', 20)->default('received');
            $table->json('payload')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['provider', 'external_id']);
            $table->index(['type', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
    }
};
