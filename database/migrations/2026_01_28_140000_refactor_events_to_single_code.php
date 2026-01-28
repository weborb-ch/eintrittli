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
        // Generate new codes for existing events
        DB::table('events')->get()->each(function ($event) {
            DB::table('events')->where('id', $event->id)->update([
                'short_code' => $this->generateUniqueCode(),
            ]);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('slug');
            $table->renameColumn('short_code', 'code');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('code', 6)->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->renameColumn('code', 'short_code');
            $table->string('slug')->nullable()->after('name');
        });

        DB::table('events')->get()->each(function ($event) {
            DB::table('events')->where('id', $event->id)->update([
                'slug' => Str::slug($event->name).'-'.Str::random(6),
            ]);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = strtolower(Str::random(6));
        } while (DB::table('events')->where('short_code', $code)->exists());

        return $code;
    }
};
