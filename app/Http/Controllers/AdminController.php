<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Package;
use App\Models\Article;
use App\Models\Testimonial;
use App\Models\Faq;
use App\Models\Banner;
use App\Models\GalleryItem;
use App\Models\Order;
use App\Models\PageView;
use App\Models\Setting;
use App\Models\Seo;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'categories' => Category::count(),
            'products' => Product::count(),
            'packages' => Package::count(),
            'articles' => Article::count(),
            'testimonials' => Testimonial::count(),
            'faqs' => Faq::count(),
            'banners' => Banner::count(),
            'gallery' => GalleryItem::count(),
            'orders' => Order::count(),
            'page_views' => PageView::count(),
            'settings' => Setting::count(),
            'seos' => Seo::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}