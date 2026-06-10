<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TajweedRuleCategory;
use Illuminate\Http\Request;

class TajweedCategoryController extends Controller
{
    /**
     * Return all active Tajweed categories with their rules.
     */
    public function index(Request $request)
    {
        $categories = TajweedRuleCategory::active()
            ->with(['tajweedRules' => function ($q) {
                $q->where('is_active', true)->orderBy('priority');
            }])
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->map(function ($cat) {
                return [
                    'id'             => $cat->id,
                    'slug'           => $cat->slug,
                    'name'           => $cat->name,
                    'name_ku'        => $cat->name_ku,
                    'name_ar'        => $cat->name_ar,
                    'description_ku' => $cat->description_ku,
                    'order'          => $cat->order,
                    'rules'          => $cat->tajweedRules->map(function ($rule) {
                        return [
                            'id'             => $rule->id,
                            'slug'           => $rule->slug,
                            'name'           => $rule->name,
                            'name_ku'        => $rule->name_ku,
                            'name_ar'        => $rule->name_ar,
                            'color_code'     => $rule->color_code,
                            'description'    => $rule->description,
                            'description_ku' => $rule->description_ku,
                            'example_text'   => $rule->example_text,
                            'priority'       => $rule->priority,
                        ];
                    }),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $categories,
        ]);
    }
}
