<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceOffering;
use Illuminate\Database\Seeder;

class ServiceOfferingSeeder extends Seeder
{
    public function run(): void
    {
        $offeringsBySlug = [

            // ── Barbershops ──────────────────────────────────────────────────────
            'classic-cuts-barbershop' => [
                ['name' => 'Classic Haircut', 'description' => 'Traditional haircut with clippers and scissors. Includes hot towel and scalp massage.', 'price' => 35, 'duration_minutes' => 30, 'is_popular' => true, 'category_tag' => 'Haircut', 'staff_level' => 'Barber'],
                ['name' => 'Beard Trim & Shape', 'description' => 'Professional beard trimming and shaping with razor line-up.', 'price' => 20, 'duration_minutes' => 20, 'is_popular' => false, 'category_tag' => 'Grooming', 'staff_level' => 'Barber'],
                ['name' => 'Hot Towel Shave', 'description' => 'Relaxing hot towel treatment with straight razor shave.', 'price' => 40, 'duration_minutes' => 45, 'is_popular' => true, 'category_tag' => 'Shave', 'staff_level' => 'Senior Barber'],
                ['name' => 'Hair & Beard Combo', 'description' => 'Full haircut with beard trim and styling.', 'price' => 50, 'duration_minutes' => 45, 'is_popular' => false, 'category_tag' => 'Combo', 'staff_level' => 'Barber'],
            ],

            'urban-fade-studio' => [
                ['name' => 'Fade Haircut', 'description' => 'Modern fade haircut with any length on top. Skin fades to zero.', 'price' => 45, 'duration_minutes' => 35, 'is_popular' => true, 'category_tag' => 'Haircut', 'staff_level' => 'Stylist'],
                ['name' => 'Hair Design', 'description' => 'Custom hair design or pattern shaved into haircut.', 'price' => 25, 'duration_minutes' => 15, 'is_popular' => false, 'category_tag' => 'Design', 'staff_level' => 'Stylist'],
                ['name' => 'Buzz Cut', 'description' => 'All-over buzz cut with clippers. Quick and clean.', 'price' => 25, 'duration_minutes' => 20, 'is_popular' => false, 'category_tag' => 'Haircut', 'staff_level' => 'Barber'],
                ['name' => 'Kids Haircut', 'description' => 'Haircut for children under 12 years old.', 'price' => 25, 'duration_minutes' => 25, 'is_popular' => false, 'category_tag' => 'Haircut', 'staff_level' => 'Barber'],
            ],

            'gentleman-s-groom-lounge' => [
                ['name' => 'Premium Haircut', 'description' => 'Luxury haircut with scalp massage, hot towel treatment, and styling.', 'price' => 65, 'duration_minutes' => 45, 'is_popular' => true, 'category_tag' => 'Haircut', 'staff_level' => 'Master Barber'],
                ['name' => 'Executive Shave', 'description' => 'Full hot towel shave with aftershave treatment and facial massage.', 'price' => 55, 'duration_minutes' => 45, 'is_popular' => false, 'category_tag' => 'Shave', 'staff_level' => 'Master Barber'],
                ['name' => 'Full Grooming Package', 'description' => 'Complete package: haircut, beard trim, hot towel shave, and facial.', 'price' => 120, 'duration_minutes' => 90, 'is_popular' => true, 'category_tag' => 'Package', 'staff_level' => 'Master Barber'],
            ],

            // ── Nail Salons ──────────────────────────────────────────────────────
            'polished-perfection' => [
                ['name' => 'Classic Manicure', 'description' => 'Nail shaping, cuticle care, hand massage, and regular polish.', 'price' => 30, 'duration_minutes' => 30, 'is_popular' => true, 'category_tag' => 'Manicure', 'staff_level' => 'Nail Tech'],
                ['name' => 'Gel Manicure', 'description' => 'Long-lasting gel polish with chip-free finish. Includes manicure.', 'price' => 50, 'duration_minutes' => 45, 'is_popular' => true, 'category_tag' => 'Manicure', 'staff_level' => 'Nail Tech'],
                ['name' => 'Classic Pedicure', 'description' => 'Foot soak, exfoliation, nail care, and regular polish.', 'price' => 40, 'duration_minutes' => 40, 'is_popular' => false, 'category_tag' => 'Pedicure', 'staff_level' => 'Nail Tech'],
                ['name' => 'Gel Pedicure', 'description' => 'Spa pedicure with long-lasting gel polish.', 'price' => 60, 'duration_minutes' => 50, 'is_popular' => false, 'category_tag' => 'Pedicure', 'staff_level' => 'Nail Tech'],
                ['name' => 'Nail Art (per nail)', 'description' => 'Custom nail art designs. French tips, gems, or intricate designs.', 'price' => 5, 'duration_minutes' => 10, 'is_popular' => false, 'category_tag' => 'Art', 'staff_level' => 'Nail Tech'],
            ],

            'luxe-nail-bar' => [
                ['name' => 'Organic Manicure', 'description' => 'Manicure using organic, chemical-free products. Includes hand spa treatment.', 'price' => 45, 'duration_minutes' => 40, 'is_popular' => true, 'category_tag' => 'Manicure', 'staff_level' => 'Senior Nail Tech'],
                ['name' => 'Spa Pedicure', 'description' => 'Luxurious pedicure with foot mask, hot stones, and extended massage.', 'price' => 65, 'duration_minutes' => 60, 'is_popular' => true, 'category_tag' => 'Pedicure', 'staff_level' => 'Senior Nail Tech'],
                ['name' => 'Acrylic Full Set', 'description' => 'Full acrylic nail application with choice of polish.', 'price' => 75, 'duration_minutes' => 75, 'is_popular' => false, 'category_tag' => 'Extensions', 'staff_level' => 'Nail Tech'],
                ['name' => 'Dip Powder Manicure', 'description' => 'Long-lasting dip powder application. No UV light needed.', 'price' => 55, 'duration_minutes' => 45, 'is_popular' => false, 'category_tag' => 'Manicure', 'staff_level' => 'Nail Tech'],
            ],

            'quick-tips-nail-salon' => [
                ['name' => 'Express Manicure', 'description' => 'Quick nail shaping and polish. Perfect for busy schedules.', 'price' => 18, 'duration_minutes' => 20, 'is_popular' => true, 'category_tag' => 'Manicure', 'staff_level' => 'Nail Tech'],
                ['name' => 'Express Pedicure', 'description' => 'Quick pedicure service. Nail trim, file, and polish.', 'price' => 25, 'duration_minutes' => 25, 'is_popular' => true, 'category_tag' => 'Pedicure', 'staff_level' => 'Nail Tech'],
                ['name' => 'Polish Change (Hands)', 'description' => 'Quick polish change for existing manicure.', 'price' => 12, 'duration_minutes' => 10, 'is_popular' => false, 'category_tag' => 'Polish', 'staff_level' => 'Nail Tech'],
                ['name' => 'Polish Change (Feet)', 'description' => 'Quick polish change for existing pedicure.', 'price' => 15, 'duration_minutes' => 10, 'is_popular' => false, 'category_tag' => 'Polish', 'staff_level' => 'Nail Tech'],
            ],

            // ── Restaurants ──────────────────────────────────────────────────────
            'bella-italia' => [
                ['name' => 'Dinner for Two', 'description' => 'Includes appetizer, two entrees, and dessert. Perfect for date night.', 'price' => 120, 'duration_minutes' => 90, 'is_popular' => true, 'category_tag' => 'Dinner', 'staff_level' => 'Server'],
                ['name' => 'Pasta Making Class', 'description' => 'Learn to make fresh pasta with our chef. Includes meal and wine.', 'price' => 85, 'duration_minutes' => 120, 'is_popular' => false, 'category_tag' => 'Class', 'staff_level' => 'Chef'],
                ['name' => 'Wine Tasting Experience', 'description' => 'Guided wine tasting with Italian appetizers. 5 wines.', 'price' => 65, 'duration_minutes' => 60, 'is_popular' => false, 'category_tag' => 'Wine', 'staff_level' => 'Sommelier'],
                ['name' => 'Private Dining', 'description' => 'Exclusive use of private dining room. Custom menu available.', 'price' => 300, 'duration_minutes' => 180, 'is_popular' => false, 'category_tag' => 'Private', 'staff_level' => 'Server'],
            ],

            'dragon-palace' => [
                ['name' => 'Family Feast', 'description' => 'Complete family-style meal for 4. Includes appetizers, entrees, and rice.', 'price' => 95, 'duration_minutes' => 45, 'is_popular' => true, 'category_tag' => 'Family', 'staff_level' => 'Server'],
                ['name' => 'Dim Sum Brunch', 'description' => 'All-you-can-eat dim sum selection. Weekend brunch only.', 'price' => 35, 'duration_minutes' => 60, 'is_popular' => true, 'category_tag' => 'Brunch', 'staff_level' => 'Server'],
                ['name' => 'Peking Duck Dinner', 'description' => 'Full Peking duck experience with pancakes, scallions, and sauce.', 'price' => 75, 'duration_minutes' => 45, 'is_popular' => false, 'category_tag' => 'Dinner', 'staff_level' => 'Chef'],
                ['name' => 'Private Room Booking', 'description' => 'Reserve private dining room for groups of 8+.', 'price' => 150, 'duration_minutes' => 120, 'is_popular' => false, 'category_tag' => 'Private', 'staff_level' => 'Server'],
            ],

            'the-burger-joint' => [
                ['name' => 'Gourmet Burger Meal', 'description' => 'Signature burger with choice of toppings, fries, and drink.', 'price' => 22, 'duration_minutes' => 20, 'is_popular' => true, 'category_tag' => 'Meal', 'staff_level' => 'Server'],
                ['name' => 'Build Your Own Burger', 'description' => 'Custom burger with choice of patty, buns, toppings, and sides.', 'price' => 25, 'duration_minutes' => 25, 'is_popular' => true, 'category_tag' => 'Meal', 'staff_level' => 'Server'],
                ['name' => 'Milkshake', 'description' => 'Hand-spun milkshake. Various flavors available.', 'price' => 8, 'duration_minutes' => 5, 'is_popular' => false, 'category_tag' => 'Drink', 'staff_level' => 'Server'],
                ['name' => 'Loaded Fries', 'description' => 'Crispy fries topped with cheese, bacon, and special sauce.', 'price' => 12, 'duration_minutes' => 10, 'is_popular' => false, 'category_tag' => 'Appetizer', 'staff_level' => 'Server'],
            ],

            // ── Coffee Shops ──────────────────────────────────────────────────────
            'morning-brew-cafe' => [
                ['name' => 'Coffee & Pastry', 'description' => 'Any hot coffee drink with a fresh pastry of your choice.', 'price' => 8, 'duration_minutes' => 5, 'is_popular' => true, 'category_tag' => 'Breakfast', 'staff_level' => 'Barista'],
                ['name' => 'Breakfast Sandwich', 'description' => 'Egg, cheese, and choice of bacon or sausage on fresh bread.', 'price' => 10, 'duration_minutes' => 8, 'is_popular' => true, 'category_tag' => 'Breakfast', 'staff_level' => 'Barista'],
                ['name' => 'Latte Art Class', 'description' => 'Learn to create beautiful latte art. Includes coffee and supplies.', 'price' => 45, 'duration_minutes' => 60, 'is_popular' => false, 'category_tag' => 'Class', 'staff_level' => 'Barista'],
                ['name' => 'Cold Brew Flight', 'description' => 'Tasting flight of 3 different cold brew recipes.', 'price' => 12, 'duration_minutes' => 10, 'is_popular' => false, 'category_tag' => 'Coffee', 'staff_level' => 'Barista'],
            ],

            'espresso-lab' => [
                ['name' => 'Pour-Over Coffee', 'description' => 'Single-origin pour-over brewed to perfection. Ask for today\'s selection.', 'price' => 7, 'duration_minutes' => 8, 'is_popular' => true, 'category_tag' => 'Coffee', 'staff_level' => 'Barista'],
                ['name' => 'Espresso Tasting', 'description' => 'Flight of 3 espresso shots from different roasters.', 'price' => 15, 'duration_minutes' => 15, 'is_popular' => true, 'category_tag' => 'Tasting', 'staff_level' => 'Barista'],
                ['name' => 'Latte', 'description' => 'Smooth latte with house-made espresso and steamed milk.', 'price' => 5, 'duration_minutes' => 5, 'is_popular' => false, 'category_tag' => 'Coffee', 'staff_level' => 'Barista'],
                ['name' => 'Coffee Subscription', 'description' => 'Monthly subscription for 1lb bags of single-origin beans.', 'price' => 30, 'duration_minutes' => 0, 'is_popular' => false, 'category_tag' => 'Subscription', 'staff_level' => 'Barista'],
            ],

            // ── Pet Grooming ──────────────────────────────────────────────────────
            'paws-claws-grooming' => [
                ['name' => 'Full Groom (Small Dog)', 'description' => 'Complete grooming: bath, haircut, nail trim, ear cleaning. For dogs under 20 lbs.', 'price' => 65, 'duration_minutes' => 90, 'is_popular' => true, 'category_tag' => 'Grooming', 'staff_level' => 'Groomer'],
                ['name' => 'Full Groom (Large Dog)', 'description' => 'Complete grooming for dogs over 50 lbs.', 'price' => 95, 'duration_minutes' => 120, 'is_popular' => true, 'category_tag' => 'Grooming', 'staff_level' => 'Groomer'],
                ['name' => 'Bath & Brush', 'description' => 'Bath, blow dry, and brushing. Nail trim included.', 'price' => 40, 'duration_minutes' => 45, 'is_popular' => false, 'category_tag' => 'Grooming', 'staff_level' => 'Groomer'],
                ['name' => 'Nail Trim & File', 'description' => 'Nail clipping, filing, and conditioning treatment.', 'price' => 20, 'duration_minutes' => 20, 'is_popular' => false, 'category_tag' => 'Nails', 'staff_level' => 'Groomer'],
                ['name' => 'Cat Grooming', 'description' => 'Gentle grooming for cats. Bath, brush, and nail trim.', 'price' => 55, 'duration_minutes' => 60, 'is_popular' => false, 'category_tag' => 'Grooming', 'staff_level' => 'Groomer'],
            ],

            'happy-tails-spa' => [
                ['name' => 'Spa Package (Small)', 'description' => 'Luxury spa day: aromatherapy bath, massage, facial, and full styling. Under 20 lbs.', 'price' => 120, 'duration_minutes' => 150, 'is_popular' => true, 'category_tag' => 'Spa', 'staff_level' => 'Master Groomer'],
                ['name' => 'Spa Package (Large)', 'description' => 'Luxury spa day for dogs over 50 lbs.', 'price' => 160, 'duration_minutes' => 180, 'is_popular' => true, 'category_tag' => 'Spa', 'staff_level' => 'Master Groomer'],
                ['name' => 'Puppy First Groom', 'description' => 'Gentle introduction to grooming for puppies under 6 months.', 'price' => 45, 'duration_minutes' => 45, 'is_popular' => false, 'category_tag' => 'Grooming', 'staff_level' => 'Groomer'],
                ['name' => 'De-shedding Treatment', 'description' => 'Special treatment to reduce shedding. Includes bath and brushing.', 'price' => 75, 'duration_minutes' => 90, 'is_popular' => false, 'category_tag' => 'Treatment', 'staff_level' => 'Groomer'],
            ],

            'mobile-grooming-van' => [
                ['name' => 'Mobile Groom (Small)', 'description' => 'Full grooming service at your home. For dogs under 25 lbs.', 'price' => 85, 'duration_minutes' => 90, 'is_popular' => true, 'category_tag' => 'Mobile', 'staff_level' => 'Groomer'],
                ['name' => 'Mobile Groom (Medium)', 'description' => 'Full grooming at your door. For dogs 25-50 lbs.', 'price' => 105, 'duration_minutes' => 105, 'is_popular' => true, 'category_tag' => 'Mobile', 'staff_level' => 'Groomer'],
                ['name' => 'Mobile Groom (Large)', 'description' => 'Full grooming at your location. For dogs over 50 lbs.', 'price' => 135, 'duration_minutes' => 120, 'is_popular' => false, 'category_tag' => 'Mobile', 'staff_level' => 'Groomer'],
                ['name' => 'Quick Wash', 'description' => 'Bath and blow dry only. No haircut.', 'price' => 45, 'duration_minutes' => 45, 'is_popular' => false, 'category_tag' => 'Mobile', 'staff_level' => 'Groomer'],
            ],

            // ── Fitness & Gyms ───────────────────────────────────────────────────
            'iron-temple-fitness' => [
                ['name' => 'Day Pass', 'description' => 'Full access to gym facilities for one day.', 'price' => 25, 'duration_minutes' => 0, 'is_popular' => true, 'category_tag' => 'Pass', 'staff_level' => 'Staff'],
                ['name' => 'Personal Training Session', 'description' => 'One-on-one training with certified personal trainer.', 'price' => 75, 'duration_minutes' => 60, 'is_popular' => true, 'category_tag' => 'Training', 'staff_level' => 'Trainer'],
                ['name' => 'Month Membership', 'description' => 'Unlimited gym access for one month. Includes all equipment.', 'price' => 99, 'duration_minutes' => 0, 'is_popular' => false, 'category_tag' => 'Membership', 'staff_level' => 'Staff'],
                ['name' => 'Group Fitness Class', 'description' => 'Join any group class: spin, HIIT, yoga, etc.', 'price' => 20, 'duration_minutes' => 60, 'is_popular' => false, 'category_tag' => 'Class', 'staff_level' => 'Instructor'],
            ],

            'zen-yoga-studio' => [
                ['name' => 'Drop-in Yoga Class', 'description' => 'Any single yoga class. Vinyasa, Hatha, or Yin available.', 'price' => 25, 'duration_minutes' => 60, 'is_popular' => true, 'category_tag' => 'Class', 'staff_level' => 'Instructor'],
                ['name' => 'Hot Yoga Session', 'description' => 'Heated yoga class in our infrared studio.', 'price' => 30, 'duration_minutes' => 60, 'is_popular' => true, 'category_tag' => 'Class', 'staff_level' => 'Instructor'],
                ['name' => 'Private Yoga Session', 'description' => 'One-on-one yoga instruction tailored to your needs.', 'price' => 100, 'duration_minutes' => 60, 'is_popular' => false, 'category_tag' => 'Private', 'staff_level' => 'Instructor'],
                ['name' => '10-Class Package', 'description' => '10 yoga classes valid for 3 months.', 'price' => 200, 'duration_minutes' => 0, 'is_popular' => false, 'category_tag' => 'Package', 'staff_level' => 'Instructor'],
                ['name' => 'Meditation Workshop', 'description' => 'Group meditation and mindfulness session.', 'price' => 35, 'duration_minutes' => 90, 'is_popular' => false, 'category_tag' => 'Workshop', 'staff_level' => 'Instructor'],
            ],

            'crossfit-manhattan' => [
                ['name' => 'Intro to CrossFit', 'description' => 'One-on-one basics session to learn fundamental movements.', 'price' => 50, 'duration_minutes' => 60, 'is_popular' => true, 'category_tag' => 'Intro', 'staff_level' => 'Coach'],
                ['name' => 'CrossFit Class', 'description' => 'Group CrossFit workout of the day.', 'price' => 25, 'duration_minutes' => 60, 'is_popular' => true, 'category_tag' => 'Class', 'staff_level' => 'Coach'],
                ['name' => 'Open Gym Access', 'description' => 'Unrestricted access to gym during open hours.', 'price' => 35, 'duration_minutes' => 0, 'is_popular' => false, 'category_tag' => 'Access', 'staff_level' => 'Staff'],
                ['name' => 'Monthly Unlimited', 'description' => 'Unlimited CrossFit classes and open gym access.', 'price' => 175, 'duration_minutes' => 0, 'is_popular' => false, 'category_tag' => 'Membership', 'staff_level' => 'Coach'],
            ],

            // ── Spa & Massage ─────────────────────────────────────────────────────
            'serenity-spa-wellness' => [
                ['name' => 'Swedish Massage', 'description' => 'Relaxing full-body massage using gentle to firm pressure.', 'price' => 120, 'duration_minutes' => 60, 'is_popular' => true, 'category_tag' => 'Massage', 'staff_level' => 'Therapist'],
                ['name' => 'Deep Tissue Massage', 'description' => 'Intense massage targeting deep muscle layers.', 'price' => 140, 'duration_minutes' => 60, 'is_popular' => true, 'category_tag' => 'Massage', 'staff_level' => 'Therapist'],
                ['name' => 'Signature Facial', 'description' => 'Custom facial with cleansing, exfoliation, and hydration.', 'price' => 95, 'duration_minutes' => 60, 'is_popular' => false, 'category_tag' => 'Facial', 'staff_level' => 'Esthetician'],
                ['name' => 'Couples Massage', 'description' => 'Side-by-side massage in private suite. Includes champagne.', 'price' => 280, 'duration_minutes' => 90, 'is_popular' => true, 'category_tag' => 'Couples', 'staff_level' => 'Therapist'],
                ['name' => 'Full Day Spa Package', 'description' => 'Complete day: massage, facial, body treatment, and lunch.', 'price' => 350, 'duration_minutes' => 240, 'is_popular' => false, 'category_tag' => 'Package', 'staff_level' => 'Therapist'],
            ],

            'healing-hands-massage' => [
                ['name' => 'Therapeutic Massage', 'description' => 'Targeted massage for pain relief and injury recovery.', 'price' => 100, 'duration_minutes' => 60, 'is_popular' => true, 'category_tag' => 'Massage', 'staff_level' => 'Therapist'],
                ['name' => 'Sports Massage', 'description' => 'Pre or post-event massage for athletes.', 'price' => 110, 'duration_minutes' => 60, 'is_popular' => true, 'category_tag' => 'Massage', 'staff_level' => 'Therapist'],
                ['name' => 'Thai Massage', 'description' => 'Traditional Thai massage with stretching and pressure points.', 'price' => 120, 'duration_minutes' => 90, 'is_popular' => false, 'category_tag' => 'Massage', 'staff_level' => 'Therapist'],
                ['name' => '90-Minute Deep Tissue', 'description' => 'Extended deep tissue session for chronic tension.', 'price' => 180, 'duration_minutes' => 90, 'is_popular' => false, 'category_tag' => 'Massage', 'staff_level' => 'Therapist'],
            ],

            'korean-spa-journey' => [
                ['name' => 'Spa Day Pass', 'description' => 'Full access to heat rooms, jacuzzi, and relaxation lounge.', 'price' => 45, 'duration_minutes' => 180, 'is_popular' => true, 'category_tag' => 'Pass', 'staff_level' => 'Attendant'],
                ['name' => 'Body Scrub', 'description' => 'Traditional Korean body scrub with exfoliation.', 'price' => 50, 'duration_minutes' => 30, 'is_popular' => true, 'category_tag' => 'Scrub', 'staff_level' => 'Therapist'],
                ['name' => 'Scrub & Massage Combo', 'description' => 'Body scrub followed by Swedish massage.', 'price' => 95, 'duration_minutes' => 90, 'is_popular' => true, 'category_tag' => 'Combo', 'staff_level' => 'Therapist'],
                ['name' => 'VIP Spa Suite', 'description' => 'Private suite with jacuzzi, steam shower, and massage.', 'price' => 200, 'duration_minutes' => 120, 'is_popular' => false, 'category_tag' => 'VIP', 'staff_level' => 'Therapist'],
            ],

            // ── Beauty Salons ─────────────────────────────────────────────────────
            'glamour-hair-studio' => [
                ['name' => 'Haircut & Style', 'description' => 'Professional haircut with wash and styling.', 'price' => 65, 'duration_minutes' => 60, 'is_popular' => true, 'category_tag' => 'Cut', 'staff_level' => 'Stylist'],
                ['name' => 'Women\'s Haircut', 'description' => 'Precision cut tailored to your face shape and lifestyle.', 'price' => 55, 'duration_minutes' => 45, 'is_popular' => true, 'category_tag' => 'Cut', 'staff_level' => 'Stylist'],
                ['name' => 'Full Color', 'description' => 'Single-process hair color from root to tip.', 'price' => 120, 'duration_minutes' => 120, 'is_popular' => false, 'category_tag' => 'Color', 'staff_level' => 'Colorist'],
                ['name' => 'Balayage', 'description' => 'Hand-painted highlights for natural-looking dimension.', 'price' => 200, 'duration_minutes' => 180, 'is_popular' => true, 'category_tag' => 'Color', 'staff_level' => 'Colorist'],
                ['name' => 'Keratin Treatment', 'description' => 'Smoothing treatment for frizzy hair. Lasts 3-4 months.', 'price' => 250, 'duration_minutes' => 180, 'is_popular' => false, 'category_tag' => 'Treatment', 'staff_level' => 'Stylist'],
                ['name' => 'Blowout', 'description' => 'Professional blow dry and styling.', 'price' => 45, 'duration_minutes' => 45, 'is_popular' => false, 'category_tag' => 'Style', 'staff_level' => 'Stylist'],
            ],

            'blush-beauty-bar' => [
                ['name' => 'Makeup Application', 'description' => 'Professional makeup for any occasion. Includes trial.', 'price' => 85, 'duration_minutes' => 60, 'is_popular' => true, 'category_tag' => 'Makeup', 'staff_level' => 'Makeup Artist'],
                ['name' => 'Event Makeup', 'description' => 'Special occasion makeup with long-lasting products.', 'price' => 100, 'duration_minutes' => 60, 'is_popular' => true, 'category_tag' => 'Makeup', 'staff_level' => 'Makeup Artist'],
                ['name' => 'Brow Shaping', 'description' => 'Professional brow shaping and tinting.', 'price' => 35, 'duration_minutes' => 30, 'is_popular' => false, 'category_tag' => 'Brows', 'staff_level' => 'Beautician'],
                ['name' => 'Lash Fill', 'description' => 'Classic lash extensions fill. Must have extensions already.', 'price' => 65, 'duration_minutes' => 45, 'is_popular' => false, 'category_tag' => 'Lashes', 'staff_level' => 'Technician'],
                ['name' => 'Full Set Lashes', 'description' => 'Classic lash extensions application.', 'price' => 150, 'duration_minutes' => 120, 'is_popular' => false, 'category_tag' => 'Lashes', 'staff_level' => 'Technician'],
            ],

            'affordable-cuts-color' => [
                ['name' => 'Basic Haircut', 'description' => 'Simple haircut and styling. No-frills service.', 'price' => 25, 'duration_minutes' => 30, 'is_popular' => true, 'category_tag' => 'Cut', 'staff_level' => 'Stylist'],
                ['name' => 'Wash & Style', 'description' => 'Shampoo and blow dry styling.', 'price' => 30, 'duration_minutes' => 40, 'is_popular' => true, 'category_tag' => 'Style', 'staff_level' => 'Stylist'],
                ['name' => 'Root Touch-up', 'description' => 'Single-process color for root regrowth.', 'price' => 50, 'duration_minutes' => 60, 'is_popular' => false, 'category_tag' => 'Color', 'staff_level' => 'Stylist'],
                ['name' => 'Highlights (Partial)', 'description' => 'Partial highlights for face-framing dimension.', 'price' => 75, 'duration_minutes' => 90, 'is_popular' => false, 'category_tag' => 'Color', 'staff_level' => 'Stylist'],
            ],
        ];

        foreach ($offeringsBySlug as $slug => $offerings) {
            $service = Service::where('slug', $slug)->first();
            if (! $service) {
                continue;
            }
            foreach ($offerings as $data) {
                ServiceOffering::create(array_merge($data, ['service_id' => $service->id]));
            }
        }
    }
}

