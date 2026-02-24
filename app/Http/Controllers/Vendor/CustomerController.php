<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Display a list of customers who have booked the vendor's services.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get unique customers who have booked the vendor's services
        $bookings = Booking::with(['customer', 'service', 'offering'])
            ->where('provider_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by customer and aggregate data
        $customerData = $bookings->groupBy('user_id')->map(function ($customerBookings) {
            $customer = $customerBookings->first()->customer;
            
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone ?? 'N/A',
                'avatar_initials' => $this->getInitials($customer->name),
                'total_bookings' => $customerBookings->count(),
                'completed_bookings' => $customerBookings->where('status', 'completed')->count(),
                'cancelled_bookings' => $customerBookings->where('status', 'cancelled')->count(),
                'total_spent' => $customerBookings->where('status', '!=', 'cancelled')->sum('total_price'),
                'last_booking_date' => $customerBookings->max('booking_date'),
                'first_booking_date' => $customerBookings->min('booking_date'),
                'services_used' => $customerBookings->pluck('service.name')->unique()->values()->toArray(),
            ];
        })->values();

        // Search functionality
        $search = $request->get('search', '');
        if ($search) {
            $customerData = $customerData->filter(function ($customer) use ($search) {
                return stripos($customer['name'], $search) !== false 
                    || stripos($customer['email'], $search) !== false;
            })->values();
        }

        // Filter by status
        $filter = $request->get('filter', 'all');
        if ($filter === 'new') {
            $customerData = $customerData->filter(function ($customer) {
                return $customer['total_bookings'] === 1;
            })->values();
        } elseif ($filter === 'returning') {
            $customerData = $customerData->filter(function ($customer) {
                return $customer['total_bookings'] > 1;
            })->values();
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $total = $customerData->count();
        $start = ($page - 1) * $perPage;
        $customers = $customerData->slice($start, $perPage)->values();

        return Inertia::render('Vendor/Customers/Index', [
            'customers' => $customers,
            'meta' => [
                'current_page' => (int) $page,
                'per_page' => (int) $perPage,
                'total' => $total,
                'from' => $total > 0 ? $start + 1 : 0,
                'to' => min($start + $perPage, $total),
            ],
            'filters' => [
                'search' => $search,
                'filter' => $filter,
            ],
            'stats' => [
                'total_customers' => $customerData->count(),
                'new_customers' => $customerData->filter(fn($c) => $c['total_bookings'] === 1)->count(),
                'returning_customers' => $customerData->filter(fn($c) => $c['total_bookings'] > 1)->count(),
                'total_revenue' => $customerData->sum('total_spent'),
            ],
        ]);
    }

    /**
     * Display details of a specific customer.
     */
    public function show(Request $request, int $customerId): Response
    {
        $user = $request->user();

        // Get all bookings for this customer with this vendor
        $bookings = Booking::with(['service', 'offering'])
            ->where('provider_id', $user->id)
            ->where('user_id', $customerId)
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        if ($bookings->isEmpty()) {
            abort(404, 'Customer not found');
        }

        $customer = $bookings->first()->customer;
        
        $customerData = [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone ?? 'N/A',
            'avatar_initials' => $this->getInitials($customer->name),
            'total_bookings' => $bookings->count(),
            'completed_bookings' => $bookings->where('status', 'completed')->count(),
            'cancelled_bookings' => $bookings->where('status', 'cancelled')->count(),
            'total_spent' => $bookings->where('status', '!=', 'cancelled')->sum('total_price'),
            'last_booking_date' => $bookings->max('booking_date'),
            'first_booking_date' => $bookings->min('booking_date'),
            'services_used' => $bookings->pluck('service.name')->unique()->values()->toArray(),
            'bookings' => $bookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'service_name' => $booking->service->name,
                    'offering_name' => $booking->offering->name,
                    'date' => $booking->booking_date->format('Y-m-d'),
                    'time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'status' => $booking->status,
                    'price' => $booking->total_price,
                    'notes' => $booking->notes,
                    'customer_notes' => $booking->customer_notes,
                ];
            }),
        ];

        return Inertia::render('Vendor/Customers/Show', [
            'customer' => $customerData,
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
}

