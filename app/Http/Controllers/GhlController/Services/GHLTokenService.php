<?php

namespace App\Http\Controllers\GhlController\Services;

use App\Models\GoHighLevelToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Http\Controllers\GhlController\GhlConfig;

class GHLTokenService
{
    protected $clientId;
    protected $clientSecret;
    protected $baseUrl;

    public function __construct()
    {
        $this->clientId = config('services.gohighlevel.client_id');
        $this->clientSecret = config('services.gohighlevel.client_secret');
        $this->baseUrl = GhlConfig::API_BASE_URL;
    }

    /**
     * Get a valid access token for the given location.
     * Checks expiry and refreshes if necessary.
     *
     * @param string $locationId
     * @return string|null
     * @throws \Exception
     */
    public function getAccessToken(string $locationId)
    {
        $token = GoHighLevelToken::where('location_id', $locationId)->latest()->first();

        if (!$token) {
            Log::error("No GHL token found for location: {$locationId}");
            return null;
        }

        // Check if expired or expiring soon (5 minute buffer)
        $expiryBuffer = 5 * 60; // 5 minutes in seconds
        $expiresAt = $token->expires_at ? Carbon::parse($token->expires_at) : null;
        
        // If we don't have an expiry time, we assume it's valid or we can't check. 
        // But better to refresh if we are unsure, though if it's missing it might be a fresh install without expiry set yet.
        // Let's assume if expires_at is null, we should probably try to refresh or assume it implies "unknown/expired".
        // However, the migration just added it, so existing tokens might have null.
        // If null, we might rely on expires_in if it was saved recently? 
        // Let's rely on standard flow: if explicitly expired or within buffer.
        
        $shouldRefresh = false;
        if ($expiresAt && Carbon::now()->addSeconds($expiryBuffer)->greaterThanOrEqualTo($expiresAt)) {
            $shouldRefresh = true;
        }
        
        // Also check if expires_at is null but we want to be safe? 
        // If expires_at is missing, we might want to refresh to get a timestamp.
        if (is_null($expiresAt)) {
            // Check if we have expires_in. If not, maybe refresh.
             $shouldRefresh = true;
        }

        if ($shouldRefresh) {
            Log::info("Token for location {$locationId} is expired or near expiry. Refreshing...");
            return $this->refreshToken($token);
        }

        return $token->access_token;
    }

    /**
     * Refresh the access token using the refresh token.
     * Uses a lock to prevent multiple concurrent refreshes.
     *
     * @param GoHighLevelToken $token
     * @return string|null New access token
     * @throws \Exception
     */
    public function refreshToken(GoHighLevelToken $token)
    {
        $locationId = $token->location_id;
        $lockKey = "ghl_token_refresh_{$locationId}";
        
        // Try to acquire lock for 10 seconds
        $lock = Cache::lock($lockKey, 10);

        try {
            // Block for 5 seconds waiting for lock
            if ($lock->block(5)) {
                // Double check if token was updated while we were waiting
                $token->refresh();
                $expiresAt = $token->expires_at ? Carbon::parse($token->expires_at) : null;
                $expiryBuffer = 5 * 60;
                
                // If now valid, return it
                if ($expiresAt && Carbon::now()->addSeconds($expiryBuffer)->lessThan($expiresAt)) {
                    Log::info("Token for location {$locationId} was already refreshed by another process.");
                    return $token->access_token;
                }

                // Proceed with refresh
                $response = Http::asForm()->post("{$this->baseUrl}/oauth/token", [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $token->refresh_token,
                    'user_type' => 'Location', // Assuming Location level for now based on context
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Update DB
                    $token->access_token = $data['access_token'];
                    $token->refresh_token = $data['refresh_token'];
                    $token->expires_in = $data['expires_in'];
                    $token->expires_at = Carbon::now()->addSeconds($data['expires_in']);
                    $token->scope = $data['scope'] ?? $token->scope; // Update scope if returned
                    $token->save();

                    Log::info("Successfully refreshed token for location {$locationId}");
                    return $token->access_token;
                } else {
                    Log::error("Failed to refresh token for location {$locationId}. Status: " . $response->status(), [
                        'response' => $response->body()
                    ]);
                    
                    // Logic to mark as invalid or require re-auth could go here.
                    // For now, return null or throw exception.
                    return null;
                }
            } else {
                 Log::warning("Could not acquire lock to refresh token for location {$locationId}");
                 // If we couldn't get lock, maybe another process is doing it. 
                 // We can try to fetch from DB one last time.
                 $token->refresh();
                 return $token->access_token;
            }
        } finally {
            $lock->release();
        }
    }
    
    /**
     * Force refresh a token (e.g. on 401).
     * 
     * @param string $locationId
     * @return string|null
     */
    public function forceRefreshToken(string $locationId)
    {
        $token = GoHighLevelToken::where('location_id', $locationId)->latest()->first();
        if (!$token) return null;
        
        return $this->refreshToken($token);
    }
}
