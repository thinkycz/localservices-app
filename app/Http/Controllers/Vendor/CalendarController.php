<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    /**
     * Display the vendor calendar with real booking data.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get all services for this vendor
        $services = Service::where('user_id', $user->id)->get();
        $serviceIds = $services->pluck('id');

        // Get date range from request or default to current week
        $startDate = $request->get('start_date') 
            ? Carbon::parse($request->get('start_date')) 
            : Carbon::now()->startOfWeek();
        
        $endDate = $request->get('end_date') 
            ? Carbon::parse($request->get('end_date')) 
            : Carbon::now()->endOfWeek();

        // Get all bookings for the date range
        $bookings = Booking::whereIn('service_id', $serviceIds)
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->with(['customer', 'service', 'offering'])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        // Format bookings for the calendar
        $formattedBookings = $bookings->map(function ($booking) {
            $startTime = Carbon::parse($booking->start_time);
            $endTime = Carbon::parse($booking->end_time);
            
            // Determine color type based on status
            $colorType = match($booking->status) {
                'pending' => 'yellow',
                'confirmed' => 'blue',
                'completed' => 'green',
                'cancelled' => 'red',
                default => 'blue',
            };

            // Determine customer type
            $customerBookingsCount = Booking::where('user_id', $booking->user_id)
                ->where('provider_id', $booking->provider_id)
                ->count();
            $customerType = $customerBookingsCount > 1 ? 'Regular Customer' : 'New Customer';

            return [
                'id' => $booking->id,
                'customer' => $booking->customer->name,
                'service' => $booking->service->name,
                'serviceDetail' => $booking->offering->name,
                'dayIndex' => Carbon::parse($booking->booking_date)->dayOfWeekIso - 1, // Monday = 0
                'startHour' => (int) $startTime->format('H'),
                'startMin' => (int) $startTime->format('i'),
                'duration' => $booking->offering->duration_minutes,
                'colorType' => $colorType,
                'status' => $booking->status,
                'initials' => $this->getInitials($booking->customer->name),
                'avatarBg' => $this->getAvatarBg($booking->customer->name),
                'avatarText' => $this->getAvatarText($booking->customer->name),
                'dateStr' => Carbon::parse($booking->booking_date)->format('D, M d'),
                'timeStr' => $startTime->format('h:i A') . ' - ' . $endTime->format('h:i A'),
                'price' => '$' . number_format($booking->total_price, 2),
                'customerType' => $customerType,
                'notes' => $booking->customer_notes ? '"' . $booking->customer_notes . '"' : '',
            ];
        });

        // Generate week days
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $weekDays[] = [
                'day' => $date->format('D'), // MON, TUE, etc.
                'date' => (int) $date->format('d'),
                'dayIndex' => $i,
                'isToday' => $date->isToday(),
                'fullDate' => $date->format('Y-m-d'),
            ];
        }

        // Calculate stats for the week
        $weekStats = [
            'total_bookings' => $bookings->count(),
            'completed' => $bookings->where('status', 'completed')->count(),
            'pending' => $bookings->where('status', 'pending')->count(),
            'confirmed' => $bookings->where('status', 'confirmed')->count(),
            'cancelled' => $bookings->where('status', 'cancelled')->count(),
            'revenue' => $bookings->where('status', '!=', 'cancelled')->sum('total_price'),
        ];

        return Inertia::render('Vendor/Calendar', [
            'bookings' => $formattedBookings,
            'weekDays' => $weekDays,
            'weekRange' => $startDate->format('M d') . ' – ' . $endDate->format('M d, Y'),
            'weekStats' => $weekStats,
            'currentView' => $request->get('view', 'week'),
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Get initials from name.
     */
    private function getInitials(string $name): string
    {
        $words = explode(' ', trim($name));
        if (count($words) >= 2) {
            return strtoupper($words[0][0] . $words[1][0]);
        }
        return strtoupper(substr($name, 0, 2));
    }

    /**
     * Get avatar background color based on name.
     */
    private function getAvatarBg(string $name): string
    {
        $colors = ['bg-blue-200', 'bg-green-200', 'bg-purple-200', 'bg-pink-200', 'bg-orange-200', 'bg-teal-200'];
        $index = crc32($name) % count($colors);
        return $colors[$index];
    }

    /**
     * Get avatar text color based on name.
     */
    private function getAvatarText(string $name): string
    {
        $colors = ['text-blue-700', 'text-green-700', 'text-purple-700', 'text-pink-700', 'text-orange-700', 'text-teal-700'];
        $index = crc32($name) % count($colors);
        return $colors[$index];
    }
}
