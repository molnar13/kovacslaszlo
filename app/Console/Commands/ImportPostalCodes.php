<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\County;
use App\Models\PostalCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPostalCodes extends Command
{
    protected $signature = 'import:postal-codes {file}';
    protected $description = 'Import postal codes from Excel file';

    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info('Starting import...');
        
        // Excel fájl beolvasása SimpleXLSX vagy hasonló library-vel
        // Alternatívaként CSV-re konvertálhatod
        
        // Példa CSV feldolgozásra:
        $this->importFromCsv($filePath);
        
        $this->info('Import completed successfully!');
        return 0;
    }

    private function importFromCsv($filePath)
    {
        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle); // Skip header
        
        DB::beginTransaction();
        
        try {
            $counties = [];
            $cities = [];
            $postalCodes = [];
            
            while (($row = fgetcsv($handle)) !== false) {
                [$postalCode, $cityName, $countyName] = $row;
                
                // County cache
                if (!isset($counties[$countyName])) {
                    $county = County::firstOrCreate(['name' => $countyName]);
                    $counties[$countyName] = $county->id;
                }
                
                // City cache
                $cityKey = $cityName . '_' . $countyName;
                if (!isset($cities[$cityKey])) {
                    $city = City::firstOrCreate([
                        'name' => $cityName,
                        'county_id' => $counties[$countyName]
                    ]);
                    $cities[$cityKey] = $city->id;
                }
                
                // Postal code
                PostalCode::firstOrCreate([
                    'code' => $postalCode,
                    'city_id' => $cities[$cityKey]
                ]);
                
                $this->output->write('.');
            }
            
            DB::commit();
            $this->newLine();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: ' . $e->getMessage());
            throw $e;
        }
        
        fclose($handle);
    }
}