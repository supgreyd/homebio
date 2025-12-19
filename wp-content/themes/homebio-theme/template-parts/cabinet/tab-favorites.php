<?php
/**
 * Cabinet Favorites Tab Template Part
 *
 * Displays the user's favorite properties.
 *
 * @package HomeBio
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$favorites = homebio_get_user_favorites($current_user->ID);
?>

<div class="cabinet-section">
    <h1><?php esc_html_e('My Favorites', 'homebio'); ?></h1>
    <p class="cabinet-section-desc"><?php esc_html_e('Properties you have saved', 'homebio'); ?></p>

    <?php if (!empty($favorites)) : ?>
        <?php
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
                                    <?php homebio_icon_e('image', 48, ['stroke-width' => '1']); ?>
                                </a>
                            <?php endif; ?>
                            <button type="button" class="favorite-remove" data-property-id="<?php the_ID(); ?>" title="<?php esc_attr_e('Remove from favorites', 'homebio'); ?>">
                                <?php homebio_icon_e('x', 20); ?>
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
                                    <?php echo esc_html(number_format($price)); ?> &euro;
                                </div>
                            <?php endif; ?>
                            <?php if ($location) : ?>
                                <div class="favorite-card-location">
                                    <?php homebio_icon_e('map-pin', 14); ?>
                                    <?php echo esc_html($location); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php wp_reset_postdata(); ?>
        <?php endif; ?>
    <?php else : ?>
        <?php
        get_template_part('template-parts/cabinet/empty-state', null, [
            'icon'        => 'heart',
            'title'       => __('No favorites yet', 'homebio'),
            'message'     => __('Start browsing properties and save your favorites here.', 'homebio'),
            'button_url'  => get_post_type_archive_link('property'),
            'button_text' => __('Browse Properties', 'homebio'),
        ]);
        ?>
    <?php endif; ?>
</div>
