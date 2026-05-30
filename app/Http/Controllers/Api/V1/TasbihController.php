<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tasbih;
use Illuminate\Http\Request;

class TasbihController extends Controller
{
    public function index(Request $request)
    {
        $tasbihs = Tasbih::query()
            ->where('is_active', true)
            ->get();

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $tasbihs
        ]);
    }
}
