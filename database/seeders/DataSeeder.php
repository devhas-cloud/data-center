<?php

namespace Database\Seeders;

use App\Models\DataModel;
use App\Models\SensorModel;
use App\Models\DeviceModel;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder generates random sensor data for the last 24 hours
     * with 5-minute intervals (288 data points per parameter per device)
     */
    public function run(): void
    {
        // Get all sensors from database
        $sensors = SensorModel::with('device')->get();

        if ($sensors->isEmpty()) {
            $this->command->warn('No sensors found in database. Please seed sensors first.');
            return;
        }

        $this->command->info('Starting to generate random sensor data...');

        // Time settings: Last 24 hours with 5-minute intervals
        $endTime = Carbon::now();
        $startTime = $endTime->copy()->subHours(24);
        $interval = 5; // minutes
        $totalDataPoints = (24 * 60) / $interval; // 288 data points

        $insertedCount = 0;
        $batchSize = 1000;
        $dataToInsert = [];

        foreach ($sensors as $sensor) {
            $this->command->info("Generating data for Device: {$sensor->device_id} - Parameter: {$sensor->parameter_name}");

            // Get parameter range for realistic values
            $maxRange = $sensor->parameter_indicator_range ?? 100;
            $minRange = 0;

            // Generate data for each time slot
            $currentTime = $startTime->copy();

            for ($i = 0; $i < $totalDataPoints; $i++) {
                // Generate random value within parameter range
                // Add some randomness (70-90% of max range for realistic simulation)
                $baseValue = rand(70, 90) / 100;
                $randomVariation = (rand(-10, 10) / 100); // ±10% variation
                $value = round($maxRange * ($baseValue + $randomVariation), 2);

                // Ensure value stays within range
                $value = max($minRange, min($maxRange, $value));

                $dataToInsert[] = [
                    'device_id' => $sensor->device_id,
                    'parameter_name' => $sensor->parameter_name,
                    'value' => $value,
                    'recorded_at' => $currentTime->format('Y-m-d H:i:s'),
                    'timestamp' => $currentTime->timestamp,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $insertedCount++;

                // Batch insert for performance
                if (count($dataToInsert) >= $batchSize) {
                    DataModel::insert($dataToInsert);
                    $dataToInsert = [];
                    $this->command->info("Inserted {$insertedCount} records...");
                }

                // Move to next time slot
                $currentTime->addMinutes($interval);
            }
        }

        // Insert remaining data
        if (!empty($dataToInsert)) {
            DataModel::insert($dataToInsert);
        }

        $this->command->info("✓ Successfully inserted {$insertedCount} random data records!");
        $this->command->info("Data range: {$startTime->format('Y-m-d H:i')} to {$endTime->format('Y-m-d H:i')}");
    }
}
