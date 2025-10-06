<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\County;
use App\Models\PostalCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostalCodeSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = database_path('data/iranyitoszamok.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found at: ' . $csvFile);
            return;
        }
        
        $this->command->info('Importing postal codes...');
        
        DB::transaction(function () use ($csvFile) {
            $handle = fopen($csvFile, 'r');
            fgetcsv($handle); // Skip header
            
            $counties = [];
            $cities = [];
            $count = 0;
            $duplicates = 0;
            
            while (($row = fgetcsv($handle)) !== false) {
                if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                    continue;
                }
                
                [$code, $cityName, $countyName] = $row;
                
                // Ensure 4-digit postal code
                $code = str_pad($code, 4, '0', STR_PAD_LEFT);
                
                // County cache
                if (!isset($counties[$countyName])) {
                    $county = County::firstOrCreate(['name' => trim($countyName)]);
                    $counties[$countyName] = $county->id;
                }
                
                // City cache
                $cityKey = trim($cityName) . '_' . trim($countyName);
                if (!isset($cities[$cityKey])) {
                    $city = City::firstOrCreate([
                        'name' => trim($cityName),
                        'county_id' => $counties[$countyName]
                    ]);
                    $cities[$cityKey] = $city->id;
                }
                
                // Check if postal code already exists
                $existingPostalCode = PostalCode::where('code', $code)->first();
                
                if (!$existingPostalCode) {
                    PostalCode::create([
                        'code' => $code,
                        'city_id' => $cities[$cityKey]
                    ]);
                    $count++;
                } else {
                    // It's a duplicate in the source file
                    $duplicates++;
                }
                
                // Progress indicator
                if (($count + $duplicates) % 100 == 0) {
                    $this->command->info("Processed " . ($count + $duplicates) . " records...");
                }
            }
            
            fclose($handle);
            
            $this->command->info("✅ Import completed!");
            $this->command->info("   - Imported: {$count} postal codes");
            $this->command->info("   - Skipped duplicates: {$duplicates}");
        });
    }
}