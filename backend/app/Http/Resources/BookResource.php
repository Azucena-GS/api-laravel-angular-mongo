<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read \stdClass $resource
 * @property-read string $id
 * @property-read string $title
 * @property-read string $author
 * @property-read int $published_year
 * @property-read string $genre
 * @property-read string $description
 */
class BookResource extends JsonResource
{
    /**
     * @return array{id: string, title: string, author: string, published_year: int, genre: string, description: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'published_year' => (int) $this->published_year,
            'genre' => $this->genre,
            'description' => $this->description,
        ];
    }
}
