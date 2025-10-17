<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE `arrival_sections` (
  `id` int(5) NOT NULL,
  `title` varchar(500) NOT NULL,
  `header` varchar(500) NOT NULL,
  `photo` varchar(300) NOT NULL,
  `status` tinyint(5) NOT NULL DEFAULT 0,
  `position` tinyint(5) NOT NULL DEFAULT 0,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `updated_at` timestamp(6) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `arrival_sections`
  ADD PRIMARY KEY (`id`);
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `arrival_sections`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('arrival_sections');
    }
};
