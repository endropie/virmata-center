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
        Schema::create('tenant_invites', function (Blueprint $table) {
            $table->id();
            $table->string('context');
            $table->string('token', 64)->unique();
            $table->foreignIdFor(\App\Models\Tenant::class);
            $table->enum('level', ['operator', 'administrator', 'visitor'])->nullable();
            $table->enum('confirm', ['accepted', 'rejected'])->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignUuid('created_uid');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('tenant_id')->on('tenants')->references('id')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_invites');
    }
};
