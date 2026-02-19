<?php

namespace App\Http\Controllers;

use App\Services\ZohoService;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    public function __construct(private ZohoService $zohoService) {}

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'account_name' => 'required|string',
            'account_website' => 'required|url',
            'account_phone' => ['required', 'regex:/^\+?[0-9]{7,15}$/'],
            'deal_name' => 'required|string',
            'deal_stage' => 'required|string',
        ]);

        try {
            $accountId = $this->zohoService->createAccount($validated);
            $this->zohoService->createDeal($validated, $accountId);

            return response()->json(['message' => 'Created successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
