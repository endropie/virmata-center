<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->text('address')->nullable();
            $table->foreignIdFor(\App\Models\TenantType::class);
            $table->json('data')->nullable();
            $table->foreignUuid('owner_uid');
            $table->timestamps();
            
            $table->foreign('tenant_type_id')->on('tenant_types')->references('id')->restrictOnDelete();
            $table->foreign('owner_uid')->on('users')->references('id')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
