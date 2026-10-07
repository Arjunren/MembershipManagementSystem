<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration_months');
            $table->decimal('price', 12, 2);
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('membership_plan_id')->constrained()->restrictOnDelete();
            $table->date('starts_on')->index();
            $table->date('ends_on')->index();
            $table->decimal('price', 12, 2);
            $table->string('status', 20)->default('active')->index();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('membership_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('method', 20);
            $table->foreignId('received_by')->constrained('users');
            $table->timestamp('paid_at');
            $table->timestamps();
        });

        Schema::create('check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('membership_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checked_in_by')->constrained('users');
            $table->date('check_in_date');
            $table->timestamp('checked_in_at');
            $table->timestamps();
            $table->unique(['member_id', 'check_in_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_ins');
        Schema::dropIfExists('membership_payments');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('membership_plans');
    }
};
