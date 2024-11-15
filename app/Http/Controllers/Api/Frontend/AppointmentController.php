<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use App\Models\BuisnessHour;
use App\Models\Service;
use App\Models\User; // Ensure you import User model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Import Hash for password hashing
use Illuminate\Support\Facades\Log; // Import Log for logging
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; // Import Str for string generation
class AppointmentController extends Controller
{


public function getUserAppointments()
{
    // Get the authenticated user
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Retrieve the user's email
    $email = $user->email;

    // Fetch appointments for the authenticated user and eager load the related service
    $appointments = Appointment::with('service') // Eager load the related service
        ->where('email', $email)
        ->get();

    return response()->json([
        'appointments' => $appointments,
    ]);
}


public function confirm(Request $request, $id)
{

    // Validate the payment method in the request
    $request->validate([
        'payment_method' => 'required|string',
        'stripePaymentMethodId' => 'required_if:payment_method,stripe|string',
    ]);

    Log::info('Confirm appointment called', ['appointment_id' => $id, 'payment_method' => $request->payment_method]);

    // Find the appointment by ID
    $appointment = Appointment::find($id);

    if (!$appointment) {
        Log::error('Appointment not found', ['appointment_id' => $id]);
        return response()->json(['message' => 'Appointment not found'], 404);
    }

    // Check if the payment method is Stripe
    if ($request->payment_method === 'stripe') {
        try {
            Log::info('Processing Stripe payment', ['appointment_id' => $id, 'amount' => $appointment->service->cost]);

            // Initialize Stripe with your secret key
            \Stripe\Stripe::setApiKey('sk_test_51Psjz4IoXz8untciW3VMD1DR6pIBIcBbiXgFwIR5DhAJDDxBEBtMziAfWYwadQfo3JdkH1ZtwHIRmP6bB0SpWCN9009Q3SlKnd');

            // Create a PaymentIntent without confirming it
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $appointment->service->cost * 100, // Convert to cents
                'currency' => 'usd', // Adjust to your currency
                'payment_method' => $request->stripePaymentMethodId,
                'confirm' => false, // Don't confirm immediately
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never', // Disable redirects
                ],
            ]);

            Log::info('Stripe payment intent created', ['payment_intent_id' => $paymentIntent->id, 'status' => $paymentIntent->status]);

            // After successful Stripe payment, update appointment status
            $appointment->status = 'confirmed';
            $appointment->payment_method = 'stripe';
            $appointment->payment_status = 'pending'; // Set as pending before confirmation
            $appointment->save();

            Log::info('Appointment confirmed and payment processed via Stripe', ['appointment_id' => $id]);

            // Return the client secret to the frontend for further processing
            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error processing Stripe payment', [
                'error' => $e->getMessage(),
                'appointment_id' => $id,
                'payment_method' => $request->payment_method,
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    } else if ($request->payment_method === 'paypal') {
        // Handle PayPal payment method (Assumed to be processed already)
        Log::info('Processing PayPal payment', ['appointment_id' => $id]);

        // Confirm the appointment and set the payment method and status
        $appointment->status = 'confirmed';
        $appointment->payment_method = 'paypal';
        $appointment->payment_status = 'paid';
        $appointment->save();

        Log::info('Appointment confirmed and payment processed via PayPal', ['appointment_id' => $id]);

        return response()->json([
            'message' => 'Appointment confirmed and paid via PayPal',
            'appointment' => $appointment,
        ], 200);
    } else if ($request->payment_method === 'cod') {
        // Handle COD payment method (No payment gateway, just confirm the appointment)
        Log::info('Confirming appointment with COD', ['appointment_id' => $id]);

        // Confirm the appointment and set the payment method and status
        $appointment->status = 'confirmed';
        $appointment->payment_method = 'cod';
        $appointment->payment_status = 'paid';
        $appointment->save();

        Log::info('Appointment confirmed with COD', ['appointment_id' => $id]);

        return response()->json([
            'message' => 'Appointment confirmed with Cash on Delivery',
            'appointment' => $appointment,
        ], 200);
    }
    // Additional conditions if needed
}


public function updateStatus(Request $request, $id)
{
    // Validate the incoming request data
    $request->validate([
        'payment_status' => 'required|string',
        'payment_method' => 'required|string',
    ]);

    // Log incoming request data for debugging purposes
    Log::debug('Received request to update appointment status', [
        'appointment_id' => $id,
        'payment_status' => $request->payment_status,
        'payment_method' => $request->payment_method
    ]);

    // Find the appointment by ID
    $appointment = Appointment::find($id);

    if (!$appointment) {
        // Log error when appointment is not found
        Log::error('Appointment not found', [
            'appointment_id' => $id
        ]);
        
        return response()->json(['message' => 'Appointment not found'], 404);
    }

    // Log appointment details before updating
    Log::debug('Appointment found, updating status', [
        'appointment_id' => $appointment->id,
        'current_payment_status' => $appointment->payment_status,
    ]);

    // Update the payment status and method
    $appointment->payment_status = $request->payment_status;
    $appointment->save();

    // Log the updated appointment details
    Log::debug('Appointment status updated', [
        'appointment_id' => $appointment->id,
        'updated_payment_status' => $appointment->payment_status,
    ]);

    // Return the response with updated appointment details
    return response()->json([
        'success' => true,
        'message' => 'Appointment status updated successfully',
        'appointment' => $appointment
    ], 200);
}



    // AppointmentController.php

public function show($id)
{
    // Find the appointment by ID and eager load the related service
    $appointment = Appointment::with('service')->find($id);

    // Check if the appointment exists
    if (!$appointment) {
        return response()->json(['message' => 'Appointment not found'], 404);
    }

    // Return the appointment with the related service data
    return response()->json($appointment, 200);
}

public function store(Request $request)
{
    // Log the incoming request data
    Log::info('Appointment booking attempt', ['request' => $request->all()]);

    // Validate appointment data
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255',
        'phone' => 'required|string|max:15',
        'service_id' => 'required|exists:services,id',
        'date' => 'required|date',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
    ]);

    if ($validator->fails()) {
        Log::warning('Validation failed', ['errors' => $validator->errors()]);
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Check if user exists or create a new user
    $user = User::where('email', $request->email)->first();
    $isNewUser = false; // Flag to check if the user is new

    if (!$user) {
        // Create new user
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make(Str::random(10)), // Generate a random password for internal purposes.
                'role' => 'customer', // Default role
            ]);
            Log::info('User created successfully', ['user_id' => $user->id]);

            // Generate password reset token
            $token = Password::createToken($user);

            // Send reset password email
            try {
                Mail::send('emails.reset_password', ['token' => $token, 'user' => $user], function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Password Reset Request');
                });

                Log::info('Password reset email sent successfully to ' . $user->email);
            } catch (\Exception $e) {
                Log::error('Error sending password reset email', ['error' => $e->getMessage()]);
            }

            // Set flag to true to indicate new user
            $isNewUser = true;
        } catch (\Exception $e) {
            Log::error('User creation failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'User registration failed'], 500);
        }
    }

    // Generate token for the user (logged in)
    $token = $user->createToken('API Token')->plainTextToken;

    // Create the appointment
    try {
        $appointment = Appointment::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'service_id' => $request->service_id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_new_user' => $isNewUser, 

        ]);

        // Log appointment details
        Log::info('Appointment created successfully', ['appointment' => $appointment]);

        // Return success response with appointment ID and user information, including the new user message
        return response()->json([
            'message' => 'Appointment booked successfully',
            'appointment' => $appointment,
            'user' => $user,
            'token' => $token,
        ], 201);
    } catch (\Exception $e) {
        // Handle error during appointment creation
        Log::error('Appointment creation failed', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Appointment creation failed'], 500);
    }
}




    // The rest of your methods remain the same...


    public function getAvailableSlots(Request $request)
    {
        // Fetch available slots based on business hours and existing appointments
        $date = $request->query('date');
        $serviceId = $request->query('service_id');

        // Get business hours for the selected day
        $businessHours = BuisnessHour::where('day', date('l', strtotime($date)))->first();

        if (!$businessHours) {
            return response()->json(['message' => 'Business hours not set for this day'], 404);
        }

      $startTime = \Carbon\Carbon::parse($businessHours->open_time)->format('H:i');
    $endTime = \Carbon\Carbon::parse($businessHours->close_time)->format('H:i');


        // Get the selected service to determine its duration
        $service = Service::find($serviceId);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        // Get existing appointments for the selected date and service
        $appointments = Appointment::where('date', $date)
            ->where('service_id', $serviceId)
            ->get();

        // Calculate available slots
        $availableSlots = [];
        $currentTime = \Carbon\Carbon::createFromFormat('H:i', $startTime);

        while ($currentTime->format('H:i') < $endTime) {
            $slotStart = $currentTime->format('H:i');
            $slotEnd = $currentTime->copy()->addMinutes($service->duration)->format('H:i'); // Using service duration

            // Check if this slot is already booked
            $isBooked = $appointments->contains(function ($appointment) use ($slotStart, $slotEnd) {
                return ($appointment->start_time < $slotEnd && $appointment->end_time > $slotStart);
            });

            if (!$isBooked) {
                $availableSlots[] = [
                    'start_time' => $slotStart,
                    'end_time' => $slotEnd,
                ];
            }

            $currentTime->addMinutes($service->duration); // Move to the next slot
        }

        return response()->json($availableSlots); // Return available slots as JSON response
    }
    // The rest of your methods remain the same...

public function cancel(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'id' => 'required|exists:appointments,id',  // Ensure the appointment exists
        ]);

        try {
            // Find the appointment by ID
            $appointment = Appointment::find($request->id);

            if (!$appointment) {
                return response()->json(['error' => 'Appointment not found'], 404);
            }

            // Change the status to 'canceled'
            $appointment->status = 'canceled';
            $appointment->save();

            // Optionally log the cancellation
            Log::info("Appointment with ID {$appointment->id} has been canceled.");

            // Return success response
            return response()->json(['message' => 'Appointment canceled successfully'], 200);
        } catch (\Exception $e) {
            // Log the exception and return error
            Log::error('Error canceling appointment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to cancel the appointment'], 500);
        }
    }

}


