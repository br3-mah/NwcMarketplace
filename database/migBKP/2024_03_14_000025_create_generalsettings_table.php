<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE `generalsettings` (
  `id` bigint(20) NOT NULL,
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `favicon` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `colors` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loader` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin_loader` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_talkto` tinyint(1) NOT NULL DEFAULT 1,
  `talkto` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_language` tinyint(1) NOT NULL DEFAULT 1,
  `is_loader` tinyint(1) NOT NULL DEFAULT 1,
  `is_disqus` tinyint(1) NOT NULL DEFAULT 0,
  `disqus` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guest_checkout` tinyint(1) NOT NULL DEFAULT 0,
  `currency_format` tinyint(1) NOT NULL DEFAULT 0,
  `withdraw_fee` double NOT NULL DEFAULT 0,
  `withdraw_charge` double NOT NULL DEFAULT 0,
  `shipping_cost` double NOT NULL DEFAULT 0,
  `mail_driver` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_host` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_port` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_encryption` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_user` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_pass` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_smtp` tinyint(1) NOT NULL DEFAULT 0,
  `is_comment` tinyint(1) NOT NULL DEFAULT 1,
  `is_currency` tinyint(1) NOT NULL DEFAULT 1,
  `is_affilate` tinyint(1) NOT NULL DEFAULT 1,
  `affilate_charge` int(100) NOT NULL DEFAULT 0,
  `affilate_banner` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fixed_commission` double NOT NULL DEFAULT 0,
  `percentage_commission` double NOT NULL DEFAULT 0,
  `multiple_shipping` tinyint(1) NOT NULL DEFAULT 0,
  `multiple_packaging` tinyint(4) NOT NULL DEFAULT 0,
  `vendor_ship_info` tinyint(1) NOT NULL DEFAULT 0,
  `reg_vendor` tinyint(1) NOT NULL DEFAULT 0,
  `footer_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `copyright_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `copyright` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_admin_loader` tinyint(1) NOT NULL DEFAULT 0,
  `is_verification_email` tinyint(1) NOT NULL DEFAULT 0,
  `wholesell` int(191) NOT NULL DEFAULT 0,
  `is_capcha` tinyint(1) NOT NULL DEFAULT 0,
  `capcha_secret_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capcha_site_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `error_banner_404` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `error_banner_500` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_popup` tinyint(1) NOT NULL DEFAULT 0,
  `popup_background` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `breadcrumb_banner` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_secure` tinyint(1) NOT NULL DEFAULT 0,
  `is_report` tinyint(1) NOT NULL,
  `footer_logo` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_stock` tinyint(1) NOT NULL DEFAULT 0,
  `is_maintain` tinyint(1) NOT NULL DEFAULT 0,
  `header_color` enum('light','dark') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'light',
  `maintain_text` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_buy_now` tinyint(4) NOT NULL,
  `version` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `affilate_product` tinyint(1) NOT NULL DEFAULT 1,
  `verify_product` tinyint(1) NOT NULL DEFAULT 0,
  `page_count` int(11) NOT NULL DEFAULT 0,
  `flash_count` int(11) NOT NULL DEFAULT 0,
  `hot_count` int(11) NOT NULL DEFAULT 0,
  `new_count` int(11) NOT NULL DEFAULT 0,
  `sale_count` int(11) NOT NULL DEFAULT 0,
  `best_seller_count` int(11) NOT NULL DEFAULT 0,
  `popular_count` int(11) NOT NULL DEFAULT 0,
  `top_rated_count` int(11) NOT NULL DEFAULT 0,
  `big_save_count` int(11) NOT NULL DEFAULT 0,
  `trending_count` int(11) NOT NULL DEFAULT 0,
  `seller_product_count` int(11) NOT NULL DEFAULT 0,
  `wishlist_count` int(11) NOT NULL DEFAULT 0,
  `vendor_page_count` int(11) NOT NULL DEFAULT 0,
  `min_price` double NOT NULL DEFAULT 0,
  `max_price` double NOT NULL DEFAULT 0,
  `post_count` tinyint(1) NOT NULL DEFAULT 0,
  `product_page` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wishlist_page` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_contact_seller` tinyint(1) NOT NULL DEFAULT 0,
  `is_debug` tinyint(1) NOT NULL DEFAULT 0,
  `decimal_separator` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thousand_separator` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_cookie` tinyint(1) NOT NULL DEFAULT 0,
  `product_affilate` tinyint(1) NOT NULL DEFAULT 0,
  `product_affilate_bonus` int(10) NOT NULL DEFAULT 0,
  `is_reward` int(11) NOT NULL DEFAULT 0,
  `reward_point` int(11) NOT NULL DEFAULT 0,
  `reward_dolar` int(11) NOT NULL DEFAULT 0,
  `physical` tinyint(4) NOT NULL DEFAULT 1,
  `digital` tinyint(4) NOT NULL DEFAULT 1,
  `license` tinyint(4) NOT NULL DEFAULT 1,
  `affilite` tinyint(4) NOT NULL DEFAULT 1,
  `partner_title` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `partner_text` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deal_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deal_details` varchar(600) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deal_time` date DEFAULT NULL,
  `deal_background` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `generalsettings`
  ADD PRIMARY KEY (`id`);
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `generalsettings`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('generalsettings');
    }
};
