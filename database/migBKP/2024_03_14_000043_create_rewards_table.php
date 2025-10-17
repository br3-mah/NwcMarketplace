<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE `rewards` (
  `id` bigint(20) NOT NULL,
  `order_amount` double NOT NULL DEFAULT 0,
  `reward` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `rewards`
  ADD PRIMARY KEY (`id`);
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `rewards`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
