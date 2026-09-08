<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Region;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Arusha' => ['Arusha City', 'Arumeru', 'Karatu', 'Longido', 'Monduli', 'Ngorongoro'],
            'Dar es Salaam' => ['Ilala', 'Kinondoni', 'Temeke', 'Ubungo', 'Kigamboni'],
            'Dodoma' => ['Bahi', 'Chamwino', 'Chemba', 'Dodoma City', 'Kondoa', 'Kongwa', 'Mpwapwa'],
            'Geita' => ['Bukombe', 'Chato', 'Geita', 'Mbogwe', 'Nyang\'hwale'],
            'Iringa' => ['Iringa', 'Iringa Municipal', 'Kilolo', 'Mufindi'],
            'Kagera' => ['Biharamulo', 'Bukoba', 'Bukoba Municipal', 'Karagwe', 'Kyerwa', 'Missenyi', 'Muleba', 'Ngara'],
            'Katavi' => ['Mlele', 'Mpanda', 'Mpanda Municipal'],
            'Kigoma' => ['Buhigwe', 'Kakonko', 'Kasulu', 'Kasulu Town', 'Kibondo', 'Kigoma', 'Kigoma-Ujiji Municipal', 'Uvinza'],
            'Kilimanjaro' => ['Hai', 'Moshi', 'Moshi Municipal', 'Mwanga', 'Rombo', 'Same', 'Siha'],
            'Lindi' => ['Kilwa', 'Lindi', 'Lindi Municipal', 'Liwale', 'Nachingwea', 'Ruangwa'],
            'Manyara' => ['Babati', 'Babati Town', 'Hanang', 'Kiteto', 'Mbulu', 'Simanjiro'],
            'Mara' => ['Bunda', 'Butiama', 'Musoma', 'Musoma Municipal', 'Rorya', 'Serengeti', 'Tarime'],
            'Mbeya' => ['Busokelo', 'Chunya', 'Kyela', 'Mbarali', 'Mbeya', 'Mbeya City', 'Rungwe'],
            'Morogoro' => ['Gairo', 'Kilombero', 'Kilosa', 'Morogoro', 'Morogoro Municipal', 'Mvomero', 'Ulanga', 'Malinyi', 'Ifakara'],
            'Mtwara' => ['Masasi', 'Masasi Town', 'Mtwara', 'Mtwara Municipal', 'Nanyumbu', 'Newala', 'Tandahimba'],
            'Mwanza' => ['Ilemela Municipal', 'Kwimba', 'Magu', 'Misungwi', 'Nyamagana Municipal', 'Sengerema', 'Ukerewe', 'Buchosa'],
            'Njombe' => ['Ludewa', 'Makambako Town', 'Makete', 'Njombe', 'Njombe Town', 'Wanging\'ombe'],
            'Pemba North' => ['Micheweni', 'Wete'],
            'Pemba South' => ['Chake Chake', 'Mkoani'],
            'Pwani' => ['Bagamoyo', 'Kibaha', 'Kibaha Town', 'Kisarawe', 'Mafia', 'Mkuranga', 'Rufiji', 'Kibiti'],
            'Rukwa' => ['Kalambo', 'Nkansi', 'Sumbawanga', 'Sumbawanga Municipal'],
            'Ruvuma' => ['Mbinga', 'Namtumbo', 'Nyasa', 'Songea', 'Songea Municipal', 'Tunduru', 'Madaba'],
            'Shinyanga' => ['Kahama Town', 'Kahama', 'Kishapu', 'Shinyanga', 'Shinyanga Municipal'],
            'Simiyu' => ['Bariadi', 'Itilima', 'Maswa', 'Meatu', 'Busega'],
            'Singida' => ['Ikungi', 'Iramba', 'Manyoni', 'Mkalama', 'Singida', 'Singida Municipal'],
            'Songwe' => ['Ileje', 'Mbozi', 'Momba', 'Songwe', 'Tunduma Town'],
            'Tabora' => ['Igunga', 'Kaliua', 'Nzega', 'Sikonge', 'Tabora Municipal', 'Urambo', 'Uyui'],
            'Tanga' => ['Handeni', 'Handeni Town', 'Kilindi', 'Korogwe', 'Korogwe Town', 'Lushoto', 'Mkinga', 'Muheza', 'Pangani', 'Tanga City'],
            'Zanzibar North' => ['Kaskazini A', 'Kaskazini B'],
            'Zanzibar South' => ['Kati', 'Kusini'],
            'Zanzibar West' => ['Magharibi', 'Mjini'],
        ];

        foreach ($data as $regionName => $districts) {
            $region = Region::where('region_name', $regionName)->first();
            
            if ($region) {
                foreach ($districts as $name) {
                    District::firstOrCreate(
                        ['region_id' => $region->id, 'district_name' => $name]
                    );
                }
            }
        }
    }
}
