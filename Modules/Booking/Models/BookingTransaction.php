<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Commission\Models\CommissionEarning;
use Modules\Tip\Models\TipEarning;

class BookingTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['booking_id', 'external_transaction_id', 'transaction_type', 'discount_percentage', 'discount_amount', 'tip_amount', 'tax_percentage', 'payment_status'];

    protected $casts = [
        'tax_percentage' => 'array',
        'booking_id' => 'integer',
        'discount_percentage' => 'double',
        'discount_amount' => 'double',
        'tip_amount' => 'double',
    ];

    protected static function newFactory()
    {
        return \Modules\Booking\Database\factories\BookingTransactionFactory::new();
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class)->with('services');
    }

    public function commissions()
    {
        return $this->hasMany(CommissionEarning::class, 'employee_id');
    }

    public function tipEarnings()
    {
        return $this->hasMany(TipEarning::class, 'tippable_id', 'booking_id');
    }

    public function commissionEarnings()
    {
        return $this->hasMany(CommissionEarning::class, 'commissionable_id', 'booking_id');
    }
    public function getTotalTaxAmountAttribute(): float
    {
        \Log::info('BookingTransaction::getTotalTaxAmountAttribute - Transaction ID: ' . $this->id);
        
        $taxPercentage = $this->tax_percentage;
        $rawTaxPercentage = $this->getRawOriginal('tax_percentage');
        
        \Log::info('BookingTransaction::getTotalTaxAmountAttribute - Tax data', [
            'transaction_id' => $this->id,
            'booking_id' => $this->booking_id,
            'tax_percentage_raw' => $rawTaxPercentage,
            'tax_percentage_cast' => $taxPercentage,
            'tax_percentage_type' => gettype($taxPercentage),
            'is_array' => is_array($taxPercentage),
            'is_null' => is_null($taxPercentage),
            'is_empty' => empty($taxPercentage),
        ]);

        if (!is_array($taxPercentage)) {
            \Log::warning('BookingTransaction::getTotalTaxAmountAttribute - tax_percentage is not an array', [
                'transaction_id' => $this->id,
                'type' => gettype($taxPercentage),
            ]);
            return 0;
        }

        if (empty($taxPercentage)) {
            \Log::warning('BookingTransaction::getTotalTaxAmountAttribute - tax_percentage is empty array', [
                'transaction_id' => $this->id,
            ]);
            return 0;
        }

        // Get service amount from booking for tax calculation
        $serviceAmount = 0;
        $packageAmount = 0;
        $productAmount = 0;
        $couponDiscount = 0;
        
        // Load booking with relationships if not already loaded
        $booking = $this->booking;
        if (!$booking && $this->booking_id) {
            $booking = \Modules\Booking\Models\Booking::with(['services', 'packages'])->find($this->booking_id);
        }
        
        if ($booking) {
            $serviceAmount = $booking->services ? $booking->services->sum('service_price') : 0;
            $packageAmount = $booking->packages ? $booking->packages->sum('package_price') : 0;
            
            // Get product amount
            $bookingProducts = \Modules\Booking\Models\BookingProduct::where('booking_id', $this->booking_id)->get();
            if ($bookingProducts->count() > 0) {
                $productAmount = $bookingProducts->sum(function($bp) {
                    return ($bp->product_qty ?? 0) * ($bp->product_price ?? 0);
                });
            }
            
            // Get coupon discount
            $couponRedeem = \Modules\Promotion\Models\UserCouponRedeem::where('booking_id', $this->booking_id)->first();
            $couponDiscount = $couponRedeem ? ($couponRedeem->discount ?? 0) : 0;
        }
        
        $taxableAmount = ($serviceAmount + $packageAmount + $productAmount) - $couponDiscount;

        $taxAmount = collect($taxPercentage)
            ->sum(function ($tax) use ($taxableAmount) {
                $taxType = $tax['type'] ?? 'percent';
                $calculatedAmount = 0;
                
                if ($taxType === 'percent') {
                    // Recalculate percent-based tax
                    $percent = (float) ($tax['percent'] ?? 0);
                    $calculatedAmount = ($taxableAmount * $percent) / 100;
                    
                    \Log::debug('BookingTransaction::getTotalTaxAmountAttribute - Recalculated percent tax', [
                        'tax_name' => $tax['name'] ?? 'Unknown',
                        'percent' => $percent,
                        'taxable_amount' => $taxableAmount,
                        'calculated_amount' => $calculatedAmount,
                    ]);
                } else {
                    // For fixed tax, use stored amount if available, otherwise use tax_amount field
                    $storedAmount = (float) ($tax['amount'] ?? 0);
                    $taxAmountField = (float) ($tax['tax_amount'] ?? 0);
                    
                    // Use stored amount if it's not zero, otherwise use tax_amount field
                    $calculatedAmount = $storedAmount > 0 ? $storedAmount : $taxAmountField;
                    
                    \Log::debug('BookingTransaction::getTotalTaxAmountAttribute - Fixed tax', [
                        'tax_name' => $tax['name'] ?? 'Unknown',
                        'stored_amount' => $storedAmount,
                        'tax_amount_field' => $taxAmountField,
                        'calculated_amount' => $calculatedAmount,
                    ]);
                }
                
                \Log::debug('BookingTransaction::getTotalTaxAmountAttribute - Processing tax item', [
                    'tax_item' => $tax,
                    'tax_type' => $taxType,
                    'calculated_amount' => $calculatedAmount,
                ]);
                
                return $calculatedAmount;
            });
        
        \Log::info('BookingTransaction::getTotalTaxAmountAttribute - Final tax amount', [
            'transaction_id' => $this->id,
            'booking_id' => $this->booking_id,
            'taxable_amount' => $taxableAmount,
            'service_amount' => $serviceAmount,
            'package_amount' => $packageAmount,
            'product_amount' => $productAmount,
            'coupon_discount' => $couponDiscount,
            'tax_amount' => $taxAmount,
            'tax_count' => count($taxPercentage),
        ]);

        return $taxAmount;
    }
}
