<?php

namespace App\Http\Controllers\GhlController\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\GoHighLevelToken;
// GHLTokenService is in the same namespace, so no import needed, but typehint requires it to be resolved.
// Since it's in the same namespace, `public function __construct(GHLTokenService $tokenService)` works.
use App\Http\Controllers\GhlController\GhlConfig;

class GHLRecordService
{
    /**
     * Create a record in GHL Custom Object
     * 
     * @param array $recordData
     * @param string $locationId (optional - will fetch from DB if not provided)
     * @return array
     */
    protected $tokenService;

    public function __construct(GHLTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Create a record in GHL Custom Object
     * 
     * @param array $recordData
     * @param string $objectKey
     * @param string $locationId (optional - will fetch from DB if not provided)
     * @return array
     */
    public function createRecord(array $recordData, string $objectKey, ?string $locationId = null)
    {
        try {
            // Get the access token and location ID
            if (!$locationId) {
                // If no location ID provided, try to find the latest valid token to get a location ID
                // This is a bit ambiguous if there are multiple locations. 
                // We'll mimic old behavior: get latest token.
                $token = GoHighLevelToken::latest()->first();
                if (!$token) {
                    Log::error('No GHL token found in database');
                    return ['success' => false, 'message' => 'No GHL token found'];
                }
                $locationId = $token->location_id;
            }

            $accessToken = $this->tokenService->getAccessToken($locationId);

            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Unable to retrieve valid access token'
                ];
            }

            $version = GhlConfig::API_VERSION;
            $recordUrl = GhlConfig::API_BASE_URL . GhlConfig::ENDPOINTS['objects'] . $objectKey . '/records';

            // Map the incoming data to GHL custom field keys
            $properties = $this->mapToCustomFields($recordData, $objectKey);

            Log::info('Mapped properties:', $properties);

            // Prepare the request body
            $requestBody = [
                'locationId' => $locationId,
                'properties' => $properties
            ];

            Log::info('Creating GHL record with data:', $requestBody);

            // Make the API request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Version' => $version,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($recordUrl, $requestBody);

            // Handle 401 Unauthorized - Retry logic
            if ($response->status() === 401) {
                Log::warning("Received 401 from GHL. Forcing token refresh for location {$locationId} and retrying.");

                $accessToken = $this->tokenService->forceRefreshToken($locationId);

                if ($accessToken) {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Version' => $version,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->post($recordUrl, $requestBody);
                } else {
                    Log::error("Failed to force refresh token for location {$locationId}");
                    return [
                        'success' => false,
                        'message' => 'Authentication failed. Please re-connect GHL.'
                    ];
                }
            }

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('GHL record created successfully:', $responseData);

                return [
                    'success' => true,
                    'message' => 'Record created successfully',
                    'data' => $responseData
                ];
            } else {
                // Handle 400 Bad Request
                if ($response->status() === 400) {
                    Log::error('Failed to create GHL record. Status: ' . $response->status() . ', Response: ' . $response->body());

                    return [
                        'success' => false,
                        'message' => 'Failed to create record in GHL',
                        'status' => $response->status(),
                        'error' => $response->body()
                    ];
                }

                Log::error('Failed to create GHL record. Status: ' . $response->status() . ', Response: ' . $response->body());

                return [
                    'success' => false,
                    'message' => 'Failed to create record in GHL',
                    'status' => $response->status(),
                    'error' => $response->body()
                ];
            }

        } catch (\Exception $e) {
            Log::error('Exception while creating GHL record: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Map incoming data to GHL custom field keys by prepending the object key
     * (except for the 'name' field which is usually the primary display property)
     * 
     * @param array $data
     * @param string $objectKey
     * @return array
     */
    protected function mapToCustomFields(array $data, string $objectKey)
    {
        // GHL usually expects short keys in the properties bag when the object is already specified in the URL
        return $data;
    }


    /**
     * Update an existing record in GHL Custom Object
     * 
     * @param string $recordId
     * @param array $recordData
     * @param string $objectKey
     * @param string $locationId (optional)
     * @return array
     */
    public function updateRecord(string $recordId, array $recordData, string $objectKey, ?string $locationId = null)
    {
        try {
            // Get the access token and location ID
            if (!$locationId) {
                $token = GoHighLevelToken::latest()->first();

                if (!$token) {
                    Log::error('No GHL token found in database');
                    return [
                        'success' => false,
                        'message' => 'No GHL token found'
                    ];
                }

                $locationId = $token->location_id;
            }

            $accessToken = $this->tokenService->getAccessToken($locationId);

            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Unable to retrieve valid access token'
                ];
            }

            $version = GhlConfig::API_VERSION;
            $recordUrl = GhlConfig::API_BASE_URL . GhlConfig::ENDPOINTS['objects'] . $objectKey . '/records/' . $recordId;

            // Map the incoming data to GHL custom field keys
            $properties = $this->mapToCustomFields($recordData, $objectKey);

            // Prepare the request body - GHL API expects only 'properties'
            $requestBody = [
                'locationId' => $locationId,
                'properties' => $properties
            ];

            Log::info('Updating GHL record with data:', $requestBody);

            // Make the API request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Version' => $version,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->put($recordUrl, $requestBody);

            // Handle 401 Unauthorized - Retry logic
            if ($response->status() === 401) {
                Log::warning("Received 401 from GHL. Forcing token refresh for location {$locationId} and retrying.");

                $accessToken = $this->tokenService->forceRefreshToken($locationId);

                if ($accessToken) {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Version' => $version,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->put($recordUrl, $requestBody);
                } else {
                    Log::error("Failed to force refresh token for location {$locationId}");
                    return [
                        'success' => false,
                        'message' => 'Authentication failed. Please re-connect GHL.'
                    ];
                }
            }

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('GHL record updated successfully:', $responseData);

                return [
                    'success' => true,
                    'message' => 'Record updated successfully',
                    'data' => $responseData
                ];
            } else {
                // Check for invalid phone number error and retry without phone
                $responseBody = $response->body();
                if ($response->status() === 400 && str_contains($responseBody, 'Invalid phone number')) {
                    Log::warning('GHL record update failed due to invalid phone. Retrying without phone.', [
                        'omitted_phone' => $properties['phone'] ?? 'unknown'
                    ]);

                    // Remove phone from properties and request body
                    unset($properties['phone']);
                    $requestBody['properties'] = $properties;

                    // Retry the request
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Version' => $version,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->put($recordUrl, $requestBody);

                    if ($response->successful()) {
                        $responseData = $response->json();
                        Log::info('GHL record updated successfully (retry without phone):', $responseData);

                        return [
                            'success' => true,
                            'message' => 'Record updated successfully (phone omitted)',
                            'data' => $responseData
                        ];
                    }
                }

                Log::error('Failed to update GHL record. Status: ' . $response->status() . ', Response: ' . $response->body());

                return [
                    'success' => false,
                    'message' => 'Failed to update record in GHL',
                    'status' => $response->status(),
                    'error' => $response->body()
                ];
            }

        } catch (\Exception $e) {
            Log::error('Exception while updating GHL record: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Upsert a contact in GHL
     * 
     * @param array $contactData
     * @param string|null $locationId
     * @return array
     */
    public function upsertContact(array $contactData, ?string $locationId = null)
    {
        try {
            if (!$locationId) {
                $token = GoHighLevelToken::latest()->first();
                if (!$token) {
                    Log::error('No GHL token found in database');
                    return ['success' => false, 'message' => 'No GHL token found'];
                }
                $locationId = $token->location_id;
            }

            $accessToken = $this->tokenService->getAccessToken($locationId);

            if (!$accessToken) {
                return ['success' => false, 'message' => 'Unable to retrieve valid access token'];
            }

            $version = '2023-02-21'; // Using the newer version for upsert
            $url = GhlConfig::API_BASE_URL . GhlConfig::ENDPOINTS['contacts_upsert'];

            $payload = array_merge(['locationId' => $locationId], $contactData);

            Log::info('Upserting GHL contact with data:', $payload);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Version' => $version,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($url, $payload);

            if ($response->status() === 401) {
                $accessToken = $this->tokenService->forceRefreshToken($locationId);
                if ($accessToken) {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Version' => $version,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->post($url, $payload);
                }
            }

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Contact upserted successfully',
                    'data' => $response->json()
                ];
            }

            Log::error('Failed to upsert GHL contact. Status: ' . $response->status() . ', Response: ' . $response->body());
            return [
                'success' => false,
                'message' => 'Failed to upsert contact in GHL',
                'status' => $response->status(),
                'error' => $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Exception while upserting GHL contact: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage()
            ];
        }
    }
}
