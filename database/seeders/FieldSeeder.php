<?php

namespace Database\Seeders;
use App\Models\Field;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Field::insert([
        ['name'=>'Lapangan Voli', 'type'=>'Outdoor', 'price'=>120000],
        ['name'=>'Lapangan Badminton A', 'type'=>'Indoor', 'price'=>100000],
        ['name'=>'Lapangan Badminton B', 'type'=>'Indoor', 'price'=>100000],
        ['name'=>'Lapangan Futsal', 'type'=>'Indoor', 'price'=>150000],
        ['name'=>'Lapangan Basket', 'type'=>'Outdoor', 'price'=>130000],
        ['name'=>'Lapangan Tenis', 'type'=>'Outdoor', 'price'=>140000],
    ]);
    }
}
