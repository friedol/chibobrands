<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DesignTaskType;
use Illuminate\Support\Facades\File;

class DesignTaskTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = public_path('Item.csv');
        
        if (!File::exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            return;
        }

        $file = fopen($csvPath, 'r');
        $headers = fgetcsv($file);
        
        // CSV now has headers: name, price, description
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 2) continue;

            $name = $row[0];
            $price = $row[1];
            $description = isset($row[2]) ? $row[2] : null;

            DesignTaskType::updateOrCreate(
                ['name' => $name],
                [
                    'price' => (float) $price,
                    'description' => $description,
                ]
            );
        }

        fclose($file);
        $this->command->info('Design task types imported successfully from CSV.');
    }
}
