# Multi-Tenant E-Commerce & POS System Roadmap
**Project:** OmniMart Multi-Tenant Platform  
**Framework:** Laravel 12 (PHP 8.2) + Stancl Tenancy v3  
**Architecture:** Multi-Database Tenancy (Central DB + Isolated Tenant DBs)  
**Date Created:** 2026-11-24  

---

## 📊 বর্তমান অবস্থা (Completed Features)

- [x] **Core Upgrade & Engine:** Laravel 12 upgrade & Stancl Tenancy v3 integrated.
- [x] **Database Isolation:** Automatic creation of separate MySQL databases (`db_tenant_<subdomain>`) on local/VPS, PLUS full **cPanel / Custom Database Mode** support (pre-created DB, username, password).
- [x] **Super Admin Management:** Super admin can create shops, assign subdomains, view shop lists.
- [x] **Tenant Database Migrations:** `tenant_users`, `products`, `sales`, `sale_items`, `purchases`, `purchase_items`, `tenant_settings`.
- [x] **Central Catalog Integration:** `ImportGlobalItemAction`, `CreateCustomItemAction`, `CentralCatalogService` and search API.
- [x] **Tenant Authentication:** `tenant_web` and `tenant_api` auth guards, owner/staff login flow.
- [x] **Product & Inventory Management:** Local stock, low-stock alerts, "Search Global Catalog" real-time search & 1-click import modal.
- [x] **Barcode Label Printing:** Scalable pure SVG Code 128 barcode generator with printable sticker sheets.

---

## 🚀 বাকি কাজের তালিকা ও বাস্তবায়ন পরিকল্পনা (Pending Modules)

### 📌 ফেজ ১: পিওএস (Point of Sale) ও ক্যাশ মেমো বিক্রয় মডিউল [COMPLETED ✅]
- [x] **1.1 POS Interface (`tenant.pos.index`):**
  - বারকোড স্ক্যানার দিয়ে সরাসরি কার্টে পণ্য যোগ করা (Exact match auto-add)।
  - কীবোর্ড শর্টকাট (F2: বিক্রয় সম্পন্ন ও প্রিন্ট, F4: কার্ট ক্লিয়ার, এন্টার দিয়ে স্ক্যান)।
  - দ্রুত কাস্টমার সার্চ বা নতুন ওয়াক-ইন কাস্টমার এন্ট্রি।
- [x] **1.2 Checkout & Automatic Stock Deduction:**
  - ডিসকাউন্ট (টাকা বা %), ট্যাক্স/ভ্যাট হিসাব।
  - পেমেন্ট মেথড (Cash, bKash, Nagad, Due)।
  - অর্ডার সাবমিট হওয়ার সাথে সাথে স্বয়ংক্রিয়ভাবে লোকাল স্টক কমে যাওয়া (`ProcessPosSaleAction`)।
- [x] **1.3 Thermal Cash Memo / Invoice Print:**
  - 80mm থার্মাল রিসিপ্ট প্রিন্ট ভিউ (`tenant.pos.invoice`)।
  - দোকানের নাম, ফোন, ঠিকানা, ইনভয়েস বারকোড এবং চালানের হিসাব।
- [x] **1.4 Sales History & Invoice Archive (`tenant.sales.index`):**
  - পূর্ববর্তী সকল বিক্রয়ের তালিকা, আজকের বিক্রয়, মোট বকেয়া এবং মেমো রি-প্রিন্ট সুবিধা (`tenant.pos.history`)।

---

### 📌 ফেজ ২: পাইকারি ক্রয় ও সাপ্লায়ার হিসাব (Purchases & Stock In-Flow) [COMPLETED ✅]
- [x] **2.1 Purchase Entry Screen (`tenant.purchases.create`):**
  - সাপ্লায়ার/ভেন্ডর তথ্য এন্ট্রি, চালানের তারিখ ও ভাউচার মেমো।
  - ডায়নামিক পণ্য নির্বাচন, ক্রয় মূল্য (Cost Price) এবং পরিমাণের চালান এন্ট্রি।
- [x] **2.2 Stock Inflow & Ledger:**
  - পারচেজ সাবমিট হলে স্বয়ংক্রিয়ভাবে লোকাল পণ্যের ক্রয় মূল্য আপডেট ও স্টক বৃদ্ধি (`ProcessPurchaseAction`)।
  - সাপ্লায়ারের মোট ক্রয় মূল্য, পরিশোধ ও বাকির হিসাব (Supplier Due Ledger)।
- [x] **2.3 Purchase History & Details (`tenant.purchases.index` & `show`):**
  - অতীতের সকল চালানের তালিকা, ভাউচার বিস্তারিত ও চালান বাতিলের সুবিধা।

---

### 📌 ফেজ ৩: দোকানের প্রোফাইল ও ইনভয়েস সেটিংস (Tenant Settings) [COMPLETED ✅]
- [x] **3.1 Store Information:**
  - দোকানের নাম, স্লোগান, ফোন, ইমেইল, পূর্ণ ঠিকানা ও সেন্ট্রাল সিঙ্ক (`SettingController`)।
- [x] **3.2 POS & Invoice Customization:**
  - ক্যাশ মেমোতে ভ্যাট/ট্যাক্স রেট (VAT %)।
  - মেমোর নিচের রিটার্ন পলিসি / ফুটার নোট (যেমন: "বিক্রিত মাল ৭ দিনের মধ্যে পরিবর্তনযোগ্য")।
  - স্বয়ংক্রিয় থার্মাল প্রিন্ট ডায়লগ টগল এবং রিয়েল-টাইম ৮০মিমি ক্যাশ মেমো লাইভ প্রিভিউ।

---

### 📌 ফেজ ৪: স্টাফ ও ক্যাশিয়ার ম্যানেজমেন্ট (Staff & Role Permissions) [COMPLETED ✅]
- [x] **4.1 Staff Management (`tenant.staff.index`):**
  - দোকানদার কর্তৃক ক্যাশিয়ার বা ম্যানেজার অ্যাকাউন্ট তৈরি (`tenant_users` টেবিলে)।
  - স্টাফদের তথ্য আপডেট, মোবাইল নম্বর, পদবি ও পাসওয়ার্ড রিসেট সুবিধা।
  - নিষ্ক্রিয়/সক্রিয় টগল এবং স্টাফ রিমুভ করার সুরক্ষা (Owner বা নিজের অ্যাকাউন্ট ডিলিট ব্লক)।
- [x] **4.2 Role Permissions:**
  - রোল ব্যাজ ও অ্যাক্টিভ স্ট্যাটাস ট্র্যাকিং (`owner`, `manager`, `cashier`)।

---

### 📌 ফেজ ৫: সাধারণ কাস্টমারদের জন্য পাবলিক স্টোরফ্রন্ট (Tenant eCommerce Storefront) [COMPLETED ✅]
- [x] **5.1 Subdomain Storefront (`shop1.yourdomain.com`):**
  - ভিজিটরদের জন্য দোকানের আধুনিক অনলাইন ক্যাটালগ ও হোমপেজ (`StorefrontController@index`)।
  - দোকানের নাম, স্লোগান, মোবাইল নম্বর ও সরাসরি সার্চ বার।
- [x] **5.2 Customer Online Order & Cart:**
  - সাধারণ ক্রেতাদের জন্য ফ্লোটিং কার্ট, কোয়ান্টিটি পরিবর্তন ও চেকআউট মোডাল।
  - ক্যাশ অন ডেলিভারি (COD) অর্ডার প্লেসমেন্ট ও স্বয়ংক্রিয় স্টক বুকিং।
  - অর্ডার কনফার্মেশন ও ইনভয়েস ট্র্যাকিং স্ক্রিন (`/order/success/{invoiceNo}`)।

---

### 📌 ফেজ ৬: সেন্ট্রাল সুপার অ্যাডমিন বিলিং ও সাবস্ক্রিপশন (SaaS Billing & Control) [COMPLETED ✅]
- [x] **6.1 Subscription Packages & Renewal:**
  - প্যাকেজ প্ল্যান তৈরি (বেসিক, স্ট্যান্ডার্ড, প্রিমিয়াম - মাসিক/বাৎসরিক মেয়াদ ও বিলিং ট্র্যাকিং)।
  - সুপার অ্যাডমিন প্যানেল থেকে ওয়ান-ক্লিকে প্ল্যান আপগ্রেড ও মেয়াদ বৃদ্ধি (`TenantController@updateSubscription`)।
  - মোট সক্রিয় দোকান, ট্রায়াল স্টোর, মেয়াদোত্তীর্ণ এবং সাসপেন্ডেড স্টোরের রিয়েল-টাইম SaaS মেট্রিক কার্ড।
- [x] **6.2 Store Suspension & Expiry Guard:**
  - মেয়াদোত্তীর্ণ ও স্থগিত স্টোর হ্যান্ডলিংয়ের জন্য সিকিউর মিডলওয়্যার (`CheckTenantSubscription`)।
  - সাসপেন্ডেড স্টোরে অ্যাক্সেস ব্লক (HTTP 403) ও সাপোর্ট নোটিশ।
  - মেয়াদোত্তীর্ণ স্টোরের জন্য পোর্টালে অ্যালার্ট ও মিউটেশন রিকোয়েস্ট রেস্ট্রিকশন।

---

### 📌 ফেজ ৭: কাস্টম ডোমেইন ম্যাপিং ও মিডলওয়্যার পলিশ (Custom Domain Mapping) [COMPLETED ✅]
- [x] **7.1 Custom Domain & Subdomain Unified Routing:**
  - `InitializeTenancyByDomainOrSubdomain` ইন্টিগ্রেশন যার মাধ্যমে সাবডোমেন (`bhaibhai.localhost`) এবং নিজস্ব টপ-লেভেল কাস্টম ডোমেন (`bhaibhaistore.com`) একই সাথে নিখুঁতভাবে রাউট হয়।
  - সেন্ট্রাল ডোমেইন সুরক্ষার জন্য `PreventAccessFromCentralDomains` সক্রিয় রাখা।
---

### 📌 ফেজ ৮: মেগা মার্কেটপ্লেস ও সেলস কমিশন ডুয়াল লিমিট ইঞ্জিন (Mega Marketplace & Commission Engine) [COMPLETED ✅]
- [x] **8.1 Central Database Financial Ledger:**
  - `shop_commissions` টেবিল (কমিশন রেট, বর্তমান ডিউ, মোট পরিশোধিত, ম্যাক্স ডিউ লিমিট, মিন ডিউ লিমিট, বিলিং দিন, মার্কেটপ্লেস স্ট্যাটাস)।
  - `shop_commission_logs` টেবিল (প্রতিটি অর্ডারের কমিশন চার্জ, bKash পেমেন্ট, পূর্বের ও পরের ব্যালেন্স, TrxID সহ পূর্ণাঙ্গ অডিট ট্রেইল)।
- [x] **8.2 Central Mega Marketplace Shop Directory:**
  - পাবলিক শপ ডিরেক্টরি পেজ (`/shops`): সকল ভেরিফাইড পার্টনার শপের নাম, টাইপ, ঠিকানা ও ফিল্টারিং।
  - শপ প্রোফাইল পেজ (`/shops/{subdomain}`): সেন্ট্রাল মার্কেটপ্লেসে ওই নির্দিষ্ট দোকানের পণ্য ও ক্যাটালগ প্রদর্শন।
  - সেন্ট্রাল নেভবারে "Explore Shops" মেনু ও প্রোডাক্ট কার্ডে "Sold by: [Shop Name]" ব্যাজিং।
- [x] **8.3 Dual Due Limits & Auto Pause/Resume Engine:**
  - **ম্যাক্স ডিউ লিমিট (Hard Stop):** সেলস কমিশন ম্যাক্স ডিউ সীমা (যেমন: ৳১০০০) ছাড়িয়ে গেলেই মার্কেটপ্লেসে শপ অটো-পজ (`paused_due_limit`)।
  - **মিন ডিউ লিমিট (Rollover):** বিলিং ডেটে বকেয়া মিন লিমিট (যেমন: ৳২০০) এর নিচে হলে কোনো পেনাল্টি ছাড়া পরবর্তী মাসে রোল-ওভার।
  - **অটো রেজ্যুম:** বকেয়া পরিশোধ করে লিমিটের নিচে নামলে তাৎক্ষণিকভাবে মার্কেটপ্লেসে শপ পুনরায় অটো-অ্যাক্টিভ।
- [x] **8.4 Super Admin Commission Dashboard:**
  - মোট কমিশন আয়, বকেয়া ডিউ, পেইড কমিশন ও পজড শপের রিয়েল-টাইম KPI মেট্রিক কার্ড।
  - শপভিত্তিক কমিশন রেট, লিমিট এডিট ও অফলাইন পেমেন্ট এন্ট্রি মোডাল (`/admin/commissions`)।
  - সম্পূর্ণ ট্রানজেকশন হিস্ট্রি ও ফিল্টারেবল অডিট লগ (`/admin/commissions/logs`)।
- [x] **8.5 Tenant Portal Commission & Payment Screen:**
  - দোকানদার ড্যাশবোর্ডে বর্তমান ডিউ, রিমেইনিং ক্রেডিট ও ট্রানজেকশন স্টেটমেন্ট (`/tenant-admin/commission`)।
  - bKash/Nagad দিয়ে বকেয়া কমিশন পরিশোধের ফর্ম ও অটো-আনপজ সুবিধা।
- [x] **8.6 Automation Scheduler:**
  - আর্টিসান কমান্ড `php artisan marketplace:check-due-limits` দ্বারা দৈনিক লিমিট মনিটরিং ও মাসিক বিলিং সাইকেল প্রসেসিং।
