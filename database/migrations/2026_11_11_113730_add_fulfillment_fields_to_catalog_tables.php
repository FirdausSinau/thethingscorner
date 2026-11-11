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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('weight_grams')->default(0)->after('base_price');
            $table->decimal('rating_avg', 3, 2)->default(0)->after('estimated_ready_date');
            $table->unsignedInteger('rating_count')->default(0)->after('rating_avg');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedInteger('stock_reserved')->default(0)->after('stock');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE product_variants ADD CONSTRAINT product_variants_stock_non_negative CHECK (stock >= 0)');
            DB::statement('ALTER TABLE product_variants ADD CONSTRAINT product_variants_stock_reserved_non_negative CHECK (stock_reserved >= 0)');
            DB::statement('ALTER TABLE products ADD CONSTRAINT products_rating_avg_range CHECK (rating_avg >= 0 AND rating_avg <= 5)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE product_variants DROP CONSTRAINT IF EXISTS product_variants_stock_non_negative');
            DB::statement('ALTER TABLE product_variants DROP CONSTRAINT IF EXISTS product_variants_stock_reserved_non_negative');
            DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_rating_avg_range');
        }

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('stock_reserved');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['weight_grams', 'rating_avg', 'rating_count']);
        });
    }
};
