<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    /**
     * Display a list of customers who have booked the vendor's services.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get unique customers who have booked the vendor's services
        $bookings = Booking::with(['customer', 'service', 'shop'])
            ->where('provider_id', $user->id)
            ->whereNotNull('user_id')
            ->orderBy('created_at', 'desc')
            ->get();

        // This route represents registered customer accounts. Guest contacts are
        // intentionally available through bookings and calendar instead of being
        // collapsed into a single null customer record.
        $customerData = $bookings->groupBy('user_id')
            ->filter(fn (Collection $customerBookings) => $customerBookings->first()->customer !== null)
            ->map(function (Collection $customerBookings): array {
                $customer = $customerBookings->first()->customer;
                $spentByCurrency = $this->revenueByCurrency($customerBookings);
                $spentString = $this->formatCurrencyTotals(
                    $spentByCurrency,
                    $customerBookings->pluck('currency')
                );

                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone ?? 'N/A',
                    'avatar_initials' => $this->getInitials($customer->name),
                    'total_bookings' => $customerBookings->count(),
                    'completed_bookings' => $customerBookings->where('status', 'completed')->count(),
                    'cancelled_bookings' => $customerBookings->where('status', 'cancelled')->count(),
                    'total_spent' => $spentString,
                    'total_spent_details' => $spentString,
                    'total_spent_by_currency' => $spentByCurrency->all(),
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

        $revenueByCurrency = $this->revenueByCurrency($bookings);

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
                'new_customers' => $customerData->filter(fn ($c) => $c['total_bookings'] === 1)->count(),
                'returning_customers' => $customerData->filter(fn ($c) => $c['total_bookings'] > 1)->count(),
                'total_revenue' => $this->formatCurrencyTotals(
                    $revenueByCurrency,
                    $bookings->pluck('currency')
                ),
                'total_revenue_by_currency' => $revenueByCurrency->all(),
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
        $bookings = Booking::with(['customer', 'service', 'shop'])
            ->where('provider_id', $user->id)
            ->where('user_id', $customerId)
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        if ($bookings->isEmpty()) {
            abort(404, 'Customer not found');
        }

        $customer = $bookings->first()->customer;

        $spentByCurrency = $this->revenueByCurrency($bookings);
        $spentString = $this->formatCurrencyTotals($spentByCurrency, $bookings->pluck('currency'));

        $customerData = [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone ?? 'N/A',
            'avatar_initials' => $this->getInitials($customer->name),
            'total_bookings' => $bookings->count(),
            'completed_bookings' => $bookings->where('status', 'completed')->count(),
            'cancelled_bookings' => $bookings->where('status', 'cancelled')->count(),
            'total_spent' => $spentString,
            'total_spent_by_currency' => $spentByCurrency->all(),
            'last_booking_date' => $bookings->max('booking_date'),
            'first_booking_date' => $bookings->min('booking_date'),
            'services_used' => $bookings->pluck('service.name')->unique()->values()->toArray(),
            'bookings' => $bookings->map(function (Booking $booking): array {
                $currency = strtoupper($booking->currency ?: $booking->shop?->currency ?: 'CZK');

                return [
                    'id' => $booking->id,
                    'shop_name' => $booking->shop?->name ?? 'Provozovna',
                    'service_name' => $booking->service?->name ?? 'Služba',
                    'date' => $booking->booking_date->format('Y-m-d'),
                    'time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'status' => $booking->status,
                    'price' => (float) $booking->total_price,
                    'currency' => $currency,
                    'formatted_price' => $this->formatMoney($booking->total_price, $currency),
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
        $words = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($words) >= 2) {
            return mb_strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1));
        }

        return $words === [] ? '?' : mb_strtoupper(mb_substr($words[0], 0, 2));
    }

    private function revenueByCurrency(Collection $bookings): Collection
    {
        return $bookings
            ->where('status', '!=', 'cancelled')
            ->groupBy(fn (Booking $booking) => strtoupper(
                $booking->currency ?: $booking->shop?->currency ?: 'CZK'
            ))
            ->map(fn (Collection $currencyBookings) => $currencyBookings->sum(
                fn (Booking $booking) => (float) $booking->total_price
            ));
    }

    private function formatCurrencyTotals(Collection $totals, Collection $fallbackCurrencies): string
    {
        if ($totals->isNotEmpty()) {
            return $totals->map(
                fn ($amount, string $currency) => $this->formatMoney($amount, $currency)
            )->implode(' | ');
        }

        $currencies = $fallbackCurrencies
            ->filter()
            ->map(fn ($currency) => strtoupper((string) $currency))
            ->unique()
            ->values();

        if ($currencies->isEmpty()) {
            $currencies = collect(['CZK']);
        }

        return $currencies->map(fn (string $currency) => $this->formatMoney(0, $currency))->implode(' | ');
    }

    private function formatMoney($amount, string $currency): string
    {
        return Money::format($amount, $currency);
    }
}
