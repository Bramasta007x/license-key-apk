<?php

namespace Database\Seeders;

use App\Models\Artist;
use Illuminate\Database\Seeder;

class ArtistSeeder extends Seeder
{
    public function run(): void
    {
        $artists = [
            [
                'file'  => 'ran.jpeg',
                'name'  => 'RAN',
                'genre' => 'Pop'
            ],
            [
                'file'  => 'riche-kota.jpeg',
                'name'  => 'Riche Kota',
                'genre' => 'Pop'
            ]
        ];

        foreach ($artists as $data) {

            $destPath   = 'artists/' . $data['file'];

            Artist::create([
                'image' => $destPath,
                'name'  => $data['name'],
                'genre' => $data['genre'],
            ]);
        }
    }
}
