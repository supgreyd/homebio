# HomeBio - Implementation Documentation (Phases 1-6)

## Project Overview

| Attribute | Value |
|-----------|-------|
| Project Name | HomeBio |
| Production URL | https://investvibe.net/ |
| CMS | WordPress |
| Languages | English (default), Bulgarian, Russian, Ukrainian |
| Auth Method | Google OAuth (Nextend Social Login) |
| User Management | Custom User Cabinet |

---

## Phase 1: Development Environment Setup

### 1.1 Hosting
- **Production hosting**: Traditional WordPress hosting
- **Local development**: Local by Flywheel

### 1.2 WordPress Installation
- WordPress installed on production server
- Permalink structure: Post name
- SSL configured (HTTPS)

### 1.3 Development Environment

#### Local Setup
- Local by Flywheel installed
- Local site "homebio" created
- Theme symlinked from project directory

#### Deployment Scripts

**`scripts/deploy.sh`** - Production deployment script
```bash
# Syncs theme files to production via rsync/SSH
./scripts/deploy.sh
```

**`scripts/local-setup.sh`** - Local environment setup

**`Makefile`** - Common development tasks
```bash
make deploy      # Deploy to production
make local       # Set up local environment
make watch       # Watch for changes
```

---

## Phase 2: Theme & Core Structure

### 2.1 Theme Files Structure

```
/wp-content/themes/homebio-theme/
├── style.css                    # Main styles (2200+ lines)
├── functions.php                # Theme setup & includes
├── header.php                   # Site header
├── footer.php                   # Site footer
├── index.php                    # Default template
├── page.php                     # Page template
├── front-page.php               # Homepage template
├── single-property.php          # Single property template
├── archive-property.php         # Properties archive
├── page-login.php               # Custom login page
├── page-register.php            # Custom registration page
├── page-user-cabinet.php        # User cabinet (Settings/Security/Favorites)
├── /inc/
│   ├── custom-post-types.php    # Property CPT registration
│   ├── favorites.php            # Favorites functionality
│   ├── user-cabinet.php         # Cabinet AJAX handlers
│   ├── oauth-integration.php    # Google OAuth integration
│   └── ultimate-member-integration.php  # UM compatibility
├── /template-parts/
│   └── property-card.php        # Property card component
└── /assets/
    └── /js/
        └── main.js              # Frontend JavaScript
```

### 2.2 Theme Features

#### functions.php
- Theme version: `1.0.2`
- Custom image sizes: `property-card` (600x400), `property-gallery` (1200x800)
- Navigation menus: `primary`, `footer`
- Widget areas: Sidebar, Footer
- Google Fonts: Inter
- AJAX localization with nonce

#### CSS Variables (style.css)
```css
:root {
    --color-primary: #2563eb;
    --color-primary-dark: #1d4ed8;
    --color-secondary: #64748b;
    --color-success: #22c55e;
    --color-danger: #ef4444;
    --color-warning: #f59e0b;
    --font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    --border-radius: 0.5rem;
    --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
}
```

### 2.3 Custom Post Type: Properties

**File**: `inc/custom-post-types.php`

#### Property Meta Fields
| Field | Meta Key | Type |
|-------|----------|------|
| Price | `_property_price` | Number |
| Location | `_property_location` | Text |
| Area (m²) | `_property_area` | Number |
| Bedrooms | `_property_bedrooms` | Number |
| Bathrooms | `_property_bathrooms` | Number |
| Year Built | `_property_year` | Number |
| Property Type | `_property_type` | Select |

#### Property Types Taxonomy
- `property_type` - Categories for properties

### 2.4 Favorites System

**File**: `inc/favorites.php`

#### Functions
| Function | Description |
|----------|-------------|
| `homebio_get_user_favorites($user_id)` | Get user's favorite property IDs |
| `homebio_is_favorite($property_id, $user_id)` | Check if property is favorited |
| `homebio_get_favorites_count($user_id)` | Get count of favorites |

#### AJAX Actions
| Action | Handler | Description |
|--------|---------|-------------|
| `toggle_favorite` | `homebio_ajax_toggle_favorite` | Add/remove favorite |
| `remove_favorite` | `homebio_ajax_remove_favorite` | Remove from cabinet |

#### User Meta
- Key: `homebio_favorites`
- Value: Array of property IDs

---

## Phase 3: Google OAuth Integration

### 3.1 Plugin
- **Plugin**: Nextend Social Login
- **Provider**: Google

### 3.2 OAuth Integration

**File**: `inc/oauth-integration.php`

#### Features
1. **Custom Login Page Redirect**
   - `wp-login.php` redirects to `/login/`
   - Allows OAuth callback (`?loginSocial=google`)
   - Allows password reset actions

2. **User Registration Handling**
   - Sets default role: `subscriber`
   - Stores OAuth provider in user meta
   - Sends welcome email on registration

3. **Welcome Email**
   - HTML email template
   - Includes site branding
   - CTA buttons: Browse Properties, My Cabinet

4. **Redirect Handling**
   - After login: `/user-cabinet/`
   - After registration: `/user-cabinet/`
   - After logout: Homepage

#### Key Functions
| Function | Description |
|----------|-------------|
| `homebio_get_profile_url()` | Returns cabinet URL |
| `homebio_is_oauth_user($user_id)` | Check if user registered via OAuth |
| `homebio_get_oauth_provider($user_id)` | Get OAuth provider name |
| `homebio_send_welcome_email($user_id)` | Send welcome email |

#### User Meta (OAuth Users)
| Key | Value |
|-----|-------|
| `oauth_provider` | `google` |
| `oauth_registered_at` | DateTime |
| `oauth_profile_picture` | URL |

### 3.3 Custom Login Page

**File**: `page-login.php`

#### Features
- Google OAuth button (Nextend Social Login shortcode)
- Traditional WordPress login form
- "Forgot Password" link
- "Create Account" link
- Benefits section
- Terms & Privacy links
- Error message display

#### Template Slug
Create a WordPress page with slug: `login`

### 3.4 Custom Registration Page

**File**: `page-register.php`

#### Features
- Google OAuth button
- Registration form with fields:
  - First Name
  - Last Name
  - Email
  - Password (min 8 chars)
  - Confirm Password
  - Terms agreement checkbox
- Form validation (server-side)
- Success message with login link

#### Template Slug
Create a WordPress page with slug: `register`

---

## Phase 4: User Cabinet

### 4.1 Cabinet Page Template

**File**: `page-user-cabinet.php`

#### Template Slug
Create a WordPress page with slug: `user-cabinet`

#### Layout
- **Sidebar** (280px)
  - User avatar
  - User name & email
  - Navigation (Settings, Security, Favorites)
  - Logout button
- **Main Content**
  - Tab content based on `?tab=` parameter

### 4.2 Settings Tab (`?tab=settings`)

#### Form Fields
| Field | Name | Type |
|-------|------|------|
| First Name | `first_name` | text |
| Last Name | `last_name` | text |
| Email | `email` | email (readonly for OAuth) |
| Phone | `phone` | tel |
| Date of Birth | `birth_date` | date |

#### AJAX Handler
- Action: `update_settings`
- Handler: `homebio_ajax_update_settings()`

### 4.3 Security Tab (`?tab=security`)

#### For OAuth Users
- Shows "Google Account Connected" status
- No password change (managed by Google)

#### For Regular Users
- Password change form:
  - Current Password
  - New Password (min 8 chars)
  - Confirm Password

#### AJAX Handler
- Action: `change_password`
- Handler: `homebio_ajax_change_password()`

#### Delete Account
- Confirmation modal
- Action: `delete_account`
- Handler: `homebio_ajax_delete_account()`
- Deletes user and all associated data
- Redirects to homepage

### 4.4 Favorites Tab (`?tab=favorites`)

#### Features
- Grid display of favorite properties
- Property cards with:
  - Image
  - Title (linked)
  - Price
  - Location
  - Remove button (X)
- Empty state with "Browse Properties" CTA
- Animated card removal

#### AJAX Handler
- Action: `remove_favorite`
- Handler: `homebio_ajax_remove_favorite()`

### 4.5 AJAX Handlers

**File**: `inc/user-cabinet.php`

| Action | Function | Description |
|--------|----------|-------------|
| `update_settings` | `homebio_ajax_update_settings` | Save profile settings |
| `change_password` | `homebio_ajax_change_password` | Change password |
| `delete_account` | `homebio_ajax_delete_account` | Delete user account |
| `remove_favorite` | `homebio_ajax_remove_favorite` | Remove from favorites |

#### Nonce Handling
All handlers accept either:
- Form nonce (`cabinet_nonce` / `password_nonce`)
- Global nonce (`homebio_nonce`)

### 4.6 JavaScript

**File**: `assets/js/main.js`

#### Initialized Functions
| Function | Description |
|----------|-------------|
| `initMobileMenu()` | Mobile hamburger menu toggle |
| `initFavorites()` | Favorite button click handling |
| `initCabinetForms()` | Settings & password form submission |
| `initLanguageSwitcher()` | Header language selector |
| `initDeleteAccount()` | Delete account modal |
| `initRemoveFavorite()` | Remove favorite from cabinet |

#### Notification System
```javascript
showNotification(message, type)
// Types: 'success', 'error', 'warning', 'info'
```

---

## WordPress Pages Required

| Page Title | Slug | Template |
|------------|------|----------|
| Login | `login` | page-login.php (auto) |
| Register | `register` | page-register.php (auto) |
| User Cabinet | `user-cabinet` | page-user-cabinet.php (auto) |
| Terms of Service | `terms` | Default |
| Privacy Policy | `privacy` | Default |

---

## Required Plugins

| Plugin | Purpose | Status |
|--------|---------|--------|
| Nextend Social Login | Google OAuth | Required |
| Ultimate Member | User profiles (optional) | Installed but using custom cabinet |

---

## User Roles & Permissions

| Role | Can Access Cabinet | Can Favorite |
|------|-------------------|--------------|
| Administrator | Yes | Yes |
| Subscriber | Yes | Yes |
| Guest | No (redirect to login) | No (prompt to login) |

---

## Redirects Summary

| From | To | Condition |
|------|-----|-----------|
| `/wp-login.php` | `/login/` | GET request, no special params |
| `/wp-login.php?action=register` | `/register/` | Registration attempt |
| `/login/` | `/user-cabinet/` | Already logged in |
| `/register/` | `/user-cabinet/` | Already logged in |
| `/user-cabinet/` | `/login/` | Not logged in |
| After login | `/user-cabinet/` | Successful login |
| After OAuth | `/user-cabinet/` | Successful OAuth |
| After logout | `/` | Homepage |

---

## CSS Classes Reference

### Cabinet Page
| Class | Element |
|-------|---------|
| `.cabinet-page` | Main container |
| `.cabinet-layout` | Grid layout |
| `.cabinet-sidebar` | Sidebar |
| `.cabinet-nav` | Navigation |
| `.cabinet-nav-item` | Nav link |
| `.cabinet-nav-item.active` | Active nav |
| `.cabinet-content` | Main content |
| `.cabinet-form` | Forms |
| `.cabinet-section` | Content section |

### Favorites
| Class | Element |
|-------|---------|
| `.favorites-grid` | Grid container |
| `.favorite-card` | Property card |
| `.favorite-remove` | Remove button |
| `.favorites-empty` | Empty state |

### Forms
| Class | Element |
|-------|---------|
| `.form-row` | Two-column row |
| `.form-group` | Field wrapper |
| `.form-hint` | Helper text |
| `.form-message` | Success/error message |
| `.form-actions` | Button container |

### Modal
| Class | Element |
|-------|---------|
| `.modal` | Modal container |
| `.modal-overlay` | Dark backdrop |
| `.modal-content` | Modal box |
| `.modal-actions` | Button row |

---

## Troubleshooting

### Issue: Styles not loading
1. Check version in `functions.php`: `HOMEBIO_VERSION`
2. Bump version number
3. Clear browser cache (Ctrl+Shift+R)
4. Clear server cache if applicable

### Issue: AJAX not working
1. Check browser console for errors
2. Verify `homebioAjax` object exists
3. Check nonce is valid
4. Verify user is logged in (for protected actions)

### Issue: Redirects not working
1. Check `.htaccess` for conflicts
2. Verify page slugs match templates
3. Check for caching plugins interfering
4. Flush permalinks (Settings > Permalinks > Save)

### Issue: OAuth not working
1. Verify Nextend Social Login is configured
2. Check Google Console redirect URIs
3. Allow `?loginSocial=google` through redirects
4. Check SSL certificate is valid

---

## Phase 5: Main Page & Properties Display

### 5.1 Homepage Template

**File**: `front-page.php`

#### Sections
- Hero section with search/CTA
- Featured properties grid
- Benefits/features section

### 5.2 Property Cards

**File**: `template-parts/property-card.php`

#### Card Elements
| Element | Class | Description |
|---------|-------|-------------|
| Image | `.property-card__image` | 16:10 aspect ratio |
| Favorite Button | `.property-card__favorite` | Heart icon, top-right |
| Price | `.property-card__price` | Prominent blue text |
| Title | `.property-card__title` | Property name, linked |
| Location | `.property-card__location` | City/area |
| Features | `.property-card__features` | Beds, baths, area |
| CTA | `.property-card__cta` | View Details link |

### 5.3 Single Property Page

**File**: `single-property.php`

- Property gallery/images
- Full property details
- Features list
- Contact/inquiry section
- Favorite button
- Related properties

### 5.4 Properties Archive

**File**: `archive-property.php`

- Grid layout of property cards
- Filter/sort options (prepared for)
- Pagination support

---

## Phase 6: Header & Navigation

### 6.1 Header Structure

**File**: `header.php`

#### Components
| Component | Description |
|-----------|-------------|
| Site Branding | Logo or site title |
| Main Navigation | Primary menu (centered) |
| Language Switcher | EN/BG/RU/UA selector |
| User Dropdown | For logged-in users |
| Login/Register | Buttons for guests |
| Mobile Toggle | Hamburger menu button |
| Mobile Menu | Slide-in panel |

### 6.2 User Dropdown Menu

#### Structure
```html
<div class="user-dropdown">
    <button class="user-dropdown__toggle">
        <span class="user-avatar">...</span>
        <span class="user-name">...</span>
        <svg class="dropdown-arrow">...</svg>
    </button>
    <div class="user-dropdown__menu">
        <a class="user-dropdown__item">My Cabinet</a>
        <a class="user-dropdown__item">Favorites</a>
        <div class="user-dropdown__divider"></div>
        <a class="user-dropdown__item--logout">Logout</a>
    </div>
</div>
```

#### CSS Classes
| Class | Description |
|-------|-------------|
| `.user-dropdown` | Container |
| `.user-dropdown.is-open` | Open state |
| `.user-dropdown__toggle` | Button trigger |
| `.user-dropdown__menu` | Dropdown menu |
| `.user-dropdown__item` | Menu item |
| `.user-dropdown__divider` | Separator |
| `.favorites-badge` | Count badge |

### 6.3 Mobile Menu

#### Structure
- Slide-in panel from right side
- Dark overlay backdrop
- Close button in header
- Full navigation menu
- Action buttons (Login/Cabinet)

#### CSS Classes
| Class | Description |
|-------|-------------|
| `.mobile-menu` | Panel container |
| `.mobile-menu.is-open` | Open state |
| `.mobile-menu__header` | Header with close |
| `.mobile-menu__nav` | Navigation list |
| `.mobile-menu__actions` | CTA buttons |
| `.mobile-menu__overlay` | Dark backdrop |
| `.mobile-menu__overlay.is-open` | Visible state |

### 6.4 JavaScript Functions

**File**: `assets/js/main.js`

| Function | Description |
|----------|-------------|
| `initMobileMenu()` | Mobile menu open/close |
| `initUserDropdown()` | User dropdown toggle |

#### Mobile Menu Features
- Opens with hamburger click
- Closes with X button
- Closes on overlay click
- Closes on Escape key
- Body scroll lock when open

#### User Dropdown Features
- Toggle on click
- Close on outside click
- Close on Escape key
- Animated arrow rotation

### 6.5 Responsive Breakpoints

| Breakpoint | Changes |
|------------|---------|
| 1024px | Main nav hidden, mobile toggle visible, user name hidden |
| 768px | Typography scaled down, layout adjustments |

### 6.6 Language Switcher

**Function**: `homebio_language_switcher()`
**File**: `inc/user-cabinet.php`

#### Supported Languages
| Code | Label |
|------|-------|
| `en_US` | EN |
| `bg_BG` | BG |
| `ru_RU` | RU |
| `uk` | UA |

---

## Next Steps (Phases 7-10)

### Phase 7: Multilingual Setup
- WPML or Polylang installation
- String translations
- Content translations

### Phase 8: Access Control
- Protected page handling
- Role-based access
- Redirect refinements

### Phase 9: Testing & QA
- Cross-browser testing
- Responsive testing
- Performance optimization
- Security audit

### Phase 10: Deployment & Launch
- Production deployment
- Monitoring setup
- Documentation finalization
