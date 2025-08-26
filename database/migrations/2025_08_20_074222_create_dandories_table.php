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
        Schema::dropIfExists('dandories');

        Schema::create('dandories', function (Blueprint $table) {
            $table->id();
            $table->string('ddcnk_id')->unique();
            $table->string('customer');
            $table->string('nama_part');
            $table->string('nomor_part');
            $table->string('proses');
            $table->string('mesin');
            $table->integer('qty_pcs');
            $table->string('planning_shift')->nullable();
            $table->string('status')->default('TO DO');
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null'); 
            $table->foreignId('added_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dandories');
    }
};
