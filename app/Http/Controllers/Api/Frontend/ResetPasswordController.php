<?php
namespace App\Http\Controllers\Api\Frontend;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // Import the Log facade
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    public function showResetForm($token)
    {
        // Log the token received
        Log::info('Show Reset Form request', ['token' => $token]);

        // Return the Vue component or API response for resetting the password
        return response()->json(['token' => $token]);
    }

   public function reset(Request $request)
{
    // Log incoming request data for debugging
    Log::info('Password Reset attempt', ['request_data' => $request->all()]);

    // Validate the request
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|confirmed|min:8',
        'token' => 'required',
    ]);

    // Log validation errors if any
    if ($validator->fails()) {
        Log::warning('Validation failed', ['errors' => $validator->errors()]);
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Log that validation passed
    Log::info('Validation passed, proceeding with password reset');

    // Attempt to reset the password
    $response = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user) use ($request) {
            try {
                // Update the user's password
                $user->password = Hash::make($request->password);
                $user->save();

                // Log success message
                Log::info('Password reset successful for user: ' . $user->email);
            } catch (\Exception $e) {
                // Log error message in case of failure
                Log::error('Password reset failed for user: ' . $request->email . '. Error: ' . $e->getMessage());
            }
        }
    );

    // Log the response status for debugging
    Log::info('Password reset response: ' . $response);

    // Check the password reset status and log the result
    if ($response == Password::PASSWORD_RESET) {
        Log::info('Password reset successful', ['email' => $request->email]);
        return response()->json(['message' => 'Password has been reset successfully.'], 200);
    }

    // Log failure case
    Log::error('Password reset failed', ['email' => $request->email, 'response' => $response]);
    return response()->json(['error' => 'Password reset failed.'], 500);
}




public function sendResetLinkEmail(Request $request)
{
    $request->validate(['email' => 'required|email']);

    // Attempt to find the user
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        // If the user doesn't exist, return an error
        return back()->withErrors(['email' => 'We could not find a user with that email address.']);
    }

    try {
        // Generate password reset token
        $token = Password::createToken($user);

        // Send reset password email using the custom view
        Mail::send('emails.reset_password', ['token' => $token, 'user' => $user], function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Password Reset Request');
        });

        // Log successful email sending
        Log::info('Password reset email sent successfully to ' . $user->email);

        // Return a success message
        return back()->with('status', 'Password reset link sent successfully.');

    } catch (\Exception $e) {
        // Log any errors that occur while sending the email
        Log::error('Error sending password reset email', ['error' => $e->getMessage()]);

        // Return an error message
        return back()->withErrors(['email' => 'There was an error sending the reset email.']);
    }
}


}
