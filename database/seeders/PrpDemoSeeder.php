<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PrpDemoSeeder extends Seeder
{
    private string $img = 'assets/images/prp-demo/';

    public function run(): void
    {
        DB::transaction(function () {
            $now = Carbon::now();

            // 1) Basic site setting update. Change phone/email/address before client demo.
            DB::table('settings')->updateOrInsert(
                ['id' => 1],
                [
                    'title' => 'PRP Kit Bangladesh',
                    'primary_color' => '#0f766e',
                    'contact_email' => 'sales@example.com',
                    'footer_email' => 'sales@example.com',
                    'footer_phone' => '+8801XXXXXXXXX',
                    'footer_address' => 'Dhaka, Bangladesh',
                    'meta_keywords' => 'PRP kit, PRP tube, sterile PRP kit, clinic supply Bangladesh',
                    'meta_description' => 'Sterile PRP kits and clinic consumables for professional healthcare and aesthetic clinic use.',
                    'announcement' => 'Clinic bulk price available - WhatsApp us for quotation',
                    'announcement_title' => 'Bulk Supply',
                    'announcement_link' => '/contact',
                    'is_slider' => 1,
                    'is_category' => 1,
                    'is_product' => 1,
                    'is_brand' => 1,
                    'is_blogs' => 1,
                    'is_service' => 1,
                    'is_guest_checkout' => 1,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            // 2) Categories
            $categories = [
                ['name' => 'PRP Kits', 'photo' => 'prp-kit-10ml-gel.jpg', 'serial' => 1],
                ['name' => 'PRP Tubes', 'photo' => 'prp-tube-sodium-citrate.jpg', 'serial' => 2],
                ['name' => 'PRP Gel Tubes', 'photo' => 'prp-gel-tube-pack.jpg', 'serial' => 3],
                ['name' => 'PRP Accessories', 'photo' => 'prp-accessories-set.jpg', 'serial' => 4],
                ['name' => 'Clinic Consumables', 'photo' => 'clinic-prp-starter-pack.jpg', 'serial' => 5],
                ['name' => 'Bulk Supply', 'photo' => 'bulk-prp-kit-pack-50pcs.jpg', 'serial' => 6],
            ];

            $catIds = [];
            foreach ($categories as $cat) {
                $slug = Str::slug($cat['name']);
                $catIds[$slug] = $this->upsert('categories', ['slug' => $slug], [
                    'name' => $cat['name'],
                    'slug' => $slug,
                    'photo' => $this->img . $cat['photo'],
                    'meta_keywords' => $cat['name'] . ', PRP, clinic supply, Bangladesh',
                    'meta_descriptions' => $cat['name'] . ' for professional clinic supply and PRP preparation workflow.',
                    'status' => 1,
                    'is_feature' => 1,
                    'serial' => $cat['serial'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // 3) Subcategories and child categories
            $subCategories = [
                ['name' => 'Single PRP Kits', 'cat' => 'prp-kits'],
                ['name' => 'Bulk PRP Packs', 'cat' => 'prp-kits'],
                ['name' => 'Gel Separator Tubes', 'cat' => 'prp-gel-tubes'],
                ['name' => 'Sodium Citrate Tubes', 'cat' => 'prp-tubes'],
                ['name' => 'Clinic Starter Packs', 'cat' => 'clinic-consumables'],
                ['name' => 'Preparation Accessories', 'cat' => 'prp-accessories'],
            ];
            $subIds = [];
            foreach ($subCategories as $sub) {
                $slug = Str::slug($sub['name']);
                $subIds[$slug] = $this->upsert('subcategories', ['slug' => $slug], [
                    'name' => $sub['name'],
                    'slug' => $slug,
                    'category_id' => $catIds[$sub['cat']] ?? 0,
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $childIds = [];
            foreach ([
                ['10ml PRP Kit', 'single-prp-kits', 'prp-kits'],
                ['12ml PRP Kit', 'single-prp-kits', 'prp-kits'],
                ['15ml PRP Kit', 'single-prp-kits', 'prp-kits'],
                ['20ml PRP Kit', 'bulk-prp-packs', 'prp-kits'],
                ['50 pcs Supply Pack', 'bulk-prp-packs', 'prp-kits'],
            ] as $child) {
                [$name, $subSlug, $catSlug] = $child;
                $childSlug = Str::slug($name);
                $childIds[$childSlug] = $this->upsert('chield_categories', ['slug' => $childSlug], [
                    'name' => $name,
                    'slug' => $childSlug,
                    'category_id' => $catIds[$catSlug] ?? 0,
                    'subcategory_id' => $subIds[$subSlug] ?? 0,
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // 4) Brands
            $brandIds = [];
            foreach ([
                ['PRP Medical', 'brand-prp-medical.jpg'],
                ['Clinic Supply Co', 'brand-clinic-supply.jpg'],
                ['SterileCare', 'brand-sterilecare.jpg'],
            ] as $brand) {
                [$name, $photo] = $brand;
                $brandIds[Str::slug($name)] = $this->upsert('brands', ['slug' => Str::slug($name)], [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'photo' => $this->img . $photo,
                    'status' => 1,
                    'is_popular' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // 5) Products / Items
            $products = [
                [
                    'name' => 'PRP Kit 10ml Gel Tube', 'cat' => 'prp-kits', 'sub' => 'single-prp-kits', 'child' => '10ml-prp-kit', 'brand' => 'prp-medical',
                    'price' => 850, 'prev' => 1050, 'stock' => 120, 'sku' => 'PRP-10G-001', 'image' => 'prp-kit-10ml-gel.jpg',
                    'short' => 'Sterile 10ml PRP gel tube kit for professional clinic use.',
                    'specs' => ['Tube Size' => '10ml', 'Tube Type' => 'Gel separator', 'Pack Size' => 'Single kit', 'Sterile' => 'Yes', 'Usage' => 'Professional clinic use only'],
                ],
                [
                    'name' => 'PRP Kit 12ml ACD-A Tube', 'cat' => 'prp-kits', 'sub' => 'single-prp-kits', 'child' => '12ml-prp-kit', 'brand' => 'prp-medical',
                    'price' => 950, 'prev' => 1200, 'stock' => 95, 'sku' => 'PRP-12A-002', 'image' => 'prp-kit-12ml-acda.jpg',
                    'short' => '12ml PRP preparation kit with ACD-A style demo specification.',
                    'specs' => ['Tube Size' => '12ml', 'Anticoagulant' => 'ACD-A demo spec', 'Pack Size' => 'Single kit', 'Sterile' => 'Yes', 'Certificate' => 'Available on request'],
                ],
                [
                    'name' => 'PRP Kit 15ml Double Spin', 'cat' => 'prp-kits', 'sub' => 'single-prp-kits', 'child' => '15ml-prp-kit', 'brand' => 'clinic-supply-co',
                    'price' => 1250, 'prev' => 1500, 'stock' => 70, 'sku' => 'PRP-15D-003', 'image' => 'prp-kit-15ml-double-spin.jpg',
                    'short' => '15ml kit positioned for standardized double-spin PRP preparation workflow.',
                    'specs' => ['Tube Size' => '15ml', 'Protocol' => 'Double-spin workflow support', 'Pack Size' => 'Single kit', 'Sterile' => 'Yes', 'Use Case' => 'Aesthetic and dermatology clinic supply'],
                ],
                [
                    'name' => 'PRP Kit 20ml Premium Pack', 'cat' => 'prp-kits', 'sub' => 'bulk-prp-packs', 'child' => '20ml-prp-kit', 'brand' => 'sterilecare',
                    'price' => 1650, 'prev' => 1950, 'stock' => 55, 'sku' => 'PRP-20P-004', 'image' => 'prp-kit-20ml-premium.jpg',
                    'short' => '20ml premium demo PRP kit for professional clinical workflow.',
                    'specs' => ['Tube Size' => '20ml', 'Pack Type' => 'Premium demo pack', 'Sterile' => 'Yes', 'MOQ' => '10 pcs for wholesale', 'Support' => 'Bulk quotation available'],
                ],
                [
                    'name' => 'PRP Tube Sodium Citrate 10ml', 'cat' => 'prp-tubes', 'sub' => 'sodium-citrate-tubes', 'child' => null, 'brand' => 'clinic-supply-co',
                    'price' => 420, 'prev' => 550, 'stock' => 300, 'sku' => 'TUBE-SC-010', 'image' => 'prp-tube-sodium-citrate.jpg',
                    'short' => '10ml sodium citrate style PRP tube for demo catalog listing.',
                    'specs' => ['Tube Size' => '10ml', 'Anticoagulant' => 'Sodium citrate demo spec', 'Sterile' => 'Yes', 'Pack' => 'Single tube', 'Storage' => 'Store as supplier guideline'],
                ],
                [
                    'name' => 'PRP Gel Separator Tube 12ml Pack', 'cat' => 'prp-gel-tubes', 'sub' => 'gel-separator-tubes', 'child' => null, 'brand' => 'sterilecare',
                    'price' => 4800, 'prev' => 5600, 'stock' => 40, 'sku' => 'GEL-12-PACK', 'image' => 'prp-gel-tube-pack.jpg',
                    'short' => 'Pack of demo 12ml PRP gel separator tubes for clinic stock.',
                    'specs' => ['Tube Size' => '12ml', 'Tube Type' => 'Gel separator', 'Pack Size' => '10 pcs', 'Sterile' => 'Yes', 'Bulk Price' => 'Available'],
                ],
                [
                    'name' => 'Clinic PRP Starter Pack', 'cat' => 'clinic-consumables', 'sub' => 'clinic-starter-packs', 'child' => null, 'brand' => 'clinic-supply-co',
                    'price' => 9500, 'prev' => 11000, 'stock' => 25, 'sku' => 'CLINIC-START-01', 'image' => 'clinic-prp-starter-pack.jpg',
                    'short' => 'Starter demo pack for clinics planning regular PRP preparation workflow.',
                    'specs' => ['Pack Type' => 'Starter pack', 'Includes' => 'PRP kits and consumable demo items', 'Sterile' => 'Packed sterile items', 'Best For' => 'New clinic setup', 'Support' => 'WhatsApp sales support'],
                ],
                [
                    'name' => 'Bulk PRP Kit Pack - 50 pcs', 'cat' => 'bulk-supply', 'sub' => 'bulk-prp-packs', 'child' => '50-pcs-supply-pack', 'brand' => 'prp-medical',
                    'price' => 38500, 'prev' => 45000, 'stock' => 12, 'sku' => 'BULK-PRP-50', 'image' => 'bulk-prp-kit-pack-50pcs.jpg',
                    'short' => '50 pcs PRP kit demo bulk pack for clinics and distributors.',
                    'specs' => ['Pack Size' => '50 pcs', 'Buyer Type' => 'Clinic / distributor', 'MOQ' => '10 pcs for wholesale', 'Certificate' => 'Available on request', 'Quote' => 'Dealer price available'],
                ],
                [
                    'name' => 'PRP Preparation Accessories Set', 'cat' => 'prp-accessories', 'sub' => 'preparation-accessories', 'child' => null, 'brand' => 'clinic-supply-co',
                    'price' => 1850, 'prev' => 2300, 'stock' => 65, 'sku' => 'ACC-PRP-SET', 'image' => 'prp-accessories-set.jpg',
                    'short' => 'Demo accessories set to support PRP preparation workflow in clinics.',
                    'specs' => ['Type' => 'Accessories', 'Use' => 'Preparation workflow support', 'Pack' => 'Set', 'Sterile' => 'As per individual item', 'Note' => 'For trained professional use'],
                ],
                [
                    'name' => 'Sterile PRP Tube Pack', 'cat' => 'prp-tubes', 'sub' => 'sodium-citrate-tubes', 'child' => null, 'brand' => 'sterilecare',
                    'price' => 4200, 'prev' => 5000, 'stock' => 45, 'sku' => 'STERILE-TUBE-PACK', 'image' => 'sterile-prp-tube-pack.jpg',
                    'short' => 'Sterile PRP tube pack for clinic consumable inventory demo.',
                    'specs' => ['Pack Size' => '10 pcs', 'Sterile' => 'Yes', 'Use' => 'Clinic consumable', 'Storage' => 'Cool, dry place', 'Certificate' => 'Available on request'],
                ],
            ];

            foreach ($products as $product) {
                $slug = Str::slug($product['name']);
                $specNames = array_keys($product['specs']);
                $specValues = array_values($product['specs']);
                $details = $this->productDetails($product['name'], $product['short'], $product['specs']);
                $itemId = $this->upsert('items', ['sku' => $product['sku']], [
                    'category_id' => $catIds[$product['cat']] ?? 0,
                    'subcategory_id' => $subIds[$product['sub']] ?? 0,
                    'childcategory_id' => $childIds[$product['child']] ?? 0,
                    'brand_id' => $brandIds[$product['brand']] ?? 0,
                    'name' => $product['name'],
                    'slug' => $slug,
                    'sku' => $product['sku'],
                    'tags' => 'PRP kit, PRP tube, sterile kit, clinic supply, Bangladesh',
                    'sort_details' => $product['short'],
                    'specification_name' => json_encode($specNames),
                    'specification_description' => json_encode($specValues),
                    'is_specification' => 1,
                    'details' => $details,
                    'photo' => $this->img . $product['image'],
                    'thumbnail' => $this->img . $product['image'],
                    'discount_price' => $product['price'],
                    'previous_price' => $product['prev'],
                    'stock' => $product['stock'],
                    'shipping_weight' => 0.20,
                    'meta_keywords' => $product['name'] . ', sterile PRP kit, clinic supply',
                    'meta_description' => $product['short'],
                    'status' => 1,
                    'is_type' => 'feature',
                    'item_type' => 'normal',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Gallery images: main + a common accessory/certificate style image.
                $this->upsert('galleries', ['item_id' => $itemId, 'photo' => $this->img . $product['image']], [
                    'item_id' => $itemId,
                    'photo' => $this->img . $product['image'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $this->upsert('galleries', ['item_id' => $itemId, 'photo' => $this->img . 'banner-certificate-support.jpg'], [
                    'item_id' => $itemId,
                    'photo' => $this->img . 'banner-certificate-support.jpg',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Product attributes for filter/variant UI.
                foreach ($product['specs'] as $attrName => $attrValue) {
                    $attrId = $this->upsert('attributes', ['item_id' => $itemId, 'keyword' => Str::slug($attrName)], [
                        'item_id' => $itemId,
                        'name' => $attrName,
                        'keyword' => Str::slug($attrName),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $this->upsert('attribute_options', ['attribute_id' => $attrId, 'keyword' => Str::slug($attrValue)], [
                        'attribute_id' => $attrId,
                        'name' => $attrValue,
                        'price' => 0,
                        'keyword' => Str::slug($attrValue),
                        'stock' => 'unlimited',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            // 6) Homepage sliders and banners
            foreach ([
                ['Sterile PRP Kits for Clinics', 'Professional PRP preparation kits for trained healthcare and aesthetic professionals.', 'hero-prp-clinic-supply.jpg', '/shop'],
                ['Clinic Bulk Supply Available', 'Request dealer price for monthly clinic supply and distributor orders.', 'banner-bulk-prp-supply.jpg', '/contact'],
                ['Certificate & Documentation Support', 'Product documents available on request for eligible wholesale buyers.', 'banner-certificate-support.jpg', '/page/certificates'],
            ] as $s) {
                [$title, $details, $photo, $link] = $s;
                $this->upsert('sliders', ['title' => $title], [
                    'photo' => $this->img . $photo,
                    'title' => $title,
                    'details' => $details,
                    'link' => $link,
                    'logo' => null,
                    'home_page' => 'theme1',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            foreach ([
                ['Bulk PRP Kit Pack', 'Dealer price available', '/contact', 'banner-bulk-prp-supply.jpg', 'top'],
                ['Documentation Support', 'Certificate available on request', '/page/certificates', 'banner-certificate-support.jpg', 'middle'],
                ['WhatsApp Quick Order', 'Talk to sales for clinic supply', '/contact', 'banner-whatsapp-order.jpg', 'bottom'],
            ] as $b) {
                [$title, $subtitle, $url, $image, $type] = $b;
                $this->upsert('banners', ['title' => $title, 'type' => $type], [
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'url' => $url,
                    'image' => $this->img . $image,
                    'type' => $type,
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // 7) Service / trust blocks
            foreach ([
                ['Sterile Packaging', 'Demo products are positioned as sterile-packed clinic consumables. Replace demo text with supplier-confirmed details before launch.'],
                ['Clinic Bulk Supply', 'Monthly and distributor quotation flow for doctors, clinics, hospitals and aesthetic centers.'],
                ['Certificate Available', 'Add CE, ISO, import or DGDA documents only when you have verified documents from supplier.'],
                ['Fast Delivery in Bangladesh', 'Use courier or local delivery service for confirmed clinic orders.'],
                ['Professional Use Only', 'PRP kits should be used by trained healthcare professionals according to protocol.'],
                ['WhatsApp Sales Support', 'Add a floating WhatsApp button and product-level bulk inquiry message.'],
            ] as $i => $service) {
                [$title, $details] = $service;
                $this->upsert('services', ['title' => $title], [
                    'title' => $title,
                    'details' => $details,
                    'photo' => $this->img . ['prp-kit-10ml-gel.jpg','bulk-prp-kit-pack-50pcs.jpg','banner-certificate-support.jpg','banner-whatsapp-order.jpg','sterile-prp-tube-pack.jpg','clinic-prp-starter-pack.jpg'][$i],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // 8) Pages
            foreach ([
                ['About Us', 'about-us', $this->pageHtml('About PRP Kit Bangladesh', 'We supply PRP kits, tubes and clinic consumables for professional healthcare and aesthetic clinic buyers in Bangladesh. This demo content should be updated with real company registration, supplier details, and verified product documentation before launch.')],
                ['Certificates', 'certificates', $this->pageHtml('Certificates & Documentation', 'Certificate, batch, expiry and import documentation should be shown only after verification. Upload CE, ISO, DGDA/import or supplier documents as downloadable PDFs when available.')],
                ['How It Works', 'how-it-works', $this->pageHtml('How to Order', 'Choose a PRP kit, request a quotation, confirm quantity and delivery address, then complete payment. Clinical use must be performed by trained professionals.')],
                ['Bulk Order', 'bulk-order', $this->pageHtml('Bulk Order for Clinics', 'For monthly clinic supply or distributor pricing, share clinic name, city, required quantity, tube size, and expected monthly usage through WhatsApp or contact form.')],
                ['Return Policy', 'return-policy', $this->pageHtml('Return Policy', 'Because PRP kits are sterile clinical consumables, opened or used items should not be accepted for return. Replace this policy with your lawyer-reviewed final policy.')],
                ['Terms & Service', 'terms-service', $this->pageHtml('Terms & Service', 'Products are listed for professional use only. This website does not provide medical advice, diagnosis or treatment claims. Replace demo terms before going live.')],
                ['Privacy Policy', 'privacy-policy', $this->pageHtml('Privacy Policy', 'We collect inquiry and order information to process clinic supply requests. Replace with your final privacy policy before launch.')],
            ] as $page) {
                [$title, $slug, $details] = $page;
                $this->upsert('pages', ['slug' => $slug], [
                    'title' => $title,
                    'slug' => $slug,
                    'details' => $details,
                    'meta_keywords' => $title . ', PRP kit, clinic supply',
                    'meta_descriptions' => strip_tags($details),
                    'pos' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // 9) FAQ categories and FAQ entries
            $faqCatId = $this->upsert('fcategories', ['slug' => 'prp-kit-faq'], [
                'name' => 'PRP Kit FAQ',
                'text' => 'Common questions about ordering PRP kits',
                'slug' => 'prp-kit-faq',
                'meta_keywords' => 'PRP kit FAQ, clinic supply FAQ',
                'meta_descriptions' => 'Frequently asked questions for PRP kit buyers.',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            foreach ([
                ['Who can buy PRP kits?', 'These demo products are intended for clinics, doctors, healthcare professionals and verified institutional buyers.'],
                ['Do you provide bulk price?', 'Yes. Add your clinic name, city, required monthly quantity and product type in the contact form or WhatsApp message.'],
                ['Are certificates available?', 'Certificate and product documents should be provided only when verified from supplier or manufacturer.'],
                ['Can I return opened sterile items?', 'For sterile clinical consumables, opened or used items should not be returned. Use your final approved return policy before launch.'],
                ['Do you guarantee treatment results?', 'No. The website should not claim guaranteed results. PRP preparation and treatment decisions belong to qualified professionals.'],
            ] as $faq) {
                [$title, $details] = $faq;
                $this->upsert('faqs', ['title' => $title, 'category_id' => $faqCatId], [
                    'category_id' => $faqCatId,
                    'title' => $title,
                    'details' => $details,
                    'meta_keywords' => 'PRP kit FAQ',
                    'meta_descriptions' => $details,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // 10) Blog categories and posts
            $blogCatId = $this->upsert('bcategories', ['slug' => 'clinic-supply-guide'], [
                'name' => 'Clinic Supply Guide',
                'slug' => 'clinic-supply-guide',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            foreach ([
                ['How to Choose PRP Kits for Clinic Supply', 'blog-prp-kit-selection.jpg', 'A practical buyer guide for comparing tube size, pack size, documentation and supplier support.'],
                ['Storage and Handling Basics for PRP Consumables', 'blog-prp-storage-handling.jpg', 'General storage and inventory notes for sterile clinic consumables. Always follow supplier instructions.'],
                ['Bulk PRP Kit Ordering Checklist for Clinics', 'blog-clinic-bulk-supply.jpg', 'A simple checklist for clinic name, monthly quantity, tube size preference, delivery city and documentation needs.'],
            ] as $post) {
                [$title, $image, $summary] = $post;
                $this->upsert('posts', ['slug' => Str::slug($title)], [
                    'title' => $title,
                    'slug' => Str::slug($title),
                    'details' => '<p>' . e($summary) . '</p><p>This demo article is for ecommerce content layout only. Replace with verified product and compliance information before public launch.</p>',
                    'photo' => $this->img . $image,
                    'category_id' => $blogCatId,
                    'tags' => 'PRP kit, clinic supply, bulk order',
                    'meta_keywords' => 'PRP kit, clinic supply, Bangladesh',
                    'meta_descriptions' => $summary,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // 11) Optional homepage customize values. Many themes read these as JSON/text ID arrays.
            DB::table('home_cutomizes')->updateOrInsert(
                ['id' => 1],
                [
                    'popular_category' => json_encode(array_values($catIds)),
                    'feature_category' => json_encode(array_values(array_slice($catIds, 0, 4))),
                    'two_column_category' => json_encode(array_values(array_slice($catIds, 0, 2))),
                    'hero_banner' => json_encode(['title' => 'Sterile PRP Kits for Clinics', 'button' => 'View Products', 'link' => '/shop']),
                    'home_page4' => null,
                    'home_4_popular_category' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        });
    }

    private function upsert(string $table, array $where, array $data): int
    {
        $row = DB::table($table)->where($where)->first();
        if ($row) {
            DB::table($table)->where('id', $row->id)->update($data);
            return (int) $row->id;
        }
        return (int) DB::table($table)->insertGetId(array_merge($where, $data));
    }

    private function productDetails(string $name, string $short, array $specs): string
    {
        $list = '';
        foreach ($specs as $k => $v) {
            $list .= '<li><strong>' . e($k) . ':</strong> ' . e($v) . '</li>';
        }
        return '<h3>' . e($name) . '</h3>'
            . '<p>' . e($short) . '</p>'
            . '<ul>' . $list . '</ul>'
            . '<p><strong>Important:</strong> For professional clinical use only. This product listing is demo content and does not provide medical advice, diagnosis, treatment claims, or guaranteed results. Replace all demo text with supplier-confirmed product details before launch.</p>'
            . '<p>For bulk clinic supply, request quotation with required quantity, tube size, city and delivery timeline.</p>';
    }

    private function pageHtml(string $heading, string $body): string
    {
        return '<h2>' . e($heading) . '</h2><p>' . e($body) . '</p>';
    }
}
