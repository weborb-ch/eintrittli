<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('short_code', 8)->nullable()->after('slug');
        });

        DB::table('events')->whereNull('short_code')->get()->each(function ($event) {
            DB::table('events')->where('id', $event->id)->update([
                'short_code' => strtoupper(Str::random(6)),
            ]);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('short_code', 8)->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('short_code');
        });
    }
};
