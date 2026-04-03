<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->string('order_id')->primary();
            $table->string('customer_id');
            $table->string('item_id');
            $table->unsignedInteger('quantity');
            $table->string('category');
            $table->boolean('requires_loaders')->default(false);
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('order_details_view', function (Blueprint $table) {
            $table->string('order_id')->primary();
            $table->string('customer_id');
            $table->string('item_id');
            $table->unsignedInteger('quantity');
            $table->string('category');
            $table->boolean('requires_loaders')->default(false);
            $table->string('status');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('order_list_view', function (Blueprint $table) {
            $table->string('order_id')->primary();
            $table->string('customer_id');
            $table->string('category');
            $table->string('status');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('domain_events', function (Blueprint $table) {
            $table->id();
            $table->string('aggregate_id');
            $table->string('event_name');
            $table->string('event_class');
            $table->json('payload');
            $table->timestamp('occurred_at');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('external_service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('order_id');
            $table->string('service_name');
            $table->string('request_type');
            $table->json('payload');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_service_requests');
        Schema::dropIfExists('domain_events');
        Schema::dropIfExists('order_list_view');
        Schema::dropIfExists('order_details_view');
        Schema::dropIfExists('orders');
    }
};
