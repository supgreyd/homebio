<?php
/**
 * Property Archive Template
 *
 * @package HomeBio
 */

get_header();

// Get filter values
$current_type = isset($_GET['property_type']) ? sanitize_text_field($_GET['property_type']) : '';
$current_location = isset($_GET['property_location']) ? sanitize_text_field($_GET['property_location']) : '';
$current_sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'date';

// Get taxonomies for filters
$property_types = get_terms([
    'taxonomy'   => 'property_type',
    'hide_empty' => true,
]);

$property_locations = get_terms([
    'taxonomy'   => 'property_location',
    'hide_empty' => true,
]);
?>

<main id="primary" class="site-main">
    <!-- Archive Header -->
    <header class="archive-header">
        <div class="container">
            <h1><?php esc_html_e('Properties', 'homebio'); ?></h1>
            <p><?php esc_html_e('Browse our selection of quality properties', 'homebio'); ?></p>
        </div>
    </header>

    <div class="container">
        <!-- Filters -->
        <form class="property-filters" method="get" action="<?php echo esc_url(get_post_type_archive_link('property')); ?>">
            <?php if ($property_types && !is_wp_error($property_types)) : ?>
                <select name="property_type" onchange="this.form.submit()">
                    <option value=""><?php esc_html_e('All Types', 'homebio'); ?></option>
                    <?php foreach ($property_types as $type) : ?>
                        <option value="<?php echo esc_attr($type->slug); ?>" <?php selected($current_type, $type->slug); ?>>
                            <?php echo esc_html($type->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <?php if ($property_locations && !is_wp_error($property_locations)) : ?>
                <select name="property_location" onchange="this.form.submit()">
                    <option value=""><?php esc_html_e('All Locations', 'homebio'); ?></option>
                    <?php foreach ($property_locations as $location) : ?>
                        <option value="<?php echo esc_attr($location->slug); ?>" <?php selected($current_location, $location->slug); ?>>
                            <?php echo esc_html($location->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <select name="sort" onchange="this.form.submit()">
                <option value="date" <?php selected($current_sort, 'date'); ?>><?php esc_html_e('Newest First', 'homebio'); ?></option>
                <option value="price_low" <?php selected($current_sort, 'price_low'); ?>><?php esc_html_e('Price: Low to High', 'homebio'); ?></option>
                <option value="price_high" <?php selected($current_sort, 'price_high'); ?>><?php esc_html_e('Price: High to Low', 'homebio'); ?></option>
                <option value="area" <?php selected($current_sort, 'area'); ?>><?php esc_html_e('Largest First', 'homebio'); ?></option>
            </select>
        </form>

        <!-- Properties Grid -->
        <?php
        // Build query args
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;
        $args = [
            'post_type'      => 'property',
            'posts_per_page' => 9,
            'paged'          => $paged,
        ];

        // Add taxonomy filters
        $tax_query = [];

        if ($current_type) {
            $tax_query[] = [
                'taxonomy' => 'property_type',
                'field'    => 'slug',
                'terms'    => $current_type,
            ];
        }

        if ($current_location) {
            $tax_query[] = [
                'taxonomy' => 'property_location',
                'field'    => 'slug',
                'terms'    => $current_location,
            ];
        }

        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }

        // Add sorting
        switch ($current_sort) {
            case 'price_low':
                $args['meta_key'] = 'property_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                break;
            case 'price_high':
                $args['meta_key'] = 'property_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            case 'area':
                $args['meta_key'] = 'property_area';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
        }

        $properties = new WP_Query($args);

        if ($properties->have_posts()) :
        ?>
            <div class="properties-grid">
                <?php
                while ($properties->have_posts()) :
                    $properties->the_post();
                    get_template_part('template-parts/property', 'card');
                endwhile;
                ?>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <?php
                echo paginate_links([
                    'total'     => $properties->max_num_pages,
                    'current'   => $paged,
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                ]);
                ?>
            </div>

            <?php wp_reset_postdata(); ?>

        <?php else : ?>
            <div class="no-properties">
                <p><?php esc_html_e('No properties found matching your criteria.', 'homebio'); ?></p>
                <a href="<?php echo esc_url(get_post_type_archive_link('property')); ?>" class="btn btn-primary">
                    <?php esc_html_e('View All Properties', 'homebio'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
get_footer();
