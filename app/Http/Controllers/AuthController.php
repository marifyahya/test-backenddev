<?php

namespace App\Http\Controllers;

use App\Helpers\JWT;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Mailgun\Mailgun;

class AuthController extends Controller
{
    // Register
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="User Register",
     *     tags={"AuthController"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 example={"id": "eve.holt@reqres.in", "name": "pistol"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"message": "success"}, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"errors": {"email": {"The email field is required."},"password": {"The password field is required."}}}, summary="An result object."),
     *         ),
     *     )
     * )
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    $user = User::where('email', $value)->first();
                    if ($user) {
                        $fail('The ' . $attribute . ' has already been taken.');
                    }
                },
            ],
            'password' => 'required|min:6'
        ]);

        User::create([
            'email' => $request->email,
            'password' => $request->password
        ]);

        return response(['message' => 'success']);
    }

    // Login
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User Login",
     *     tags={"AuthController"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 example={"id": "eve.holt@reqres.in", "name": "pistol"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *              "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE2NDUxODc4MTcsInVpZCI6IjYyMGVjZTk3MjcwOTAwMDAxNDAwNzc2NiIsImV4cCI6MTY0NTIwOTQxNywiaXNzIjoibG9jYWxob3N0In0.eutV9MDUzejajQsWERThDFeP4TmqOPgrKnt8Hq9-z9M",
     *              "token_type": "bearer",
     *              "expires_in": 1645209417
     *              }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"message": "Email atau password tidak sesuai"}, summary="An result object."),
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            $token = $this->generateToken($user->_id);
            return response($token);
        }

        return response(['message' => 'Email atau password tidak sesuai'], 400);
    }

    // Refresh token JWT
    public function refreshTokenJWT(Request $request)
    {
        $user = Auth::user();
        $token = $this->generateToken($user->_id);
        return response($token);
    }

    // Generate token
    public function generateToken($uid)
    {
        $token = JWT::generate($uid);
        $user = User::find($uid);
        $user->token = $token['access_token'];
        $user->save();

        return $token;
    }

    // Logout
    /**
     * @OA\POST(
     *     path="/api/logout",
     *     summary="User Logout",
     *     tags={"AuthController"},
     *     @OA\SecurityScheme(
     *          type="http",
     *          description="Login with email and password to get the authentication token",
     *          name="Token based Based",
     *          in="header",
     *          scheme="bearer",
     *          bearerFormat="JWT",
     *          securityScheme="apiAuth",
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"message": "Logout berhasil"}, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"message": "Unauthorized."}, summary="An result object."),
     *         ),
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->token = null;
        $user->save();

        return response(['message' => 'Logout berhasil']);
    }

    // Get Profile
    /**
     * @OA\Get(
     *     path="/api/profile",
     *     summary="User Profile",
     *     tags={"AuthController"},
     *     @OA\SecurityScheme(
     *          securityScheme="bearerAuth",
     *          type="http",
     *          scheme="bearer"
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"_id": "620ece972709000014007766","email": "arif@gmail.com","updated_at": "2022-02-18T12:36:57.937000Z","created_at": "2022-02-17T22:39:19.581000Z"}, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"message": "Unauthorized."}, summary="An result object."),
     *         ),
     *     )
     * )
     */
    public function getProfile()
    {
        return response(Auth::user());
    }

    // Forgote Password
    /**
     * @OA\POST(
     *     path="/api/forgote-password",
     *     summary="User Forgote Password",
     *     tags={"AuthController"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 example={"email": "eve.holt@reqres.in"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"message": "Cek OTP di akun email anda"}, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"message": "Tidak ada Akun dengan email yang Anda berikan"}, summary="An result object."),
     *         ),
     *     )
     * )
     */
    public function forgotePassword(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        // Cek user
        $user = User::where('email', $request->email)->first();
        if (!$user) return response()->json([
            'message' => 'Tidak ada Akun dengan email yang Anda berikan'
        ], 400);

        // Create kode OTP
        $otp = rand(10000, 99999);
        $user->otp = [
            'code' => $otp,
            'expired' => date('Y-m-d H:i:s', strtotime("+10 minutes"))
        ];
        $user->save();

        // First, instantiate the SDK with your API credentials
        $mg = Mailgun::create(env('MAILGUN_SECRET')); // For US servers

        // Now, compose and send your message.
        // $mg->messages()->send($domain, $params);
        $mg->messages()->send(ENV('MAILGUN_DOMAIN'), [
            'from'    => 'support@marifyahya.com',
            'to'      => env('MAILGUN_MAILTO_SANDBOX'),
            'subject' => 'Kode OTP konfirmasi akun',
            'text'    => "Hi... Masukan kode OTP ini $otp. Kode ini hanya berlaku 10 menit. Jangan beritahu kode ini pada siapa pun."
        ]);

        return response(['message' => 'Cek OTP di akun email anda']);
    }

    // Reset password
    /**
     * @OA\POST(
     *     path="/api/reset-password",
     *     summary="User reset Password",
     *     tags={"AuthController"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="otp",
     *                     type="string"
     *                 ),
     *                 example={"email": "arif@gmail.com", "password": "123456", "password_confirmation": "123456", "otp": "20519"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"message": "Password berhasil diperbaharui."}, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"message": "User tidak ditemukan atau kode OTP expired."}, summary="An result object."),
     *         ),
     *     )
     * )
     */
    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            'otp' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $otp = $user->otp;
            if ($otp) {
                // check is otp expired & valid
                if ($otp['expired'] < date('Y-m-d H:i:s')) return response(['message' => 'Kode OTP Expired.'], 400);
                if ($otp['code'] != $request->otp) return response(['message' => 'Kode OTP tidak sesuai.'], 400);

                // update user password
                $user->password = $request->password;
                $user->unset('otp');
                $user->save();

                return response(['message' => 'Password berhasil diperbaharui.']);
            }
        }

        return response(['message' => 'User tidak ditemukan atau kode OTP expired.'], 400);
    }
}
