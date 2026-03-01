<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceOffering;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    /**
     * Show the vendor onboarding start page.
     */
    public function index(): Response
    {
        return Inertia::render('Vendor/Onboarding/Index');
    }

    /**
     * Show step 1: Personal/Business Information.
     */
    public function step1(): Response
    {
        $user = auth()->user();
        
        return Inertia::render('Vendor/Onboarding/Step1', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Process step 1.
     */
    public function storeStep1(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_phone' => 'required|string|max:20',
            'business_email' => 'required|email|max:255',
        ]);

        // Store in session for multi-step form
        session()->put('onboarding.step1', $validated);

        return redirect()->route('vendor.onboarding.step2');
    }

    /**
     * Show step 2: Service Details.
     */
    public function step2(): Response
    {
        $categories = Category::all();

        return Inertia::render('Vendor/Onboarding/Step2', [
            'categories' => $categories,
        ]);
    }

    /**
     * Process step 2.
     */
    public function storeStep2(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'service_name' => 'required|string|max:255',
            'description' => 'required|string|min:50|max:1000',
            'price_range' => 'required|integer|min:1|max:4',
        ]);

        session()->put('onboarding.step2', $validated);

        return redirect()->route('vendor.onboarding.step3');
    }

    /**
     * Show step 3: Service Offerings.
     */
    public function step3(): Response
    {
        return Inertia::render('Vendor/Onboarding/Step3');
    }

    /**
     * Process step 3 and complete onboarding.
     */
    public function storeStep3(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'offerings' => 'required|array|min:1',
            'offerings.*.name' => 'required|string|max:255',
            'offerings.*.description' => 'required|string|max:500',
            'offerings.*.price' => 'required|numeric|min:0',
            'offerings.*.duration_minutes' => 'required|integer|min:15|max:480',
        ]);

        // Get all session data
        $step1 = session()->get('onboarding.step1');
        $step2 = session()->get('onboarding.step2');
        $step3 = $validated;

        // Update user to service provider
        $user = $request->user();
        $user->update([
            'is_service_provider' => true,
            'phone' => $step1['business_phone'],
        ]);

        // Create the service
        $slug = \Illuminate\Support\Str::slug($step2['service_name']);
        $counter = 1;
        $originalSlug = $slug;
        while (Service::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $service = Service::create([
            'user_id' => $user->id,
            'category_id' => $step2['category_id'],
            'name' => $step2['service_name'],
            'slug' => $slug,
            'description' => $step2['description'],
            'price_range' => $step2['price_range'],
            'is_available' => true,
            'rating' => 0,
            'reviews_count' => 0,
            'city' => 'New York',
            'state' => 'NY',
        ]);

        // Create service offerings
        foreach ($step3['offerings'] as $offering) {
            ServiceOffering::create([
                'service_id' => $service->id,
                'name' => $offering['name'],
                'description' => $offering['description'],
                'price' => $offering['price'],
                'duration_minutes' => $offering['duration_minutes'],
                'is_popular' => false,
            ]);
        }

        // Clear onboarding session
        session()->forget('onboarding');

        return redirect()->route('vendor.dashboard')
            ->with('success', __('Welcome! Your service has been created successfully. You can now start receiving bookings.'));
    }
}
