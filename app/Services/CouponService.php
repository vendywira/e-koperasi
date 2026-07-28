<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function validateCoupon(string $code, string $planId): Coupon
    {
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            throw ValidationException::withMessages(['coupon' => 'Kode promo tidak ditemukan.']);
        }

        if (!$coupon->isValidForPlan($planId)) {
            throw ValidationException::withMessages(['coupon' => 'Kode promo tidak berlaku.']);
        }

        return $coupon;
    }

    public function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        $amount = $coupon->discount_type === 'percentage'
            ? $subtotal * $coupon->discount_value / 100
            : $coupon->discount_value;

        return min($amount, $subtotal);
    }
}