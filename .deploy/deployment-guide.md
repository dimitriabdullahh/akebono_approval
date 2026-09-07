# Deployment Guide - Akebono Approval System

## Prerequisites

- PHP 8.1+
- PostgreSQL 13+
- Composer
- Node.js & npm (optional, for frontend assets)
- Redis (recommended for queue)
- Supervisor (for queue workers)

## Development Environment Setup

```bash
# Clone repository
git clone https://github.com/yourusername/akebono-approval.git
cd akebono-approval

# Install dependencies
composer install

# Setup environment file
cp .env.example .env

# Generate APP_KEY
php artisan key:generate

# Run migrations
php artisan migrate

# Seed initial data (optional)
php artisan db:seed

# Create storage symlink
php artisan storage:link

# Start development server
php artisan serve
```

## Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test class
php artisan test tests/Feature/AuthenticationTest.php

# Run in parallel
php artisan test --parallel
```

## Production Deployment

### 1. Server Setup

```bash
# Create application directory
sudo mkdir -p /var/www/akebono-approval
sudo chown -R www-data:www-data /var/www/akebono-approval

# Install PHP extensions
sudo apt-get install -y php8.1-pgsql php8.1-redis php8.1-gd php8.1-mbstring php8.1-xml

# Install PostgreSQL
sudo apt-get install -y postgresql postgresql-contrib

# Install Redis
sudo apt-get install -y redis-server

# Install Supervisor
sudo apt-get install -y supervisor
```

### 2. Application Deployment

```bash
# Deploy code (from CI/CD or manual)
cd /var/www/akebono-approval

# Install dependencies
composer install --optimize-autoloader --no-dev

# Setup environment
cp .env.example .env
# Edit .env with production values

# Generate key
php artisan key:generate

# Clear caches
php artisan config:clear
php artisan cache:clear

# Run migrations
php artisan migrate --force

# Create storage symlink
php artisan storage:link

# Set permissions
chmod -R 775 storage bootstrap/cache
```

### 3. Queue Worker Setup

Create `/etc/supervisor/conf.d/akebono-queue.conf`:

```ini
[program:akebono-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/akebono-approval/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/akebono-queue.log
user=www-data
```

Then run:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start akebono-queue-worker:*
```

### 4. Scheduler Setup

Add to crontab (`crontab -e`):

```
* * * * * cd /var/www/akebono-approval && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Web Server Configuration (Nginx)

Create `/etc/nginx/sites-available/akebono-approval`:

```nginx
server {
    listen 80;
    server_name akebono-approval.example.com;
    root /var/www/akebono-approval/public;
    index index.php;

    # SSL redirect (if using Let's Encrypt)
    # return 301 https://$server_name$request_uri;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Prevent access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ /config {
        deny all;
    }

    location ~ /storage {
        deny all;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/akebono-approval /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 6. SSL Configuration (Let's Encrypt)

```bash
sudo apt-get install -y certbot python3-certbot-nginx
sudo certbot certonly --nginx -d akebono-approval.example.com
```

Update nginx config to use SSL and redirect HTTP to HTTPS.

### 7. Environment Configuration

Edit `/var/www/akebono-approval/.env`:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://akebono-approval.example.com

DB_HOST=localhost
DB_DATABASE=akebono_approval
DB_USERNAME=akebono
DB_PASSWORD=<strong-password>

QUEUE_CONNECTION=redis
REDIS_HOST=localhost

MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=<mailtrap-username>
MAIL_PASSWORD=<mailtrap-password>
```

### 8. Backup Strategy

Create daily backup script `/usr/local/bin/akebono-backup.sh`:

```bash
#!/bin/bash

BACKUP_DIR="/backups/akebono"
DATE=$(date +%Y%m%d_%H%M%S)

# Database backup
pg_dump akebono_approval | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/akebono-approval/storage/app

# Keep only last 30 days
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

Add to crontab:

```
0 2 * * * /usr/local/bin/akebono-backup.sh
```

## Monitoring & Logging

```bash
# Monitor queue worker
sudo supervisorctl tail -f akebono-queue-worker

# Check logs
tail -f /var/log/akebono-queue.log
tail -f /var/www/akebono-approval/storage/logs/laravel.log

# Monitor disk usage
df -h
du -sh /var/www/akebono-approval/storage

# Check PHP-FPM status
systemctl status php8.1-fpm
```

## Troubleshooting

### Queue not processing jobs

```bash
# Check if workers are running
sudo supervisorctl status
sudo supervisorctl restart akebono-queue-worker:*

# Check Redis connection
redis-cli ping
```

### Storage permission issues

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Database connection issues

```bash
# Test connection
psql -h localhost -U akebono -d akebono_approval -c "SELECT 1"
```

### Email not sending

Check mail configuration in `.env` and review logs:

```bash
grep -i mail /var/www/akebono-approval/storage/logs/laravel.log
```

## Health Check

```bash
# Create health check endpoint (if not exists)
curl https://akebono-approval.example.com/health
```

## Rollback Procedure

```bash
# If deployment fails, revert to previous release
cd /var/www/akebono-approval

# List recent git tags/commits
git log --oneline -10

# Revert to previous version
git checkout <previous-commit>
composer install --optimize-autoloader --no-dev
php artisan cache:clear
sudo supervisorctl restart akebono-queue-worker:*
```

## Performance Optimization

### Database Query Optimization

```bash
# Create indexes
php artisan tinker
>>> \DB::statement('CREATE INDEX idx_status ON request_documents(status)');
>>> \DB::statement('CREATE INDEX idx_deadline ON request_documents(revision_deadline_at)');
```

### Cache Configuration

Update `.env`:

```
CACHE_DRIVER=redis
CACHE_TTL=3600
```

### Session Configuration

```
SESSION_DRIVER=redis
SESSION_LIFETIME=120
```

## Maintenance Mode

```bash
# Enable maintenance mode
php artisan down

# Do maintenance tasks...

# Disable maintenance mode
php artisan up
```

## Support & Contact

For issues or support, contact:
- Email: support@akebono.example.com
- Documentation: https://docs.akebono.example.com
