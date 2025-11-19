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
        Schema::create('pix', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subadquirente', 50);
            $table->string('pix_id')->nullable()->index();
            $table->string('transaction_id')->nullable()->index();
            $table->decimal('amount', 12, 2);
            $table->string('status', 30)->index();
            $table->string('payer_name')->nullable();
            $table->string('payer_document', 20)->nullable();
            $table->string('description')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->text('qr_code')->nullable();
            $table->longText('qr_code_base64')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'subadquirente']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pix');
    }
};
