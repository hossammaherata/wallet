<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\WalletConfiguration;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * WalletConfigurationController
 * 
 * Handles admin operations for managing wallet configuration including:
 * - Viewing current configuration
 * - Updating transfer and withdrawal fees
 * 
 * @package App\Http\Controllers\Admin
 */
class WalletConfigurationController extends BaseController
{
    /**
     * Display the configuration page.
     * 
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $authUser = $request->user();
        if ($authUser->isStore()) {
            return redirect()->route('store.dashboard');
        }

        $configuration = WalletConfiguration::getCurrent();
        
        return Inertia::render('Admin/wallet-configuration/Index', [
            'configuration' => $configuration,
        ]);
    }

    /**
     * Update the configuration.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'transfer_fee_percentage' => 'required|numeric|min:0|max:100',
            'withdrawal_fee_percentage' => 'required|numeric|min:0|max:100',
        ], [
            'transfer_fee_percentage.required' => 'نسبة رسوم التحويل مطلوبة',
            'transfer_fee_percentage.numeric' => 'نسبة رسوم التحويل يجب أن تكون رقماً',
            'transfer_fee_percentage.min' => 'نسبة رسوم التحويل يجب أن تكون أكبر من أو تساوي صفر',
            'transfer_fee_percentage.max' => 'نسبة رسوم التحويل يجب أن تكون أقل من أو تساوي 100',
            'withdrawal_fee_percentage.required' => 'نسبة رسوم السحب مطلوبة',
            'withdrawal_fee_percentage.numeric' => 'نسبة رسوم السحب يجب أن تكون رقماً',
            'withdrawal_fee_percentage.min' => 'نسبة رسوم السحب يجب أن تكون أكبر من أو تساوي صفر',
            'withdrawal_fee_percentage.max' => 'نسبة رسوم السحب يجب أن تكون أقل من أو تساوي 100',
        ]);

        try {
            $configuration = WalletConfiguration::getCurrent();
            
            $configuration->update([
                'transfer_fee_percentage' => $request->transfer_fee_percentage,
                'withdrawal_fee_percentage' => $request->withdrawal_fee_percentage,
            ]);

            return redirect()->back()
                ->with('success', 'تم تحديث الإعدادات بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'حدث خطأ أثناء تحديث الإعدادات: ' . $e->getMessage()]);
        }
    }
}
