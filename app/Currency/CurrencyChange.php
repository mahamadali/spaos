<?php

namespace App\Currency;

use Modules\Currency\Models\Currency;
use App\Models\User;
use Illuminate\Support\Facades\Cache;


class CurrencyChange
{
    public $defaultCurrency;
    public $currencyList;
    private $superAdminCurrency;
    private $vendorCurrency;
    private $user;

    public function __construct()
    {
        $this->user = auth()->user();

        if ($this->user && $this->user->hasRole('admin')) {

            $this->currencyList = Currency::where('created_by', $this->user->id)->get();
            $this->defaultCurrency = $this->currencyList->where('is_primary', 1)->first();
        } else {

            $this->currencyList = Currency::getAllCurrency();
            $this->defaultCurrency = $this->currencyList->where('is_primary', 1)->first();
        }

        $superAdmin = User::role('super admin')->first();
        if ($superAdmin) {
            $this->superAdminCurrency = Currency::where('created_by', $superAdmin->id)
                ->where('is_primary', 1)
                ->first();
        }
    }

    /**
     * Get the current admin's default currency.
     */
    public function getDefaultCurrency()
    {
        return $this->defaultCurrency;
    }

    public function getSuperAdminDefaultCurrency()
    {
        $superAdmin = User::role('super admin')->first();
        return $this->superAdminCurrency = Currency::where('created_by', $superAdmin->id)
            ->where('is_primary', 1)
            ->first();
    }

    public function getVendorDefaultCurrency()
    {
        return $this->vendorCurrency = Currency::where('created_by', session('current_vendor_id'))
            ->where('is_primary', 1)
            ->first();
    }


    /**
     * Get the currency symbol for the current admin.
     */
    public function defaultSymbol()
    {
        return $this->defaultCurrency->currency_symbol ?? '$';
    }

    /**
     * Format an amount based on the **super admin's** currency (For Billing Only)
     */
    public function formatSuperadmin($amount)
    {
        $currency = $this->superAdminCurrency ?? $this->defaultCurrency;

        return $this->formatCurrency($currency, $amount);
    }

    /**
     * Format an amount based on the **admin's** default currency (For Regular Pages)
     */
    public function format($amount)
    {

        $currency = $this->defaultCurrency;
        return $this->formatCurrency($currency, $amount);
    }

    public function vendorCurrencyFormate($amount)
    {
        // Ensure vendor currency is loaded
        if (!$this->vendorCurrency) {
            $this->getVendorDefaultCurrency();
        }

        $currency = $this->vendorCurrency ?? $this->defaultCurrency;
        return $this->formatCurrency($currency, $amount);
    }

    /**
     * Generic function to format currency.
     */
    private function formatCurrency($currency, $amount)
    {
        if (!Cache::has('currency.all')) {
            \Modules\Currency\Models\Currency::flushCache(); // Only clear the currency cache
        }

        $noOfDecimal = $currency->no_of_decimal ?? 2;
        $decimalSeparator = $currency->decimal_separator ?? '.';
        $thousandSeparator = $currency->thousand_separator ?? ',';
        $currencyPosition = $currency->currency_position ?? 'left'; // Default to 'left' if null
        $currencySymbol = $currency->currency_symbol ?? '$'; // Default to '$' if null

        return formatCurrency($amount, $noOfDecimal, $decimalSeparator, $thousandSeparator, $currencyPosition, $currencySymbol);
    }
}
