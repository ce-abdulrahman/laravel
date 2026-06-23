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
            $category = $request->category;
            $query->where(function ($q) use ($category) {
                $q->where('tajweed_rule_category_id', $category)
                  ->orWhereHas('category', function ($sub) use ($category) {
                      $sub->where('slug', $category);
                  });
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereTranslationLikeAny('name', $search)
                  ->orWhereTranslationLikeAny('description', $search);
            });
        }

        $rules = $query->orderBy('priority')->get();

        return response()->json([
            'status' => 'success',
            'data' => $rules
        ]);
    }
}
