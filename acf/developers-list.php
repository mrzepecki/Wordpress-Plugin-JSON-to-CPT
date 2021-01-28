<?php

/**
 * Developers list Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'developers-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

$className = 'developers-list';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

?>

<style>
    .developers-list {
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-gap: 30px;
    }
</style>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">
    <?php

    $args = array(
        'post_type' => 'developers',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    );

    $loop = new WP_Query($args);

    while ($loop->have_posts()) : $loop->the_post();
    ?>
        <div>
            <p><strong><?php the_field('first_name', get_the_ID()); ?> <?php the_field('last_name', get_the_ID()); ?></strong></p>
            <p>ID: <?php the_field('id', get_the_ID()); ?></p>
            <p>Gender: <?php the_field('gender', get_the_ID()); ?></p>
            <p>IP Address: <?php the_field('ip_address', get_the_ID()); ?></p>
        </div>
    <?php
    endwhile;

    ?>
</div>