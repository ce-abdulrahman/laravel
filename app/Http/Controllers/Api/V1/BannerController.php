<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $banners = Banner::query()
            ->where('is_active', true)
            ->with('surah')
            ->orderBy('order')
            ->get();

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $banners
        ]);
    }
}
