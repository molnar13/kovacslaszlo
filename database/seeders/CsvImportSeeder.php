<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\County;
use App\Models\Settlement;
use Illuminate\Support\Facades\DB;

class CsvImportSeeder extends Seeder
{
    public function run()
    {
        // 1. Megnyitjuk a fájlt
        $csvFile = base_path('database/data/iranyitoszamok.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error("A fájl nem található: $csvFile");
            return;
        }

        // Kikapcsoljuk az időbélyeg frissítést a gyorsabb futásért
        $this->command->info('Adatok importálása folyamatban... Ez eltarthat egy percig.');
        
        $handle = fopen($csvFile, "r");

        // 2. Fejlécek átugrása
        // A fájlodban az első sor a fejléc, a második sor üres (,,) a minta alapján
        fgetcsv($handle); // 1. sor (Header)
        fgetcsv($handle); // 2. sor (Üres)

        $count = 0;
        
        // Cache-eljük a megyéket, hogy ne kelljen mindig az adatbázishoz fordulni
        // [ 'Pest' => 1, 'Zala' => 2, ... ]
        $counties = County::pluck('id', 'name')->toArray();

        DB::beginTransaction(); // Tranzakció indítása a biztonságért

        try {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // A CSV oszlopai a minta alapján:
                // $data[0] -> Irányítószám (pl. 8128)
                // $data[1] -> Település neve (pl. Aba)
                // $data[2] -> Megye neve (pl. Fejér)

                $zip = trim($data[0]);
                $cityName = trim($data[1]);
                $countyName = trim($data[2]);

                // Ha üres a sor, ugorjuk át
                if (empty($zip) || empty($cityName) || empty($countyName)) {
                    continue;
                }

                // 3. Megye kezelése
                // Ha még nincs a cache-ben ez a megye, létrehozzuk
                if (!isset($counties[$countyName])) {
                    $newCounty = County::create(['name' => $countyName]);
                    $counties[$countyName] = $newCounty->id;
                    $this->command->info("Új megye létrehozva: $countyName");
                }

                $countyId = $counties[$countyName];

                // 4. Város létrehozása
                Settlement::create([
                    'postal_code' => $zip,
                    'name'        => $cityName,
                    'county_id'   => $countyId,
                ]);

                $count++;
                if ($count % 100 == 0) {
                    $this->command->getOutput()->write('.');
                }
            }

            DB::commit(); // Véglegesítés
            fclose($handle);
            
            $this->command->info("\nKész! $count település sikeresen importálva.");

        } catch (\Exception $e) {
            DB::rollBack(); // Hiba esetén visszavonunk mindent
            $this->command->error("Hiba történt: " . $e->getMessage());
        }
    }
}