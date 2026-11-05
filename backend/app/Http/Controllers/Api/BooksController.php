<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Throwable;

class BooksController extends Controller
{
    /**
     * List books stored in the MongoDB books collection.
     */
    public function index(): AnonymousResourceCollection|JsonResponse
    {
        try {
            $books = DB::connection('mongodb')->table('books')->get();

            return BookResource::collection($books);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'The books could not be loaded.',
            ], 503);
        }
    }

    /**
     * Create a book in the MongoDB books collection.
     */
    public function store(Request $request): BookResource|JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'published_year' => ['required', 'integer', 'min:0'],
            'genre' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
        ]);

        try {
            $id = DB::connection('mongodb')->table('books')->insertGetId($validated);
            $book = DB::connection('mongodb')->table('books')->find($id);

            if ($book === null) {
                throw new \RuntimeException('The created book could not be retrieved.');
            }

            return BookResource::make($book)->response()->setStatusCode(201);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'The book could not be created.',
            ], 503);
        }
    }

    /**
     * Show a book from the MongoDB books collection.
     */
    public function show(string $id): BookResource|JsonResponse
    {
        try {
            $book = DB::connection('mongodb')->table('books')->find($id);

            if ($book === null) {
                return response()->json(['message' => 'Book not found.'], 404);
            }

            return BookResource::make($book);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'The book could not be loaded.',
            ], 503);
        }
    }

    /**
     * Update a book in the MongoDB books collection.
     */
    public function update(Request $request, string $id): BookResource|JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'published_year' => ['required', 'integer', 'min:0'],
            'genre' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
        ]);

        return self::saveUpdate($id, $validated);
    }

    /**
     * Partially update a book in the MongoDB books collection.
     */
    public function patch(Request $request, string $id): BookResource|JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'author' => ['sometimes', 'required', 'string', 'max:255'],
            'published_year' => ['sometimes', 'required', 'integer', 'min:0'],
            'genre' => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['sometimes', 'required', 'string'],
        ]);

        if ($validated === []) {
            return response()->json(['message' => 'At least one book field must be provided.'], 422);
        }

        return self::saveUpdate($id, $validated);
    }

    private static function saveUpdate(string $id, array $validated): BookResource|JsonResponse
    {
        try {
            $books = DB::connection('mongodb')->table('books');

            if ($books->find($id) === null) {
                return response()->json(['message' => 'Book not found.'], 404);
            }

            $books->where('id', $id)->update($validated);
            $book = DB::connection('mongodb')->table('books')->find($id);

            if ($book === null) {
                throw new \RuntimeException('The updated book could not be retrieved.');
            }

            return BookResource::make($book);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'The book could not be updated.',
            ], 503);
        }
    }

    /**
     * Delete a book from the MongoDB books collection.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = DB::connection('mongodb')->table('books')->where('id', $id)->delete();

            if ($deleted === 0) {
                return response()->json(['message' => 'Book not found.'], 404);
            }

            return response()->json(null, 204);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'The book could not be deleted.',
            ], 503);
        }
    }
}
