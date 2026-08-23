<?php

namespace App\Http\Controllers;

use App\Mail\ContactSubmissionReceived;
use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    /**
     * Display the Terms of Service page.
     */
    public function terms(): Response
    {
        return Inertia::render('Pages/Terms', [
            'title' => 'Terms of Service',
            'content' => $this->getTermsContent(),
            'lastUpdated' => app()->isLocale('cs') ? 'srpen 2026' : 'August 2026',
        ]);
    }

    /**
     * Display the Privacy Policy page.
     */
    public function privacy(): Response
    {
        return Inertia::render('Pages/Privacy', [
            'title' => 'Privacy Policy',
            'content' => $this->getPrivacyContent(),
            'lastUpdated' => app()->isLocale('cs') ? 'srpen 2026' : 'August 2026',
        ]);
    }

    /**
     * Display the FAQ page.
     */
    public function faq(): Response
    {
        return Inertia::render('Pages/FAQ', [
            'title' => 'Frequently Asked Questions',
            'faqs' => $this->getFAQs(),
        ]);
    }

    /**
     * Display the Contact page.
     */
    public function contact(): Response
    {
        return Inertia::render('Pages/Contact', [
            'title' => 'Contact Us',
        ]);
    }

    /**
     * Handle contact form submission.
     */
    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|min:10|max:5000',
            'type' => 'required|in:general,support,partnership,feedback',
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $submission = ContactSubmission::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'type' => $validated['type'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $to = config('services.contact.to') ?: config('mail.from.address');
            if ($to) {
                DB::afterCommit(function () use ($submission, $to): void {
                    try {
                        Mail::to($to)->queue(new ContactSubmissionReceived($submission));
                    } catch (\Throwable $exception) {
                        Log::warning('Post-commit contact email failed.', [
                            'contact_submission_id' => $submission->id,
                            'exception' => $exception::class,
                        ]);
                    }
                });
            }
        });

        return back()->with('success', __('Thank you. Your message has been received.'));
    }

    /**
     * Get Terms of Service content.
     */
    private function getTermsContent(): array
    {
        return [
            'sections' => [
                [
                    'title' => '1. Acceptance of Terms',
                    'content' => 'By using Domluveno, you agree to these terms. If you do not agree, do not use the platform.',
                ],
                [
                    'title' => '2. User Accounts',
                    'content' => 'You must create an account to use certain features. You are responsible for maintaining the confidentiality of your account credentials and for all activities under your account.',
                ],
                [
                    'title' => '3. Vendors',
                    'content' => 'Providers are responsible for the accuracy of their shop, availability, qualifications, prices, and service information. Domluveno may remove accounts that misuse the platform.',
                ],
                [
                    'title' => '4. Bookings and cancellations',
                    'content' => 'Bookings are subject to provider confirmation and availability. Domluveno does not process payments. Customers and guests may cancel at least 24 hours before the appointment starts.',
                ],
                [
                    'title' => '5. Reviews and Ratings',
                    'content' => 'Users may leave reviews for completed services. Reviews must be honest and based on actual experiences. We reserve the right to remove fake or inappropriate reviews.',
                ],
                [
                    'title' => '6. Limitation of Liability',
                    'content' => 'Domluveno connects customers with independent providers. Providers remain responsible for the services they deliver.',
                ],
            ],
        ];
    }

    /**
     * Get Privacy Policy content.
     */
    private function getPrivacyContent(): array
    {
        return [
            'sections' => [
                [
                    'title' => 'Information We Collect',
                    'content' => 'We collect information you provide directly, including name, email, phone number, account information, booking details, reviews, and support requests.',
                ],
                [
                    'title' => 'How We Use Your Information',
                    'content' => 'We use your information to process bookings, communicate with you, improve our platform, prevent fraud, and comply with legal obligations.',
                ],
                [
                    'title' => 'Information Sharing',
                    'content' => 'We share necessary information with vendors to fulfill bookings. We do not sell your personal information to third parties for marketing purposes.',
                ],
                [
                    'title' => 'Data Security',
                    'content' => 'We use access controls and secure, hashed guest-management tokens to protect booking information. No payment-card information is collected by Domluveno.',
                ],
                [
                    'title' => 'Your Rights',
                    'content' => 'You have the right to access, correct, or delete your personal information. Contact us to exercise these rights or for any privacy-related questions.',
                ],
            ],
        ];
    }

    /**
     * Get FAQ content.
     */
    private function getFAQs(): array
    {
        return [
            [
                'category' => 'General',
                'questions' => [
                    [
                        'question' => 'What is Domluveno?',
                        'answer' => 'Domluveno helps customers find local providers, compare services, and request an appointment in one place.',
                    ],
                    [
                        'question' => 'How do I create an account?',
                        'answer' => 'An account is optional for booking. Create one if you want all bookings and reviews in one place, or verify your email before setting up a provider profile.',
                    ],
                    [
                        'question' => 'Which locations are supported?',
                        'answer' => 'The shop list shows the cities currently represented by active providers. Domluveno does not yet use live proximity or location tracking.',
                    ],
                ],
            ],
            [
                'category' => 'Booking',
                'questions' => [
                    [
                        'question' => 'How do I book a service?',
                        'answer' => 'Browse services, select one you like, choose your preferred date and time, and complete the booking process. You will receive a confirmation email.',
                    ],
                    [
                        'question' => 'Can I cancel or reschedule a booking?',
                        'answer' => 'You can cancel a pending or confirmed booking at least 24 hours before it starts. Guests use the secure link sent by email; account customers use My bookings.',
                    ],
                    [
                        'question' => 'Does Domluveno process payments?',
                        'answer' => 'No. The displayed price is booking information; payment arrangements are handled directly with the provider.',
                    ],
                ],
            ],
            [
                'category' => 'Vendors',
                'questions' => [
                    [
                        'question' => 'How do I become a vendor?',
                        'answer' => 'Verify your email, choose Become a provider, and complete the three setup steps for your shop, hours, and services.',
                    ],
                    [
                        'question' => 'Does Domluveno verify provider qualifications?',
                        'answer' => 'Domluveno verifies the provider email address but does not currently conduct background or professional-license checks. Providers are responsible for truthful profile information.',
                    ],
                    [
                        'question' => 'How are payments handled?',
                        'answer' => 'Domluveno does not process provider payouts. Agree payment details directly with the customer.',
                    ],
                ],
            ],
            [
                'category' => 'Support',
                'questions' => [
                    [
                        'question' => 'How do I contact customer support?',
                        'answer' => 'Use the Contact page. Your request is stored for the support team and a copy is sent to the configured support mailbox.',
                    ],
                    [
                        'question' => 'What if I have an issue with a service?',
                        'answer' => 'Contact the vendor first. If unresolved, reach out to our support team within 48 hours and we will help mediate.',
                    ],
                    [
                        'question' => 'Does Domluveno offer a satisfaction guarantee?',
                        'answer' => 'No automatic refund or satisfaction guarantee is offered. Contact the provider first and use the Contact page if you need to report a platform issue.',
                    ],
                ],
            ],
        ];
    }
}
