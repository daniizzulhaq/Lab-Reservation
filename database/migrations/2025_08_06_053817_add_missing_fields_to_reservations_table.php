<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('reservations', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('notes');
            }
            
            if (!Schema::hasColumn('reservations', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->after('admin_notes');
            }
            
            if (!Schema::hasColumn('reservations', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            
            if (!Schema::hasColumn('reservations', 'reservation_code')) {
                $table->string('reservation_code')->unique()->nullable()->after('id');
            }
            
            // Make sure status column exists and has proper enum values
            if (Schema::hasColumn('reservations', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'completed'])->default('pending')->change();
            } else {
                $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'completed'])->default('pending')->after('purpose');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['admin_notes', 'approved_by', 'approved_at', 'reservation_code']);
        });
    }
};