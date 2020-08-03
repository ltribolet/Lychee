<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToUsersTable extends Migration
{
    private const USER_TYPE = 'user';

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('type')->default(self::USER_TYPE);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('votes');
        });
    }
}
