#!/bin/sh

set -e

echo "Starting PHP-FPM container..."

DB_HOST="${DB_HOST:-postgresql}"
DB_PORT="${DB_PORT:-5432}"
DB_NAME="${DB_NAME:-loans}"
DB_USER="${DB_USER:-user}"
DB_PASSWORD="${DB_PASSWORD:-password}"

if [ -t 1 ]; then
    RED='\033[0;31m'
    GREEN='\033[0;32m'
    YELLOW='\033[1;33m'
    NC='\033[0m'
else
    RED=''
    GREEN=''
    YELLOW=''
    NC=''
fi

print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_status "Waiting for PostgreSQL to be ready..."
timeout=120
counter=0

until nc -z $DB_HOST $DB_PORT; do
  counter=$((counter + 1))
  if [ $counter -gt $timeout ]; then
    print_error "PostgreSQL is not ready after ${timeout} seconds"
    print_error "Database connection details: host=$DB_HOST, port=$DB_PORT"
    exit 1
  fi
  if [ $((counter % 10)) -eq 0 ]; then
    echo "PostgreSQL not ready yet... waiting (${counter}/${timeout})"
  fi
  sleep 1
done

print_status "PostgreSQL is ready!"

sleep 3

print_status "Testing database connection..."
connection_attempts=0
max_connection_attempts=5

while [ $connection_attempts -lt $max_connection_attempts ]; do
    if php -r "
    try {
        \$pdo = new PDO('pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME', '$DB_USER', '$DB_PASSWORD');
        echo 'Database connection successful!' . PHP_EOL;
        exit(0);
    } catch (Exception \$e) {
        echo 'Database connection failed: ' . \$e->getMessage() . PHP_EOL;
        exit(1);
    }
    "; then
        break
    else
        connection_attempts=$((connection_attempts + 1))
        if [ $connection_attempts -lt $max_connection_attempts ]; then
            print_warning "Database connection attempt $connection_attempts failed, retrying..."
            sleep 2
        else
            print_error "Database connection failed after $max_connection_attempts attempts"
            exit 1
        fi
    fi
done

if [ ! -d "/var/www/html/vendor" ]; then
    print_status "Installing composer dependencies..."
    if composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist; then
        print_status "Composer dependencies installed successfully."
    else
        print_warning "Failed to install composer dependencies, skipping migrations"
        print_status "Starting PHP-FPM without migrations..."
        exec "$@"
    fi
fi

print_status "Running database migrations..."
migration_attempts=0
max_migration_attempts=3

while [ $migration_attempts -lt $max_migration_attempts ]; do
    if php yii migrate --interactive=0; then
        print_status "Database migrations completed successfully."
        break
    else
        migration_attempts=$((migration_attempts + 1))
        if [ $migration_attempts -lt $max_migration_attempts ]; then
            print_warning "Migration attempt $migration_attempts failed, retrying..."
            sleep 3
        else
            print_warning "Database migrations failed after $max_migration_attempts attempts, continuing without migrations"
            break
        fi
    fi
done

print_status "Verifying application setup..."
if [ ! -d "/var/www/html/runtime" ]; then
    print_warning "Runtime directory not found, creating..."
    mkdir -p /var/www/html/runtime/logs
    chown -R www-data:www-data /var/www/html/runtime
fi

if [ ! -d "/var/www/html/web/assets" ]; then
    print_warning "Assets directory not found, creating..."
    mkdir -p /var/www/html/web/assets
    chown -R www-data:www-data /var/www/html/web/assets
fi

if [ "$YII_ENV" = "dev" ]; then
    print_status "Clearing application cache (development mode)..."
    php yii cache/flush-all || print_warning "Cache flush failed, continuing..."
fi

print_status "Application setup completed successfully."
print_status "Starting PHP-FPM..."

exec "$@"