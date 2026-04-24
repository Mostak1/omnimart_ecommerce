<?php

namespace App\Http\Controllers\Back;

use Illuminate\Http\Request;

use App\{
    Helpers\VisibilityHelper,
    Models\Setting,
    Models\Language,
    Models\EmailTemplate,
    Http\Controllers\Controller,
    Http\Requests\SettingRequest,
    Repositories\Back\SettingRepository
};
use App\Models\ExtraSetting;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{

    /**
     * Constructor Method.
     *
     * Setting Authentication
     *
     * @param  \App\Repositories\Back\SettingRepository $repository
     *
     */
    public function __construct(SettingRepository $repository)
    {
        $this->middleware('auth:admin');
        $this->middleware('adminlocalize');
        $this->repository = $repository;
    }

    /**
     * Show the form for updating resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function system()
    {

        return view('back.settings.system');
    }


    /**
     * Show the form for updating resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function menu()
    {

        return view('back.settings.menu');
    }

    /**
     * Show the form for updating resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function language()
    {
        $data = Language::first();
        $data_results = file_get_contents(resource_path().'/lang/'.$data->file);
        $lang = json_decode($data_results, true);
        return view('back.settings.language',compact('data','lang'));
    }

    /**
     * Show the form for updating resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function social()
    {
        return view('back.settings.social',[
            'google_url' => url('/auth/google/callback'),
            'facebook_url' => preg_replace("/^http:/i", "https:", url('/auth/facebook/callback'))
        ]);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(SettingRequest $request)
    {
        $this->repository->update($request);
        return redirect()->back()->withSuccess(__('Data Updated Successfully.'));
    }


    public function section()
    {
        return view('back.settings.section', [
            'pageVisibilityOptions' => $this->pageVisibilityOptions(),
            'sectionVisibilityOptions' => $this->sectionVisibilityOptions(),
        ]);
    }

    public function storage()
    {
        return view('back.settings.storage');
    }

    public function storageLink(Request $request)
    {
       Artisan::call('storage:unlink');
       Artisan::call('storage:link');
       return redirect()->back()->withSuccess(__('Storage Linked Successfully.'));
    }

    public function visiable(Request $request)
    {

        $feilds = ['is_slider','is_three_c_b_first','is_popular_category','is_three_c_b_second','is_highlighted','is_two_column_category','is_popular_brand','is_featured_category','is_two_c_b','is_blogs','is_service','is_t2_slider','is_t2_service_section','is_t2_3_column_banner_first','is_t2_flashdeal','is_t2_new_product','is_t2_3_column_banner_second','is_t2_featured_product','is_t2_bestseller_product','is_t2_toprated_product','is_t2_2_column_banner','is_t2_blog_section','is_t2_brand_section','is_t3_slider','is_t3_service_section','is_t3_3_column_banner_first','is_t3_popular_category','is_t3_flashdeal','is_t3_3_column_banner_second','is_t3_pecialpick','is_t3_brand_section','is_t3_2_column_banner','is_t3_blog_section','is_t4_slider','is_t4_featured_banner','is_t4_specialpick','is_t4_3_column_banner_first','is_t4_flashdeal','is_t4_3_column_banner_second','is_t4_popular_category','is_t4_2_column_banner','is_t4_blog_section','is_t4_brand_section','is_t4_service_section', 'is_t1_falsh',
        'is_t2_falsh',
        'is_t3_falsh',
        'is_t2_three_column_category',
        'is_t3_three_column_category',
        ];
        $extrasetting = ExtraSetting::find(1);
        $setting = Setting::find(1);
        $input = [];
        $setting_input = [];

        foreach($feilds as $field){
            if($request->has($field)){
                $setting_input[$field] = 1;
                $input[$field] = 1;
            }else{
                if($this->checkVisibaltyUrl(url()->previous())){
                 $input[$field] = 0;
                 $setting_input[$field] = 0;
                }
            }
        }


        $visibilityInput = VisibilityHelper::map($setting);
        foreach ($this->visibilityKeys() as $key) {
            $visibilityInput[$key] = $request->has($key) ? 1 : 0;
        }

        $extrasetting->update($input);
        $setting->update(array_merge($setting_input, [
            'page_section_visibility' => json_encode($visibilityInput),
        ]));

        return redirect()->back()->withSuccess(__('Data Updated Successfully.'));

    }

    public function checkVisibaltyUrl($url){
        $segment = explode('/',url()->previous());
        $value = end($segment);
        if($value == 'section'){
            return true;
        }else{
            return false;
        }
    }


    public function announcement(){
        return view('back.settings.announcement');
    }

    public function cookie(){
        return view('back.settings.cookie');
    }

    public function maintainance(){
        return view('back.settings.maintainance');
    }

    private function pageVisibilityOptions(): array
    {
        return [
            __('Main Pages') => [
                ['key' => 'home_page', 'label' => __('Home Page')],
                ['key' => 'shop_page', 'label' => __('Shop Page')],
                ['key' => 'product_details_page', 'label' => __('Product Details Page')],
                ['key' => 'campaign_page', 'label' => __('Campaign Page')],
                ['key' => 'brands_page', 'label' => __('Brands Page')],
                ['key' => 'blog_page', 'label' => __('Blog Page')],
                ['key' => 'blog_details_page', 'label' => __('Blog Details Page')],
                ['key' => 'faq_page', 'label' => __('FAQ Page')],
                ['key' => 'faq_details_page', 'label' => __('FAQ Details Page')],
                ['key' => 'contact_page', 'label' => __('Contact Page')],
            ],
            __('Commerce Pages') => [
                ['key' => 'compare_page', 'label' => __('Compare Page')],
                ['key' => 'cart_page', 'label' => __('Cart Page')],
                ['key' => 'checkout_page', 'label' => __('Checkout Page')],
                ['key' => 'checkout_success_page', 'label' => __('Checkout Success Page')],
                ['key' => 'track_order_page', 'label' => __('Track Order Page')],
                ['key' => 'custom_page', 'label' => __('Custom CMS Page')],
            ],
            __('Auth Pages') => [
                ['key' => 'login_page', 'label' => __('Login Page')],
                ['key' => 'register_page', 'label' => __('Register Page')],
            ],
        ];
    }

    private function sectionVisibilityOptions(): array
    {
        return [
            __('Global Sections') => [
                ['key' => 'global_topbar', 'label' => __('Header Topbar')],
                ['key' => 'global_header_search', 'label' => __('Header Search Box')],
                ['key' => 'global_compare_button', 'label' => __('Header Compare Button')],
                ['key' => 'global_wishlist_button', 'label' => __('Header Wishlist Button')],
                ['key' => 'global_cart_button', 'label' => __('Header Cart Button')],
                ['key' => 'global_category_menu', 'label' => __('Header Category Menu')],
                ['key' => 'global_announcement_popup', 'label' => __('Announcement Popup')],
                ['key' => 'global_footer', 'label' => __('Footer Area')],
                ['key' => 'global_footer_contact', 'label' => __('Footer Contact Column')],
                ['key' => 'global_footer_links', 'label' => __('Footer Links Column')],
                ['key' => 'global_footer_newsletter', 'label' => __('Footer Newsletter Column')],
            ],
            __('Shop Page Sections') => [
                ['key' => 'shop_breadcrumb', 'label' => __('Shop Breadcrumb')],
                ['key' => 'shop_toolbar', 'label' => __('Shop Filter Toolbar')],
                ['key' => 'shop_products', 'label' => __('Shop Product Grid')],
                ['key' => 'shop_sidebar', 'label' => __('Shop Sidebar')],
            ],
            __('Product Details Sections') => [
                ['key' => 'product_breadcrumb', 'label' => __('Product Breadcrumb')],
                ['key' => 'product_gallery', 'label' => __('Product Gallery')],
                ['key' => 'product_summary', 'label' => __('Product Summary')],
                ['key' => 'product_description', 'label' => __('Description And Specification')],
                ['key' => 'product_reviews', 'label' => __('Product Reviews')],
                ['key' => 'product_related_products', 'label' => __('Related Products')],
            ],
            __('Blog Sections') => [
                ['key' => 'blog_breadcrumb', 'label' => __('Blog Breadcrumb')],
                ['key' => 'blog_posts', 'label' => __('Blog Post List')],
                ['key' => 'blog_sidebar', 'label' => __('Blog Sidebar')],
                ['key' => 'blog_details_breadcrumb', 'label' => __('Blog Details Breadcrumb')],
                ['key' => 'blog_details_content', 'label' => __('Blog Details Content')],
                ['key' => 'blog_details_sidebar', 'label' => __('Blog Details Sidebar')],
            ],
            __('Other Page Sections') => [
                ['key' => 'campaign_breadcrumb', 'label' => __('Campaign Breadcrumb')],
                ['key' => 'campaign_products', 'label' => __('Campaign Products')],
                ['key' => 'brands_breadcrumb', 'label' => __('Brands Breadcrumb')],
                ['key' => 'brands_list', 'label' => __('Brands Grid')],
                ['key' => 'faq_breadcrumb', 'label' => __('FAQ Breadcrumb')],
                ['key' => 'faq_categories', 'label' => __('FAQ Category Grid')],
                ['key' => 'faq_details_breadcrumb', 'label' => __('FAQ Details Breadcrumb')],
                ['key' => 'faq_details_content', 'label' => __('FAQ Details Content')],
                ['key' => 'contact_breadcrumb', 'label' => __('Contact Breadcrumb')],
                ['key' => 'contact_info', 'label' => __('Contact Info Column')],
                ['key' => 'contact_form', 'label' => __('Contact Form')],
                ['key' => 'compare_breadcrumb', 'label' => __('Compare Breadcrumb')],
                ['key' => 'compare_table', 'label' => __('Compare Table')],
                ['key' => 'cart_breadcrumb', 'label' => __('Cart Breadcrumb')],
                ['key' => 'cart_table', 'label' => __('Cart Table')],
                ['key' => 'cart_summary', 'label' => __('Cart Summary')],
                ['key' => 'checkout_breadcrumb', 'label' => __('Checkout Breadcrumb')],
                ['key' => 'checkout_billing_form', 'label' => __('Checkout Form Area')],
                ['key' => 'checkout_order_summary', 'label' => __('Checkout Order Summary')],
                ['key' => 'checkout_payment_methods', 'label' => __('Checkout Payment Methods')],
                ['key' => 'checkout_success_content', 'label' => __('Checkout Success Content')],
                ['key' => 'track_order_breadcrumb', 'label' => __('Track Order Breadcrumb')],
                ['key' => 'track_order_form', 'label' => __('Track Order Form')],
                ['key' => 'page_breadcrumb', 'label' => __('Custom Page Breadcrumb')],
                ['key' => 'page_content', 'label' => __('Custom Page Content')],
            ],
        ];
    }

    private function visibilityKeys(): array
    {
        $keys = [];
        foreach ([$this->pageVisibilityOptions(), $this->sectionVisibilityOptions()] as $groups) {
            foreach ($groups as $options) {
                foreach ($options as $option) {
                    $keys[] = $option['key'];
                }
            }
        }

        return $keys;
    }
}
