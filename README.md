# HomeBio - Real Estate WordPress Theme

A multilingual WordPress theme for real estate property browsing with Google OAuth authentication.

## Requirements

- WordPress 6.0+
- PHP 8.1+
- MySQL 8.0+ or MariaDB

## Local Development Setup

### Using Local by Flywheel

1. Download and install [Local by Flywheel](https://localwp.com/)
2. Create a new site named "homebio"
3. Symlink the theme to your Local site:

```bash
# macOS/Linux
ln -s /path/to/this/repo/wp-content/themes/homebio-theme ~/Local\ Sites/homebio/app/public/wp-content/themes/homebio-theme

# Example:
ln -s ~/WebstormProjects/homebio/wp-content/themes/homebio-theme ~/Local\ Sites/homebio/app/public/wp-content/themes/homebio-theme
```

4. In WordPress admin, go to Appearance > Themes and activate "HomeBio"

## Theme Features

- Custom "Properties" post type with meta fields
- Favorites system (save properties to user profile)
- User cabinet with profile management
- Multilingual ready (EN, BG, RU, UA)
- Google OAuth integration ready
- Responsive design

## Recommended Plugins

Install these plugins via WordPress admin:

- **Advanced Custom Fields (ACF)** - Enhanced property fields
- **Nextend Social Login** - Google OAuth
- **Polylang** or **WPML** - Multilingual support
- **Wordfence Security** - Security
- **LiteSpeed Cache** - Performance

## File Structure

```
wp-content/themes/homebio-theme/
├── style.css              # Theme styles
├── functions.php          # Theme setup
├── index.php              # Main template
├── header.php             # Header template
├── footer.php             # Footer template
├── front-page.php         # Homepage template
├── template-parts/
│   └── property-card.php  # Property card component
├── inc/
│   ├── custom-post-types.php  # Property CPT
│   ├── favorites.php          # Favorites functionality
│   └── user-cabinet.php       # User cabinet
├── assets/
│   ├── css/
│   ├── js/
│   │   └── main.js        # Theme JavaScript
│   └── images/
└── languages/             # Translation files
```

## WordPress Configuration

After installation, configure:

1. **Permalinks**: Settings > Permalinks > "Post name"
2. **Timezone**: Settings > General > Timezone
3. **Reading**: Settings > Reading > Static page (set homepage)

## Deployment

### Initial Setup

Before deploying, update the configuration in `scripts/deploy.sh`:
- `STAGING_HOST` - SSH user and hostname for staging
- `STAGING_PATH` - Path to theme directory on staging
- `PRODUCTION_HOST` - SSH user and hostname for production
- `PRODUCTION_PATH` - Path to theme directory on production

### Deploy Commands

```bash
# Deploy to staging
make deploy-staging
# or
./scripts/deploy.sh staging

# Deploy to production (requires confirmation)
make deploy-production
# or
./scripts/deploy.sh production
```

### Workflow

1. **Local Development** - Work on your Local by Flywheel site
2. **Test Locally** - Verify changes work correctly
3. **Commit** - Commit your changes to git
4. **Deploy Staging** - Test on staging server
5. **Deploy Production** - Push to live site
