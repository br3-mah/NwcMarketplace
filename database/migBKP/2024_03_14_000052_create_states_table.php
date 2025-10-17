<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE `states` (
  `id` bigint(20) NOT NULL,
  `country_id` int(11) NOT NULL DEFAULT 0,
  `state` varchar(111) DEFAULT NULL,
  `tax` double NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 1,
  `owner_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`);
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `states`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};
