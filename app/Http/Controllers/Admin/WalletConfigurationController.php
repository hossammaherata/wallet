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
        $rules = [
            'transfer_fee_percentage' => 'required|numeric|min:0|max:100',
            'withdrawal_fee_percentage' => 'required|numeric|min:0|max:100',
            'ugc_prizes' => 'nullable|array|size:3',
            'ugc_prizes.*' => 'required|numeric|min:0',
            'nomination_prizes' => 'nullable|array',
            'nomination_prizes.attendance_fan' => 'nullable|array|size:5',
            'nomination_prizes.attendance_fan.*' => 'required|numeric|min:0',
            'nomination_prizes.online_fan' => 'nullable|array|size:5',
            'nomination_prizes.online_fan.*' => 'required|numeric|min:0',
        ];

        $messages = [
            'transfer_fee_percentage.required' => 'نسبة رسوم التحويل مطلوبة',
            'transfer_fee_percentage.numeric' => 'نسبة رسوم التحويل يجب أن تكون رقماً',
            'transfer_fee_percentage.min' => 'نسبة رسوم التحويل يجب أن تكون أكبر من أو تساوي صفر',
            'transfer_fee_percentage.max' => 'نسبة رسوم التحويل يجب أن تكون أقل من أو تساوي 100',
            'withdrawal_fee_percentage.required' => 'نسبة رسوم السحب مطلوبة',
            'withdrawal_fee_percentage.numeric' => 'نسبة رسوم السحب يجب أن تكون رقماً',
            'withdrawal_fee_percentage.min' => 'نسبة رسوم السحب يجب أن تكون أكبر من أو تساوي صفر',
            'withdrawal_fee_percentage.max' => 'نسبة رسوم السحب يجب أن تكون أقل من أو تساوي 100',
            'ugc_prizes.array' => 'جوائز UGC يجب أن تكون مصفوفة',
            'ugc_prizes.size' => 'يجب أن تكون هناك 3 جوائز لـ UGC (للمراكز 1، 2، 3)',
            'ugc_prizes.*.required' => 'جميع قيم جوائز UGC مطلوبة',
            'ugc_prizes.*.numeric' => 'قيم جوائز UGC يجب أن تكون أرقاماً',
            'ugc_prizes.*.min' => 'قيم جوائز UGC يجب أن تكون أكبر من أو تساوي صفر',
            'nomination_prizes.array' => 'جوائز Nomination يجب أن تكون مصفوفة',
            'nomination_prizes.attendance_fan.size' => 'يجب أن تكون هناك 5 جوائز لـ attendance_fan',
            'nomination_prizes.attendance_fan.*.required' => 'جميع قيم جوائز attendance_fan مطلوبة',
            'nomination_prizes.attendance_fan.*.numeric' => 'قيم جوائز attendance_fan يجب أن تكون أرقاماً',
            'nomination_prizes.attendance_fan.*.min' => 'قيم جوائز attendance_fan يجب أن تكون أكبر من أو تساوي صفر',
            'nomination_prizes.online_fan.size' => 'يجب أن تكون هناك 5 جوائز لـ online_fan',
            'nomination_prizes.online_fan.*.required' => 'جميع قيم جوائز online_fan مطلوبة',
            'nomination_prizes.online_fan.*.numeric' => 'قيم جوائز online_fan يجب أن تكون أرقاماً',
            'nomination_prizes.online_fan.*.min' => 'قيم جوائز online_fan يجب أن تكون أكبر من أو تساوي صفر',
        ];

        $request->validate($rules, $messages);

        try {
            $configuration = WalletConfiguration::getCurrent();
            
            $updateData = [
                'transfer_fee_percentage' => $request->transfer_fee_percentage,
                'withdrawal_fee_percentage' => $request->withdrawal_fee_percentage,
            ];

            // Update UGC prizes if provided
            if ($request->has('ugc_prizes') && is_array($request->ugc_prizes)) {
                $updateData['ugc_prizes'] = array_values($request->ugc_prizes); // Ensure indexed array
            }

            // Update Nomination prizes if provided
            if ($request->has('nomination_prizes') && is_array($request->nomination_prizes)) {
                $nominationPrizes = [];
                if (isset($request->nomination_prizes['attendance_fan']) && is_array($request->nomination_prizes['attendance_fan'])) {
                    $nominationPrizes['attendance_fan'] = array_values($request->nomination_prizes['attendance_fan']);
                }
                if (isset($request->nomination_prizes['online_fan']) && is_array($request->nomination_prizes['online_fan'])) {
                    $nominationPrizes['online_fan'] = array_values($request->nomination_prizes['online_fan']);
                }
                if (!empty($nominationPrizes)) {
                    $updateData['nomination_prizes'] = $nominationPrizes;
                }
            }
            
            $configuration->update($updateData);

            return redirect()->back()
                ->with('success', 'تم تحديث الإعدادات بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'حدث خطأ أثناء تحديث الإعدادات: ' . $e->getMessage()]);
        }
    }
}
