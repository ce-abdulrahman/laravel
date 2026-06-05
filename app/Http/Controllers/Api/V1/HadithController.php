<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\HadithCategory;
use Illuminate\Http\Request;

class HadithController extends Controller
{
    public function index(Request $request)
    {
        $categories = HadithCategory::query()
            ->where('is_active', true)
            ->with(['hadiths' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            }])
            ->orderBy('order')
            ->get();

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $categories
        ]);
    }
}
