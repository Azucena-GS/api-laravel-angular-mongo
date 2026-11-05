<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BooksSeeder extends Seeder
{
    public function run(): void
    {
        $books = DB::connection('mongodb')->table('books');

        if ($books->count() > 0) {
            return;
        }

        DB::connection('mongodb')->getCollection('books')->insertMany([
            [
                'title' => 'Don Quijote de la Mancha',
                'author' => 'Miguel de Cervantes',
                'published_year' => 1605,
                'genre' => 'Novela',
                'description' => 'Las aventuras de Don Quijote y Sancho Panza.',
            ],
            [
                'title' => 'Orgullo y prejuicio',
                'author' => 'Jane Austen',
                'published_year' => 1813,
                'genre' => 'Novela romantica',
                'description' => 'Una novela sobre la familia Bennet y la sociedad inglesa.',
            ],
            [
                'title' => 'Frankenstein',
                'author' => 'Mary Shelley',
                'published_year' => 1818,
                'genre' => 'Novela gotica',
                'description' => 'Un relato clasico sobre Victor Frankenstein y su criatura.',
            ],
            [
                'title' => 'La vuelta al mundo en ochenta dias',
                'author' => 'Julio Verne',
                'published_year' => 1872,
                'genre' => 'Aventura',
                'description' => 'Phileas Fogg emprende una carrera alrededor del mundo.',
            ],
        ]);
    }
}
