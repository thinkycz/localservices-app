<?php

namespace App\Http\Controllers;

use App\Mail\ContactSubmissionReceived;
use App\Models\ContactSubmission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    /**
     * Display the About page.
     */
    public function about(): Response
    {
        return Inertia::render('Pages/About', [
            'title' => 'About Us',
            'content' => $this->getAboutContent(),
        ]);
    }

    /**
     * Display the Terms of Service page.
     */
    public function terms(): Response
    {
        return Inertia::render('Pages/Terms', [
            'title' => 'Terms of Service',
            'content' => $this->getTermsContent(),
            'lastUpdated' => 'February 2026',
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
            'lastUpdated' => 'February 2026',
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
    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|min:10|max:5000',
            'type' => 'required|in:general,support,partnership,feedback',
        ]);

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
            Mail::to($to)->send(new ContactSubmissionReceived($submission));
        }

        return back()->with('success', 'Thank you for your message! We will get back to you within 24 hours.');
    }

    /**
     * Get About page content.
     */
    private function getAboutContent(): array
    {
        return [
            'mission' => 'Local Services connects skilled service providers with customers who need quality services. Our mission is to make finding and booking local services simple, reliable, and trustworthy.',
            'story' => 'Founded in 2024, Local Services started with a simple idea: make it easier for people to find trusted local professionals. Today, we serve thousands of customers and service providers across the country.',
            'values' => [
                [
                    'title' => 'Trust & Safety',
                    'description' => 'We verify all service providers and maintain strict quality standards.',
                    'icon' => 'shield-check',
                ],
                [
                    'title' => 'Convenience',
                    'description' => 'Book services anytime, anywhere with our easy-to-use platform.',
                    'icon' => 'clock',
                ],
                [
                    'title' => 'Quality',
                    'description' => 'We partner only with skilled professionals who meet our high standards.',
                    'icon' => 'star',
                ],
                [
                    'title' => 'Community',
                    'description' => 'Supporting local businesses and building stronger communities.',
                    'icon' => 'users',
                ],
            ],
            'stats' => [
                'providers' => '2,500+',
                'customers' => '50,000+',
                'bookings' => '100,000+',
                'cities' => '150+',
            ],
        ];
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
                    'content' => 'By accessing or using Local Services, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our platform.',
                ],
                [
                    'title' => '2. User Accounts',
                    'content' => 'You must create an account to use certain features. You are responsible for maintaining the confidentiality of your account credentials and for all activities under your account.',
                ],
                [
                    'title' => '3. Service Providers',
                    'content' => 'Service providers must meet our qualification standards and maintain appropriate licenses and insurance. We reserve the right to remove providers who violate our policies.',
                ],
                [
                    'title' => '4. Bookings and Payments',
                    'content' => 'All bookings are subject to availability. Payments are processed securely through our platform. Cancellation policies vary by service and are clearly displayed before booking.',
                ],
                [
                    'title' => '5. Reviews and Ratings',
                    'content' => 'Users may leave reviews for completed services. Reviews must be honest and based on actual experiences. We reserve the right to remove fake or inappropriate reviews.',
                ],
                [
                    'title' => '6. Limitation of Liability',
                    'content' => 'Local Services acts as a platform connecting customers and service providers. We are not responsible for the quality of services provided by independent contractors.',
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
                    'content' => 'We collect information you provide directly (name, email, phone), booking details, payment information, and usage data to improve our services.',
                ],
                [
                    'title' => 'How We Use Your Information',
                    'content' => 'We use your information to process bookings, communicate with you, improve our platform, prevent fraud, and comply with legal obligations.',
                ],
                [
                    'title' => 'Information Sharing',
                    'content' => 'We share necessary information with service providers to fulfill bookings. We do not sell your personal information to third parties for marketing purposes.',
                ],
                [
                    'title' => 'Data Security',
                    'content' => 'We implement industry-standard security measures to protect your data. All payment information is encrypted and processed by PCI-compliant providers.',
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
                        'question' => 'What is Local Services?',
                        'answer' => 'Local Services is a platform that connects customers with trusted local service providers. You can browse, compare, and book services all in one place.',
                    ],
                    [
                        'question' => 'How do I create an account?',
                        'answer' => 'Click the "Sign Up" button and fill in your details. You can register as a customer to book services or apply to become a service provider.',
                    ],
                    [
                        'question' => 'Is Local Services available in my area?',
                        'answer' => 'We are rapidly expanding! Enter your location on the homepage to see available services in your area.',
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
                        'answer' => 'Yes, you can cancel or reschedule through your account dashboard. Please check the cancellation policy for each service as they may vary.',
                    ],
                    [
                        'question' => 'What payment methods are accepted?',
                        'answer' => 'We accept all major credit cards, debit cards, and digital wallets including Apple Pay and Google Pay.',
                    ],
                ],
            ],
            [
                'category' => 'Service Providers',
                'questions' => [
                    [
                        'question' => 'How do I become a service provider?',
                        'answer' => 'Click "Become a Provider" and complete our onboarding process. We will review your application and verify your credentials.',
                    ],
                    [
                        'question' => 'What are the requirements to join?',
                        'answer' => 'Requirements vary by service category but generally include relevant experience, licenses, insurance, and passing our background check.',
                    ],
                    [
                        'question' => 'How and when do I get paid?',
                        'answer' => 'Payments are processed automatically after service completion. Funds are typically deposited within 2-3 business days.',
                    ],
                ],
            ],
            [
                'category' => 'Support',
                'questions' => [
                    [
                        'question' => 'How do I contact customer support?',
                        'answer' => 'You can reach us through the Contact page, email at support@localservices.com, or call 1-800-LOCAL-SRV.',
                    ],
                    [
                        'question' => 'What if I have an issue with a service?',
                        'answer' => 'Contact the service provider first. If unresolved, reach out to our support team within 48 hours and we will help mediate.',
                    ],
                    [
                        'question' => 'Is there a satisfaction guarantee?',
                        'answer' => 'Yes! If you are not satisfied with a service, contact us within 24 hours and we will work to make it right or provide a refund.',
                    ],
                ],
            ],
        ];
    }
}
