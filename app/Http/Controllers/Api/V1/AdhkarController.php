<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AdhkarCategory;
use Illuminate\Http\Request;

class AdhkarController extends Controller
{
    public function index(Request $request)
    {
        $categories = AdhkarCategory::query()
            ->where('is_active', true)
            ->with(['adhkars'])
            ->orderBy('order')
            ->get();

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $categories
        ]);
    }
}
