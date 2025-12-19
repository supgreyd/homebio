<?php
/**
 * The header template
 *
 * @package HomeBio
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary">
        <?php esc_html_e('Skip to content', 'homebio'); ?>
    </a>

    <header id="masthead" class="site-header">
        <div class="container header-container">
            <!-- Logo -->
            <div class="site-branding">
                <?php if (has_custom_logo()) : ?>
                    <div class="site-logo">
                        <?php the_custom_logo(); ?>
                    </div>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-title" rel="home">
                        <?php bloginfo('name'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Header Actions -->
            <div class="header-actions">
                <!-- Language Switcher -->
                <?php homebio_polylang_language_switcher(); ?>

                <?php if (is_user_logged_in()) : ?>
                    <!-- Logged-in User Menu -->
                    <?php $current_user = wp_get_current_user(); ?>
                    <div class="user-dropdown">
                        <button class="user-dropdown__toggle" aria-expanded="false">
                            <span class="user-avatar">
                                <?php echo homebio_get_avatar($current_user->ID, 32); ?>
                            </span>
                            <span class="user-name" data-user-display-name><?php echo esc_html($current_user->display_name); ?></span>
                            <svg class="dropdown-arrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="user-dropdown__menu">
                            <a href="<?php echo esc_url(home_url('/user-cabinet/')); ?>" class="user-dropdown__item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <?php esc_html_e('My Cabinet', 'homebio'); ?>
                            </a>
                            <a href="<?php echo esc_url(home_url('/user-cabinet/?tab=favorites')); ?>" class="user-dropdown__item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                                <?php esc_html_e('Favorites', 'homebio'); ?>
                                <?php
                                $favorites_count = homebio_get_favorites_count($current_user->ID);
                                if ($favorites_count > 0) :
                                ?>
                                    <span class="favorites-badge"><?php echo intval($favorites_count); ?></span>
                                <?php endif; ?>
                            </a>
                            <div class="user-dropdown__divider"></div>
                            <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="user-dropdown__item user-dropdown__item--logout">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                <?php esc_html_e('Logout', 'homebio'); ?>
                            </a>
                        </div>
                    </div>
                <?php else : ?>
                    <!-- Guest User -->
                    <a href="<?php echo esc_url(home_url('/login')); ?>" class="btn btn-outline btn-sm">
                        <?php esc_html_e('Login', 'homebio'); ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/register')); ?>" class="btn btn-primary btn-sm">
                        <?php esc_html_e('Sign Up', 'homebio'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Toggle -->
            <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                <span class="screen-reader-text"><?php esc_html_e('Menu', 'homebio'); ?></span>
                <span class="hamburger"></span>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div class="mobile-menu" id="mobile-menu">
            <div class="mobile-menu__header">
                <span class="mobile-menu__title"><?php esc_html_e('Menu', 'homebio'); ?></span>
                <button class="mobile-menu__close" aria-label="<?php esc_attr_e('Close menu', 'homebio'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <nav class="mobile-menu__nav">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'menu_id'        => 'mobile-primary-menu',
                    'container'      => false,
                    'fallback_cb'    => 'homebio_fallback_menu',
                ]);
                ?>
            </nav>
            <div class="mobile-menu__actions">
                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo esc_url(home_url('/user-cabinet/')); ?>" class="btn btn-primary btn-block">
                        <?php esc_html_e('My Cabinet', 'homebio'); ?>
                    </a>
                    <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="btn btn-outline btn-block">
                        <?php esc_html_e('Logout', 'homebio'); ?>
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/login')); ?>" class="btn btn-primary btn-block">
                        <?php esc_html_e('Login', 'homebio'); ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/register')); ?>" class="btn btn-outline btn-block">
                        <?php esc_html_e('Sign Up', 'homebio'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="mobile-menu__overlay"></div>
    </header>
