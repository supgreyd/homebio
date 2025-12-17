<?php
/**
 * Template Name: Register Page
 *
 * Registration page with Google OAuth and traditional form
 *
 * @package HomeBio
 */

// Redirect logged-in users to cabinet
if (is_user_logged_in()) {
    wp_redirect(home_url('/user-cabinet'));
    exit;
}

// Check if registration is allowed
if (!get_option('users_can_register')) {
    wp_redirect(home_url('/login'));
    exit;
}

// Get redirect URL if set
$redirect_to = isset($_GET['redirect_to']) ? esc_url($_GET['redirect_to']) : home_url('/user-cabinet');

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['homebio_register_nonce'])) {
    if (wp_verify_nonce($_POST['homebio_register_nonce'], 'homebio_register')) {
        $result = homebio_process_registration($_POST);

        if (is_wp_error($result)) {
            $errors = $result->get_error_messages();
        } else {
            $success = true;
        }
    } else {
        $errors[] = __('Security check failed. Please try again.', 'homebio');
    }
}

get_header();
?>

<main id="primary" class="site-main">
    <div class="login-page">
        <div class="container">
            <div class="login-wrapper">
                <div class="login-card">
                    <div class="login-header">
                        <h1><?php esc_html_e('Create Account', 'homebio'); ?></h1>
                        <p><?php esc_html_e('Join HomeBio to save favorites and get personalized recommendations.', 'homebio'); ?></p>
                    </div>

                    <div class="login-content">
                        <?php if ($success) : ?>
                            <div class="register-success">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <h2><?php esc_html_e('Registration Successful!', 'homebio'); ?></h2>
                                <p><?php esc_html_e('Please check your email to verify your account.', 'homebio'); ?></p>
                                <a href="<?php echo esc_url(home_url('/login')); ?>" class="btn btn-primary">
                                    <?php esc_html_e('Sign In', 'homebio'); ?>
                                </a>
                            </div>
                        <?php else : ?>

                            <!-- Google OAuth Button -->
                            <div class="social-login">
                                <?php
                                if (shortcode_exists('nextend_social_login')) {
                                    echo do_shortcode('[nextend_social_login provider="google" redirect="' . esc_attr($redirect_to) . '"]');
                                } else {
                                    ?>
                                    <button type="button" class="btn-google">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                        </svg>
                                        <?php esc_html_e('Sign up with Google', 'homebio'); ?>
                                    </button>
                                    <?php
                                }
                                ?>
                                <p class="social-login-hint">
                                    <?php esc_html_e('Quick and secure - no password needed', 'homebio'); ?>
                                </p>
                            </div>

                            <div class="login-divider">
                                <span><?php esc_html_e('or register with email', 'homebio'); ?></span>
                            </div>

                            <!-- Display Errors -->
                            <?php if (!empty($errors)) : ?>
                                <div class="login-error">
                                    <?php foreach ($errors as $error) : ?>
                                        <p><?php echo esc_html($error); ?></p>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Registration Form -->
                            <form method="post" class="register-form" id="homebio-register-form">
                                <?php wp_nonce_field('homebio_register', 'homebio_register_nonce'); ?>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="first_name"><?php esc_html_e('First Name', 'homebio'); ?></label>
                                        <input type="text" id="first_name" name="first_name"
                                               value="<?php echo isset($_POST['first_name']) ? esc_attr($_POST['first_name']) : ''; ?>"
                                               required>
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name"><?php esc_html_e('Last Name', 'homebio'); ?></label>
                                        <input type="text" id="last_name" name="last_name"
                                               value="<?php echo isset($_POST['last_name']) ? esc_attr($_POST['last_name']) : ''; ?>"
                                               required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="user_email"><?php esc_html_e('Email Address', 'homebio'); ?></label>
                                    <input type="email" id="user_email" name="user_email"
                                           value="<?php echo isset($_POST['user_email']) ? esc_attr($_POST['user_email']) : ''; ?>"
                                           required>
                                </div>

                                <div class="form-group">
                                    <label for="user_pass"><?php esc_html_e('Password', 'homebio'); ?></label>
                                    <input type="password" id="user_pass" name="user_pass"
                                           minlength="8" required>
                                    <small><?php esc_html_e('Minimum 8 characters', 'homebio'); ?></small>
                                </div>

                                <div class="form-group">
                                    <label for="user_pass_confirm"><?php esc_html_e('Confirm Password', 'homebio'); ?></label>
                                    <input type="password" id="user_pass_confirm" name="user_pass_confirm"
                                           minlength="8" required>
                                </div>

                                <div class="form-group form-checkbox">
                                    <label>
                                        <input type="checkbox" name="agree_terms" value="1" required>
                                        <?php
                                        printf(
                                            /* translators: %1$s: Terms link, %2$s: Privacy link */
                                            esc_html__('I agree to the %1$s and %2$s', 'homebio'),
                                            '<a href="' . esc_url(home_url('/terms')) . '" target="_blank">' . esc_html__('Terms of Service', 'homebio') . '</a>',
                                            '<a href="' . esc_url(home_url('/privacy')) . '" target="_blank">' . esc_html__('Privacy Policy', 'homebio') . '</a>'
                                        );
                                        ?>
                                    </label>
                                </div>

                                <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>">

                                <button type="submit" class="btn btn-primary btn-block">
                                    <?php esc_html_e('Create Account', 'homebio'); ?>
                                </button>
                            </form>

                            <div class="login-links" style="margin-top: var(--spacing-lg); text-align: center;">
                                <p>
                                    <?php esc_html_e('Already have an account?', 'homebio'); ?>
                                    <a href="<?php echo esc_url(home_url('/login')); ?>">
                                        <?php esc_html_e('Sign in', 'homebio'); ?>
                                    </a>
                                </p>
                            </div>

                        <?php endif; ?>
                    </div>
                </div>

                <!-- Benefits Section -->
                <div class="login-benefits">
                    <h2><?php esc_html_e('Join thousands of happy users', 'homebio'); ?></h2>
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
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <div>
                                <strong><?php esc_html_e('Secure & Private', 'homebio'); ?></strong>
                                <span><?php esc_html_e('Your data is protected', 'homebio'); ?></span>
                            </div>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <div>
                                <strong><?php esc_html_e('Free Forever', 'homebio'); ?></strong>
                                <span><?php esc_html_e('No hidden fees or charges', 'homebio'); ?></span>
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
