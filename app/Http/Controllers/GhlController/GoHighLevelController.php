<?php

namespace App\Http\Controllers\GhlController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GoHighLevelToken;
use App\Http\Controllers\GhlController\Jobs\CreateGHLCustomObject;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\GhlController\GhlConfig;

class GoHighLevelController extends Controller
{

    public function initiate()
    {
        $clientId = env('GHL_CLIENT_ID');
        // Use dynamic URL generation to handle different ports/hosts
        $redirectUri = url('/getAccessToken'); 
        $scopes = GhlConfig::SCOPES;
        
        $scopeString = implode(' ', $scopes);
        
        $url = GhlConfig::AUTH_URL . "?" . http_build_query([
            'response_type' => GhlConfig::DEFAULTS['response_type'],
            'redirect_uri' => $redirectUri,
            'client_id' => $clientId,
            'scope' => $scopeString,
            'version_id' => GhlConfig::VERSION_ID
        ]);

        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        $code = $request->query('code');
        
        Log::info('GHL Callback Hit', ['code' => $code]);

        if (!$code) {
            return response()->json(['error' => 'No code provided'], 400);
        }

        $clientId = env('GHL_CLIENT_ID');
        $clientSecret = env('GHL_CLIENT_SECRET');
        $redirectUri = url('/getAccessToken');

        Log::info('Exchanging code for token...');

        $response = Http::asForm()->post(GhlConfig::TOKEN_URL, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => GhlConfig::DEFAULTS['grant_type'],
            'code' => $code,
            'user_type' => GhlConfig::DEFAULTS['user_type'],
            'redirect_uri' => $redirectUri,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            Log::info('Token Exchange Successful', $data);
            
            try {
                $token = GoHighLevelToken::updateOrCreate(
                    ['location_id' => $data['locationId'] ?? null],
                    [
                        'access_token' => $data['access_token'],
                        'refresh_token' => $data['refresh_token'],
                        'token_type' => $data['token_type'] ?? GhlConfig::DEFAULTS['token_type'],
                        'expires_in' => $data['expires_in'] ?? GhlConfig::DEFAULTS['expires_in'],
                        'expires_at' => \Carbon\Carbon::now()->addSeconds($data['expires_in'] ?? GhlConfig::DEFAULTS['expires_in']),
                        'user_type' => $data['userType'] ?? GhlConfig::DEFAULTS['user_type'],
                        'scope' => $data['scope'] ?? '',
                    ]
                );
                
                Log::info('Token Saved to DB', ['id' => $token->id]);

                // Dispatch Job Synchronously to ensure object is created immediately
                CreateGHLCustomObject::dispatchSync($token);

                // "crm should open at that time" - Redirect back to GHL Location Dashboard
                if (!empty($data['locationId'])) {
                    $ghlDashboardUrl = GhlConfig::APP_URL . $data['locationId'];
                    return redirect()->away($ghlDashboardUrl);
                }

                // Fallback if no location ID found
                return response()->json(['message' => 'App installed successfully. Token saved.', 'data' => $data]);

            } catch (\Exception $e) {
                Log::error('DB Save Failed', ['error' => $e->getMessage()]);
                return response()->json([
                    'error' => 'Failed to save to database',
                    'message' => $e->getMessage(),
                ], 500);
            }
        } else {
            Log::error('Token Exchange Failed', ['response' => $response->body()]);
            return response()->json(['error' => 'Failed to retrieve token', 'details' => $response->json()], $response->status());
        }
    }

    // TEMPORARILY DISABLED: Uninstall functionality
    /*
    public function uninstall(Request $request, \App\Http\Controllers\GhlController\Services\GHLTokenService $tokenService)
    {
        $data = $request->all();
        Log::info("GHL Webhook received at uninstall endpoint:", $data);

        // Check for event type to ensure we only process Uninstalls
        // GHL usually sends 'type' => 'AppUninstall' or 'Uninstall'
        $type = $data['type'] ?? '';
        
        // STRICT CHECK: Only process actual uninstall events
        if (!in_array($type, ['AppUninstall', 'Uninstall'])) {
            // If this endpoint is used as a Default Webhook URL, it will receive other events (like INSTALL).
            // We MUST return 200 OK and ignore them to prevent false positives or errors in GHL.
            return response()->json(['message' => 'Ignored event type: ' . $type]);
        }

        $locationId = $data['locationId'] ?? $data['location_id'] ?? null;

        if (!$locationId) {
             return response()->json(['error' => 'Location ID required'], 400); 
        }

        Log::info("Processing App Uninstall for location: {$locationId}");

        // 1. Delete Token from DB (This effectively logs them out of our system)
        $token = GoHighLevelToken::where('location_id', $locationId)->first();
        if ($token) {
             $token->delete();
             Log::info("Token deleted from DB for location: {$locationId}");
        } else {
             Log::info("No token found for location: {$locationId} (already deleted?)");
        }

        // Note: We previously attempted to delete the Custom Object Schema here via API.
        // However, GHL API returned 400 "Deleting object schema is not supported yet".
        // Therefore, we only perform local cleanup (Token deletion).
        // If GHL supports schema deletion in the future, add the logic back here.

        return response()->json(['status' => 'success', 'message' => 'App processed for uninstall']);
    }
    */
}
