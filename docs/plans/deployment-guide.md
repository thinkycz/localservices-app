# Production Deployment Guide

## Overview
This guide provides step-by-step instructions for deploying the Local Services App to production.

## Prerequisites
- PHP 8.3+
- Node.js 18+
- MySQL 8.0+ or PostgreSQL 13+
- Redis server
- Web server (Nginx or Apache)
- SSL certificate

## Environment Setup

### 1. Clone Repository
```bash
git clone <repository-url>
cd localservices-app
```

### 2. Install Dependencies
```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure .env Variables
```env
APP_NAME="Local Services"
APP_ENV=production
APP_KEY=base64:generated_key
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=localservices
DB_USERNAME=username
DB_PASSWORD=password

# Cache Configuration
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=hello@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Queue Configuration
QUEUE_CONNECTION=redis

# File Storage
FILESYSTEM_DISK=public
```

## Database Setup

### 1. Create Database
```sql
CREATE DATABASE localservices CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Run Migrations
```bash
php artisan migrate --force
```

### 3. Seed Database (Optional)
```bash
php artisan db:seed --class=DatabaseSeeder --force
```

## Application Optimization

### 1. Cache Configuration
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Storage Link
```bash
php artisan storage:link
```

### 3. Set Permissions
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

## Web Server Configuration

### Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /var/www/localservices-app/public;
    index index.php;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### Apache Configuration
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/localservices-app/public
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key

    <Directory /var/www/localservices-app>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## SSL Configuration

### 1. Obtain SSL Certificate
```bash
# Using Let's Encrypt
sudo certbot --nginx -d yourdomain.com
```

### 2. Auto-renewal Setup
```bash
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

## Security Hardening

### 1. Firewall Configuration
```bash
# UFW example
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### 2. Security Headers
Add to `app/Http/Middleware/TrustProxies.php`:
```php
protected $headers = Request::HEADER_X_FORWARDED_ALL;
```

### 3. File Permissions
```bash
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 600 .env
```

## Monitoring Setup

### 1. Error Tracking
```bash
# Install Sentry (optional)
composer require sentry/sentry-laravel
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"
```

### 2. Log Rotation
```bash
sudo nano /etc/logrotate.d/localservices-app
```

### 3. Uptime Monitoring
Set up external monitoring service (UptimeRobot, Pingdom, etc.)

## Performance Optimization

### 1. Redis Configuration
```bash
# /etc/redis/redis.conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

### 2. PHP OPcache
```ini
; /etc/php/8.3/cli/php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
```

### 3. Database Optimization
```sql
-- Add indexes for better performance
CREATE INDEX idx_shops_category_id ON shops(category_id);
CREATE INDEX idx_bookings_user_id ON bookings(user_id);
CREATE INDEX idx_bookings_shop_id ON bookings(shop_id);
```

## Backup Strategy

### 1. Database Backup
```bash
# Daily backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u username -p localservices > backup_$DATE.sql
```

### 2. File Backup
```bash
# Backup storage directory
tar -czf storage_backup_$DATE.tar.gz storage/
```

### 3. Automated Backups
```bash
# Add to crontab
0 2 * * * /path/to/backup_script.sh
```

## Deployment Process

### 1. Zero-Downtime Deployment
```bash
# Using Laravel Envoyer or custom script
git pull origin main
composer install --optimize-autoloader --no-dev
npm install && npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

### 2. Health Checks
```bash
# Application health check
curl -f https://yourdomain.com/health || exit 1
```

## Troubleshooting

### Common Issues
1. **500 Internal Server Error**
   - Check Laravel logs: `tail -f storage/logs/laravel.log`
   - Verify file permissions
   - Check .env configuration

2. **Database Connection Issues**
   - Verify database credentials
   - Check database server status
   - Test connection manually

3. **Asset Loading Issues**
   - Run `php artisan storage:link`
   - Check web server configuration
   - Verify asset permissions

### Performance Issues
1. **Slow Page Loads**
   - Enable caching
   - Optimize database queries
   - Use CDN for assets

2. **High Memory Usage**
   - Check for memory leaks
   - Optimize PHP configuration
   - Monitor queue processing

## Maintenance

### Regular Tasks
1. **Weekly**
   - Check error logs
   - Monitor performance metrics
   - Update dependencies

2. **Monthly**
   - Security updates
   - Database optimization
   - Backup verification

3. **Quarterly**
   - SSL certificate renewal
   - Security audit
   - Performance review

## Emergency Procedures

### 1. Site Down
1. Check server status
2. Review error logs
3. Restart services if needed
4. Restore from backup if necessary

### 2. Security Incident
1. Identify and contain breach
2. Assess damage
3. Patch vulnerabilities
4. Notify affected users
5. Implement additional security measures

---
*Last updated: 2026-03-20*
