<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceOffering;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $services = Service::with('offerings')->get();
        $customers = User::where('is_service_provider', false)->get();
        
        if ($services->isEmpty() || $customers->isEmpty()) {
            return;
        }

        $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        $statusWeights = [15, 25, 50, 10]; // Weighted probabilities
        
        // Create bookings for the past 3 months and next 2 weeks
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now()->addWeeks(2);
        
        $bookingCount = 0;
        $maxBookings = 150;
        
        // Generate bookings across the date range
        for ($date = $startDate->copy(); $date <= $endDate && $bookingCount < $maxBookings; $date->addDay()) {
            // Skip some days randomly to make it realistic
            if (rand(1, 100) > 70) continue;
            
            // Number of bookings for this day (0-5)
            $dailyBookings = rand(0, 5);
            
            for ($i = 0; $i < $dailyBookings && $bookingCount < $maxBookings; $i++) {
                $service = $services->random();
                
                // Skip if service has no offerings
                if ($service->offerings->isEmpty()) continue;
                
                $offering = $service->offerings->random();
                $customer = $customers->random();
                
                // Determine status based on date
                if ($date->isPast()) {
                    // Past bookings are mostly completed or cancelled
                    $status = $this->weightedRandomChoice(
                        ['completed', 'cancelled', 'confirmed'],
                        [70, 15, 15]
                    );
                } elseif ($date->isToday()) {
                    // Today's bookings can be any status
                    $status = $this->weightedRandomChoice($statuses, $statusWeights);
                } else {
                    // Future bookings are pending or confirmed
                    $status = $this->weightedRandomChoice(
                        ['pending', 'confirmed'],
                        [40, 60]
                    );
                }
                
                // Generate random time between 8 AM and 6 PM
                $hour = rand(8, 17);
                $minute = rand(0, 1) * 30; // 00 or 30
                $startTime = sprintf('%02d:%02d', $hour, $minute);
                $endTime = Carbon::createFromFormat('H:i', $startTime)
                    ->addMinutes($offering->duration_minutes)
                    ->format('H:i');
                
                // Create the booking
                Booking::create([
                    'user_id' => $customer->id,
                    'service_id' => $service->id,
                    'service_offering_id' => $offering->id,
                    'provider_id' => $service->user_id,
                    'status' => $status,
                    'booking_date' => $date->copy(),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'total_price' => $offering->price,
                    'customer_notes' => $this->getRandomNote(),
                    'created_at' => $date->copy()->subDays(rand(1, 7)),
                    'updated_at' => $date->copy(),
                ]);
                
                $bookingCount++;
            }
        }
        
        // Ensure we have some bookings for today specifically
        $today = Carbon::today();
        $todayServices = $services->take(3);
        
        foreach ($todayServices as $index => $service) {
            if ($service->offerings->isEmpty()) continue;
            
            $offering = $service->offerings->first();
            $customer = $customers[$index % $customers->count()];
            
            $times = ['09:00', '11:00', '14:00', '16:00'];
            
            foreach (array_slice($times, 0, rand(2, 4)) as $time) {
                $endTime = Carbon::createFromFormat('H:i', $time)
                    ->addMinutes($offering->duration_minutes)
                    ->format('H:i');
                
                Booking::create([
                    'user_id' => $customer->id,
                    'service_id' => $service->id,
                    'service_offering_id' => $offering->id,
                    'provider_id' => $service->user_id,
                    'status' => $this->weightedRandomChoice(
                        ['pending', 'confirmed', 'completed'],
                        [30, 50, 20]
                    ),
                    'booking_date' => $today,
                    'start_time' => $time,
                    'end_time' => $endTime,
                    'total_price' => $offering->price,
                    'customer_notes' => $this->getRandomNote(),
                    'created_at' => $today->copy()->subDays(rand(1, 3)),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    /**
     * Get a random customer note
     */
    private function getRandomNote(): ?string
    {
        $notes = [
            'Please call before arriving',
            'I have pets, please be careful',
            'Parking is available in the driveway',
            'Please wear masks inside',
            'Gate code is 1234',
            'I prefer morning appointments',
            'Please bring your own tools',
            'The issue is in the upstairs bathroom',
            'Please be quiet, baby is sleeping',
            null,
            null,
            null, // 50% chance of no note
        ];
        
        return $notes[array_rand($notes)];
    }
    
    /**
     * Weighted random choice from array
     */
    private function weightedRandomChoice(array $choices, array $weights): string
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($choices as $index => $choice) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $choice;
            }
        }
        
        return $choices[0];
    }
}
