<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\LeadNotification;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LeadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'contact_method' => 'required|in:call,telegram,viber,whatsapp',
            'company' => 'nullable|string|max:255',
            'details' => 'nullable|string|max:2000',
            'source' => 'required|string|max:50',
        ]);

        $lead = Lead::create($validated);

        try {
            if ($adminEmail = config('mail.admin_email')) {
                Mail::to($adminEmail)->send(new LeadNotification($lead));
            }
        } catch (\Exception $e) {
            Log::error('Lead mail failed: '.$e->getMessage());
        }

        return response()->json(['success' => true, 'id' => $lead->id], 201);
    }
}
