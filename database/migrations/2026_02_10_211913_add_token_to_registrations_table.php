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
        Schema::table('registrations', function (Blueprint $table) {
            $table->uuid('registration_group_id')->nullable()->after('confirmation_code');
        });

        DB::table('registrations')->whereNull('registration_group_id')->update([
            'registration_group_id' => DB::raw('gen_random_uuid()'),
        ]);

        Schema::table('registrations', function (Blueprint $table) {
            $table->uuid('registration_group_id')->nullable(false)->index()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn('registration_group_id');
        });
    }
};
