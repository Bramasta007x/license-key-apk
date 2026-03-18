<?php

namespace Database\Seeders;

use App\Models\LandingPageConfig;
use Illuminate\Database\Seeder;

class LandingPageConfigSeeder extends Seeder
{
    public function run()
    {
        $bannerPath = 'landing-page/banner.jpeg';
        $venuePath  = 'landing-page/venue.jpeg';
        $logoPath   = 'landing-page/logo.jpeg';


        $terms = [
            ["id" => 1, "text" => 'Tiket resmi hanya dijual melalui situs www.jayapuramusicfest.com ...'],
            ["id" => 2, "text" => 'Satu Entry Pass berlaku untuk satu orang.'],
            ["id" => 3, "text" => 'Maksimal pembelian 4 tiket dalam 1 kali transaksi.'],
            ["id" => 4, "text" => 'Tiket bersifat pribadi dan tidak dapat dialihkan ...'],
            ["id" => 5, "text" => 'Setiap pemegang tiket bertanggung jawab atas keselamatan ...'],
            ["id" => 6, "text" => 'Tiket yang hilang atau dicuri tidak akan diganti ...'],
            ["id" => 7, "text" => 'Panitia tidak bertanggung jawab atas biaya transportasi ...'],
            ["id" => 8, "text" => 'Panitia, penyelenggara, atau promotor berhak merevisi ...'],
            ["id" => 9, "text" => 'Tiket yang sudah dibeli tidak dapat direfund ...'],
            ["id" => 10, "text" => 'E-ticket dan kartu identitas asli menjadi syarat wajib.'],

            [
                "id" => 11,
                "text" => 'Promotor berhak untuk:',
                "children" => [
                    'Melarang penonton masuk jika Entry Pass telah digunakan orang lain.',
                    'Melarang penonton masuk jika Entry Pass tidak valid.',
                    'Memproses hukuman terhadap pengunjung yang memperoleh Entry Pass secara tidak sah.',
                ],
            ],

            [
                "id" => 12,
                "text" => 'Barang yang boleh dibawa:',
                "children" => [
                    'Kartu identitas dan uang pribadi.',
                    'Bukti tiket/tanda masuk.',
                    'Obat-obatan pribadi.',
                    'Jas hujan.',
                    'Handphone atau perangkat lainnya.',
                    'Botol minum kosong (tumblr).',
                ],
            ],

            [
                "id" => 13,
                "text" => 'Barang yang tidak diperbolehkan:',
                "children" => [
                    'Makanan dan minuman dari luar.',
                    'Kamera profesional seperti drone, SLR, DSLR.',
                    'Tongsis atau selfie stick.',
                    'Minuman beralkohol, narkoba, psikotropika.',
                    'Senjata tajam/api dan benda berbahaya lainnya.',
                    'Cairan yang mudah terbakar.',
                    'Tas atau ransel besar.',
                    'Kursi lipat.',
                    'Laser pointer.',
                    'Rokok, vape, rokok elektrik.',
                    'Barang berbahaya lainnya.',
                ],
            ],

            ["id" => 14, "text" => 'Penyelenggara berhak menyita barang terlarang ...'],
            ["id" => 15, "text" => 'Dilarang membuat kerusuhan dalam area event ...'],
            ["id" => 16, "text" => 'Syarat dan ketentuan tidak dapat diganggu gugat ...'],
            ["id" => 17, "text" => 'Pembelian tiket berarti menyetujui seluruh syarat dan ketentuan ...'],
        ];
        
        $formattedTerms = [];

        foreach ($terms as $term) {
            $items = [$term["text"]];

            if (!empty($term["children"])) {
                $items = array_merge($items, $term["children"]);
            }

            $formattedTerms[] = [
                "label" => "Terms and Conditions " . $term["id"],
                "items" => $items
            ];
        }

        LandingPageConfig::create([
            'banner_image'       => $bannerPath,
            'venue_image'        => $venuePath,
            'logo_image'           => $logoPath,
            'event_creator'      => 'mtp.project',
            'event_name'         => 'Jayapura Music Fest 2026',
            'event_date'         => '2026-02-14',
            'event_time_start'   => '13:30',
            'event_time_end'     => '17:30',
            'event_location'     => 'Istora Papua, Jayapura',
            'terms_and_conditions' => $formattedTerms
        ]);
    }
}
