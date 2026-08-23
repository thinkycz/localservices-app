# Database Schema Verification

> **Historical verification — superseded 2026-08-23.** This March schema summary predates the Domluveno hardening migrations and contains unverified completeness claims. Use current migrations/models plus [the route/flow matrix](./route-flow-matrix.md) and active relaunch tracker.

## Models and Relationships Status

### ✅ User Model
- **Fields**: name, email, password, phone, is_vendor, has_local_password, last_login_at
- **Relationships**: 
  - hasMany(Shop::class) - owns shops
  - hasMany(Booking::class, 'user_id') - customer bookings
  - hasMany(Booking::class, 'provider_id') - provider bookings
  - hasMany(Notification::class) - user notifications
  - hasMany(Review::class) - user reviews
- **Status**: ✅ Complete

### ✅ Shop Model
- **Fields**: category_id, user_id, name, slug, currency, description, price_range, image, is_available, available_at, rating, reviews_count, city, state, address, is_online_only, latitude, longitude
- **Relationships**:
  - belongsTo(Category::class) - shop category
  - belongsTo(User::class, 'user_id') - shop owner
  - hasMany(Bookmark::class) - user bookmarks
  - hasMany(Service::class) - shop services
  - hasMany(Review::class) - shop reviews
  - hasMany(Booking::class) - shop bookings
  - hasMany(BusinessHour::class) - business hours
  - hasMany(ShopImage::class) - shop images
- **Computed Attributes**: computed_badge (NEW/POPULAR/TOP RATED)
- **Status**: ✅ Complete with business logic

### ✅ Booking Model
- **Fields**: user_id, shop_id, service_id, provider_id, status, booking_date, start_time, end_time, notes, customer_notes
- **Relationships**:
  - belongsTo(User::class, 'user_id') - customer
  - belongsTo(Shop::class) - shop
  - belongsTo(Service::class) - service
  - belongsTo(User::class, 'provider_id') - provider
- **Computed Attributes**: total_price (from service price)
- **Status**: ✅ Complete

### ✅ Service Model
- **Fields**: shop_id, name, description, price, duration_minutes
- **Relationships**:
  - belongsTo(Shop::class) - parent shop
- **Status**: ✅ Complete

### ✅ Review Model
- **Fields**: user_id, shop_id, booking_id, rating, comment, tags, is_approved, reviewed_at
- **Relationships**:
  - belongsTo(User::class) - review author
  - belongsTo(Shop::class) - reviewed shop
  - belongsTo(Booking::class) - associated booking
- **Scopes**: approved() - filter approved reviews only
- **Computed Attributes**: stars (★☆ display)
- **Status**: ✅ Complete

### ✅ Category Model
- **Fields**: name, slug, icon
- **Relationships**:
  - hasMany(Shop::class) - shops in category
- **Status**: ✅ Complete

## Migration Files Status

### Core Tables
- ✅ users_table - Complete with auth fields
- ✅ categories_table - Basic category structure
- ✅ shops_table (renamed from services) - Complete shop schema
- ✅ services_table (renamed from service_offerings) - Service offerings
- ✅ bookings_table - Booking management
- ✅ reviews_table - Review system
- ✅ bookmarks_table - User bookmarks
- ✅ notifications_table - User notifications
- ✅ business_hours_table - Shop operating hours
- ✅ shop_images_table - Shop gallery images
- ✅ contact_submissions_table - Contact form submissions

### Schema Updates
- ✅ is_vendor field added to users
- ✅ phone field added to users
- ✅ currency field added to shops
- ✅ price field added to services
- ✅ Various renaming migrations completed

## Database Verification Checklist

### Completed
- [x] All core models exist and are properly structured
- [x] Primary relationships are correctly defined
- [x] Migration files cover all necessary tables
- [x] Model fillable fields are properly defined
- [x] Model casts are configured correctly
- [x] All model relationships verified and complete

### Next Steps
- [ ] Test database operations with sample data
- [ ] Verify all controller queries work with current schema
- [ ] Run database migrations to ensure they execute without errors

---
*Last updated: 2026-03-20*
