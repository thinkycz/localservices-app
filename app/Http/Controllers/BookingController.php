<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function show(string $slug, Request $request): Response
    {
        $service = Service::with(['category', 'offerings'])
            ->where('slug', $slug)
            ->firstOrFail();

        $offering = null;
        if ($request->filled('offering_id')) {
            $offering = $service->offerings->firstWhere('id', (int) $request->offering_id);
        }

        return Inertia::render('Booking/Index', [
            'service'  => $service,
            'offering' => $offering,
            'date'     => $request->get('date'),
            'time'     => $request->get('time'),
        ]);
    }
}
