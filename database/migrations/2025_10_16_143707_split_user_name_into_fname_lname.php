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
        $hasFirst = Schema::hasColumn('users', 'first_name');
        $hasLast = Schema::hasColumn('users', 'last_name');

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'fname')) {
                $table->string('fname')->nullable()->after('name');
            }

            if (!Schema::hasColumn('users', 'lname')) {
                $table->string('lname')->nullable()->after('fname');
            }
        });

        $columns = ['id', 'name'];
        if ($hasFirst) {
            $columns[] = 'first_name';
        }
        if ($hasLast) {
            $columns[] = 'last_name';
        }

        DB::table('users')
            ->select($columns)
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($hasFirst, $hasLast) {
                foreach ($users as $user) {
                    $fname = $hasFirst ? ($user->first_name ?? null) : null;
                    $lname = $hasLast ? ($user->last_name ?? null) : null;

                    if (!$fname && !$lname && $user->name) {
                        $parts = preg_split('/\s+/', trim($user->name));
                        $fname = $parts ? array_shift($parts) : null;
                        $lname = $parts ? implode(' ', $parts) : null;
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'fname' => $fname,
                            'lname' => $lname,
                        ]);
                }
            });

        if ($hasFirst && Schema::hasColumn('users', 'first_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('first_name');
            });
        }

        if ($hasLast && Schema::hasColumn('users', 'last_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('last_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasFname = Schema::hasColumn('users', 'fname');
        $hasLname = Schema::hasColumn('users', 'lname');

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('name');
            }

            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
        });

        $columns = ['id', 'name'];
        if ($hasFname) {
            $columns[] = 'fname';
        }
        if ($hasLname) {
            $columns[] = 'lname';
        }

        DB::table('users')
            ->select($columns)
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($hasFname, $hasLname) {
                foreach ($users as $user) {
                    $first = $hasFname ? ($user->fname ?? null) : null;
                    $last = $hasLname ? ($user->lname ?? null) : null;

                    if (!$first && !$last && $user->name) {
                        $parts = preg_split('/\s+/', trim($user->name));
                        $first = $parts ? array_shift($parts) : null;
                        $last = $parts ? implode(' ', $parts) : null;
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'first_name' => $first,
                            'last_name' => $last,
                        ]);
                }
            });

        if ($hasFname && Schema::hasColumn('users', 'fname')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('fname');
            });
        }

        if ($hasLname && Schema::hasColumn('users', 'lname')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('lname');
            });
        }
    }
};
