<?php

namespace App\Http\Controllers\GhlController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Exam;
use App\Models\StudentExam;
use App\Http\Controllers\GhlController\Services\GHLRecordService;

class WebhookController extends Controller
{
    /**
     * Handle incoming webhook data from external sources
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {
        try {
            // Get all incoming data
            $data = $request->all();
            
            // Log the incoming webhook data for debugging
            Log::info('Webhook received:', $data);
            
            // Check if customdata exists
            if (isset($data['customdata'])) {
                $customData = $data['customdata'];
                
                // Extract the data
                $event = $customData['event'] ?? null;
                $ghlContactId = $customData['ghl_contact_id'] ?? null;
                $email = $customData['email'] ?? null;
                $firstName = $customData['firstName'] ?? null;
                $lastName = $customData['lastName'] ?? null;
                $phone = $customData['phone'] ?? null;
                $examTitle = $customData['exam_title'] ?? null;
                $examDescription = $customData['exam_description'] ?? null;
                
                // Log extracted data
                Log::info('Extracted webhook data:', [
                    'event' => $event,
                    'ghl_contact_id' => $ghlContactId,
                    'email' => $email,
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'phone' => $phone,
                    'exam_title' => $examTitle,
                    'exam_description' => $examDescription,
                ]);
                
                // Validate required fields
                if (empty($email) || empty($firstName) || empty($lastName) || empty($phone)) {
                    Log::warning('Webhook validation failed: Missing required fields');
                    return response()->json([
                        'success' => false,
                        'message' => 'Missing required fields: email, firstName, lastName, and phone are required'
                    ], 400);
                }
                
                // Handle contact.created event
                if ($event === 'contact.created') {
                    // Check if user exists or create new one
                    $user = User::where('email', $email)->first();
                    $isNewUser = false;
                    $generatedPassword = null;

                    if (!$user) {
                        $isNewUser = true;
                        // Generate password in format: firstName@randomnumber
                        $randomNumber = rand(100000, 999999); // 6-digit random number
                        $generatedPassword = $firstName . '@' . $randomNumber;

                        $user = User::create([
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'email' => $email,
                            'phone' => $phone,
                            'password' => Hash::make($generatedPassword),
                            'role' => 'student', // Default role
                            'status' => 1, // 1 = active, 0 = inactive
                            'is_blocked' => false,
                        ]);

                        Log::info('New user created successfully:', [
                            'user_id' => $user->id,
                            'email' => $email,
                            'generated_password' => $generatedPassword // Log for debugging (remove in production)
                        ]);
                    } else {
                        Log::info('Existing user found:', [
                            'user_id' => $user->id,
                            'email' => $email
                        ]);
                    }

                    // Assign Exam if provided
                    $examAssignmentStatus = 'Not provided';
                    $examAssignmentMessage = null;

                    if (!empty($examTitle)) {
                        $exam = Exam::where('name', $examTitle)->first();
                        
                        // Check if exam exists and is active (status 1) AND published (is_active 1)
                        if ($exam && $exam->status == 1 && $exam->is_active == 1) {
                            $existingStudentExam = StudentExam::where('student_id', $user->id)
                                ->where('exam_id', $exam->id)
                                ->first();

                            if ($existingStudentExam) {
                                // Just increase the attempts by 3
                                $existingStudentExam->increment('attempts_allowed', 3);
                                $examAssignmentStatus = 'Updated';
                                $examAssignmentMessage = "Existing assignment found. Increased attempts by 3 for '{$exam->name}'.";
                                
                                Log::info('Exam attempts increased for existing assignment:', [
                                    'user_id' => $user->id,
                                    'exam_name' => $exam->name,
                                    'new_attempts_allowed' => $existingStudentExam->attempts_allowed
                                ]);
                            } else {
                                StudentExam::create([
                                    'student_id' => $user->id, // Assign to the student
                                    'exam_id' => $exam->id,
                                    'expiry_date' => now()->addWeeks(4), // 4 weeks from now
                                    'attempts_allowed' => 3, // 3 attempts
                                    'attempts_used' => 0,
                                    'source' => 'Webhook',
                                    'status' => 1
                                ]);
                                
                                $examAssignmentStatus = 'Assigned';
                                $examAssignmentMessage = "Successfully assigned new exam '{$exam->name}'.";

                                Log::info('Exam assigned successfully:', [
                                    'user_id' => $user->id,
                                    'exam_name' => $exam->name
                                ]);
                            }
                        } else {
                            $examAssignmentStatus = 'Failed';
                            if (!$exam) {
                                $examAssignmentMessage = "Exam '{$examTitle}' not found.";
                            } elseif ($exam->status != 1) {
                                $examAssignmentMessage = "Exam '{$examTitle}' is deleted.";
                            } elseif ($exam->is_active != 1) {
                                $examAssignmentMessage = "Exam '{$examTitle}' is unpublished (Draft mode).";
                            }
                            
                            Log::warning('Exam assignment passed: ' . $examAssignmentMessage, [
                                'exam_title' => $examTitle
                            ]);
                        }
                    }
                    
                    if ($isNewUser) {
                        // Generate password reset token
                        $token = \Illuminate\Support\Facades\Password::createToken($user);
                        
                        // Generate reset URL
                        $resetUrl = url(route('password.reset', [
                            'token' => $token,
                            'email' => $user->email,
                            ], false));
                        
                        // Send welcome email
                        try {
                            \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\NewUserWelcomeMail($user, $generatedPassword, $resetUrl));
                            Log::info('Welcome email sent to: ' . $email);
                        } catch (\Exception $e) {
                            Log::error('Failed to send welcome email: ' . $e->getMessage());
                        }
                    }
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Webhook processed successfully',
                        'received_data' => $data,
                        'user_details' => [
                            'id' => $user->id,
                            'email' => $user->email,
                            'is_new_user' => $isNewUser,
                            'password' => $generatedPassword, // Only if new
                            'exam_assignment' => [
                                'status' => $examAssignmentStatus,
                                'message' => $examAssignmentMessage
                            ]
                        ]
                    ], 200);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook processed successfully (Event not handled or fallthrough)',
                    'received_data' => $data,
                     // Add debug info to understand why it fell through
                    'debug_event_received' => $event
                ], 200);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook data format'
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('Webhook processing error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing webhook',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle exam completion webhook and create record in GHL Custom Object
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleExamCompletion(Request $request)
    {
        try {
            // Get all incoming data
            $data = $request->all();
            
            // Log the incoming webhook data for debugging
            Log::info('Exam completion webhook received:', $data);
            
            // Validate required fields
            $requiredFields = ['name', 'email', 'exam_name'];
            $missingFields = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                Log::warning('Exam completion webhook validation failed: Missing required fields', [
                    'missing_fields' => $missingFields
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields: ' . implode(', ', $missingFields),
                    'required_fields' => $requiredFields
                ], 400);
            }
            
            // Prepare the record data for GHL
            $recordData = [
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? '',
                'ig_score' => $data['ig_score'] ?? 0,
                'dm_score' => $data['dm_score'] ?? 0,
                'total_score' => $data['total_score'] ?? 0,
                'attempts' => $data['attempts'] ?? 1,
                'status' => $data['status'] ?? '',
                'exam_name' => $data['exam_name'] ?? '',
            ];
            
            Log::info('Prepared record data for GHL:', $recordData);
            
            // Create the record in GHL Custom Object
            $ghlService = app(GHLRecordService::class);
            $locationId = $data['location_id'] ?? null; // Optional: can be passed in webhook
            
            $result = $ghlService->createRecord($recordData, $locationId);
            
            if ($result['success']) {
                Log::info('Exam completion record created successfully in GHL');
                
                return response()->json([
                    'success' => true,
                    'message' => 'Exam completion record created successfully',
                    'ghl_response' => $result['data'] ?? null
                ], 200);
            } else {
                Log::error('Failed to create exam completion record in GHL', [
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create record in GHL',
                    'error' => $result['message'] ?? 'Unknown error',
                    'details' => $result['error'] ?? null
                ], 500);
            }
            
        } catch (\Throwable $e) {
            Log::error('Exam completion webhook processing error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing exam completion webhook',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
