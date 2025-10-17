<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE `pagesettings` (
  `id` int(10) UNSIGNED NOT NULL,
  `contact_email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `street` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fax` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `best_seller_banner` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `best_seller_banner_link` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `big_save_banner` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `big_save_banner_link` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `best_seller_banner1` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `best_seller_banner_link1` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `big_save_banner1` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `big_save_banner_link1` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rightbanner1` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rightbanner2` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rightbannerlink1` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rightbannerlink2` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `home` tinyint(1) NOT NULL DEFAULT 0,
  `blog` tinyint(1) NOT NULL DEFAULT 0,
  `faq` tinyint(1) NOT NULL DEFAULT 0,
  `contact` tinyint(1) NOT NULL DEFAULT 0,
  `category` tinyint(1) NOT NULL DEFAULT 0,
  `arrival_section` tinyint(1) NOT NULL DEFAULT 1,
  `our_services` tinyint(1) NOT NULL DEFAULT 1,
  `second_left_banner` tinyint(1) NOT NULL DEFAULT 1,
  `popular_products` tinyint(1) NOT NULL DEFAULT 1,
  `third_left_banner` tinyint(1) NOT NULL DEFAULT 1,
  `slider` tinyint(1) NOT NULL DEFAULT 0,
  `flash_deal` tinyint(1) NOT NULL DEFAULT 1,
  `deal_of_the_day` tinyint(1) NOT NULL DEFAULT 1,
  `best_sellers` tinyint(1) NOT NULL DEFAULT 1,
  `partner` tinyint(1) NOT NULL DEFAULT 1,
  `top_big_trending` tinyint(1) NOT NULL DEFAULT 0,
  `top_brand` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `pagesettings`
  ADD PRIMARY KEY (`id`);
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `pagesettings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('pagesettings');
    }
};
