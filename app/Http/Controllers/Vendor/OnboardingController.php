<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\BusinessHour;
use App\Models\Category;
use App\Models\Service;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
    public function step2(): Response|RedirectResponse
    {
        if (! session()->has('onboarding.step1')) {
            return redirect()->route('vendor.onboarding.step1')
                ->with('error', __('Complete the business details first.'));
        }

        $categories = Category::all();

        return Inertia::render('Vendor/Onboarding/Step2', [
            'categories' => $categories,
            'saved' => session('onboarding.step2'),
        ]);
    }

    /**
     * Process step 2.
     */
    public function storeStep2(Request $request): RedirectResponse
    {
        if (! session()->has('onboarding.step1')) {
            return redirect()->route('vendor.onboarding.step1')
                ->with('error', __('Complete the business details first.'));
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'shop_name' => 'required|string|max:255',
            'description' => 'required|string|min:50|max:1000',
            'city' => 'required|string|max:120',
            'address' => 'required|string|max:255',
            'currency' => 'required|in:CZK,EUR',
            'business_hours' => 'required|array|min:1',
            'business_hours.*.day_of_week' => 'required|integer|min:0|max:6|distinct',
            'business_hours.*.is_closed' => 'required|boolean',
            'business_hours.*.time_from' => 'nullable|required_if:business_hours.*.is_closed,false|date_format:H:i',
            'business_hours.*.time_to' => 'nullable|required_if:business_hours.*.is_closed,false|date_format:H:i',
        ]);

        foreach ($validated['business_hours'] as $index => $hours) {
            if (! $hours['is_closed'] && $hours['time_to'] <= $hours['time_from']) {
                return back()->withErrors([
                    "business_hours.{$index}.time_to" => __('Closing time must be after opening time.'),
                ])->withInput();
            }
        }

        session()->put('onboarding.step2', $validated);

        return redirect()->route('vendor.onboarding.step3');
    }

    /**
     * Show step 3: Service Offerings.
     */
    public function step3(): Response|RedirectResponse
    {
        if (! session()->has('onboarding.step1')) {
            return redirect()->route('vendor.onboarding.step1')
                ->with('error', __('Complete the business details first.'));
        }
        if (! session()->has('onboarding.step2')) {
            return redirect()->route('vendor.onboarding.step2')
                ->with('error', __('Complete the shop details first.'));
        }

        return Inertia::render('Vendor/Onboarding/Step3');
    }

    /**
     * Process step 3 and complete onboarding.
     */
    public function storeStep3(Request $request): RedirectResponse
    {
        if (! session()->has('onboarding.step1')) {
            return redirect()->route('vendor.onboarding.step1')
                ->with('error', __('Complete the business details first.'));
        }
        if (! session()->has('onboarding.step2')) {
            return redirect()->route('vendor.onboarding.step2')
                ->with('error', __('Complete the shop details first.'));
        }

        $validated = $request->validate([
            'services' => 'required|array|min:1',
            'services.*.name' => 'required|string|max:255',
            'services.*.description' => 'required|string|max:500',
            'services.*.price' => 'required|numeric|min:0',
            'services.*.duration_minutes' => 'required|integer|min:15|max:480',
        ]);

        // Get all session data
        $step1 = session()->get('onboarding.step1');
        $step2 = session()->get('onboarding.step2');
        $shop = DB::transaction(function () use ($request, $step1, $step2, $validated): ?Shop {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            if ($user->is_vendor) {
                return null;
            }

            $user->update([
                'is_vendor' => true,
                'provider_onboarding_pending' => false,
                'phone' => $step1['business_phone'],
            ]);

            $slug = Str::slug($step2['shop_name']) ?: 'provozovna';
            $originalSlug = $slug;
            $counter = 1;
            while (Shop::where('slug', $slug)->exists()) {
                $slug = $originalSlug.'-'.$counter++;
            }

            $shop = Shop::create([
                'user_id' => $user->id,
                'category_id' => $step2['category_id'],
                'name' => $step2['shop_name'],
                'slug' => $slug,
                'description' => $step2['description'],
                'price_range' => 2,
                'currency' => $step2['currency'],
                'timezone' => 'Europe/Prague',
                'contact_email' => mb_strtolower($step1['business_email']),
                'contact_phone' => $step1['business_phone'],
                'is_available' => true,
                'rating' => 0,
                'reviews_count' => 0,
                'city' => $step2['city'],
                'state' => 'Česko',
                'address' => $step2['address'],
            ]);

            foreach ($step2['business_hours'] as $hours) {
                if (! $hours['is_closed']) {
                    BusinessHour::create([
                        'shop_id' => $shop->id,
                        'day_of_week' => $hours['day_of_week'],
                        'time_from' => $hours['time_from'],
                        'time_to' => $hours['time_to'],
                    ]);
                }
            }

            foreach ($validated['services'] as $service) {
                Service::create([
                    'shop_id' => $shop->id,
                    'name' => $service['name'],
                    'description' => $service['description'],
                    'price' => $service['price'],
                    'duration_minutes' => $service['duration_minutes'],
                    'is_popular' => false,
                    'is_available' => true,
                ]);
            }

            return $shop;
        }, 3);

        // Clear onboarding session
        session()->forget('onboarding');

        return redirect()->route('vendor.dashboard')
            ->with('success', __('Welcome! Your service has been created successfully. You can now start receiving bookings.'));
    }
}
