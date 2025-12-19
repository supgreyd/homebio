<?php
/**
 * Cabinet Empty State Template Part
 *
 * Reusable empty state component for cabinet sections.
 *
 * @package HomeBio
 *
 * @param array $args {
 *     @type string $icon        Icon name for homebio_icon()
 *     @type string $title       Empty state title
 *     @type string $message     Empty state message
 *     @type string $button_url  Optional button URL
 *     @type string $button_text Optional button text
 * }
 */

if (!defined('ABSPATH')) {
    exit;
}

$icon = isset($args['icon']) ? $args['icon'] : 'info';
$title = isset($args['title']) ? $args['title'] : '';
$message = isset($args['message']) ? $args['message'] : '';
$button_url = isset($args['button_url']) ? $args['button_url'] : '';
$button_text = isset($args['button_text']) ? $args['button_text'] : '';
?>

<div class="empty-state">
    <?php echo homebio_icon($icon, 64, ['stroke-width' => '1']); ?>
    <?php if ($title) : ?>
        <h3><?php echo esc_html($title); ?></h3>
    <?php endif; ?>
    <?php if ($message) : ?>
        <p><?php echo esc_html($message); ?></p>
    <?php endif; ?>
    <?php if ($button_url && $button_text) : ?>
        <a href="<?php echo esc_url($button_url); ?>" class="btn btn-primary">
            <?php echo esc_html($button_text); ?>
        </a>
    <?php endif; ?>
</div>
