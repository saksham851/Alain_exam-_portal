<?php

namespace App\Http\Controllers\GhlController\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\GhlController\GhlConfig;

class RefreshGoHighLevelTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ghl:refresh-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for expiring GoHighLevel tokens and refreshes them using the refresh token.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tokens = \App\Models\GoHighLevelToken::all();
        $clientId = env('GHL_CLIENT_ID', '693a42c5c515cfdbdf604462-mj0xz7k2');
        $clientSecret = env('GHL_CLIENT_SECRET');

        foreach ($tokens as $token) {
            // Calculate seconds since creation
            $secondsSinceCreation = now()->diffInSeconds($token->updated_at);
            $expiresIn = $token->expires_in ?? 86400; // Default 24h

            // If token is older than 23 hours (giving 1 hour buffer), refresh it.
            // Or if you want to force refresh all for testing, remove the condition.
            // Using 23 hours (82800 seconds) as threshold.
            if ($secondsSinceCreation > ($expiresIn - 3600)) { 
                $this->info("Refreshing token for Location ID: {$token->location_id}");
                
                $response = \Illuminate\Support\Facades\Http::asForm()->post(GhlConfig::TOKEN_URL, [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $token->refresh_token,
                    'user_type' => $token->user_type ?? 'Location',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    $token->update([
                        'access_token' => $data['access_token'],
                        'refresh_token' => $data['refresh_token'],
                        'expires_in' => $data['expires_in'] ?? 86400,
                        'user_type' => $data['userType'] ?? $token->user_type,
                    ]);

                    $this->info("Success: Token updated for Location ID: {$token->location_id}");
                } else {
                    $this->error("Failed to refresh token for Location ID: {$token->location_id}. Error: " . $response->body());
                }
            } else {
                $this->info("Skipping Location ID: {$token->location_id} (Not expired yet)");
            }
        }
    }
}
