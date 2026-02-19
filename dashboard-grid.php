<?php
/**
 * Plugin Name: CIPIT Dashboard Grid
 * Plugin URI: https://github.com/Muchwat/dashboard-grid.git
 * Description: Compact dashboard cards themed like your blog, with customizable column counts and Golden Ratio logic.
 * Version: 4.0
 * Author: Kevin Muchwat
 * Author URI: https://github.com/Muchwat
 * Text Domain: dashboard-grid
 */

if (!defined('ABSPATH'))
    exit;

// 1. Register Custom Post Type & Taxonomy
add_action('init', function () {
    register_post_type('ai_dashboard', [
        'labels' => ['name' => 'Dashboards', 'singular_name' => 'Dashboard'],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-chart-bar',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'show_in_rest' => true,
    ]);

    register_taxonomy('dashboard_cat', 'ai_dashboard', [
        'hierarchical' => true,
        'labels' => ['name' => 'Categories', 'singular_name' => 'Category'],
        'show_admin_column' => true,
        'show_in_rest' => true,
    ]);
});

// 2. Meta Box for URL
add_action('add_meta_boxes', function () {
    add_meta_box('dash_url_meta', 'Dashboard Link', function ($post) {
        wp_nonce_field('dash_save_meta', 'dash_meta_nonce');
        $url = get_post_meta($post->ID, '_dashboard_url', true);
        echo '<input type="url" name="dashboard_url" value="' . esc_attr($url) . '" style="width:100%;" placeholder="https://...">';
    }, 'ai_dashboard', 'normal', 'high');
});

add_action('save_post', function ($post_id) {
    if (!isset($_POST['dash_meta_nonce']) || !wp_verify_nonce($_POST['dash_meta_nonce'], 'dash_save_meta'))
        return;
    if (isset($_POST['dashboard_url'])) {
        update_post_meta($post_id, '_dashboard_url', esc_url_raw($_POST['dashboard_url']));
    }
});

// 3. Shortcode: [dashboard-grid id="ai" cols="4"]
add_shortcode('dashboard-grid', function ($atts) {
    $atts = shortcode_atts([
        'id' => '',
        'cols' => '3'
    ], $atts);

    $category_slug = sanitize_text_field($atts['id']);
    $cols = intval($atts['cols']);

    $args = [
        'post_type' => 'ai_dashboard',
        'posts_per_page' => -1,
    ];

    if (!empty($category_slug)) {
        $args['tax_query'] = [['taxonomy' => 'dashboard_cat', 'field' => 'slug', 'terms' => $category_slug]];
    }

    $query = new WP_Query($args);
    if (!$query->have_posts())
        return '';

    ob_start();
    ?>
    <div class="dash-hub-wrapper" style="--grid-cols: <?php echo $cols; ?>;">
        <div class="blog-posts dash-compact-grid">
            <?php while ($query->have_posts()):
                $query->the_post();
                $dash_url = get_post_meta(get_the_ID(), '_dashboard_url', true);
                $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium_large') ?: 'https://via.placeholder.com/400x250';
                ?>
                <article class="blog-card dash-mini-card">
                    <div class="blog-card-image mini-media">
                        <a href="<?php echo esc_url($dash_url); ?>" target="_blank">
                            <img src="<?php echo esc_url($thumb); ?>" alt="<?php the_title(); ?>">
                            <div class="dash-mini-overlay">
                                <i class="dashicons dashicons-external"></i>
                            </div>
                        </a>
                    </div>

                    <div class="blog-card-content mini-content">
                        <h3><a href="<?php echo esc_url($dash_url); ?>" target="_blank">
                                <?php the_title(); ?>
                            </a></h3>
                        <p>
                            <?php echo wp_trim_words(get_the_excerpt(), 10); ?>
                        </p>
                        <a href="<?php echo esc_url($dash_url); ?>" target="_blank" class="read-more-btn mini-btn">View</a>
                    </div>
                </article>
            <?php endwhile;
            wp_reset_postdata(); ?>
        </div>
    </div>

    <style>
        .dash-hub-wrapper {
            width: 100%;
            margin: 1.5rem 0;
        }

        /* Customizable Grid */
        .dash-compact-grid {
            display: grid !important;
            grid-template-columns: repeat(var(--grid-cols), 1fr) !important;
            gap: 1.5rem !important;
            margin: 0 !important;
        }

        /* Adjusting Card to maintain Golden Ratio at smaller sizes */
        .dash-mini-card {
            min-height: auto !important;
            height: 100%;
        }

        .mini-media {
            height: 160px !important;
            /* Fixed height for consistency in tabs */
            background: var(--secondary-color);
        }

        .dash-mini-overlay {
            position: absolute;
            inset: 0;
            background: rgba(192, 33, 38, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: 0.3s ease;
        }

        .dash-mini-overlay i {
            color: #fff;
            font-size: 2rem;
        }

        .blog-card:hover .dash-mini-overlay {
            opacity: 1;
        }

        .mini-content {
            padding: 1.2rem !important;
        }

        .mini-content h3 a {
            font-size: 1rem !important;
            /* Slightly smaller for 4-col layout */
            margin-bottom: 0.5rem !important;
        }

        .mini-content p {
            font-size: 0.85rem !important;
            margin-bottom: 1rem !important;
        }

        .mini-btn {
            padding: 0.4rem 1.2rem !important;
            font-size: 0.85rem !important;
        }

        /* Tablet/Mobile Responsiveness overrides */
        @media (max-width: 1024px) {
            .dash-compact-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 600px) {
            .dash-compact-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
    <?php
    return ob_get_clean();
});
