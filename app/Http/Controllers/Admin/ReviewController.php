<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with('product')->latest()->paginate(20);
        return view('admin.review.index', compact('reviews'));
    }

    public function approve(Review $review)
    {
        $review->update(['approved' => true]);
        return back()->with('success', 'Ulasan disetujui.');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return back()->with('success', 'Ulasan dihapus.');
    }
}
