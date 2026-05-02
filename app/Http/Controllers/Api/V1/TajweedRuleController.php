<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TajweedRule;
use Illuminate\Http\Request;

class TajweedRuleController extends Controller
{
    public function index(Request $request)
    {
        $query = TajweedRule::active();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $rules = $query->orderBy('priority')->get();

        return response()->json([
            'status' => 'success',
            'data' => $rules
        ]);
    }
}
