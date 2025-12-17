<?php
/**
 * The front page template
 *
 * @package HomeBio
 */

get_header();
?>

<main id="primary" class="site-main">
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero__content">
                <h1><?php esc_html_e('Find Your Dream Home', 'homebio'); ?></h1>
                <p><?php esc_html_e('Discover the perfect property from our curated selection of homes.', 'homebio'); ?></p>
                <a href="<?php echo esc_url(get_post_type_archive_link('property')); ?>" class="btn btn-primary btn-lg">
                    <?php esc_html_e('Browse Properties', 'homebio'); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Properties Section -->
    <section class="featured-properties">
        <div class="container">
            <div class="section-header">
                <h2><?php esc_html_e('Featured Properties', 'homebio'); ?></h2>
                <a href="<?php echo esc_url(get_post_type_archive_link('property')); ?>" class="view-all">
                    <?php esc_html_e('View All', 'homebio'); ?> &rarr;
                </a>
            </div>

            <?php
            $properties = new WP_Query([
                'post_type'      => 'property',
                'posts_per_page' => 5,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ]);

            if ($properties->have_posts()) :
            ?>
                <div class="properties-grid">
                    <?php
                    while ($properties->have_posts()) :
                        $properties->the_post();
                        get_template_part('template-parts/property', 'card');
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            <?php else : ?>
                <p class="no-properties">
                    <?php esc_html_e('No properties available at the moment.', 'homebio'); ?>
                </p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
        <div class="container">
            <h2><?php esc_html_e('Why Choose HomeBio', 'homebio'); ?></h2>
            <div class="features-grid">
                <div class="feature">
                    <div class="feature__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                    </div>
                    <h3><?php esc_html_e('Quality Properties', 'homebio'); ?></h3>
                    <p><?php esc_html_e('Hand-picked selection of premium properties.', 'homebio'); ?></p>
                </div>
                <div class="feature">
                    <div class="feature__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <h3><?php esc_html_e('Save Time', 'homebio'); ?></h3>
                    <p><?php esc_html_e('Quick and easy property search experience.', 'homebio'); ?></p>
                </div>
                <div class="feature">
                    <div class="feature__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </div>
                    <h3><?php esc_html_e('Save Favorites', 'homebio'); ?></h3>
                    <p><?php esc_html_e('Keep track of properties you love.', 'homebio'); ?></p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
