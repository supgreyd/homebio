<?php
/**
 * Template part for displaying property cards
 *
 * @package HomeBio
 */

$price     = get_post_meta(get_the_ID(), 'property_price', true);
$address   = get_post_meta(get_the_ID(), 'property_address', true);
$area      = get_post_meta(get_the_ID(), 'property_area', true);
$bedrooms  = get_post_meta(get_the_ID(), 'property_bedrooms', true);
$bathrooms = get_post_meta(get_the_ID(), 'property_bathrooms', true);
?>

<article id="property-<?php the_ID(); ?>" <?php post_class('property-card'); ?>>
    <div class="property-card__image">
        <?php if (has_post_thumbnail()) : ?>
            <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail('property-card'); ?>
            </a>
        <?php else : ?>
            <a href="<?php the_permalink(); ?>" class="property-card__placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
            </a>
        <?php endif; ?>

        <?php homebio_favorite_button(); ?>
    </div>

    <div class="property-card__content">
        <?php if ($price) : ?>
            <div class="property-card__price">
                <?php echo esc_html(number_format($price, 0, '.', ' ')); ?> &euro;
            </div>
        <?php endif; ?>

        <h3 class="property-card__title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>

        <?php if ($address) : ?>
            <div class="property-card__location">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <?php echo esc_html($address); ?>
            </div>
        <?php endif; ?>

        <div class="property-card__details">
            <?php if ($area) : ?>
                <span class="property-card__detail">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    </svg>
                    <?php echo esc_html($area); ?> m&sup2;
                </span>
            <?php endif; ?>

            <?php if ($bedrooms) : ?>
                <span class="property-card__detail">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 4v16"></path>
                        <path d="M22 4v16"></path>
                        <path d="M2 8h20"></path>
                        <path d="M2 16h20"></path>
                        <path d="M6 8v8"></path>
                        <path d="M18 8v8"></path>
                    </svg>
                    <?php
                    printf(
                        esc_html(_n('%d bed', '%d beds', $bedrooms, 'homebio')),
                        intval($bedrooms)
                    );
                    ?>
                </span>
            <?php endif; ?>

            <?php if ($bathrooms) : ?>
                <span class="property-card__detail">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-.5C4.683 3 4 3.683 4 4.5V17a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"></path>
                        <line x1="10" y1="5" x2="8" y2="7"></line>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <line x1="7" y1="19" x2="7" y2="21"></line>
                        <line x1="17" y1="19" x2="17" y2="21"></line>
                    </svg>
                    <?php
                    printf(
                        esc_html(_n('%d bath', '%d baths', $bathrooms, 'homebio')),
                        intval($bathrooms)
                    );
                    ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</article>
