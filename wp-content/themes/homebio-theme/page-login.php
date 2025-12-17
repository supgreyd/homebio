<?php
/**
 * Template Name: Login Page
 *
 * Login/Register page with Google OAuth
 *
 * @package HomeBio
 */

// Redirect logged-in users to cabinet
if (is_user_logged_in()) {
    wp_redirect(home_url('/user-cabinet'));
    exit;
}

// Get redirect URL if set
$redirect_to = isset($_GET['redirect_to']) ? esc_url($_GET['redirect_to']) : home_url('/user-cabinet');

get_header();
?>

<main id="primary" class="site-main">
    <div class="login-page">
        <div class="container">
            <div class="login-wrapper">
                <div class="login-card">
                    <div class="login-header">
                        <h1><?php esc_html_e('Welcome to HomeBio', 'homebio'); ?></h1>
                        <p><?php esc_html_e('Sign in to save your favorite properties and manage your profile.', 'homebio'); ?></p>
                    </div>

                    <div class="login-content">
                        <!-- Google OAuth Button -->
                        <div class="social-login">
                            <?php
                            // Nextend Social Login shortcode
                            if (shortcode_exists('nextend_social_login')) {
                                echo do_shortcode('[nextend_social_login provider="google" redirect="' . esc_attr($redirect_to) . '"]');
                            } else {
                                // Fallback if plugin not configured
                                ?>
                                <div class="google-login-placeholder">
                                    <button type="button" class="btn-google" disabled>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                        </svg>
                                        <?php esc_html_e('Continue with Google', 'homebio'); ?>
                                    </button>
                                    <p class="setup-notice">
                                        <?php esc_html_e('Google login needs to be configured in WordPress admin.', 'homebio'); ?>
                                    </p>
                                </div>
                                <?php
                            }
                            ?>
                        </div>

                        <div class="login-divider">
                            <span><?php esc_html_e('or', 'homebio'); ?></span>
                        </div>

                        <!-- Traditional Login Form (Optional) -->
                        <div class="traditional-login">
                            <?php
                            // Show login form
                            $args = [
                                'redirect'       => $redirect_to,
                                'form_id'        => 'homebio-login-form',
                                'label_username' => __('Email or Username', 'homebio'),
                                'label_password' => __('Password', 'homebio'),
                                'label_remember' => __('Remember Me', 'homebio'),
                                'label_log_in'   => __('Sign In', 'homebio'),
                                'remember'       => true,
                            ];
                            wp_login_form($args);
                            ?>

                            <div class="login-links">
                                <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">
                                    <?php esc_html_e('Forgot password?', 'homebio'); ?>
                                </a>
                            </div>
                        </div>

                        <div class="login-register-link">
                            <p>
                                <?php esc_html_e("Don't have an account?", 'homebio'); ?>
                                <a href="<?php echo esc_url(home_url('/register')); ?>">
                                    <?php esc_html_e('Create one', 'homebio'); ?>
                                </a>
                            </p>
                        </div>

                        <?php
                        // Display login errors
                        if (isset($_GET['login']) && $_GET['login'] === 'failed') :
                        ?>
                            <div class="login-error">
                                <?php esc_html_e('Invalid username or password. Please try again.', 'homebio'); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="login-footer">
                        <p>
                            <?php esc_html_e('By signing in, you agree to our', 'homebio'); ?>
                            <a href="<?php echo esc_url(home_url('/terms')); ?>"><?php esc_html_e('Terms of Service', 'homebio'); ?></a>
                            <?php esc_html_e('and', 'homebio'); ?>
                            <a href="<?php echo esc_url(home_url('/privacy')); ?>"><?php esc_html_e('Privacy Policy', 'homebio'); ?></a>.
                        </p>
                    </div>
                </div>

                <!-- Benefits Section -->
                <div class="login-benefits">
                    <h2><?php esc_html_e('Why create an account?', 'homebio'); ?></h2>
                    <ul>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                            <div>
                                <strong><?php esc_html_e('Save Favorites', 'homebio'); ?></strong>
                                <span><?php esc_html_e('Keep track of properties you love', 'homebio'); ?></span>
                            </div>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <div>
                                <strong><?php esc_html_e('Get Notifications', 'homebio'); ?></strong>
                                <span><?php esc_html_e('Receive updates on new properties', 'homebio'); ?></span>
                            </div>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M12 6v6l4 2"></path>
                            </svg>
                            <div>
                                <strong><?php esc_html_e('Quick Access', 'homebio'); ?></strong>
                                <span><?php esc_html_e('Sign in instantly with Google', 'homebio'); ?></span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
