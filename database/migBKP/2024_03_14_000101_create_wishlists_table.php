<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE `wishlists` (
  `id` int(191) UNSIGNED NOT NULL,
  `user_id` int(191) UNSIGNED NOT NULL,
  `product_id` int(191) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`);
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `wishlists`
  MODIFY `id` int(191) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
