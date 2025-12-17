#!/bin/bash

# HomeBio Theme Deployment Script
# Usage: ./scripts/deploy.sh [staging|production]

set -e

# Configuration - Update these values for your hosting
STAGING_HOST="user@staging.yourserver.com"
STAGING_PATH="/var/www/staging/wp-content/themes/homebio-theme"

PRODUCTION_HOST="user@yourserver.com"
PRODUCTION_PATH="/var/www/html/wp-content/themes/homebio-theme"

# Local paths
THEME_PATH="$(dirname "$0")/../wp-content/themes/homebio-theme"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if rsync is available
if ! command -v rsync &> /dev/null; then
    print_error "rsync is required but not installed."
    exit 1
fi

# Determine target environment
ENV=${1:-staging}

case $ENV in
    staging)
        TARGET_HOST=$STAGING_HOST
        TARGET_PATH=$STAGING_PATH
        ;;
    production)
        TARGET_HOST=$PRODUCTION_HOST
        TARGET_PATH=$PRODUCTION_PATH
        ;;
    *)
        print_error "Unknown environment: $ENV"
        echo "Usage: $0 [staging|production]"
        exit 1
        ;;
esac

print_status "Deploying to $ENV environment..."
print_status "Target: $TARGET_HOST:$TARGET_PATH"

# Confirm production deployment
if [ "$ENV" = "production" ]; then
    print_warning "You are about to deploy to PRODUCTION!"
    read -p "Are you sure? (yes/no): " confirm
    if [ "$confirm" != "yes" ]; then
        print_status "Deployment cancelled."
        exit 0
    fi
fi

# Run rsync
print_status "Syncing theme files..."
rsync -avz --delete \
    --exclude '.git' \
    --exclude '.gitignore' \
    --exclude 'node_modules' \
    --exclude '.DS_Store' \
    --exclude '*.map' \
    "$THEME_PATH/" \
    "$TARGET_HOST:$TARGET_PATH/"

print_status "Deployment to $ENV complete!"

# Optional: Clear cache on remote server (uncomment if using WP-CLI)
# print_status "Clearing cache..."
# ssh $TARGET_HOST "cd $(dirname $TARGET_PATH)/.. && wp cache flush"

echo ""
print_status "Done! Remember to verify the deployment in your browser."
