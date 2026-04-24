<?php

namespace App\Helpers;

use App\Models\Setting;

class VisibilityHelper
{
    public static function defaults(): array
    {
        return [
            'home_page' => 1,
            'shop_page' => 1,
            'product_details_page' => 1,
            'campaign_page' => 1,
            'brands_page' => 1,
            'blog_page' => 1,
            'blog_details_page' => 1,
            'faq_page' => 1,
            'faq_details_page' => 1,
            'contact_page' => 1,
            'compare_page' => 1,
            'cart_page' => 1,
            'checkout_page' => 1,
            'checkout_success_page' => 1,
            'track_order_page' => 1,
            'custom_page' => 1,
            'login_page' => 1,
            'register_page' => 1,
            'global_topbar' => 1,
            'global_header_search' => 1,
            'global_compare_button' => 1,
            'global_wishlist_button' => 1,
            'global_cart_button' => 1,
            'global_category_menu' => 1,
            'global_announcement_popup' => 1,
            'global_footer' => 1,
            'global_footer_contact' => 1,
            'global_footer_links' => 1,
            'global_footer_newsletter' => 1,
            'shop_breadcrumb' => 1,
            'shop_toolbar' => 1,
            'shop_products' => 1,
            'shop_sidebar' => 1,
            'product_breadcrumb' => 1,
            'product_gallery' => 1,
            'product_summary' => 1,
            'product_description' => 1,
            'product_reviews' => 1,
            'product_related_products' => 1,
            'campaign_breadcrumb' => 1,
            'campaign_products' => 1,
            'brands_breadcrumb' => 1,
            'brands_list' => 1,
            'blog_breadcrumb' => 1,
            'blog_posts' => 1,
            'blog_sidebar' => 1,
            'blog_details_breadcrumb' => 1,
            'blog_details_content' => 1,
            'blog_details_sidebar' => 1,
            'faq_breadcrumb' => 1,
            'faq_categories' => 1,
            'faq_details_breadcrumb' => 1,
            'faq_details_content' => 1,
            'contact_breadcrumb' => 1,
            'contact_info' => 1,
            'contact_form' => 1,
            'compare_breadcrumb' => 1,
            'compare_table' => 1,
            'cart_breadcrumb' => 1,
            'cart_table' => 1,
            'cart_summary' => 1,
            'checkout_breadcrumb' => 1,
            'checkout_billing_form' => 1,
            'checkout_order_summary' => 1,
            'checkout_payment_methods' => 1,
            'checkout_success_content' => 1,
            'track_order_breadcrumb' => 1,
            'track_order_form' => 1,
            'page_breadcrumb' => 1,
            'page_content' => 1,
        ];
    }

    public static function map($setting = null): array
    {
        if (! $setting) {
            $setting = Setting::find(1);
        }

        $saved = [];
        $rawValue = $setting->page_section_visibility ?? null;

        if (is_array($rawValue)) {
            $saved = $rawValue;
        } elseif (is_string($rawValue) && $rawValue !== '') {
            $decoded = json_decode($rawValue, true);
            if (is_array($decoded)) {
                $saved = $decoded;
            }
        }

        return array_map(static function ($value) {
            return (int) $value;
        }, array_merge(static::defaults(), $saved));
    }

    public static function isEnabled(string $key, bool $default = true, $setting = null): bool
    {
        $map = static::map($setting);

        if (! array_key_exists($key, $map)) {
            return $default;
        }

        return (int) $map[$key] === 1;
    }
}
