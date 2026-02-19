<?php
/**
 * Plugin Name: CIPIT Dashboard Grid
 * Plugin URI: https://github.com/Muchwat/dashboard-grid.git
 * Description: A premium e-commerce style grid for Tableau and PowerBI dashboards using the Golden Ratio (1.618).
 * Version: 2.0
 * Author: Kevin Muchwat
 * Author URI: https://github.com/Muchwat
 * Text Domain: dashboard-grid
 */

if (!defined('ABSPATH')) exit;

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

// 2. Add Metabox for Dashboard URL
add_action('add_meta_boxes', function () {
    add_meta_box('dash_url_meta', 'Dashboard Link Settings', function ($post) {
        wp_nonce_field('dash_save_meta', 'dash_meta_nonce');
        $url = get_post_meta($post->ID, '_dashboard_url', true);
        ?>
        <p>
            <label for="dashboard_url"><strong>External URL</strong> (Tableau, PowerBI, etc.)</label><br>
            <input type="url" id="dashboard_url" name="dashboard_url" value="<?php echo esc_attr($url); ?>" style="width:100%; margin-top:5px;" placeholder="https://public.tableau.com/...">
        </p>
        <?php
    }, 'ai_dashboard', 'normal', 'high');
});

add_action('save_post', function ($post_id) {
    if (!isset($_POST['dash_meta_nonce']) || !wp_verify_nonce($_POST['dash_meta_nonce'], 'dash_save_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    if (isset($_POST['dashboard_url'])) {
        update_post_meta($post_id, '_dashboard_url', esc_url_raw($_POST['dashboard_url']));
    }
});

// 3. Shortcode: [dashboard-grid id="ai"]
add_shortcode('dashboard-grid', function ($atts) {
    $atts = shortcode_atts(['id' => ''], $atts);
    $category_slug = sanitize_text_field($atts['id']);

    $args = [
        'post_type' => 'ai_dashboard',
        'posts_per_page' => -1,
    ];

    if (!empty($category_slug)) {
        $args['tax_query'] = [[
            'taxonomy' => 'dashboard_cat',
            'field'    => 'slug',
            'terms'    => $category_slug,
        ]];
    }

    $query = new WP_Query($args);
    if (!$query->have_posts()) return '';

    ob_start();
    ?>
    <div class="dash-grid-container">
        <div class="dash-grid-wrapper">
            <?php while ($query->have_posts()) : $query->the_post(); 
                $dash_url = get_post_meta(get_the_ID(), '_dashboard_url', true);
                $thumb = get_the_post_thumbnail_url(get_the_ID(), 'large');
                $thumb = $thumb ?: 'https://via.placeholder.com/600x970?text=CIPIT+Preview';
            ?>
                <div class="dash-item-card">
                    <a href="<?php echo esc_url($dash_url); ?>" target="_blank" class="dash-card-anchor">
                        <div class="dash-media">
                            <img src="<?php echo esc_url($thumb); ?>" alt="<?php the_title(); ?>">
                            <div class="dash-hover-overlay">
                                <span class="dash-view-btn">Launch Dashboard</span>
                            </div>
                        </div>
                        <div class="dash-info">
                            <h3 class="dash-title"><?php the_title(); ?></h3>
                            <div class="dash-excerpt">
                                <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>

    <style>
        .dash-grid-container {
            --cipit-red: #c02126;
            --card-white: #ffffff;
            --text-dark: #1a1a1a;
            --text-muted: #666666;
            --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            margin: 3rem 0;
            width: 100%;
        }

        .dash-grid-wrapper {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 40px;
        }

        /* Enforcing Golden Ratio Proportions */
        .dash-item-card {
            background: var(--card-white);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #eeeeee;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .dash-card-anchor {
            text-decoration: none !important;
            color: inherit;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .dash-media {
            position: relative;
            /* 1:1 Aspect Ratio for the visual component */
            aspect-ratio: 1 / 1; 
            overflow: hidden;
            background: #fdfdfd;
        }

        .dash-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .dash-hover-overlay {
            position: absolute;
            inset: 0;
            background: rgba(192, 33, 38, 0.88);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
        }

        .dash-view-btn {
            color: #ffffff;
            font-weight: 700;
            padding: 12px 24px;
            border: 2px solid #ffffff;
            border-radius: 4px;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 1px;
            transform: translateY(10px);
            transition: var(--transition);
        }

        .dash-info {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .dash-title {
            margin: 0 0 12px 0;
            font-size: 1.25rem;
            color: var(--text-dark);
            font-weight: 800;
            line-height: 1.3;
        }

        .dash-excerpt {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin: 0;
        }

        /* Hover Interactions */
        .dash-item-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            border-color: var(--cipit-red);
        }

        .dash-item-card:hover .dash-media img {
            transform: scale(1.1);
        }

        .dash-item-card:hover .dash-hover-overlay {
            opacity: 1;
        }

        .dash-item-card:hover .dash-view-btn {
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .dash-grid-wrapper {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <?php
    return ob_get_clean();
});
