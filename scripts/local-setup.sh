#!/bin/bash

# HomeBio Local Development Setup Script
# This script sets up the symlink for Local by Flywheel development

set -e

# Configuration
LOCAL_SITES_PATH="$HOME/Local Sites"
SITE_NAME="homebio"
PROJECT_PATH="$(cd "$(dirname "$0")/.." && pwd)"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

print_status() { echo -e "${GREEN}[INFO]${NC} $1"; }
print_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# Check if Local site exists
LOCAL_THEME_DIR="$LOCAL_SITES_PATH/$SITE_NAME/app/public/wp-content/themes"

if [ ! -d "$LOCAL_SITES_PATH/$SITE_NAME" ]; then
    print_error "Local site '$SITE_NAME' not found at $LOCAL_SITES_PATH"
    echo ""
    echo "Please create a site named '$SITE_NAME' in Local by Flywheel first:"
    echo "1. Open Local.app"
    echo "2. Click '+ Create a new site'"
    echo "3. Name it: $SITE_NAME"
    echo "4. Complete the setup wizard"
    echo "5. Run this script again"
    exit 1
fi

# Source and target paths
SOURCE_THEME="$PROJECT_PATH/wp-content/themes/homebio-theme"
TARGET_LINK="$LOCAL_THEME_DIR/homebio-theme"

# Check if symlink already exists
if [ -L "$TARGET_LINK" ]; then
    print_status "Symlink already exists at: $TARGET_LINK"
    ls -la "$TARGET_LINK"
    exit 0
fi

# Check if directory exists (not a symlink)
if [ -d "$TARGET_LINK" ]; then
    print_warning "Directory exists at: $TARGET_LINK"
    print_warning "Backing up to: ${TARGET_LINK}.backup"
    mv "$TARGET_LINK" "${TARGET_LINK}.backup"
fi

# Create symlink
print_status "Creating symlink..."
ln -s "$SOURCE_THEME" "$TARGET_LINK"

print_status "Symlink created successfully!"
echo ""
print_status "Source: $SOURCE_THEME"
print_status "Target: $TARGET_LINK"
echo ""
print_status "Next steps:"
echo "1. Start your Local site if not running"
echo "2. Go to WordPress Admin > Appearance > Themes"
echo "3. Activate 'HomeBio' theme"
