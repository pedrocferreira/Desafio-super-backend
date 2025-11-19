<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pix_id')->nullable()->constrained('pix')->cascadeOnDelete();
            $table->foreignId('withdraw_id')->nullable()->constrained('withdraws')->cascadeOnDelete();
            $table->string('subadquirente', 50);
            $table->string('event_type', 100);
            $table->json('payload');
            $table->boolean('processed')->default(false);
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();

            $table->index(['subadquirente', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
