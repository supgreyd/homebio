<?php
/**
 * Single Property Template
 *
 * @package HomeBio
 */

get_header();

while (have_posts()) :
    the_post();

    // Get property meta
    $price     = get_post_meta(get_the_ID(), 'property_price', true);
    $address   = get_post_meta(get_the_ID(), 'property_address', true);
    $area      = get_post_meta(get_the_ID(), 'property_area', true);
    $rooms     = get_post_meta(get_the_ID(), 'property_rooms', true);
    $bedrooms  = get_post_meta(get_the_ID(), 'property_bedrooms', true);
    $bathrooms = get_post_meta(get_the_ID(), 'property_bathrooms', true);
    $year      = get_post_meta(get_the_ID(), 'property_year_built', true);

    // Get taxonomies
    $property_types = get_the_terms(get_the_ID(), 'property_type');
    $locations = get_the_terms(get_the_ID(), 'property_location');
?>

<main id="primary" class="site-main">
    <article id="post-<?php the_ID(); ?>" <?php post_class('property-single'); ?>>
        <div class="container">
            <!-- Property Header -->
            <header class="property-header">
                <div class="property-header__top">
                    <?php homebio_favorite_button(); ?>
                </div>
                <h1><?php the_title(); ?></h1>
                <div class="property-meta">
                    <?php if ($address) : ?>
                        <span class="property-meta__location">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <?php echo esc_html($address); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($property_types && !is_wp_error($property_types)) : ?>
                        <span class="property-meta__type">
                            <?php echo esc_html($property_types[0]->name); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Property Gallery -->
            <div class="property-gallery">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="property-gallery__main">
                        <?php the_post_thumbnail('property-gallery'); ?>
                    </div>
                <?php endif; ?>

                <?php
                // Gallery from meta (if using ACF or custom gallery)
                $gallery = get_post_meta(get_the_ID(), 'property_gallery', true);
                if ($gallery && is_array($gallery)) :
                ?>
                    <div class="property-gallery__thumbs">
                        <?php foreach ($gallery as $image_id) : ?>
                            <div class="property-gallery__thumb">
                                <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Property Content -->
            <div class="property-content">
                <div class="property-main">
                    <!-- Description -->
                    <section class="property-description">
                        <h2><?php esc_html_e('Description', 'homebio'); ?></h2>
                        <div class="property-description__content">
                            <?php the_content(); ?>
                        </div>
                    </section>

                    <!-- Features -->
                    <section class="property-features">
                        <h2><?php esc_html_e('Property Features', 'homebio'); ?></h2>
                        <div class="property-features__grid">
                            <?php if ($area) : ?>
                                <div class="property-feature">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    </svg>
                                    <div>
                                        <span class="property-feature__value"><?php echo esc_html($area); ?> m²</span>
                                        <span class="property-feature__label"><?php esc_html_e('Area', 'homebio'); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($rooms) : ?>
                                <div class="property-feature">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    </svg>
                                    <div>
                                        <span class="property-feature__value"><?php echo esc_html($rooms); ?></span>
                                        <span class="property-feature__label"><?php esc_html_e('Rooms', 'homebio'); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($bedrooms) : ?>
                                <div class="property-feature">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M2 4v16"></path>
                                        <path d="M22 4v16"></path>
                                        <path d="M2 12h20"></path>
                                        <path d="M2 8h20"></path>
                                    </svg>
                                    <div>
                                        <span class="property-feature__value"><?php echo esc_html($bedrooms); ?></span>
                                        <span class="property-feature__label"><?php esc_html_e('Bedrooms', 'homebio'); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($bathrooms) : ?>
                                <div class="property-feature">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-.5C4.683 3 4 3.683 4 4.5V17a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"></path>
                                        <line x1="10" x2="8" y1="5" y2="7"></line>
                                        <line x1="2" x2="22" y1="12" y2="12"></line>
                                        <line x1="7" x2="7" y1="19" y2="21"></line>
                                        <line x1="17" x2="17" y1="19" y2="21"></line>
                                    </svg>
                                    <div>
                                        <span class="property-feature__value"><?php echo esc_html($bathrooms); ?></span>
                                        <span class="property-feature__label"><?php esc_html_e('Bathrooms', 'homebio'); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($year) : ?>
                                <div class="property-feature">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <div>
                                        <span class="property-feature__value"><?php echo esc_html($year); ?></span>
                                        <span class="property-feature__label"><?php esc_html_e('Year Built', 'homebio'); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- Location -->
                    <?php if ($locations && !is_wp_error($locations)) : ?>
                        <section class="property-location-info">
                            <h2><?php esc_html_e('Location', 'homebio'); ?></h2>
                            <p>
                                <?php
                                $location_names = wp_list_pluck($locations, 'name');
                                echo esc_html(implode(', ', $location_names));
                                ?>
                            </p>
                            <?php if ($address) : ?>
                                <p><strong><?php esc_html_e('Address:', 'homebio'); ?></strong> <?php echo esc_html($address); ?></p>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <aside class="property-sidebar">
                    <div class="property-price-card">
                        <?php if ($price) : ?>
                            <div class="property-price">
                                €<?php echo number_format($price, 0, ',', ' '); ?>
                            </div>
                        <?php endif; ?>

                        <a href="#contact" class="btn btn-primary">
                            <?php esc_html_e('Contact Agent', 'homebio'); ?>
                        </a>

                        <?php homebio_favorite_button(); ?>
                    </div>
                </aside>
            </div>

            <!-- Related Properties -->
            <?php
            $related_args = [
                'post_type'      => 'property',
                'posts_per_page' => 3,
                'post__not_in'   => [get_the_ID()],
                'orderby'        => 'rand',
            ];

            // Try to get properties from same location
            if ($locations && !is_wp_error($locations)) {
                $related_args['tax_query'] = [
                    [
                        'taxonomy' => 'property_location',
                        'field'    => 'term_id',
                        'terms'    => wp_list_pluck($locations, 'term_id'),
                    ],
                ];
            }

            $related = new WP_Query($related_args);

            if ($related->have_posts()) :
            ?>
                <section class="related-properties">
                    <h2><?php esc_html_e('Similar Properties', 'homebio'); ?></h2>
                    <div class="properties-grid">
                        <?php
                        while ($related->have_posts()) :
                            $related->the_post();
                            get_template_part('template-parts/property', 'card');
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </article>
</main>

<?php
endwhile;

get_footer();
