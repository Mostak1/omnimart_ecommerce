<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PoliceStationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $districtsUrl = 'https://raw.githubusercontent.com/nuhil/bangladesh-geocode/master/districts/districts.json';
        $upazilasUrl = 'https://raw.githubusercontent.com/nuhil/bangladesh-geocode/master/upazilas/upazilas.json';

        $ch = curl_init($districtsUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $districtsJson = curl_exec($ch);
        curl_close($ch);

        $ch = curl_init($upazilasUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $upazilasJson = curl_exec($ch);
        curl_close($ch);

        if (!$districtsJson || !$upazilasJson) {
            $this->command->error("Failed to fetch data from github raw files.");
            return;
        }

        $extDistricts = json_decode($districtsJson, true);
        $extUpazilas = json_decode($upazilasJson, true);

        if (isset($extDistricts[2]['data'])) {
            $extDistricts = $extDistricts[2]['data'];
        }
        if (isset($extUpazilas[2]['data'])) {
            $extUpazilas = $extUpazilas[2]['data'];
        }

        // Map external district ID to external District Name
        $extDistrictMap = [];
        foreach ($extDistricts as $ed) {
            $extDistrictMap[$ed['id']] = $ed['name'];
        }

        // Get DB districts
        $dbDistricts = \App\Models\District::all()->keyBy('name');

        $policeStationsData = [];
        $now = now();

        $aliases = [
            'Chittagong' => 'Chattogram',
            'Comilla' => 'Cumilla',
            'Bogra' => 'Bogura',
            'Barisal' => 'Barishal',
            'Jessor' => 'Jashore',
        ];

        foreach ($extUpazilas as $eu) {
            if (!isset($eu['district_id'])) continue;
            
            $extDistrictName = $extDistrictMap[$eu['district_id']] ?? null;
            if (!$extDistrictName) continue;
            
            $dbName = $extDistrictName;
            if (isset($aliases[$extDistrictName])) {
                $dbName = $aliases[$extDistrictName];
            }

            if (isset($dbDistricts[$dbName])) {
                $policeStationsData[] = [
                    'district_id' => $dbDistricts[$dbName]->id,
                    'name' => $eu['name'],
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            } else {
                // To catch mismatching names
                // $this->command->warn("District not found in DB: " . $dbName);
            }
        }

        if (count($policeStationsData) > 0) {
            \App\Models\PoliceStation::truncate();
            foreach (array_chunk($policeStationsData, 100) as $chunk) {
                \App\Models\PoliceStation::insert($chunk);
            }
            $this->command->info(count($policeStationsData) . " Police Stations seeded successfully.");
        } else {
            $this->command->error("No police stations were prepared for insertion. Check API response or mapping.");
        }
    }
}
