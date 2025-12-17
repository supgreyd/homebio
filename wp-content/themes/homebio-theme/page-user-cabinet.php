<?php
/**
 * Template Name: User Cabinet
 *
 * Custom user profile/cabinet page with sidebar navigation
 *
 * @package HomeBio
 */

// Redirect non-logged-in users to login
if (!is_user_logged_in()) {
    $redirect_url = add_query_arg(
        'redirect_to',
        urlencode(get_permalink()),
        home_url('/login')
    );
    wp_redirect($redirect_url);
    exit;
}

$current_user = wp_get_current_user();
$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'settings';

// Get user data
$first_name = get_user_meta($current_user->ID, 'first_name', true);
$last_name = get_user_meta($current_user->ID, 'last_name', true);
$phone = get_user_meta($current_user->ID, 'phone', true);
$birth_date = get_user_meta($current_user->ID, 'birth_date', true);
$oauth_provider = get_user_meta($current_user->ID, 'oauth_provider', true);

get_header();
?>

<main id="primary" class="site-main">
    <div class="cabinet-page">
        <div class="container">
            <div class="cabinet-layout">
                <!-- Sidebar -->
                <aside class="cabinet-sidebar">
                    <div class="cabinet-user-info">
                        <?php echo homebio_get_avatar($current_user->ID, 80); ?>
                        <div class="cabinet-user-name">
                            <?php echo esc_html($first_name ?: $current_user->display_name); ?>
                        </div>
                        <div class="cabinet-user-email">
                            <?php echo esc_html($current_user->user_email); ?>
                        </div>
                    </div>

                    <nav class="cabinet-nav">
                        <a href="?tab=settings" class="cabinet-nav-item <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <?php esc_html_e('Settings', 'homebio'); ?>
                        </a>
                        <a href="?tab=security" class="cabinet-nav-item <?php echo $active_tab === 'security' ? 'active' : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <?php esc_html_e('Security', 'homebio'); ?>
                        </a>
                        <a href="?tab=favorites" class="cabinet-nav-item <?php echo $active_tab === 'favorites' ? 'active' : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                            <?php esc_html_e('Favorites', 'homebio'); ?>
                            <?php
                            $favorites_count = homebio_get_favorites_count($current_user->ID);
                            if ($favorites_count > 0) :
                            ?>
                                <span class="cabinet-nav-count"><?php echo intval($favorites_count); ?></span>
                            <?php endif; ?>
                        </a>
                    </nav>

                    <div class="cabinet-logout">
                        <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="cabinet-logout-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                            <?php esc_html_e('Logout', 'homebio'); ?>
                        </a>
                    </div>
                </aside>

                <!-- Main Content -->
                <div class="cabinet-content">
                    <?php if ($active_tab === 'settings') : ?>
                        <!-- Settings Tab -->
                        <div class="cabinet-section">
                            <h1><?php esc_html_e('Profile Settings', 'homebio'); ?></h1>
                            <p class="cabinet-section-desc"><?php esc_html_e('Manage your personal information', 'homebio'); ?></p>

                            <!-- Avatar Upload -->
                            <div class="avatar-upload-section">
                                <div class="avatar-upload">
                                    <div class="avatar-preview" id="avatar-preview">
                                        <?php echo homebio_get_avatar($current_user->ID, 120); ?>
                                    </div>
                                    <div class="avatar-upload-info">
                                        <h3><?php esc_html_e('Profile Photo', 'homebio'); ?></h3>
                                        <p><?php esc_html_e('Upload a photo to personalize your profile.', 'homebio'); ?></p>
                                        <div class="avatar-upload-actions">
                                            <label for="avatar-input" class="btn btn-secondary btn-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                    <polyline points="17 8 12 3 7 8"></polyline>
                                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                                </svg>
                                                <?php esc_html_e('Upload Photo', 'homebio'); ?>
                                            </label>
                                            <input type="file" id="avatar-input" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;">
                                            <?php
                                            $has_custom_avatar = get_user_meta($current_user->ID, 'homebio_custom_avatar_id', true);
                                            if ($has_custom_avatar) :
                                            ?>
                                                <button type="button" id="remove-avatar-btn" class="btn btn-outline btn-sm">
                                                    <?php esc_html_e('Remove', 'homebio'); ?>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        <small class="form-hint"><?php esc_html_e('JPG, PNG, GIF or WebP. Max 2MB.', 'homebio'); ?></small>
                                    </div>
                                </div>
                            </div>

                            <form id="cabinet-settings-form" class="cabinet-form">
                                <?php wp_nonce_field('homebio_cabinet_nonce', 'cabinet_nonce'); ?>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="first_name"><?php esc_html_e('First Name', 'homebio'); ?></label>
                                        <input type="text" id="first_name" name="first_name"
                                               value="<?php echo esc_attr($first_name); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name"><?php esc_html_e('Last Name', 'homebio'); ?></label>
                                        <input type="text" id="last_name" name="last_name"
                                               value="<?php echo esc_attr($last_name); ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="email"><?php esc_html_e('Email Address', 'homebio'); ?></label>
                                    <input type="email" id="email" name="email"
                                           value="<?php echo esc_attr($current_user->user_email); ?>"
                                           <?php echo $oauth_provider ? 'readonly' : ''; ?>>
                                    <?php if ($oauth_provider) : ?>
                                        <small class="form-hint"><?php esc_html_e('Email is managed by your Google account', 'homebio'); ?></small>
                                    <?php endif; ?>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="phone"><?php esc_html_e('Phone Number', 'homebio'); ?></label>
                                        <input type="tel" id="phone" name="phone"
                                               value="<?php echo esc_attr($phone); ?>"
                                               placeholder="+359 888 123 456">
                                    </div>
                                    <div class="form-group">
                                        <label for="birth_date"><?php esc_html_e('Date of Birth', 'homebio'); ?></label>
                                        <input type="date" id="birth_date" name="birth_date"
                                               value="<?php echo esc_attr($birth_date); ?>">
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <?php esc_html_e('Save Changes', 'homebio'); ?>
                                    </button>
                                    <span class="form-message"></span>
                                </div>
                            </form>
                        </div>

                    <?php elseif ($active_tab === 'security') : ?>
                        <!-- Security Tab -->
                        <div class="cabinet-section">
                            <h1><?php esc_html_e('Security', 'homebio'); ?></h1>
                            <p class="cabinet-section-desc"><?php esc_html_e('Manage your account security settings', 'homebio'); ?></p>

                            <?php if ($oauth_provider) : ?>
                                <!-- OAuth User -->
                                <div class="security-card">
                                    <div class="security-card-icon google">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                        </svg>
                                    </div>
                                    <div class="security-card-content">
                                        <h3><?php esc_html_e('Google Account', 'homebio'); ?></h3>
                                        <p><?php esc_html_e('Your account is connected to Google. Password is managed through your Google account.', 'homebio'); ?></p>
                                        <span class="security-status connected">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                            <?php esc_html_e('Connected', 'homebio'); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php else : ?>
                                <!-- Regular User - Password Change -->
                                <div class="security-card">
                                    <h3><?php esc_html_e('Change Password', 'homebio'); ?></h3>
                                    <form id="cabinet-password-form" class="cabinet-form">
                                        <?php wp_nonce_field('homebio_password_nonce', 'password_nonce'); ?>

                                        <div class="form-group">
                                            <label for="current_password"><?php esc_html_e('Current Password', 'homebio'); ?></label>
                                            <input type="password" id="current_password" name="current_password" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="new_password"><?php esc_html_e('New Password', 'homebio'); ?></label>
                                            <input type="password" id="new_password" name="new_password" minlength="8" required>
                                            <small class="form-hint"><?php esc_html_e('Minimum 8 characters', 'homebio'); ?></small>
                                        </div>

                                        <div class="form-group">
                                            <label for="confirm_password"><?php esc_html_e('Confirm New Password', 'homebio'); ?></label>
                                            <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                                        </div>

                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-primary">
                                                <?php esc_html_e('Update Password', 'homebio'); ?>
                                            </button>
                                            <span class="form-message"></span>
                                        </div>
                                    </form>
                                </div>
                            <?php endif; ?>

                            <!-- Delete Account -->
                            <div class="security-card danger">
                                <h3><?php esc_html_e('Delete Account', 'homebio'); ?></h3>
                                <p><?php esc_html_e('Once you delete your account, all your data will be permanently removed. This action cannot be undone.', 'homebio'); ?></p>
                                <button type="button" class="btn btn-danger" id="delete-account-btn">
                                    <?php esc_html_e('Delete My Account', 'homebio'); ?>
                                </button>
                            </div>
                        </div>

                    <?php elseif ($active_tab === 'favorites') : ?>
                        <!-- Favorites Tab -->
                        <div class="cabinet-section">
                            <h1><?php esc_html_e('My Favorites', 'homebio'); ?></h1>
                            <p class="cabinet-section-desc"><?php esc_html_e('Properties you have saved', 'homebio'); ?></p>

                            <?php
                            $favorites = homebio_get_user_favorites($current_user->ID);

                            if (!empty($favorites)) :
                                $args = [
                                    'post_type'      => 'property',
                                    'post__in'       => $favorites,
                                    'posts_per_page' => -1,
                                    'orderby'        => 'post__in',
                                ];
                                $favorites_query = new WP_Query($args);

                                if ($favorites_query->have_posts()) :
                            ?>
                                <div class="favorites-grid">
                                    <?php while ($favorites_query->have_posts()) : $favorites_query->the_post(); ?>
                                        <div class="favorite-card" data-property-id="<?php the_ID(); ?>">
                                            <div class="favorite-card-image">
                                                <?php if (has_post_thumbnail()) : ?>
                                                    <a href="<?php the_permalink(); ?>">
                                                        <?php the_post_thumbnail('medium_large'); ?>
                                                    </a>
                                                <?php else : ?>
                                                    <a href="<?php the_permalink(); ?>" class="favorite-card-placeholder">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                            <polyline points="21 15 16 10 5 21"></polyline>
                                                        </svg>
                                                    </a>
                                                <?php endif; ?>
                                                <button type="button" class="favorite-remove" data-property-id="<?php the_ID(); ?>" title="<?php esc_attr_e('Remove from favorites', 'homebio'); ?>">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="favorite-card-content">
                                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                                <?php
                                                $price = get_post_meta(get_the_ID(), '_property_price', true);
                                                $location = get_post_meta(get_the_ID(), '_property_location', true);
                                                ?>
                                                <?php if ($price) : ?>
                                                    <div class="favorite-card-price">
                                                        <?php echo esc_html(number_format($price)); ?> €
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($location) : ?>
                                                    <div class="favorite-card-location">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                                            <circle cx="12" cy="10" r="3"></circle>
                                                        </svg>
                                                        <?php echo esc_html($location); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                                <?php wp_reset_postdata(); ?>
                            <?php
                                endif;
                            else :
                            ?>
                                <div class="favorites-empty">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                    </svg>
                                    <h3><?php esc_html_e('No favorites yet', 'homebio'); ?></h3>
                                    <p><?php esc_html_e('Start browsing properties and save your favorites here.', 'homebio'); ?></p>
                                    <a href="<?php echo esc_url(get_post_type_archive_link('property')); ?>" class="btn btn-primary">
                                        <?php esc_html_e('Browse Properties', 'homebio'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div id="delete-account-modal" class="modal" style="display: none;">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <h3><?php esc_html_e('Delete Account', 'homebio'); ?></h3>
            <p><?php esc_html_e('Are you sure you want to delete your account? This action cannot be undone and all your data will be permanently removed.', 'homebio'); ?></p>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" id="cancel-delete">
                    <?php esc_html_e('Cancel', 'homebio'); ?>
                </button>
                <button type="button" class="btn btn-danger" id="confirm-delete">
                    <?php esc_html_e('Yes, Delete My Account', 'homebio'); ?>
                </button>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
