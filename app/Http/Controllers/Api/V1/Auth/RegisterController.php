<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'unique:users', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        //Below line triggers an event called "Registered" and passes the $user object as a parameter to the event.
        event(new Registered($user));

        //Below line performs an operation to extract a substring from the user agent string obtained from the incoming request.
        //$request->userAgent(): Retrieves the user agent string from the HTTP request. The user agent typically contains information about the client's browser, operating system, and device.
        // ?? '' : is the null coalescing operator that checks if the user agent string is null or empty. If it is, it returns an empty string as a fallback value.
        // substr() : is a PHP function used to extract a portion of a string. It takes three parameters: the string to extract from, the starting position (0 in this case), and the length of the substring (255 in this case).
        // The extracted substring is assigned to the variable $device which will hold the resulting substring.
        // The purpose of extracting the user agent substring is to gather information about the client's device or browser for logging, tracking, or other purposes as we will see as I progress with the project.
        $device = substr($request->userAgent() ?? '', 0, 255);
        

        return response()->json([
            'access_token' => $user->createToken($device)->plainTextToken,
        ], Response::HTTP_CREATED);
    }
}
