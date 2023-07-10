<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantTypeSeeder extends Seeder
{
    CONST TYPES = [
        "Arts & Entertainment (Seni & Hiburan)",
        "Automotive (Otomotif)",
        "Beauty & Fitness (Kecantikan & Kesehatan)",
        "Books & Literature (Buku & Literatur)",
        "Business & Industrial Markets (Bisnis & Pasar Industri)",
        "Computer & Electronics (Komputer & Elektronik)",
        "Finance (Keuangan)",
        "Food & Drink (Makanan & Minuman)",
        "Games (Permainan)",
        "Healthcare (Kesehatan)",
        "Hobbies & Leisure (Hobi & Kenyamanan)",
        "Home & Garden (Rumah & Taman)",
        "Internet & Telecom (Internet & Telekomunikasi)",
        "Jobs & Education (Pekerjaan & Pendidikan)",
        "Law & Government (Hukum & Pemerintahan)",
        "News (Berita)",
        "Online Communities (Komunitas Online)",
        "People & Society (Orang & Masyarakat)",
        "Pets & Animals (Peliharaan & Hewan)",
        "Real Estate (Perumahan)",
        "Science (Ilmu Pengetahuan)",
        "Shopping (Belanja)",
        "Sports (Olah Raga)",
        "Travel (Perjalanan)",
        "Others (Lainnya)",
    ];

    public function run(): void
    {
        foreach (static::TYPES as $key => $row) {
            \App\Models\TenantType::updateOrCreate(['id' => $key+1], [
                "name" => $row,
            ]);
        }
        
    }
}
