<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClientLogController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|string',
            'status' => 'nullable|integer',
            'type' => 'required|string'
        ]);

        if (str_contains($validated['url'], '/auth/refresh')) {
            return response()->noContent();
        }

        Log::channel('client-errors')->error($validated['type'], [
            'url' => $validated['url'],
            'status' => $validated['status'],
            'user' => $request->input('user', 'unknown'),
            'ip' => $request->ip(),
            'timestamp' => now()->toISOString()
        ]);

        return response()->noContent();
    }
}