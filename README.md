# HomeBio - Real Estate WordPress Theme

A modern, multilingual WordPress theme for real estate property browsing with user authentication, favorites system, and notifications.

## Features

- **Property Listings** - Custom post type with price, area, bedrooms, bathrooms, location
- **User Cabinet** - Profile settings, security, favorites management, notifications
- **Favorites System** - Save properties to your profile
- **Google OAuth** - One-click sign in with Google
- **Multilingual** - English, Bulgarian, Russian, Ukrainian
- **Notifications** - Get notified when favorite properties are updated
- **Responsive Design** - Mobile-first approach
- **Auto Deployment** - GitHub Actions deploys to hosting on push

## Requirements

- WordPress 6.0+
- PHP 8.1+
- MySQL 8.0+ or MariaDB

## Installation

### Option 1: Upload to WordPress

1. Download the theme from `wp-content/themes/homebio-theme/`
2. Go to WordPress Admin → Appearance → Themes → Add New → Upload
3. Activate the theme

### Option 2: Local Development with Local by Flywheel

1. Download [Local by Flywheel](https://localwp.com/)
2. Create a new site named "homebio"
3. Symlink the theme:

```bash
ln -s ~/path/to/homebio/wp-content/themes/homebio-theme ~/Local\ Sites/homebio/app/public/wp-content/themes/homebio-theme
```

4. Activate the theme in WordPress admin

## Recommended Plugins

- **Nextend Social Login** - Google OAuth authentication
- **Polylang** - Multilingual support
- **Wordfence Security** - Security hardening
- **LiteSpeed Cache** - Performance optimization

## File Structure

```
wp-content/themes/homebio-theme/
├── style.css                 # Main styles
├── functions.php             # Theme setup & configuration
├── header.php                # Site header
├── footer.php                # Site footer with navigation
├── front-page.php            # Homepage
├── index.php                 # Default template
├── page.php                  # Page template
├── page-login.php            # Login page
├── page-register.php         # Registration page
├── page-user-cabinet.php     # User profile/cabinet
├── single-property.php       # Single property view
├── archive-property.php      # Property listings
├── template-parts/
│   └── property-card.php     # Property card component
├── inc/
│   ├── custom-post-types.php # Property post type
│   ├── favorites.php         # Favorites functionality
│   ├── user-cabinet.php      # Cabinet AJAX handlers
│   ├── notifications.php     # Property update notifications
│   ├── oauth-integration.php # Google OAuth helpers
│   ├── polylang-integration.php # Language switcher
│   └── ultimate-member-integration.php
├── assets/
│   └── js/
│       └── main.js           # Frontend JavaScript
└── languages/
    ├── bg_BG.po/.mo          # Bulgarian
    ├── ru_RU.po/.mo          # Russian
    ├── uk.po/.mo             # Ukrainian
    └── homebio.pot           # Translation template
```

## Configuration

### WordPress Settings

1. **Permalinks**: Settings → Permalinks → "Post name"
2. **Homepage**: Settings → Reading → Static page
3. **Create Pages**: Login, Register, User Cabinet (assign respective templates)

### Google OAuth Setup

1. Install "Nextend Social Login" plugin
2. Create Google OAuth credentials at [Google Cloud Console](https://console.cloud.google.com/)
3. Configure the plugin with Client ID and Secret

## Deployment

Automatic deployment via GitHub Actions on push to `main` branch.

### Setup GitHub Secrets

Go to Repository → Settings → Secrets → Actions and add:

| Secret | Description |
|--------|-------------|
| `FTP_SERVER` | FTP hostname |
| `FTP_USERNAME` | FTP username |
| `FTP_PASSWORD` | FTP password |
| `FTP_SERVER_DIR` | Remote path (e.g., `/public_html/wp-content/themes/homebio-theme/`) |

### Manual Deployment

You can also trigger deployment manually from GitHub → Actions → "Deploy to Hostia.net" → Run workflow.

## Development

### Adding Translations

1. Edit the `.po` files in `languages/`
2. Compile to `.mo` using:
   ```bash
   msgfmt -o languages/uk.mo languages/uk.po
   ```
   Or use the included `compile-mo.php` script.

### Property Meta Fields

Properties use these meta keys:
- `_property_price` - Price in EUR
- `_property_area` - Area in m²
- `_property_bedrooms` - Number of bedrooms
- `_property_bathrooms` - Number of bathrooms
- `_property_address` - Full address
- `_property_location` - City/region

## License

Private project - All rights reserved.
