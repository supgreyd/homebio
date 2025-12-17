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

            <nav id="site-navigation" class="main-navigation">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'menu_id'        => 'primary-menu',
                    'container'      => false,
                    'fallback_cb'    => false,
                ]);
                ?>
            </nav>

            <div class="header-actions">
                <?php homebio_language_switcher(); ?>

                <?php if (is_user_logged_in()) : ?>
                    <div class="user-menu">
                        <a href="<?php echo esc_url(home_url('/user-cabinet')); ?>" class="btn btn-secondary">
                            <?php esc_html_e('My Cabinet', 'homebio'); ?>
                        </a>
                        <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="btn btn-outline">
                            <?php esc_html_e('Logout', 'homebio'); ?>
                        </a>
                    </div>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/login')); ?>" class="btn btn-primary">
                        <?php esc_html_e('Login', 'homebio'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                <span class="screen-reader-text"><?php esc_html_e('Menu', 'homebio'); ?></span>
                <span class="hamburger"></span>
            </button>
        </div>
    </header>
