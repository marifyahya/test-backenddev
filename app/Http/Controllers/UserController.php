<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // CRUD MongoDB

    // Get list user
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Users list",
     *     tags={"UserController"},
     *     @OA\SecurityScheme(
     *          securityScheme="bearerAuth",
     *          type="http",
     *          scheme="bearer"
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
    *                  {
    *                      "_id": "620e5cbe2709000014007762",
    *                      "email": "eve.holt@reqres.in",
    *                      "updated_at": "2022-02-17T23:10:36.439000Z",
    *                      "created_at": "2022-02-17T14:33:33.991000Z"
    *                  },
    *                  {
    *                      "_id": "620e9ff12709000014007765",
    *                      "email": "marif@gmail.com",
    *                      "updated_at": "2022-02-17T22:51:48.836000Z",
    *                       "created_at": "2022-02-17T19:20:17.786000Z"
    *                   },}, summary="An result object."),
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
    public function getUsersList(Request $request)
    {
        if ($request->q) $users = User::where('email', 'like', "%$request->q%")->get();
        else $users = User::all();

        return response($users);
    }

    // Get detail user
    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Users detail",
     *     tags={"UserController"},
     *     @OA\SecurityScheme(
     *          securityScheme="bearerAuth",
     *          type="http",
     *          scheme="bearer"
     *      ),
     *      @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="User id",
     *         required=true,
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"_id": "620e5cbe2709000014007762","email": "eve.holt@reqres.in","updated_at": "2022-02-17T23:10:36.439000Z","created_at": "2022-02-17T14:33:33.991000Z"}, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"message": "User tidak ditemukan"}, summary="An result object."),
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
    public function getUserDetail($user)
    {
        $user = User::find($user);
        if (!$user) return response(['message' => 'User tidak ditemukan'], 404);
        return response($user);
    }

    // Add user
    /**
     * @OA\POST(
     *     path="/api/users/create",
     *     summary="User add",
     *     tags={"UserController"},
     *     @OA\SecurityScheme(
     *          securityScheme="bearerAuth",
     *          type="http",
     *          scheme="bearer"
     *      ),
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
     *                 example={"email": "john@mail.com", "password": "1234567"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"email": "john@mail.com","updated_at": "2022-02-17T22:39:19.581000Z","created_at": "2022-02-17T22:39:19.581000Z","_id": "620ece972709000014007766"}, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"errors": {"email": {"The email field is required."},"password": {"The password field is required."}}}, summary="An result object."),
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
    public function create(Request $request)
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

        $user = User::create([
            'email' => $request->email,
            'password' => $request->password
        ]);

        return response($user);
    }

    // Update user
    /**
     * @OA\PUT(
     *     path="/api/users/{id}/update",
     *     summary="User update",
     *     tags={"UserController"},
     *     @OA\SecurityScheme(
     *          securityScheme="bearerAuth",
     *          type="http",
     *          scheme="bearer"
     *      ),
     *      @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="User id",
     *         required=true,
     *      ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 example={"email": "john@mail.com"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"_id": "620e9ff12709000014007765","email": "john@mail.com","updated_at": "2022-02-17T22:51:48.836000Z","created_at": "2022-02-17T19:20:17.786000Z"}, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"message": "User tidak ditemukan"}, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"errors": {"email": {"The email field is required."},"password": {"The password field is required."}}}, summary="An result object."),
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
    public function update(Request $request, $user)
    {
        $user = User::find($user);
        if (!$user) return response(['message' => 'User tidak ditemukan'], 404);
        $this->validate($request, [
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) use ($user) {
                    $otherUser = User::where('email', $value)->where('_id', $user->_id)->first();
                    if ($otherUser) {
                        $fail('The ' . $attribute . ' has already been taken.');
                    }
                },
            ]
        ]);

        $user->email = $request->email;
        $user->save();

        return response($user);
    }

    // Delete user
    /**
     * @OA\DELETE(
     *     path="/api/users/{id}/delete",
     *     summary="User delete",
     *     tags={"UserController"},
     *     @OA\SecurityScheme(
     *          securityScheme="bearerAuth",
     *          type="http",
     *          scheme="bearer"
     *      ),
     *      @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="User id",
     *         required=true,
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"message": "success"}, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"message": "User tidak ditemukan"}, summary="An result object."),
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
    public function destroy($user)
    {
        $user = User::find($user);
        if (!$user) return response(['message' => 'User tidak ditemukan'], 404);
        $user->delete();

        return response(['message' => 'success']);
    }
}
