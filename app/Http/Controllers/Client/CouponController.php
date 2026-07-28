<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validate(Request $request, CouponService $couponService): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'plan_id' => 'required|string',
        ]);

        try {
            $coupon = $couponService->validateCoupon($validated['code'], $validated['plan_id']);
            return response()->json([
                'valid' => true,
                'discount_type' => $coupon->discount_type,
                'discount_value' => (float) $coupon->discount_value,
                'code' => $coupon->code,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['valid' => false, 'message' => $e->getMessage()], 422);
        }
    }
}