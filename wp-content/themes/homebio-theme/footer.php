<?php
/**
 * The footer template
 *
 * @package HomeBio
 */
?>

    <footer id="colophon" class="site-footer">
        <div class="container footer-container">
            <div class="footer-branding">
                <h4><?php bloginfo('name'); ?></h4>
                <p><?php bloginfo('description'); ?></p>
            </div>

            <div class="footer-navigation">
                <h4><?php esc_html_e('Navigation', 'homebio'); ?></h4>
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'menu_id'        => 'footer-menu',
                    'container'      => false,
                    'fallback_cb'    => 'homebio_fallback_menu',
                    'depth'          => 1,
                ]);
                ?>
            </div>

            <div class="footer-contact">
                <h4><?php esc_html_e('Contact', 'homebio'); ?></h4>
                <p><?php esc_html_e('Email: info@homebio.com', 'homebio'); ?></p>
            </div>

            <?php if (is_active_sidebar('footer-1')) : ?>
                <div class="footer-widgets">
                    <?php dynamic_sidebar('footer-1'); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>.
                    <?php esc_html_e('All rights reserved.', 'homebio'); ?>
                </p>
            </div>
        </div>
    </footer>
</div>

<?php wp_footer(); ?>

</body>
</html>
