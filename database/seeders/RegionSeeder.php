<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['region_name' => 'Arusha', 'region_code' => 'AR', 'latitude' => -3.3731, 'longitude' => 36.6853],
            ['region_name' => 'Dar es Salaam', 'region_code' => 'DSM', 'latitude' => -6.7924, 'longitude' => 39.2083],
            ['region_name' => 'Dodoma', 'region_code' => 'DOM', 'latitude' => -6.1731, 'longitude' => 35.7419],
            ['region_name' => 'Geita', 'region_code' => 'GEI', 'latitude' => -2.8722, 'longitude' => 32.2311],
            ['region_name' => 'Iringa', 'region_code' => 'IRI', 'latitude' => -7.7731, 'longitude' => 35.6991],
            ['region_name' => 'Kagera', 'region_code' => 'KAG', 'latitude' => -1.3344, 'longitude' => 31.8156],
            ['region_name' => 'Katavi', 'region_code' => 'KAT', 'latitude' => -6.3688, 'longitude' => 31.2626],
            ['region_name' => 'Kigoma', 'region_code' => 'KIG', 'latitude' => -4.8828, 'longitude' => 29.6350],
            ['region_name' => 'Kilimanjaro', 'region_code' => 'KIL', 'latitude' => -3.3333, 'longitude' => 37.3333],
            ['region_name' => 'Lindi', 'region_code' => 'LIN', 'latitude' => -9.9969, 'longitude' => 39.7144],
            ['region_name' => 'Manyara', 'region_code' => 'MAN', 'latitude' => -4.3150, 'longitude' => 35.8150],
            ['region_name' => 'Mara', 'region_code' => 'MAR', 'latitude' => -1.7475, 'longitude' => 34.0261],
            ['region_name' => 'Mbeya', 'region_code' => 'MBY', 'latitude' => -8.9094, 'longitude' => 33.4608],
            ['region_name' => 'Morogoro', 'region_code' => 'MOR', 'latitude' => -6.8278, 'longitude' => 37.6591],
            ['region_name' => 'Mtwara', 'region_code' => 'MTW', 'latitude' => -10.2736, 'longitude' => 40.1828],
            ['region_name' => 'Mwanza', 'region_code' => 'MWZ', 'latitude' => -2.5167, 'longitude' => 32.9000],
            ['region_name' => 'Njombe', 'region_code' => 'NJO', 'latitude' => -9.3331, 'longitude' => 34.7667],
            ['region_name' => 'Pemba North', 'region_code' => 'PN', 'latitude' => -5.0217, 'longitude' => 39.7756],
            ['region_name' => 'Pemba South', 'region_code' => 'PS', 'latitude' => -5.3211, 'longitude' => 39.7711],
            ['region_name' => 'Pwani', 'region_code' => 'PWA', 'latitude' => -7.3489, 'longitude' => 38.9819],
            ['region_name' => 'Rukwa', 'region_code' => 'RUK', 'latitude' => -7.9944, 'longitude' => 31.4239],
            ['region_name' => 'Ruvuma', 'region_code' => 'RUV', 'latitude' => -10.6833, 'longitude' => 35.6500],
            ['region_name' => 'Shinyanga', 'region_code' => 'SHY', 'latitude' => -3.6619, 'longitude' => 33.4231],
            ['region_name' => 'Simiyu', 'region_code' => 'SIM', 'latitude' => -2.8550, 'longitude' => 34.1200],
            ['region_name' => 'Singida', 'region_code' => 'SIN', 'latitude' => -4.8150, 'longitude' => 34.7467],
            ['region_name' => 'Songwe', 'region_code' => 'SON', 'latitude' => -8.4144, 'longitude' => 32.7483],
            ['region_name' => 'Tabora', 'region_code' => 'TAB', 'latitude' => -5.0167, 'longitude' => 32.8167],
            ['region_name' => 'Tanga', 'region_code' => 'TAN', 'latitude' => -5.0689, 'longitude' => 39.0989],
            ['region_name' => 'Zanzibar North', 'region_code' => 'ZN', 'latitude' => -5.8833, 'longitude' => 39.3000],
            ['region_name' => 'Zanzibar South', 'region_code' => 'ZS', 'latitude' => -6.2333, 'longitude' => 39.4333],
            ['region_name' => 'Zanzibar West', 'region_code' => 'ZW', 'latitude' => -6.1667, 'longitude' => 39.2000],
        ];

        foreach ($regions as $region) {
            Region::updateOrCreate(['region_name' => $region['region_name']], $region);
        }
    }
}
