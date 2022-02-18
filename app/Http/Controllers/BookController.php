<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BookController extends Controller
{
    // CRUD Firebase 

    protected $database;
    protected $collection;
    protected $reference;

    function __construct()
    {
        $this->database = app('firebase.database');
        $this->collection = 'books';
        $this->reference = $this->database->getReference($this->collection);
    }

    // Get list book
    /**
     * @OA\Get(
     *     path="/api/books",
     *     summary="Books list",
     *     tags={"BookController"},
     *     @OA\SecurityScheme(
     *          securityScheme="bearerAuth",
     *          type="http",
     *          scheme="bearer"
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"-Mw895jMgCnMQvdc_sAD": {"name": "tutorial koding","price": "89000"},"-Mw89UrB_YOq778EHzG6": {"name": "tutorial html","price": "89000"}}, summary="An result object."),
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
    public function getBooksList() {
        $books = $this->reference->getSnapshot()->getValue();
        return response($books);
    }

    // Get book detail by id
    /**
     * @OA\Get(
     *     path="/api/books/{id}",
     *     summary="Books detail",
     *     tags={"BookController"},
     *     @OA\SecurityScheme(
     *          securityScheme="bearerAuth",
     *          type="http",
     *          scheme="bearer"
     *      ),
     *      @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Book id",
     *         required=true,
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"name": "tutorial koding","price": "89000"}, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"message": "Buku tidak ditemukan"}, summary="An result object."),
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
    public function getBookDetail($book) {
        $reference = $this->database->getReference($this->collection . '/' .  $book);
        $book = $reference->getValue();
        if (!$book) return response(['message' => 'Buku tidak ditemukan'], 404);
        return $book;
    }

    // Add book
    /**
     * @OA\POST(
     *     path="/api/books/{id}/create",
     *     summary="Books add",
     *     tags={"BookController"},
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
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="price",
     *                     type="integer"
     *                 ),
     *                 example={"id": "Buku membaca", "name": "98000"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"-Mw895jMgCnMQvdc_sAD": {"name": "tutorial koding","price": "89000"},"-Mw89UrB_YOq778EHzG6": {"name": "tutorial html","price": "89000"}}, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"errors": {"name": {"The name field is required."},"price": {"The price field is required."}}}, summary="An result object."),
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
    public function create(Request $request) {
        $this->validate($request, [
            'name' => 'required|max:120',
            'price' => 'required|numeric|digits_between:1,10'
        ]);

        // create new data
        $this->reference->push([
            'name' => $request->name,
            'price' => $request->price,
        ]);

        return $this->getBooksList();
    }

    // Update book
    /**
     * @OA\PUT(
     *     path="/api/books/{id}/update",
     *     summary="Books update",
     *     tags={"BookController"},
     *     @OA\SecurityScheme(
     *          securityScheme="bearerAuth",
     *          type="http",
     *          scheme="bearer"
     *      ),
     *      @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Book id",
     *         required=true,
     *      ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="price",
     *                     type="integer"
     *                 ),
     *                 example={"id": "Buku membaca", "name": "98000"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"name": "Buku membaca","price": "98000"}, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"errors": {"name": {"The name field is required."},"price": {"The price field is required."}}}, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"message": "Buku tidak ditemukan"}, summary="An result object."),
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
    public function update(Request $request, $book) {
        $this->validate($request, [
            'name' => 'required|max:120',
            'price' => 'required|numeric|digits_between:1,10'
        ]);

        // cek data
        $data = $this->getBookDetail($book);
        if (!$data) return response(['message' => 'Buku tidak ditemukan'], 404);

        // update data
        $reference = $this->database->getReference($this->collection . '/' .  $book);
        $reference->set([
            'name' => $request->name,
            'price' => $request->price
        ]);

        return $this->getBookDetail($book);
    }

    // Delete book
    /**
     * @OA\DELETE(
     *     path="/api/books/{id}/delete",
     *     summary="Books delete",
     *     tags={"BookController"},
     *     @OA\SecurityScheme(
     *          securityScheme="bearerAuth",
     *          type="http",
     *          scheme="bearer"
     *      ),
     *      @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Book id",
     *         required=true,
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"name": "tutorial koding","price": "89000"}, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"message": "Buku tidak ditemukan"}, summary="An result object."),
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
    public function destroy($book) {
        // get data
        $reference = $this->database->getReference($this->collection . '/' .  $book);
        $book = $reference->getValue();
        if (!$book) return response(['message' => 'Buku tidak ditemukan'], 404);

        // remove
        $reference->remove();
        
        return response(['message' => 'success']);
    }
}
