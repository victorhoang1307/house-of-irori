<?php
/**
 * Plugin Name: House of IRORI — Headless CMS Core
 * Description: Custom Post Types, Menu Book & Site Settings admin UI, initial Figma content seeder, security hardening, and transient-cached REST API (/wp-json/irori/v1/*) for the 5-page House of IRORI static frontend.
 * Version: 1.0.0
 * Author: House of IRORI
 */

if (!defined('ABSPATH')) {
    exit;
}

define('IRORI_CMS_CACHE_KEY', 'irori_bootstrap_payload_v3');

/**
 * 1. Security Hardening for Headless WordPress on Shared Hosting
 */
add_filter('xmlrpc_enabled', '__return_false');
add_filter('xmlrpc_methods', '__return_empty_array');

add_filter('rest_endpoints', function (array $endpoints): array {
    if (!is_user_logged_in()) {
        unset($endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }
    return $endpoints;
});

add_action('template_redirect', function (): void {
    if (!is_admin() && !wp_doing_ajax() && !wp_doing_cron() && !(defined('REST_REQUEST') && REST_REQUEST)) {
        wp_safe_redirect(home_url('/../'), 302);
        exit;
    }
});

/**
 * Allowlisted CORS headers for /wp-json/irori/v1/* (supports same-origin, GitHub Pages preview, and local dev).
 */
add_action('rest_api_init', function (): void {
    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
    add_filter('rest_pre_serve_request', function ($value) {
        $origin = get_http_origin();
        $allowed = [
            'https://sites-test.houseofirori.com.vn',
            'http://sites-test.houseofirori.com.vn',
            'https://houseofirori.com.vn',
            'https://www.houseofirori.com.vn',
            'https://victorhoang1307.github.io',
            'http://127.0.0.1:8091',
            'http://localhost:8091',
        ];
        header('Vary: Origin');
        if ($origin && in_array($origin, $allowed, true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Methods: GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Accept');
        }
        header('X-Content-Type-Options: nosniff');
        return $value;
    });
}, 15);

/**
 * 2. Classic Editor & Custom Post Type (google_reviews — 1:1 parity with houseofmerlin)
 */
add_filter('use_block_editor_for_post', '__return_false');

add_action('init', function (): void {
    add_theme_support('post-thumbnails');

    register_post_type('google_reviews', [
        'labels' => [
            'name' => __('Google Reviews'),
            'singular_name' => __('Google Review'),
            'add_new_item' => __('Add New Google Review'),
            'edit_item' => __('Edit Google Review'),
        ],
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-star-filled',
        'menu_position' => 22,
        'supports' => ['title', 'editor', 'thumbnail', 'page-attributes'],
        'rewrite' => ['slug' => 'google-reviews'],
    ]);

    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title' => 'Irori Config',
            'menu_title' => 'Irori Settings',
            'menu_slug' => 'irori_config-page',
            'capability' => 'edit_pages',
            'position' => 25,
            'icon_url' => 'dashicons-admin-generic',
            'redirect' => false,
        ]);
    }
});

/**
 * 3. Admin Menu Order & ACF Field Groups (1:1 parity with houseofmerlin.com.vn)
 */
add_action('admin_menu', function (): void {
    global $menu;
    remove_menu_page('index.php');
    remove_menu_page('edit-comments.php');
    remove_menu_page('edit.php?post_type=dflip');
    if (is_array($menu)) {
        foreach ($menu as $k => $item) {
            if (($item[2] ?? '') === 'edit.php') {
                $menu[$k][0] = 'Blogs';
            }
        }
    }
}, 999);

add_filter('custom_menu_order', '__return_true');
add_filter('menu_order', function (array $menu_ord): array {
    return [
        'edit.php?post_type=page',
        'edit.php',
        'edit.php?post_type=google_reviews',
        'upload.php',
        'irori_config-page',
        'separator1',
        'users.php',
        'plugins.php',
        'themes.php',
        'tools.php',
        'options-general.php',
        'separator2',
        'edit.php?post_type=acf-field-group',
    ];
});

add_action('acf/init', 'irori_register_acf_field_groups');

function irori_register_acf_field_groups(): void {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    $page_id = static function (string $slug): int {
        $p = get_page_by_path($slug, OBJECT, 'page');
        return $p ? (int) $p->ID : 0;
    };

    $welcome_id = $page_id('welcome');
    $menu_id = $page_id('menu');
    $events_id = $page_id('events');
    $blogs_id = $page_id('blogs');
    $contact_id = $page_id('contact');

    // 1. Welcome Page (1:1 with index.html sections)
    if ($welcome_id) {
        acf_add_local_field_group([
            'key' => 'group_irori_welcome_page',
            'title' => 'Welcome Page (Home)',
            'fields' => [
                [
                    'key' => 'field_irori_welcome_banner',
                    'label' => '1. Hero Banner & Framed Bartender Showcase',
                    'name' => 'welcome_banner',
                    'type' => 'group',
                    'sub_fields' => [
                        ['key' => 'field_irori_wb_banner', 'label' => 'Hero Background Image', 'name' => 'banner', 'type' => 'image', 'return_format' => 'id'],
                        ['key' => 'field_irori_wb_headline', 'label' => 'Hero Headline', 'name' => 'headline', 'type' => 'textarea', 'rows' => 3],
                        ['key' => 'field_irori_wb_sub', 'label' => 'Discover Button Text', 'name' => 'sub_headline', 'type' => 'text'],
                        ['key' => 'field_irori_wb_showcase', 'label' => 'Framed Bartender Showcase Image', 'name' => 'showcase_image', 'type' => 'image', 'return_format' => 'id'],
                    ],
                ],
                [
                    'key' => 'field_irori_story_section',
                    'label' => '2. Story Section (Hidden in Plain Sight)',
                    'name' => 'story_section',
                    'type' => 'group',
                    'sub_fields' => [
                        ['key' => 'field_irori_st_bg', 'label' => 'Background Image', 'name' => 'bg_image', 'type' => 'image', 'return_format' => 'id'],
                        ['key' => 'field_irori_st_head', 'label' => 'Headline', 'name' => 'headline', 'type' => 'text'],
                        ['key' => 'field_irori_welcome_message', 'label' => 'Story Copy', 'name' => 'content', 'type' => 'wysiwyg'],
                    ],
                ],
                [
                    'key' => 'field_irori_philosophy_section',
                    'label' => '3. Philosophy Collage (The Space Between)',
                    'name' => 'philosophy_section',
                    'type' => 'group',
                    'sub_fields' => [
                        ['key' => 'field_irori_ph_head', 'label' => 'Card Headline', 'name' => 'headline', 'type' => 'textarea', 'rows' => 2],
                        ['key' => 'field_irori_ph_sub', 'label' => 'Ribbon Subtitle', 'name' => 'sub_headline', 'type' => 'text'],
                        ['key' => 'field_irori_ph_int', 'label' => 'Upper Left Interior Image', 'name' => 'interior_image', 'type' => 'image', 'return_format' => 'id'],
                        ['key' => 'field_irori_ph_kim', 'label' => 'Lower Framed Kimono Image', 'name' => 'kimono_image', 'type' => 'image', 'return_format' => 'id'],
                    ],
                ],
                [
                    'key' => 'field_irori_level_one',
                    'label' => '4. Level One: The Alley',
                    'name' => 'level_one',
                    'type' => 'group',
                    'sub_fields' => [
                        ['key' => 'field_irori_l1_eye', 'label' => 'Eyebrow', 'name' => 'eyebrow', 'type' => 'text'],
                        ['key' => 'field_irori_l1_head', 'label' => 'Headline', 'name' => 'headline', 'type' => 'text'],
                        ['key' => 'field_irori_l1_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'rows' => 4],
                        ['key' => 'field_irori_l1_slider', 'label' => 'Gallery Slider', 'name' => 'slider', 'type' => 'gallery', 'return_format' => 'id'],
                    ],
                ],
                [
                    'key' => 'field_irori_level_two',
                    'label' => '5. Level Two: The Bar',
                    'name' => 'level_two',
                    'type' => 'group',
                    'sub_fields' => [
                        ['key' => 'field_irori_l2_eye', 'label' => 'Eyebrow', 'name' => 'eyebrow', 'type' => 'text'],
                        ['key' => 'field_irori_l2_head', 'label' => 'Headline', 'name' => 'headline', 'type' => 'text'],
                        ['key' => 'field_irori_l2_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'rows' => 4],
                        ['key' => 'field_irori_l2_sub', 'label' => 'Closing Line', 'name' => 'sub_text', 'type' => 'text'],
                        ['key' => 'field_irori_l2_slider', 'label' => 'Gallery Slider', 'name' => 'slider', 'type' => 'gallery', 'return_format' => 'id'],
                    ],
                ],
                [
                    'key' => 'field_irori_bottom_section',
                    'label' => '6. Reviews, Map & Atmosphere Strip',
                    'name' => 'bottom_section',
                    'type' => 'group',
                    'sub_fields' => [
                        ['key' => 'field_irori_bt_score', 'label' => 'Google Rating Score', 'name' => 'rating_score', 'type' => 'text'],
                        ['key' => 'field_irori_find_us_img', 'label' => 'Map Fallback Image', 'name' => 'map_image', 'type' => 'image', 'return_format' => 'id'],
                        ['key' => 'field_irori_bt_atmo', 'label' => 'Atmosphere Strip Banner Image', 'name' => 'atmosphere_image', 'type' => 'image', 'return_format' => 'id'],
                        ['key' => 'field_irori_bt_news', 'label' => 'Latest News Section Heading', 'name' => 'latest_news_heading', 'type' => 'text'],
                    ],
                ],
            ],
            'location' => [[['param' => 'page', 'operator' => '==', 'value' => (string) $welcome_id]]],
            'hide_on_screen' => ['the_content'],
        ]);
    }

    // 2. Menu Page (matches Merlin Menu Page group: Menu Banner + Occasional Menus + Main Menus PDF upload)
    if ($menu_id) {
        acf_add_local_field_group([
            'key' => 'group_irori_menu_page',
            'title' => 'Menu Page',
            'fields' => [
                [
                    'key' => 'field_irori_menu_banner',
                    'label' => 'Menu Banner',
                    'name' => 'menu_banner',
                    'type' => 'group',
                    'sub_fields' => [
                        ['key' => 'field_irori_mb_banner', 'label' => 'Banner', 'name' => 'banner', 'type' => 'image', 'return_format' => 'id'],
                        ['key' => 'field_irori_mb_headline', 'label' => 'Headline', 'name' => 'headline', 'type' => 'text'],
                    ],
                ],
                [
                    'key' => 'field_irori_occasional_menus',
                    'label' => 'Occasional Menus',
                    'name' => 'occasional_menus',
                    'type' => 'repeater',
                    'layout' => 'table',
                    'button_label' => 'Add Row',
                    'sub_fields' => [
                        ['key' => 'field_irori_om_thumb', 'label' => 'Thumbnail', 'name' => 'thumbnail', 'type' => 'image', 'return_format' => 'id'],
                        ['key' => 'field_irori_om_head', 'label' => 'Headline', 'name' => 'headline', 'type' => 'text'],
                        ['key' => 'field_irori_om_date', 'label' => 'Date Label', 'name' => 'date', 'type' => 'text'],
                        ['key' => 'field_irori_om_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea'],
                    ],
                ],
                [
                    'key' => 'field_irori_main_menus',
                    'label' => 'Main Menus',
                    'name' => 'main_menus',
                    'type' => 'repeater',
                    'layout' => 'table',
                    'button_label' => 'Add Row',
                    'sub_fields' => [
                        ['key' => 'field_irori_mm_pdf', 'label' => 'Menu file', 'name' => 'pdf', 'type' => 'file', 'return_format' => 'url', 'mime_types' => 'pdf'],
                        ['key' => 'field_irori_mm_name', 'label' => 'Menu name', 'name' => 'menu_name', 'type' => 'text'],
                    ],
                ],
            ],
            'location' => [[['param' => 'page', 'operator' => '==', 'value' => (string) $menu_id]]],
            'hide_on_screen' => ['the_content'],
        ]);
    }

    // 3. Event Page (1:1 top-to-bottom with events.html: Event Banner -> Up Coming Slider -> Occasional Events)
    if ($events_id) {
        acf_add_local_field_group([
            'key' => 'group_irori_event_page',
            'title' => 'Event Page',
            'fields' => [
                [
                    'key' => 'field_irori_event_banner',
                    'label' => '1. Event Banner & Up Coming Header',
                    'name' => 'event_banner',
                    'type' => 'group',
                    'sub_fields' => [
                        ['key' => 'field_irori_eb_banner', 'label' => 'Hero Banner Image', 'name' => 'banner', 'type' => 'image', 'return_format' => 'id'],
                        ['key' => 'field_irori_eb_headline', 'label' => 'Hero Headline', 'name' => 'headline', 'type' => 'text'],
                        ['key' => 'field_irori_eb_up_bg', 'label' => 'Up Coming Section Wood Banner', 'name' => 'upcoming_bg', 'type' => 'image', 'return_format' => 'id'],
                        ['key' => 'field_irori_eb_up_head', 'label' => 'Up Coming Section Heading', 'name' => 'upcoming_heading', 'type' => 'text'],
                    ],
                ],
                [
                    'key' => 'field_irori_other_events',
                    'label' => '2. Up Coming Events (Featured Carousel Slides)',
                    'name' => 'other_events',
                    'type' => 'repeater',
                    'layout' => 'table',
                    'button_label' => 'Add Slide',
                    'sub_fields' => [
                        ['key' => 'field_irori_ote_thumb', 'label' => 'Slide Image', 'name' => 'thumbnail', 'type' => 'image', 'return_format' => 'id'],
                    ],
                ],
                [
                    'key' => 'field_irori_occasional_events',
                    'label' => '3. Occasional Events (2x2 Grid)',
                    'name' => 'occasional_events',
                    'type' => 'repeater',
                    'layout' => 'table',
                    'button_label' => 'Add Event',
                    'sub_fields' => [
                        ['key' => 'field_irori_oe_thumb', 'label' => 'Thumbnail', 'name' => 'thumbnail', 'type' => 'image', 'return_format' => 'id'],
                        ['key' => 'field_irori_oe_head', 'label' => 'Headline', 'name' => 'headline', 'type' => 'text'],
                        ['key' => 'field_irori_oe_date', 'label' => 'Date Label', 'name' => 'date', 'type' => 'text'],
                        ['key' => 'field_irori_oe_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea'],
                    ],
                ],
            ],
            'location' => [[['param' => 'page', 'operator' => '==', 'value' => (string) $events_id]]],
            'hide_on_screen' => ['the_content'],
        ]);
    }

    // 4. Blogs Page (1:1 with blogs.html)
    if ($blogs_id) {
        acf_add_local_field_group([
            'key' => 'group_irori_blogs_page',
            'title' => 'Blogs Page',
            'fields' => [
                [
                    'key' => 'field_irori_blogs_banner',
                    'label' => 'Blogs Hero Banner & Section Heading',
                    'name' => 'blogs_banner',
                    'type' => 'group',
                    'sub_fields' => [
                        ['key' => 'field_irori_bb_banner', 'label' => 'Banner', 'name' => 'banner', 'type' => 'image', 'return_format' => 'id'],
                        ['key' => 'field_irori_bb_headline', 'label' => 'Headline', 'name' => 'headline', 'type' => 'text'],
                        ['key' => 'field_irori_bb_sec_head', 'label' => 'Listing Section Heading', 'name' => 'section_heading', 'type' => 'text'],
                    ],
                ],
            ],
            'location' => [[['param' => 'page', 'operator' => '==', 'value' => (string) $blogs_id]]],
            'hide_on_screen' => ['the_content'],
        ]);
    }

    // 5. Contact Page (Footer "A Glimpse Inside" Banner)
    if ($contact_id) {
        acf_add_local_field_group([
            'key' => 'group_irori_contact_page',
            'title' => 'Contact / Footer Banner',
            'fields' => [
                [
                    'key' => 'field_irori_contact_banner',
                    'label' => 'Footer Top Banner (A Glimpse Inside)',
                    'name' => 'contact_banner',
                    'type' => 'group',
                    'sub_fields' => [
                        ['key' => 'field_irori_cb_banner', 'label' => 'Banner Image', 'name' => 'banner', 'type' => 'image', 'return_format' => 'id'],
                        ['key' => 'field_irori_cb_headline', 'label' => 'Headline', 'name' => 'headline', 'type' => 'text'],
                        ['key' => 'field_irori_cb_sub', 'label' => 'Subtitle', 'name' => 'subtitle', 'type' => 'textarea', 'rows' => 2],
                    ],
                ],
            ],
            'location' => [[['param' => 'page', 'operator' => '==', 'value' => (string) $contact_id]]],
            'hide_on_screen' => ['the_content'],
        ]);
    }

    // 6. Irori Config Options Page (1:1 with Header & Footer across all 5 pages)
    acf_add_local_field_group([
        'key' => 'group_irori_config_options',
        'title' => 'Irori Config (Header & Footer)',
        'fields' => [
            ['key' => 'field_irori_cfg_open_text', 'label' => 'Footer Banner Text (A Glimpse Inside)', 'name' => 'open_time_text', 'type' => 'wysiwyg'],
            ['key' => 'field_irori_cfg_open_img', 'label' => 'Footer Banner Image (A Glimpse Inside)', 'name' => 'open_time_image', 'type' => 'image', 'return_format' => 'id'],
            ['key' => 'field_irori_cfg_tagline', 'label' => 'Footer Brand Tagline', 'name' => 'brand_tagline', 'type' => 'textarea', 'rows' => 2],
            ['key' => 'field_irori_cfg_address', 'label' => 'Visit Address', 'name' => 'address', 'type' => 'wysiwyg'],
            ['key' => 'field_irori_cfg_phone', 'label' => 'Phone', 'name' => 'phone', 'type' => 'text'],
            ['key' => 'field_irori_cfg_email', 'label' => 'Email', 'name' => 'email', 'type' => 'text'],
            ['key' => 'field_irori_cfg_res_url', 'label' => 'Reservations Link', 'name' => 'reservations_url', 'type' => 'text'],
            ['key' => 'field_irori_cfg_gmap_link', 'label' => 'Google Map Link', 'name' => 'google_map_link', 'type' => 'text'],
            ['key' => 'field_irori_cfg_gmap_embed', 'label' => 'Google Map Coordinates (lat,lng,zoom)', 'name' => 'google_map_embed_link', 'type' => 'text'],
            ['key' => 'field_irori_cfg_note', 'label' => 'Footer Disclaimer Note', 'name' => 'after_phone_message', 'type' => 'wysiwyg'],
            ['key' => 'field_irori_cfg_copy', 'label' => 'Footer Copyright Text', 'name' => 'copyright_text', 'type' => 'text'],
            [
                'key' => 'field_irori_cfg_socials',
                'label' => 'Social links',
                'name' => 'social_links',
                'type' => 'repeater',
                'layout' => 'table',
                'button_label' => 'Add Row',
                'sub_fields' => [
                    ['key' => 'field_irori_soc_name', 'label' => 'Name', 'name' => 'name', 'type' => 'text'],
                    ['key' => 'field_irori_soc_link', 'label' => 'Link', 'name' => 'link', 'type' => 'url'],
                ],
            ],
            ['key' => 'field_irori_cfg_head_code', 'label' => 'Custom Header Code (before end of header tag)', 'name' => 'custom_header_code', 'type' => 'textarea'],
            ['key' => 'field_irori_cfg_body_code', 'label' => 'Custom Body Code (after body tag)', 'name' => 'custom_body_code', 'type' => 'textarea'],
        ],
        'location' => [[['param' => 'options_page', 'operator' => '==', 'value' => 'irori_config-page']]],
    ]);
}

/**
 * 4. Custom Meta Boxes for Blog Posts and Google Reviews
 */
add_action('add_meta_boxes', function (): void {
    add_meta_box('irori_post_meta', 'Article Display Options', 'irori_render_post_meta_box', 'post', 'normal', 'high');
    add_meta_box('irori_review_meta', 'Google Review Details', 'irori_render_review_meta_box', 'google_reviews', 'normal', 'high');
});

function irori_render_post_meta_box(\WP_Post $post): void {
    wp_nonce_field('irori_save_meta', 'irori_meta_nonce');
    $sub = get_post_meta($post->ID, '_irori_subheading', true);
    $fb = get_post_meta($post->ID, '_irori_fallback_img', true);
    ?>
    <p>
        <label for="irori_subheading"><strong>Lead Subheading (Blog Detail H2):</strong></label><br>
        <input type="text" id="irori_subheading" name="irori_subheading" value="<?php echo esc_attr($sub); ?>" class="large-text" placeholder="Why We Work with Spirit, Not Against It">
    </p>
    <p>
        <label for="irori_fallback_img"><strong>Fallback Image Path / URL (used if Featured Image is empty):</strong></label><br>
        <input type="text" id="irori_fallback_img" name="irori_fallback_img" value="<?php echo esc_attr($fb); ?>" class="large-text" placeholder="assets/images/blog-thumb-1.jpg">
    </p>
    <?php
}

function irori_render_review_meta_box(\WP_Post $post): void {
    wp_nonce_field('irori_save_meta', 'irori_meta_nonce');
    $rating = (int) (get_post_meta($post->ID, '_irori_rating', true) ?: 5);
    $time = get_post_meta($post->ID, '_irori_time_label', true);
    $iso = get_post_meta($post->ID, '_irori_date_iso', true);
    $initial = get_post_meta($post->ID, '_irori_initial', true);
    ?>
    <p>
        <label for="irori_rating"><strong>Star Rating (1–5):</strong></label><br>
        <input type="number" id="irori_rating" name="irori_rating" min="1" max="5" value="<?php echo esc_attr((string) $rating); ?>">
    </p>
    <p>
        <label for="irori_time_label"><strong>Relative Time Label:</strong></label><br>
        <input type="text" id="irori_time_label" name="irori_time_label" value="<?php echo esc_attr($time); ?>" class="regular-text" placeholder="1 month ago">
    </p>
    <p>
        <label for="irori_date_iso"><strong>Review Date (YYYY-MM-DD):</strong></label><br>
        <input type="date" id="irori_date_iso" name="irori_date_iso" value="<?php echo esc_attr($iso); ?>">
    </p>
    <p>
        <label for="irori_initial"><strong>Avatar Initial (optional):</strong></label><br>
        <input type="text" id="irori_initial" name="irori_initial" maxlength="2" value="<?php echo esc_attr($initial); ?>" placeholder="K">
    </p>
    <?php
}

add_action('save_post', function (int $post_id): void {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!isset($_POST['irori_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['irori_meta_nonce'])), 'irori_save_meta')) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $text_fields = [
        'irori_subheading' => '_irori_subheading',
        'irori_fallback_img' => '_irori_fallback_img',
        'irori_date_iso' => '_irori_date_iso',
        'irori_time_label' => '_irori_time_label',
        'irori_initial' => '_irori_initial',
    ];
    foreach ($text_fields as $input => $meta_key) {
        if (isset($_POST[$input])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$input])));
        }
    }
    if (isset($_POST['irori_rating'])) {
        $r = max(1, min(5, (int) $_POST['irori_rating']));
        update_post_meta($post_id, '_irori_rating', $r);
    }
    irori_bust_cms_cache();
});

function irori_bust_cms_cache(): void {
    delete_transient(IRORI_CMS_CACHE_KEY);
    update_option('irori_cache_rev', ((int) get_option('irori_cache_rev', 1)) + 1, false);
}

add_action('acf/save_post', function (): void {
    irori_bust_cms_cache();
}, 20);

add_action('clean_post_cache', function (int $post_id): void {
    if (!wp_is_post_revision($post_id) && !wp_is_post_autosave($post_id)) {
        irori_bust_cms_cache();
    }
});

foreach (['deleted_post', 'trashed_post', 'untrashed_post', 'edited_term', 'create_term'] as $hook) {
    add_action($hook, 'irori_bust_cms_cache');
}

/**
 * 5. Default Settings & Media Attachment Helper
 */
function irori_default_settings(): array {
    return [
        'email' => 'houseofirori@gmail.com',
        'phone_display' => '+84 97 141 90 66',
        'phone_tel' => '+84971419066',
        'reservations_url' => '#footer',
        'address_line1' => 'Level 3, 17/3 Nguyen Thi Minh Khai,',
        'address_line2' => 'Sai Gon Ward, HCMC, Vietnam',
        'disclaimer' => 'Drink Responsibility. We Do Not Offer Online Sale. We Offer Products Less Than 5.5% Alcohol.',
        'rating_score' => '5.0 / 5.0',
        'map_lat' => '10.78492',
        'map_lng' => '106.7006',
        'map_zoom' => '16',
        'google_maps_url' => 'https://maps.google.com/?q=17/3+Nguyen+Thi+Minh+Khai,+Ben+Nghe,+District+1,+Ho+Chi+Minh+City',
        'facebook_url' => 'https://facebook.com',
        'instagram_url' => 'https://instagram.com',
        'tiktok_url' => 'https://tiktok.com',
        'threads_url' => 'https://threads.net',
    ];
}

function irori_resolve_media_value($val, string $fallback = ''): string {
    if (is_numeric($val) && (int) $val > 0) {
        $url = wp_get_attachment_url((int) $val);
        if ($url) {
            $rel = ltrim(wp_make_link_relative($url), '/');
            return $rel !== '' ? $rel : $url;
        }
    }
    if (is_string($val) && trim($val) !== '') {
        return trim($val);
    }
    return $fallback;
}

function irori_import_local_asset(string $rel_path, string $title): int {
    $existing = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'meta_key' => '_irori_source_asset',
        'meta_value' => $rel_path,
        'numberposts' => 1,
        'fields' => 'ids',
    ]);
    if (!empty($existing)) {
        return (int) $existing[0];
    }
    $webroot = dirname(ABSPATH);
    $src = $webroot . '/' . ltrim($rel_path, '/');
    if (!file_exists($src)) {
        return 0;
    }
    $uploads = wp_upload_dir();
    if (!empty($uploads['error'])) {
        return 0;
    }
    $filename = wp_unique_filename($uploads['path'], basename($src));
    $dest = trailingslashit($uploads['path']) . $filename;
    if (!@copy($src, $dest)) {
        return 0;
    }
    $filetype = wp_check_filetype($filename, null);
    $att_id = wp_insert_attachment([
        'guid' => trailingslashit($uploads['url']) . $filename,
        'post_mime_type' => $filetype['type'] ?: 'application/octet-stream',
        'post_title' => $title,
        'post_content' => '',
        'post_status' => 'inherit',
    ], $dest);
    if (!$att_id || is_wp_error($att_id)) {
        return 0;
    }
    update_post_meta($att_id, '_irori_source_asset', $rel_path);
    return (int) $att_id;
}

/**
 * 6. First-Boot Idempotent Seeder (Populates Merlin-Aligned Pages, ACF Repeaters, PDFs & Reviews)
 */
add_action('init', 'irori_maybe_seed_defaults', 30);

function irori_set_acf_meta(int $post_id, string $name, $value, string $field_key): void {
    update_post_meta($post_id, $name, $value);
    update_post_meta($post_id, '_' . $name, $field_key);
}

function irori_set_acf_option(string $name, $value, string $field_key): void {
    update_option('options_' . $name, $value);
    update_option('_options_' . $name, $field_key);
}

function irori_Ensure_Page(string $title, string $slug, int $order): int {
    $existing = get_page_by_path($slug, OBJECT, 'page');
    if ($existing) {
        wp_update_post(['ID' => $existing->ID, 'post_title' => $title, 'menu_order' => $order, 'post_status' => 'publish']);
        return (int) $existing->ID;
    }
    $pid = wp_insert_post([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => $title,
        'post_name' => $slug,
        'post_content' => '',
        'menu_order' => $order,
    ]);
    return ($pid && !is_wp_error($pid)) ? (int) $pid : 0;
}

function irori_maybe_seed_defaults(): void {
    if (get_option('irori_seeded_v3')) {
        return;
    }
    $lock = (int) get_option('irori_seed_lock_v3', 0);
    if ($lock > 0 && (time() - $lock) < 300) {
        return;
    }
    if ($lock > 0) {
        delete_option('irori_seed_lock_v3');
    }
    if (!add_option('irori_seed_lock_v3', time(), '', 'no')) {
        return;
    }

    // Clean up WP boilerplate & previous seed posts so CMS matches website 1:1
    foreach (['hello-world', 'sample-page', 'privacy-policy'] as $bp_slug) {
        foreach (['post', 'page'] as $bp_type) {
            $bp = get_page_by_path($bp_slug, OBJECT, $bp_type);
            if ($bp) {
                wp_delete_post($bp->ID, true);
            }
        }
    }
    foreach (['post', 'irori_occasional', 'irori_event', 'irori_review', 'google_reviews'] as $legacy_pt) {
        $old_posts = get_posts(['post_type' => $legacy_pt, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids']);
        foreach ($old_posts as $old_id) {
            wp_delete_post((int) $old_id, true);
        }
    }

    // Import core images & PDF menu files into WordPress Media Library
    $att_hero_bg = irori_import_local_asset('assets/images/hero-bg.jpg', 'House of IRORI Hero Background');
    $att_showcase = irori_import_local_asset('assets/images/hero-bartender-framed.jpg', 'House of IRORI Framed Bartender Showcase');
    $att_story_bg = irori_import_local_asset('assets/images/story-bg.jpg', 'Hidden in Plain Sight Background');
    $att_interior = irori_import_local_asset('assets/images/collage-interior.jpg', 'House of IRORI Timber & Spirits Interior');
    $att_kimono = irori_import_local_asset('assets/images/collage-kimono-framed.jpg', 'House of IRORI Framed Kimono Artwork');
    $att_floating = irori_import_local_asset('assets/images/floating-banner.jpg', 'House of IRORI Atmosphere Banner');
    $att_menu_banner = irori_import_local_asset('assets/images/hero-menu.jpg', 'Cocktail & Eatery Menu Banner');
    $att_event_banner = irori_import_local_asset('assets/images/hero-events.jpg', 'Events Banner');
    $att_up_banner = irori_import_local_asset('assets/images/events-upcoming-banner.jpg', 'Events Up Coming Wood Banner');
    $att_blogs_banner = irori_import_local_asset('assets/images/hero-blogs.jpg', 'Blogs Banner');
    $att_wild_turkey = irori_import_local_asset('assets/images/event-wild-turkey.jpg', 'Guestshift Wild Turkey Takeover');
    $att_upcoming = irori_import_local_asset('assets/images/event-upcoming-featured.jpg', 'Featured Upcoming Event');
    $att_level1 = irori_import_local_asset('assets/images/level1-alley.jpg', 'Level One The Alley');
    $att_level2 = irori_import_local_asset('assets/images/level2-bar.jpg', 'Level Two The Bar');
    $att_glimpse = irori_import_local_asset('assets/images/footer-glimpse-bg.jpg', 'A Glimpse Inside Footer Banner');
    $att_map = irori_import_local_asset('assets/images/saigon-map.jpg', 'House of IRORI Saigon Map');
    $att_blog_thumb = irori_import_local_asset('assets/images/blog-thumb-1.jpg', 'ELLE Decoration PopUp Office');
    $att_pdf_cocktail = irori_import_local_asset('assets/menus/irori-cocktail-menu.pdf', 'House of IRORI — Our Top Cocktails PDF Menu');
    $att_pdf_food = irori_import_local_asset('assets/menus/irori-food-menu.pdf', 'House of IRORI — Food (Pizza & Side Dishes) PDF Menu');

    // 1. Create the 5 Pages matching houseofmerlin.com.vn
    $welcome_id = irori_Ensure_Page('Welcome', 'welcome', 1);
    $menu_id = irori_Ensure_Page('Menu', 'menu', 2);
    $events_id = irori_Ensure_Page('Events', 'events', 3);
    $blogs_id = irori_Ensure_Page('Blogs', 'blogs', 4);
    $contact_id = irori_Ensure_Page('Contact', 'contact', 5);

    if ($welcome_id) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $welcome_id);
        foreach (['welcome_message', '_welcome_message', 'find_us_img', '_find_us_img'] as $old_meta) {
            delete_post_meta($welcome_id, $old_meta);
        }

        // 1.1 Hero Banner & Framed Bartender Showcase
        irori_set_acf_meta($welcome_id, 'welcome_banner', '', 'field_irori_welcome_banner');
        irori_set_acf_meta($welcome_id, 'welcome_banner_banner', $att_hero_bg, 'field_irori_wb_banner');
        irori_set_acf_meta($welcome_id, 'welcome_banner_headline', "A QUIET CORNER OF JAPAN,\nTUCKED INTO\nTHE HEART OF SAIGON", 'field_irori_wb_headline');
        irori_set_acf_meta($welcome_id, 'welcome_banner_sub_headline', 'DISCOVER THE STORY', 'field_irori_wb_sub');
        irori_set_acf_meta($welcome_id, 'welcome_banner_showcase_image', $att_showcase, 'field_irori_wb_showcase');

        // 1.2 Story Section (Hidden in Plain Sight)
        irori_set_acf_meta($welcome_id, 'story_section', '', 'field_irori_story_section');
        irori_set_acf_meta($welcome_id, 'story_section_bg_image', $att_story_bg, 'field_irori_st_bg');
        irori_set_acf_meta($welcome_id, 'story_section_headline', 'HIDDEN IN PLAIN SIGHT', 'field_irori_st_head');
        irori_set_acf_meta(
            $welcome_id,
            'story_section_content',
            "<p>House of IRORI isn't a cocktail bar. It's a moment - a small corner where time moves differently. Step down a narrow alley.</p>\n<p>Warm tungsten light spills across dark wood. A bartender works with quiet precision. Ice hits glass. Smoke rises slowly. This is omotenashi. Japanese hospitality in its purest form. Every detail whispered, never shouted.</p>",
            'field_irori_welcome_message'
        );

        // 1.3 Philosophy Collage (The Space Between)
        irori_set_acf_meta($welcome_id, 'philosophy_section', '', 'field_irori_philosophy_section');
        irori_set_acf_meta($welcome_id, 'philosophy_section_headline', "THE SPACE BETWEEN,\nSTILLNESS AND BEAUTY", 'field_irori_ph_head');
        irori_set_acf_meta($welcome_id, 'philosophy_section_sub_headline', 'TWO FLOORS. ONE PHILOSOPHY.', 'field_irori_ph_sub');
        irori_set_acf_meta($welcome_id, 'philosophy_section_interior_image', $att_interior, 'field_irori_ph_int');
        irori_set_acf_meta($welcome_id, 'philosophy_section_kimono_image', $att_kimono, 'field_irori_ph_kim');

        // 1.4 Level One: The Alley
        irori_set_acf_meta($welcome_id, 'level_one', '', 'field_irori_level_one');
        irori_set_acf_meta($welcome_id, 'level_one_eyebrow', 'LEVEL ONE:', 'field_irori_l1_eye');
        irori_set_acf_meta($welcome_id, 'level_one_headline', 'THE ALLEY', 'field_irori_l1_head');
        irori_set_acf_meta(
            $welcome_id,
            'level_one_description',
            "A cinematic passage. Narrow. Intimate. Like stepping into a 70s Tokyo film. Dark wood weathered by time. Warm lighting that doesn't reveal everything. Walls that feel like they have stories. This is where you arrive.",
            'field_irori_l1_desc'
        );
        irori_set_acf_meta($welcome_id, 'level_one_slider', array_values(array_filter([$att_level1, $att_interior, $att_floating])), 'field_irori_l1_slider');

        // 1.5 Level Two: The Bar
        irori_set_acf_meta($welcome_id, 'level_two', '', 'field_irori_level_two');
        irori_set_acf_meta($welcome_id, 'level_two_eyebrow', 'LEVEL TWO:', 'field_irori_l2_eye');
        irori_set_acf_meta($welcome_id, 'level_two_headline', 'THE BAR', 'field_irori_l2_head');
        irori_set_acf_meta(
            $welcome_id,
            'level_two_description',
            'The heart of the experience. A small counter where the bartender becomes the story. Watch spirit and ice transform into something sacred. Every pour is a conversation.',
            'field_irori_l2_desc'
        );
        irori_set_acf_meta($welcome_id, 'level_two_sub_text', 'This is where you stay.', 'field_irori_l2_sub');
        irori_set_acf_meta($welcome_id, 'level_two_slider', array_values(array_filter([$att_level2, $att_glimpse, $att_interior])), 'field_irori_l2_slider');

        // 1.6 Bottom Section (Reviews Score, Saigon Map, Atmosphere Strip & Latest News)
        irori_set_acf_meta($welcome_id, 'bottom_section', '', 'field_irori_bottom_section');
        irori_set_acf_meta($welcome_id, 'bottom_section_rating_score', '5.0 / 5.0', 'field_irori_bt_score');
        irori_set_acf_meta($welcome_id, 'bottom_section_map_image', $att_map, 'field_irori_find_us_img');
        irori_set_acf_meta($welcome_id, 'bottom_section_atmosphere_image', $att_floating, 'field_irori_bt_atmo');
        irori_set_acf_meta($welcome_id, 'bottom_section_latest_news_heading', 'LATEST NEWS', 'field_irori_bt_news');
    }

    // 2. Seed Menu Page (Menu Banner + Occasional Menus + Main Menus PDF Repeater)
    if ($menu_id) {
        irori_set_acf_meta($menu_id, 'menu_banner', '', 'field_irori_menu_banner');
        irori_set_acf_meta($menu_id, 'menu_banner_banner', $att_menu_banner, 'field_irori_mb_banner');
        irori_set_acf_meta($menu_id, 'menu_banner_headline', 'Cocktail & Eatery Menu', 'field_irori_mb_headline');

        $occ_menus = [
            ['thumb' => $att_wild_turkey, 'head' => 'Guestshift - Wild Turkey 8ys Takeover', 'date' => '16th May 2025', 'desc' => 'Beat the sweltering heat with Lunazul tequila. House of IRORI created La Familia Es Todo – refreshing, balanced, and invigorating. A harmonious blend of 100% agave tequila, sweet and sour fruit flavors, and cool mint, offering a moment of complete relaxation. 📍Enjoy this special cocktail at House of IRORI this April.'],
            ['thumb' => $att_wild_turkey, 'head' => 'Guestshift - Wild Turkey 8ys Takeover', 'date' => '16th May 2025', 'desc' => 'Beat the sweltering heat with Lunazul tequila. House of IRORI created La Familia Es Todo – refreshing, balanced, and invigorating. A harmonious blend of 100% agave tequila, sweet and sour fruit flavors, and cool mint, offering a moment of complete relaxation. 📍Enjoy this special cocktail at House of IRORI this April.'],
            ['thumb' => $att_level2, 'head' => 'Seasonal Tasting - Nikka From The Barrel Flight', 'date' => '8th June 2025', 'desc' => 'An intimate counter-side tasting exploring smoked Japanese malt pairings, hand-carved crystal block ice, and seasonal citrus infusions curated by House of IRORI’s head bartender.'],
            ['thumb' => $att_level1, 'head' => 'Okinawa Awamori & Charcoal Izakaya Pairing', 'date' => '27th June 2025', 'desc' => 'Discover aged island rice spirits paired with binchotan-kissed small plates and wood-fired artisanal flatbreads across Level One and Level Two at House of IRORI.'],
        ];
        irori_set_acf_meta($menu_id, 'occasional_menus', count($occ_menus), 'field_irori_occasional_menus');
        foreach ($occ_menus as $i => $m) {
            irori_set_acf_meta($menu_id, "occasional_menus_{$i}_thumbnail", $m['thumb'], 'field_irori_om_thumb');
            irori_set_acf_meta($menu_id, "occasional_menus_{$i}_headline", $m['head'], 'field_irori_om_head');
            irori_set_acf_meta($menu_id, "occasional_menus_{$i}_date", $m['date'], 'field_irori_om_date');
            irori_set_acf_meta($menu_id, "occasional_menus_{$i}_description", $m['desc'], 'field_irori_om_desc');
        }

        $main_menus = [
            ['name' => 'Our top cocktail', 'pdf' => $att_pdf_cocktail ?: 'assets/menus/irori-cocktail-menu.pdf'],
            ['name' => 'Food (Pizza & Side dishes)', 'pdf' => $att_pdf_food ?: 'assets/menus/irori-food-menu.pdf'],
        ];
        irori_set_acf_meta($menu_id, 'main_menus', count($main_menus), 'field_irori_main_menus');
        foreach ($main_menus as $i => $mm) {
            irori_set_acf_meta($menu_id, "main_menus_{$i}_pdf", $mm['pdf'], 'field_irori_mm_pdf');
            irori_set_acf_meta($menu_id, "main_menus_{$i}_menu_name", $mm['name'], 'field_irori_mm_name');
        }
    }

    // 3. Seed Events Page (Event Banner + Up Coming Slider + Occasional Events)
    if ($events_id) {
        irori_set_acf_meta($events_id, 'event_banner', '', 'field_irori_event_banner');
        irori_set_acf_meta($events_id, 'event_banner_banner', $att_event_banner, 'field_irori_eb_banner');
        irori_set_acf_meta($events_id, 'event_banner_headline', 'Events', 'field_irori_eb_headline');
        irori_set_acf_meta($events_id, 'event_banner_upcoming_bg', $att_up_banner, 'field_irori_eb_up_bg');
        irori_set_acf_meta($events_id, 'event_banner_upcoming_heading', 'up coming', 'field_irori_eb_up_head');

        $other_events = [$att_upcoming, $att_wild_turkey, $att_level2];
        irori_set_acf_meta($events_id, 'other_events', count($other_events), 'field_irori_other_events');
        foreach ($other_events as $i => $th) {
            irori_set_acf_meta($events_id, "other_events_{$i}_thumbnail", $th, 'field_irori_ote_thumb');
        }

        $occ_events = [
            ['thumb' => $att_wild_turkey, 'head' => 'Guestshift - Wild Turkey 8ys Takeover', 'date' => '16th May 2025', 'desc' => 'Beat the sweltering heat with Lunazul tequila. House of IRORI created La Familia Es Todo – refreshing, balanced, and invigorating. A harmonious blend of 100% agave tequila, sweet and sour fruit flavors, and cool mint, offering a moment of complete relaxation. 📍Enjoy this special cocktail at House of IRORI this April.'],
            ['thumb' => $att_wild_turkey, 'head' => 'Guestshift - Wild Turkey 8ys Takeover', 'date' => '16th May 2025', 'desc' => 'Beat the sweltering heat with Lunazul tequila. House of IRORI created La Familia Es Todo – refreshing, balanced, and invigorating. A harmonious blend of 100% agave tequila, sweet and sour fruit flavors, and cool mint, offering a moment of complete relaxation. 📍Enjoy this special cocktail at House of IRORI this April.'],
            ['thumb' => $att_wild_turkey, 'head' => 'Guestshift - Wild Turkey 8ys Takeover', 'date' => '16th May 2025', 'desc' => 'Beat the sweltering heat with Lunazul tequila. House of IRORI created La Familia Es Todo – refreshing, balanced, and invigorating. A harmonious blend of 100% agave tequila, sweet and sour fruit flavors, and cool mint, offering a moment of complete relaxation. 📍Enjoy this special cocktail at House of IRORI this April.'],
            ['thumb' => $att_wild_turkey, 'head' => 'Guestshift - Wild Turkey 8ys Takeover', 'date' => '16th May 2025', 'desc' => 'Beat the sweltering heat with Lunazul tequila. House of IRORI created La Familia Es Todo – refreshing, balanced, and invigorating. A harmonious blend of 100% agave tequila, sweet and sour fruit flavors, and cool mint, offering a moment of complete relaxation. 📍Enjoy this special cocktail at House of IRORI this April.'],
            ['thumb' => $att_upcoming, 'head' => 'Midnight Vinyl & Rare Malt Listening Session', 'date' => '14th June 2025', 'desc' => 'An unhurried late-evening session on Level Two featuring 1970s Japanese jazz pressings spun on analog turntables alongside rare single-cask pours from our reserve shelf.'],
            ['thumb' => $att_level2, 'head' => 'Tokyo Counter Takeover - Craft Highball Night', 'date' => '28th June 2025', 'desc' => 'Precision carbonation, hand-chiseled ice spears, and bespoke botanical cordials in a one-night guest bartender showcase celebrating modern Ginza highball culture at House of IRORI.'],
        ];
        irori_set_acf_meta($events_id, 'occasional_events', count($occ_events), 'field_irori_occasional_events');
        foreach ($occ_events as $i => $ev) {
            irori_set_acf_meta($events_id, "occasional_events_{$i}_thumbnail", $ev['thumb'], 'field_irori_oe_thumb');
            irori_set_acf_meta($events_id, "occasional_events_{$i}_headline", $ev['head'], 'field_irori_oe_head');
            irori_set_acf_meta($events_id, "occasional_events_{$i}_date", $ev['date'], 'field_irori_oe_date');
            irori_set_acf_meta($events_id, "occasional_events_{$i}_description", $ev['desc'], 'field_irori_oe_desc');
        }
    }

    // 4. Seed Blogs & Contact Page Banners
    if ($blogs_id) {
        irori_set_acf_meta($blogs_id, 'blogs_banner', '', 'field_irori_blogs_banner');
        irori_set_acf_meta($blogs_id, 'blogs_banner_banner', $att_blogs_banner, 'field_irori_bb_banner');
        irori_set_acf_meta($blogs_id, 'blogs_banner_headline', 'BLOGS', 'field_irori_bb_headline');
        irori_set_acf_meta($blogs_id, 'blogs_banner_section_heading', 'Latest News', 'field_irori_bb_sec_head');
    }
    if ($contact_id) {
        irori_set_acf_meta($contact_id, 'contact_banner', '', 'field_irori_contact_banner');
        irori_set_acf_meta($contact_id, 'contact_banner_banner', $att_glimpse, 'field_irori_cb_banner');
        irori_set_acf_meta($contact_id, 'contact_banner_headline', 'A GLIMPSE INSIDE', 'field_irori_cb_headline');
        irori_set_acf_meta($contact_id, 'contact_banner_subtitle', "Warm light on dark wood. Handcrafted details.\nThe kind of room that feels good to be in.", 'field_irori_cb_sub');
    }

    // 5. Seed Irori Settings (ACF Options Page matching Header & Footer across all 5 pages)
    $d_set = irori_default_settings();
    update_option('irori_site_settings', $d_set);
    irori_set_acf_option('open_time_text', "<h2>A GLIMPSE INSIDE</h2>\n<p>Warm light on dark wood. Handcrafted details.<br>The kind of room that feels good to be in.</p>", 'field_irori_cfg_open_text');
    irori_set_acf_option('open_time_image', $att_glimpse, 'field_irori_cfg_open_img');
    irori_set_acf_option('brand_tagline', 'A cinematic cocktail experience where brass, steam, and spirits converge.', 'field_irori_cfg_tagline');
    irori_set_acf_option('address', "Level 3, 17/3 Nguyen Thi Minh Khai,\nSai Gon Ward, HCMC, Vietnam", 'field_irori_cfg_address');
    irori_set_acf_option('phone', $d_set['phone_display'], 'field_irori_cfg_phone');
    irori_set_acf_option('email', $d_set['email'], 'field_irori_cfg_email');
    irori_set_acf_option('reservations_url', $d_set['reservations_url'], 'field_irori_cfg_res_url');
    irori_set_acf_option('google_map_link', $d_set['google_maps_url'], 'field_irori_cfg_gmap_link');
    irori_set_acf_option('google_map_embed_link', '10.78492,106.7006,16', 'field_irori_cfg_gmap_embed');
    irori_set_acf_option('after_phone_message', $d_set['disclaimer'], 'field_irori_cfg_note');
    irori_set_acf_option('copyright_text', '© 2026 House of IRORI', 'field_irori_cfg_copy');
    $socials = [
        ['name' => 'Facebook', 'link' => $d_set['facebook_url']],
        ['name' => 'Instagram', 'link' => $d_set['instagram_url']],
        ['name' => 'TikTok', 'link' => $d_set['tiktok_url']],
        ['name' => 'Threads', 'link' => $d_set['threads_url']],
    ];
    irori_set_acf_option('social_links', count($socials), 'field_irori_cfg_socials');
    foreach ($socials as $i => $sc) {
        irori_set_acf_option("social_links_{$i}_name", $sc['name'], 'field_irori_soc_name');
        irori_set_acf_option("social_links_{$i}_link", $sc['link'], 'field_irori_soc_link');
    }

    // 6. Seed Blog Categories & 9 Blog Posts matching blogs.html, index.html & blog-detail.html 1:1
    foreach (['interior' => 'interior', 'concept' => 'Concept', 'club' => 'Club'] as $slug => $name) {
        if (!term_exists($slug, 'category')) {
            wp_insert_term($name, 'category', ['slug' => $slug]);
        }
    }
    $cat_ids = [];
    foreach (['interior', 'concept', 'club'] as $slug) {
        $term = get_term_by('slug', $slug, 'category');
        if ($term) {
            $cat_ids[$slug] = (int) $term->term_id;
        }
    }

    $elle_block = '<p>Được khởi xướng vào 3 năm trước với mong muốn đem đến cho công chúng yêu nghệ thuật và thiết kế một trải nghiệm thưởng lãm mới mẻ, ELLE Decoration Pop-Up Office dần tạo được dấu ấn trong tâm trí cộng đồng sáng tạo. Sự trở lại của Pop-Up Office với chủ đề “The Making of Joy” - Khởi Tạo Niềm Vui đánh dấu chuỗi hoạt động trong vòng 3 tuần từ 20/12/2024 đến 10/01/2025, tại TĐX Ice Factory quận 1, TP.HCM.</p>'
        . '<p>Về chuỗi hoạt động “Văn phòng Sáng Tạo” Pop-Up Office</p>'
        . '<p>Pop-Up là một hình mẫu chương trình do ELLE Decoration khởi xướng từ năm 2022, với mong muốn tạo ra một không gian kết nối cộng đồng yêu nghệ thuật, thiết kế, văn hoá và phong cách sống.</p>'
        . '<p>Cốt lõi của chuỗi hoạt động chính là không gian văn phòng của đội ngũ tòa soạn, nơi làm việc, hội họp, phỏng vấn, sản xuất hình ảnh, tiếp đãi đối tác và trò chuyện cùng các nhân vật của ấn phẩm. Với Pop-Up Office, đội ngũ ELLE Decoration được toàn quyền cải tạo và biến đổi một không gian có sẵn thành văn phòng của mình, với sự góp sức của các đối tác về sản phẩm nội thất, vật liệu, chiếu sáng, công nghệ, vv.. nhằm tạo ra một trải nghiệm tổng hòa giữa nội thất, kiến trúc, nghệ thuật và văn hoá.</p>';
    $elle_prose = $elle_block
        . '<figure class="blog-article__figure"><img src="assets/images/blog-thumb-1.jpg" alt="Craftsmanship and cocktail preparation at House of IRORI" loading="lazy" width="900" height="630"></figure>'
        . '<h2 class="blog-article__subheading">Why We Work with Spirit, Not Against It</h2>'
        . '<div class="blog-article__prose">' . $elle_block . '</div>';

    $articles_seed = [
        ['title' => 'Why We Work with Spirit, Not Against It', 'slug' => 'why-we-work-with-spirit-not-against-it', 'cat' => 'interior', 'date' => '2026-04-14 18:09:00', 'img' => 'assets/images/blog-thumb-1.jpg', 'att' => $att_blog_thumb, 'sub' => 'Why We Work with Spirit, Not Against It', 'excerpt' => 'A conversation about respect, slow curation, and why every ingredient has a story to tell…'],
        ['title' => 'ELLE Decoration PopUp Office - DỰ ÁN VĂN PHÒNG SÁNG TẠO DÀNH CHO CỘNG ĐỒNG', 'slug' => 'elle-decoration-popup-office', 'cat' => 'concept', 'date' => '2026-04-14 18:08:00', 'img' => 'assets/images/blog-thumb-1.jpg', 'att' => $att_blog_thumb, 'sub' => 'Why We Work with Spirit, Not Against It', 'excerpt' => 'A conversation about respect, slow curation, and why every ingredient has a story to tell…'],
        ['title' => 'Why We Work with Spirit, Not Against It', 'slug' => 'why-we-work-with-spirit-club', 'cat' => 'club', 'date' => '2026-04-14 18:07:00', 'img' => 'assets/images/blog-thumb-1.jpg', 'att' => $att_blog_thumb, 'sub' => 'Why We Work with Spirit, Not Against It', 'excerpt' => 'A conversation about respect, slow curation, and why every ingredient has a story to tell…'],
        ['title' => 'Why We Work with Spirit, Not Against It', 'slug' => 'why-we-work-with-spirit-interior-2', 'cat' => 'interior', 'date' => '2026-04-14 18:06:00', 'img' => 'assets/images/blog-thumb-1.jpg', 'att' => $att_blog_thumb, 'sub' => 'Why We Work with Spirit, Not Against It', 'excerpt' => 'A conversation about respect, slow curation, and why every ingredient has a story to tell…'],
        ['title' => 'Why We Work with Spirit, Not Against It', 'slug' => 'why-we-work-with-spirit-concept-2', 'cat' => 'concept', 'date' => '2026-04-14 18:05:00', 'img' => 'assets/images/blog-thumb-1.jpg', 'att' => $att_blog_thumb, 'sub' => 'Why We Work with Spirit, Not Against It', 'excerpt' => 'A conversation about respect, slow curation, and why every ingredient has a story to tell…'],
        ['title' => 'Why We Work with Spirit, Not Against It', 'slug' => 'why-we-work-with-spirit-club-2', 'cat' => 'club', 'date' => '2026-04-14 18:04:00', 'img' => 'assets/images/blog-thumb-1.jpg', 'att' => $att_blog_thumb, 'sub' => 'Why We Work with Spirit, Not Against It', 'excerpt' => 'A conversation about respect, slow curation, and why every ingredient has a story to tell…'],
        ['title' => 'Architecture of Shadow: Designing Level One & Level Two', 'slug' => 'architecture-of-shadow-designing-level-one-and-level-two', 'cat' => 'interior', 'date' => '2026-03-28 18:00:00', 'img' => 'assets/images/collage-interior.jpg', 'att' => $att_interior, 'sub' => 'Two Floors, One Cohesive Philosophy', 'excerpt' => 'How aged cedar, warm tungsten glow, and Showa-era cinema inspired two distinct floors…'],
        ['title' => 'The Ritual of Hand-Carved Ice at the Counter', 'slug' => 'the-ritual-of-hand-carved-ice-at-the-counter', 'cat' => 'concept', 'date' => '2026-03-10 18:00:00', 'img' => 'assets/images/level2-bar.jpg', 'att' => $att_level2, 'sub' => 'Craftsmanship Behind the Upstairs Counter', 'excerpt' => 'Why crystal-clear block ice, dilution speed, and temperature shape every pour at IRORI…'],
        ['title' => 'Late-Night Listening: Vinyl & Spirits in Saigon', 'slug' => 'late-night-listening-vinyl-and-spirits-in-saigon', 'cat' => 'club', 'date' => '2026-02-22 18:00:00', 'img' => 'assets/images/level1-alley.jpg', 'att' => $att_level1, 'sub' => 'Analog Soundscapes After Midnight', 'excerpt' => 'Inside our analog sound curation that turns an unhurried evening into a lasting memory…'],
    ];

    foreach ($articles_seed as $a) {
        $existing_post = get_page_by_path($a['slug'], OBJECT, 'post');
        $pid = $existing_post ? (int) $existing_post->ID : wp_insert_post([
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_title' => $a['title'],
            'post_name' => $a['slug'],
            'post_date' => $a['date'],
            'post_excerpt' => $a['excerpt'],
            'post_content' => $elle_prose,
            'post_category' => isset($cat_ids[$a['cat']]) ? [$cat_ids[$a['cat']]] : [],
        ]);
        if ($pid && !is_wp_error($pid)) {
            update_post_meta($pid, '_irori_subheading', $a['sub']);
            update_post_meta($pid, '_irori_fallback_img', $a['img']);
            if ($a['att']) {
                set_post_thumbnail($pid, $a['att']);
            }
        }
    }

    // 7. Seed Google Reviews (CPT: google_reviews, matching houseofmerlin)
    $reviews_seed = [
        ['name' => 'Kenji Takahashi', 'initial' => 'K', 'iso' => '2026-09-01', 'time' => '1 month ago', 'text' => 'Stepping through the narrow 70s Tokyo alley into the upstairs counter feels like entering another world. The smoked Old Fashioned and quiet omotenashi hospitality are unmatched in Saigon.'],
        ['name' => 'Minh Anh Nguyen', 'initial' => 'M', 'iso' => '2026-08-15', 'time' => '2 months ago', 'text' => 'Warm tungsten light on dark timber, hand-carved ice, and bartenders who treat every pour like a conversation. Easily our favorite hidden sanctuary on Nguyen Thi Minh Khai.'],
        ['name' => 'David Laurent', 'initial' => 'D', 'iso' => '2026-07-20', 'time' => '3 months ago', 'text' => 'Two floors with one cohesive philosophy. Downstairs feels like a classic Showa-era cinema set, and Level Two is an intimate cocktail bar with extraordinary Japanese spirits.'],
        ['name' => 'Sora Watanabe', 'initial' => 'S', 'iso' => '2026-06-18', 'time' => '4 months ago', 'text' => 'Impeccable balance in every signature cocktail. The yuzu highball and charcoal-aged negroni pair effortlessly with the quiet jazz and warm cedar woodwork.'],
        ['name' => 'Hoang Nam Tran', 'initial' => 'H', 'iso' => '2026-05-29', 'time' => '5 months ago', 'text' => 'A rare place in Saigon where conversation comes first. Thoughtful service, artisanal pizza downstairs, and world-class bespoke pours at the upstairs bar.'],
    ];
    foreach ($reviews_seed as $i => $rv) {
        $pid = wp_insert_post([
            'post_type' => 'google_reviews',
            'post_status' => 'publish',
            'post_title' => $rv['name'],
            'post_content' => $rv['text'],
            'menu_order' => $i + 1,
        ]);
        if ($pid && !is_wp_error($pid)) {
            update_post_meta($pid, '_irori_rating', 5);
            update_post_meta($pid, '_irori_date_iso', $rv['iso']);
            update_post_meta($pid, '_irori_time_label', $rv['time']);
            update_post_meta($pid, '_irori_initial', $rv['initial']);
        }
    }

    // Synchronize Admin Menu Editor (ws_menu_editor) Pages submenu IDs if present
    $wme = get_option('ws_menu_editor');
    if (is_array($wme) && isset($wme['custom_menu']['tree']['edit.php?post_type=page']['items'])) {
        $page_map = [
            'Welcome Page' => $welcome_id,
            'Menu Page' => $menu_id,
            'Events Page' => $events_id,
            'Blogs Page' => $blogs_id,
            'Contact Page' => $contact_id,
        ];
        $new_items = [];
        $tpl_item = null;
        $has_blogs_item = false;
        foreach ($wme['custom_menu']['tree']['edit.php?post_type=page']['items'] as $it) {
            $t = !empty($it['menu_title']) ? $it['menu_title'] : ($it['defaults']['menu_title'] ?? '');
            if ($t === 'Blogs Page') {
                $has_blogs_item = true;
            }
            if (isset($page_map[$t]) && $page_map[$t] > 0) {
                $it['file'] = 'post.php?post=' . $page_map[$t] . '&action=edit';
                if (isset($it['defaults']) && is_array($it['defaults'])) {
                    $it['defaults']['file'] = $it['file'];
                }
                $tpl_item = $it;
            }
            $new_items[] = $it;
        }
        if (!$has_blogs_item && $blogs_id > 0 && $tpl_item) {
            $blog_it = $tpl_item;
            $blog_it['menu_title'] = 'Blogs Page';
            $blog_it['file'] = 'post.php?post=' . $blogs_id . '&action=edit';
            if (isset($blog_it['defaults']) && is_array($blog_it['defaults'])) {
                $blog_it['defaults']['menu_title'] = 'Blogs Page';
                $blog_it['defaults']['file'] = $blog_it['file'];
            }
            array_splice($new_items, 4, 0, [$blog_it]);
        }
        $wme['custom_menu']['tree']['edit.php?post_type=page']['items'] = $new_items;
        update_option('ws_menu_editor', $wme);
    }

    if (function_exists('irori_register_acf_field_groups')) {
        irori_register_acf_field_groups();
    }
    update_option('irori_seeded_v3', 1);
    delete_transient(IRORI_CMS_CACHE_KEY);
}

/**
 * 7. REST API Endpoints (/wp-json/irori/v1/bootstrap & /wp-json/irori/v1/article/<slug>)
 */
function irori_resolve_post_image(int $post_id, string $default = 'assets/images/blog-thumb-1.jpg'): array {
    $thumb_id = get_post_thumbnail_id($post_id);
    if ($thumb_id) {
        $url = wp_get_attachment_image_url($thumb_id, 'large') ?: wp_get_attachment_url($thumb_id);
        $alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
        if ($url) {
            $rel = ltrim(wp_make_link_relative($url), '/');
            return ['url' => $rel !== '' ? $rel : $url, 'alt' => $alt ?: get_the_title($post_id)];
        }
    }
    $fb = get_post_meta($post_id, '_irori_fallback_img', true);
    return ['url' => $fb ?: $default, 'alt' => get_the_title($post_id)];
}

function irori_format_article_summary(\WP_Post $p): array {
    $img = irori_resolve_post_image($p->ID, 'assets/images/blog-thumb-1.jpg');
    $terms = get_the_category($p->ID);
    $cat_slug = 'interior';
    $cat_label = 'interior';
    if (!empty($terms) && $terms[0]->slug !== 'uncategorized') {
        $cat_slug = strtolower($terms[0]->slug);
        $cat_label = $terms[0]->name;
    }
    $excerpt = $p->post_excerpt !== '' ? $p->post_excerpt : wp_trim_words(wp_strip_all_tags($p->post_content), 24, '…');
    $ts = strtotime($p->post_date) ?: time();
    return [
        'id' => (int) $p->ID,
        'slug' => $p->post_name,
        'title' => html_entity_decode($p->post_title, ENT_QUOTES, 'UTF-8'),
        'dateIso' => gmdate('Y-m-d', $ts),
        'dateLabel' => strtoupper(gmdate('F j, Y', $ts)),
        'category' => $cat_slug,
        'categoryLabel' => html_entity_decode($cat_label, ENT_QUOTES, 'UTF-8'),
        'excerpt' => html_entity_decode(wp_strip_all_tags($excerpt), ENT_QUOTES, 'UTF-8'),
        'image' => $img['url'],
        'alt' => html_entity_decode($img['alt'], ENT_QUOTES, 'UTF-8'),
    ];
}

function irori_format_article_detail(\WP_Post $p): array {
    $base = irori_format_article_summary($p);
    $ts = strtotime($p->post_date) ?: time();
    $terms = get_the_category($p->ID);
    $cats = [];
    foreach ($terms as $t) {
        if ($t->slug !== 'uncategorized') {
            $cats[] = ['slug' => strtolower($t->slug), 'name' => html_entity_decode($t->name, ENT_QUOTES, 'UTF-8')];
        }
    }
    if (empty($cats)) {
        $cats = [
            ['slug' => 'interior', 'name' => 'interior'],
            ['slug' => 'concept', 'name' => 'Concept'],
            ['slug' => 'club', 'name' => 'Club'],
        ];
    }
    $base['dateDetailLabel'] = gmdate('F j, Y', $ts);
    $base['subheading'] = get_post_meta($p->ID, '_irori_subheading', true) ?: '';
    $base['contentHtml'] = wp_kses_post(apply_filters('the_content', $p->post_content));
    $base['categories'] = $cats;
    return $base;
}

function irori_resolve_gallery($val, array $fallbacks): array {
    $out = [];
    if (is_array($val)) {
        foreach ($val as $idx => $att) {
            $u = irori_resolve_media_value($att, $fallbacks[$idx] ?? ($fallbacks[0] ?? ''));
            if ($u !== '') {
                $out[] = ['image' => $u, 'alt' => 'House of IRORI Gallery Slide ' . ($idx + 1)];
            }
        }
    }
    if (empty($out)) {
        foreach ($fallbacks as $idx => $fb) {
            $out[] = ['image' => $fb, 'alt' => 'House of IRORI Gallery Slide ' . ($idx + 1)];
        }
    }
    return $out;
}

function irori_build_bootstrap_payload(): array {
    $cached = get_transient(IRORI_CMS_CACHE_KEY);
    if (is_array($cached)) {
        return $cached;
    }

    // 1. Settings from Irori Settings (ACF Options Page options_*) with defaults
    $settings = wp_parse_args(get_option('irori_site_settings', []), irori_default_settings());
    $opt_phone = get_option('options_phone', '');
    if (is_string($opt_phone) && trim($opt_phone) !== '') {
        $settings['phone_display'] = trim($opt_phone);
        $settings['phone_tel'] = preg_replace('/[^0-9+]/', '', $opt_phone);
    }
    $opt_email = get_option('options_email', '');
    if (is_string($opt_email) && trim($opt_email) !== '') {
        $settings['email'] = sanitize_email($opt_email);
    }
    $opt_res = get_option('options_reservations_url', '');
    if (is_string($opt_res) && trim($opt_res) !== '') {
        $settings['reservations_url'] = trim($opt_res);
    }
    $opt_gmap = get_option('options_google_map_link', '');
    if (is_string($opt_gmap) && trim($opt_gmap) !== '') {
        $settings['google_maps_url'] = trim($opt_gmap);
    }
    $opt_coords = trim((string) get_option('options_google_map_embed_link', ''));
    if ($opt_coords !== '' && preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)(?:\s*,\s*(\d+))?\s*$/', $opt_coords, $cm)) {
        $settings['map_lat'] = $cm[1];
        $settings['map_lng'] = $cm[2];
        if (!empty($cm[3])) {
            $settings['map_zoom'] = $cm[3];
        }
    }
    $opt_addr = get_option('options_address', '');
    if (is_string($opt_addr) && trim($opt_addr) !== '') {
        $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|<br\s*\/?>/i', wp_strip_all_tags($opt_addr)))));
        if (!empty($lines)) {
            $settings['address_line1'] = $lines[0];
            $settings['address_line2'] = $lines[1] ?? '';
        }
    }
    $opt_note = get_option('options_after_phone_message', '');
    if (is_string($opt_note) && trim($opt_note) !== '') {
        $settings['disclaimer'] = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($opt_note)));
    }
    $settings['brand_tagline'] = trim((string) get_option('options_brand_tagline', '')) ?: 'A cinematic cocktail experience where brass, steam, and spirits converge.';
    $settings['copyright'] = trim((string) get_option('options_copyright_text', '')) ?: '© 2026 House of IRORI';
    $settings['glimpse_image'] = irori_resolve_media_value(get_option('options_open_time_image', ''), 'assets/images/footer-glimpse-bg.jpg');
    $opt_open_txt = trim((string) get_option('options_open_time_text', ''));
    if ($opt_open_txt !== '') {
        if (preg_match('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/is', $opt_open_txt, $hm)) {
            $settings['glimpse_title'] = trim(wp_strip_all_tags($hm[1]));
        }
        if (preg_match('/<p[^>]*>(.*?)<\/p>/is', $opt_open_txt, $pm)) {
            $sub_lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|<br\s*\/?>/i', wp_strip_all_tags($pm[1])))));
            if (!empty($sub_lines)) {
                $settings['glimpse_subtitle'] = implode("\n", $sub_lines);
            }
        }
    }

    $contact_page = get_page_by_path('contact', OBJECT, 'page');
    $contact_id = $contact_page ? (int) $contact_page->ID : 0;
    $cb_img = $contact_id ? irori_resolve_media_value(get_post_meta($contact_id, 'contact_banner_banner', true), '') : '';
    if ($cb_img !== '' && $settings['glimpse_image'] === 'assets/images/footer-glimpse-bg.jpg') {
        $settings['glimpse_image'] = $cb_img;
    }
    $cb_head = $contact_id ? trim((string) get_post_meta($contact_id, 'contact_banner_headline', true)) : '';
    if ($cb_head !== '' && $cb_head !== 'A GLIMPSE INSIDE') {
        $settings['glimpse_title'] = $cb_head;
    }
    $cb_sub = $contact_id ? trim((string) get_post_meta($contact_id, 'contact_banner_subtitle', true)) : '';
    if ($cb_sub !== '' && strpos($cb_sub, 'Warm light on dark wood.') !== 0) {
        $settings['glimpse_subtitle'] = $cb_sub;
    }

    $soc_count = (int) get_option('options_social_links', 0);
    for ($i = 0; $i < $soc_count; $i++) {
        $sname = strtolower(trim((string) get_option("options_social_links_{$i}_name", '')));
        $slink = trim((string) get_option("options_social_links_{$i}_link", ''));
        if ($slink !== '' && isset($settings[$sname . '_url'])) {
            $settings[$sname . '_url'] = $slink;
        }
    }

    // 1B. Welcome Page (index.html sections)
    $welcome_page = get_page_by_path('welcome', OBJECT, 'page');
    $welcome_id = $welcome_page ? (int) $welcome_page->ID : 0;
    $w_score = $welcome_id ? trim((string) get_post_meta($welcome_id, 'bottom_section_rating_score', true)) : '';
    if ($w_score !== '') {
        $settings['rating_score'] = $w_score;
    }
    $settings['txt'] = [
        ['.footer-glimpse__title', $settings['glimpse_title']],
        ['.footer-col__tagline', $settings['brand_tagline']],
        ['.footer-bottom__disclaimer', $settings['disclaimer']],
        ['.footer-bottom__copyright', $settings['copyright']],
        ['.reviews-header__score', $settings['rating_score']],
    ];
    $settings['soc'] = [
        'Facebook' => $settings['facebook_url'],
        'Instagram' => $settings['instagram_url'],
        'TikTok' => $settings['tiktok_url'],
        'Threads' => $settings['threads_url'],
    ];

    $welcome = [
        'banner' => irori_resolve_media_value($welcome_id ? get_post_meta($welcome_id, 'welcome_banner_banner', true) : '', 'assets/images/hero-bg.jpg'),
        'headline' => ($welcome_id ? trim((string) get_post_meta($welcome_id, 'welcome_banner_headline', true)) : '') ?: "A QUIET CORNER OF JAPAN,\nTUCKED INTO\nTHE HEART OF SAIGON",
        'sub_headline' => ($welcome_id ? trim((string) get_post_meta($welcome_id, 'welcome_banner_sub_headline', true)) : '') ?: 'DISCOVER THE STORY',
        'showcase_image' => irori_resolve_media_value($welcome_id ? get_post_meta($welcome_id, 'welcome_banner_showcase_image', true) : '', 'assets/images/hero-bartender-framed.jpg'),
        'story_bg' => irori_resolve_media_value($welcome_id ? get_post_meta($welcome_id, 'story_section_bg_image', true) : '', 'assets/images/story-bg.jpg'),
        'story_headline' => ($welcome_id ? trim((string) get_post_meta($welcome_id, 'story_section_headline', true)) : '') ?: 'HIDDEN IN PLAIN SIGHT',
        'story_content' => wp_kses_post(wpautop(($welcome_id ? trim((string) get_post_meta($welcome_id, 'story_section_content', true)) : '') ?: "<p>House of IRORI isn't a cocktail bar. It's a moment - a small corner where time moves differently. Step down a narrow alley.</p><p>Warm tungsten light spills across dark wood. A bartender works with quiet precision. Ice hits glass. Smoke rises slowly. This is omotenashi. Japanese hospitality in its purest form. Every detail whispered, never shouted.</p>")),
        'philosophy_headline' => ($welcome_id ? trim((string) get_post_meta($welcome_id, 'philosophy_section_headline', true)) : '') ?: "THE SPACE BETWEEN,\nSTILLNESS AND BEAUTY",
        'philosophy_sub' => ($welcome_id ? trim((string) get_post_meta($welcome_id, 'philosophy_section_sub_headline', true)) : '') ?: 'TWO FLOORS. ONE PHILOSOPHY.',
        'philosophy_interior' => irori_resolve_media_value($welcome_id ? get_post_meta($welcome_id, 'philosophy_section_interior_image', true) : '', 'assets/images/collage-interior.jpg'),
        'philosophy_kimono' => irori_resolve_media_value($welcome_id ? get_post_meta($welcome_id, 'philosophy_section_kimono_image', true) : '', 'assets/images/collage-kimono-framed.jpg'),
        'level1_eyebrow' => ($welcome_id ? trim((string) get_post_meta($welcome_id, 'level_one_eyebrow', true)) : '') ?: 'LEVEL ONE:',
        'level1_headline' => ($welcome_id ? trim((string) get_post_meta($welcome_id, 'level_one_headline', true)) : '') ?: 'THE ALLEY',
        'level1_desc' => ($welcome_id ? trim((string) get_post_meta($welcome_id, 'level_one_description', true)) : '') ?: "A cinematic passage. Narrow. Intimate. Like stepping into a 70s Tokyo film. Dark wood weathered by time. Warm lighting that doesn't reveal everything. Walls that feel like they have stories. This is where you arrive.",
        'level1_slider' => irori_resolve_gallery($welcome_id ? get_post_meta($welcome_id, 'level_one_slider', true) : [], ['assets/images/level1-alley.jpg', 'assets/images/collage-interior.jpg', 'assets/images/floating-banner.jpg']),
        'level2_eyebrow' => ($welcome_id ? trim((string) get_post_meta($welcome_id, 'level_two_eyebrow', true)) : '') ?: 'LEVEL TWO:',
        'level2_headline' => ($welcome_id ? trim((string) get_post_meta($welcome_id, 'level_two_headline', true)) : '') ?: 'THE BAR',
        'level2_desc' => ($welcome_id ? trim((string) get_post_meta($welcome_id, 'level_two_description', true)) : '') ?: 'The heart of the experience. A small counter where the bartender becomes the story. Watch spirit and ice transform into something sacred. Every pour is a conversation.',
        'level2_sub' => ($welcome_id ? trim((string) get_post_meta($welcome_id, 'level_two_sub_text', true)) : '') ?: 'This is where you stay.',
        'level2_slider' => irori_resolve_gallery($welcome_id ? get_post_meta($welcome_id, 'level_two_slider', true) : [], ['assets/images/level2-bar.jpg', 'assets/images/footer-glimpse-bg.jpg', 'assets/images/collage-interior.jpg']),
        'map_image' => irori_resolve_media_value($welcome_id ? get_post_meta($welcome_id, 'bottom_section_map_image', true) : '', 'assets/images/saigon-map.jpg'),
        'atmosphere_image' => irori_resolve_media_value($welcome_id ? get_post_meta($welcome_id, 'bottom_section_atmosphere_image', true) : '', 'assets/images/floating-banner.jpg'),
        'latest_news_heading' => ($welcome_id ? trim((string) get_post_meta($welcome_id, 'bottom_section_latest_news_heading', true)) : '') ?: 'LATEST NEWS',
    ];
    $welcome['nl'] = [
        ['.hero__title', $welcome['headline']],
        ['.philosophy__card-title', $welcome['philosophy_headline']],
    ];
    $welcome['img'] = [
        ['.hero__bg-img', $welcome['banner']],
        ['.hero-showcase__frame img', $welcome['showcase_image']],
        ['.story__bg img', $welcome['story_bg']],
        ['.philosophy__interior img', $welcome['philosophy_interior']],
        ['.philosophy__kimono img', $welcome['philosophy_kimono']],
        ['.reviews-map__fallback', $welcome['map_image']],
        ['#atmosphere-strip img', $welcome['atmosphere_image']],
    ];
    $welcome['txt'] = [
        ['.hero__discover span', $welcome['sub_headline']],
        ['.story__title', $welcome['story_headline']],
        ['.philosophy__card-ribbon span', $welcome['philosophy_sub']],
        ['#level-one .split-level__eyebrow', $welcome['level1_eyebrow']],
        ['#level-one .split-level__heading', $welcome['level1_headline']],
        ['#level-one .split-level__desc', $welcome['level1_desc']],
        ['#level-two .split-level__eyebrow', $welcome['level2_eyebrow']],
        ['#level-two .split-level__heading', $welcome['level2_headline']],
        ['#level-two .split-level__desc p', $welcome['level2_desc']],
        ['#level-two .split-level__sub', $welcome['level2_sub']],
        ['#latest-news .latest-news__title', $welcome['latest_news_heading']],
    ];

    // 2. Menu Page: Main Menus (PDF repeater) & Occasional Menus (repeater)
    $menu_page = get_page_by_path('menu', OBJECT, 'page');
    $menu_id = $menu_page ? (int) $menu_page->ID : 0;

    $main_menus = [];
    $mm_count = $menu_id ? (int) get_post_meta($menu_id, 'main_menus', true) : 0;
    for ($i = 0; $i < $mm_count; $i++) {
        $m_name = trim((string) get_post_meta($menu_id, "main_menus_{$i}_menu_name", true));
        $m_pdf_raw = get_post_meta($menu_id, "main_menus_{$i}_pdf", true);
        $m_pdf = irori_resolve_media_value($m_pdf_raw, $i === 0 ? 'assets/menus/irori-cocktail-menu.pdf' : 'assets/menus/irori-food-menu.pdf');
        if ($m_name !== '' || $m_pdf !== '') {
            $key = $i === 0 ? 'cocktail' : ($i === 1 ? 'food' : 'tab-' . $i);
            $main_menus[] = [
                'key' => $key,
                'title' => $m_name ?: ('Menu ' . ($i + 1)),
                'pdf' => $m_pdf,
            ];
        }
    }
    if (empty($main_menus)) {
        $main_menus = [
            ['key' => 'cocktail', 'title' => 'Our top cocktail', 'pdf' => 'assets/menus/irori-cocktail-menu.pdf'],
            ['key' => 'food', 'title' => 'Food (Pizza & Side dishes)', 'pdf' => 'assets/menus/irori-food-menu.pdf'],
        ];
    }

    $occasional_menus = [];
    $om_count = $menu_id ? (int) get_post_meta($menu_id, 'occasional_menus', true) : 0;
    for ($i = 0; $i < $om_count; $i++) {
        $head = trim((string) get_post_meta($menu_id, "occasional_menus_{$i}_headline", true));
        $desc = trim((string) get_post_meta($menu_id, "occasional_menus_{$i}_description", true));
        $date_lbl = trim((string) get_post_meta($menu_id, "occasional_menus_{$i}_date", true)) ?: '16th May 2025';
        $thumb_raw = get_post_meta($menu_id, "occasional_menus_{$i}_thumbnail", true);
        $img_url = irori_resolve_media_value($thumb_raw, 'assets/images/event-wild-turkey.jpg');
        if ($head !== '') {
            $ts = strtotime($date_lbl) ?: time();
            $occasional_menus[] = [
                'id' => $i + 1,
                'title' => $head,
                'dateIso' => gmdate('Y-m-d', $ts),
                'dateLabel' => $date_lbl,
                'desc' => $desc,
                'image' => $img_url,
                'alt' => $head,
            ];
        }
    }

    // 3. Events Page: Occasional Events & Other events (Up Coming slider)
    $events_page = get_page_by_path('events', OBJECT, 'page');
    $events_id = $events_page ? (int) $events_page->ID : 0;

    $events = [];
    $oe_count = $events_id ? (int) get_post_meta($events_id, 'occasional_events', true) : 0;
    for ($i = 0; $i < $oe_count; $i++) {
        $head = trim((string) get_post_meta($events_id, "occasional_events_{$i}_headline", true));
        $desc = trim((string) get_post_meta($events_id, "occasional_events_{$i}_description", true));
        $date_lbl = trim((string) get_post_meta($events_id, "occasional_events_{$i}_date", true)) ?: '16th May 2025';
        $thumb_raw = get_post_meta($events_id, "occasional_events_{$i}_thumbnail", true);
        $img_url = irori_resolve_media_value($thumb_raw, 'assets/images/event-wild-turkey.jpg');
        if ($head !== '') {
            $ts = strtotime($date_lbl) ?: time();
            $events[] = [
                'id' => $i + 1,
                'title' => $head,
                'dateIso' => gmdate('Y-m-d', $ts),
                'dateLabel' => $date_lbl,
                'desc' => $desc,
                'image' => $img_url,
                'alt' => $head,
            ];
        }
    }

    $upcoming_slides = [];
    $ote_count = $events_id ? (int) get_post_meta($events_id, 'other_events', true) : 0;
    for ($i = 0; $i < $ote_count; $i++) {
        $thumb_raw = get_post_meta($events_id, "other_events_{$i}_thumbnail", true);
        $img_url = irori_resolve_media_value($thumb_raw, 'assets/images/event-upcoming-featured.jpg');
        if ($img_url !== '') {
            $upcoming_slides[] = [
                'id' => $i + 1,
                'title' => 'House of IRORI Upcoming Event ' . ($i + 1),
                'image' => $img_url,
                'alt' => 'House of IRORI Upcoming Event ' . ($i + 1),
            ];
        }
    }

    // 4. Google Reviews (CPT: google_reviews)
    $rv_posts = get_posts([
        'post_type' => 'google_reviews',
        'post_status' => 'publish',
        'has_password' => false,
        'numberposts' => 30,
        'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC'],
    ]);
    $reviews = [];
    foreach ($rv_posts as $p) {
        $author = html_entity_decode(get_the_title($p), ENT_QUOTES, 'UTF-8');
        $init = get_post_meta($p->ID, '_irori_initial', true);
        if (!$init && $author !== '') {
            $init = mb_strtoupper(mb_substr($author, 0, 1, 'UTF-8'), 'UTF-8');
        }
        $reviews[] = [
            'id' => (int) $p->ID,
            'author' => $author,
            'initial' => $init ?: 'G',
            'rating' => max(1, min(5, (int) (get_post_meta($p->ID, '_irori_rating', true) ?: 5))),
            'dateIso' => get_post_meta($p->ID, '_irori_date_iso', true) ?: gmdate('Y-m-d', strtotime($p->post_date)),
            'timeLabel' => get_post_meta($p->ID, '_irori_time_label', true) ?: 'Recently',
            'text' => html_entity_decode(wp_strip_all_tags($p->post_content), ENT_QUOTES, 'UTF-8'),
        ];
    }

    // 5. Blog Articles (CPT: post)
    $blogs_page = get_page_by_path('blogs', OBJECT, 'page');
    $blogs_id = $blogs_page ? (int) $blogs_page->ID : 0;

    $banners = [
        'menu' => [
            'banner' => irori_resolve_media_value($menu_id ? get_post_meta($menu_id, 'menu_banner_banner', true) : '', 'assets/images/hero-menu.jpg'),
            'headline' => ($menu_id ? trim((string) get_post_meta($menu_id, 'menu_banner_headline', true)) : '') ?: 'Cocktail & Eatery Menu',
        ],
        'events' => [
            'banner' => irori_resolve_media_value($events_id ? get_post_meta($events_id, 'event_banner_banner', true) : '', 'assets/images/hero-events.jpg'),
            'headline' => ($events_id ? trim((string) get_post_meta($events_id, 'event_banner_headline', true)) : '') ?: 'Events',
            'upcoming_bg' => irori_resolve_media_value($events_id ? get_post_meta($events_id, 'event_banner_upcoming_bg', true) : '', 'assets/images/events-upcoming-banner.jpg'),
            'upcoming_heading' => ($events_id ? trim((string) get_post_meta($events_id, 'event_banner_upcoming_heading', true)) : '') ?: 'up coming',
        ],
        'blogs' => [
            'banner' => irori_resolve_media_value($blogs_id ? get_post_meta($blogs_id, 'blogs_banner_banner', true) : '', 'assets/images/hero-blogs.jpg'),
            'headline' => ($blogs_id ? trim((string) get_post_meta($blogs_id, 'blogs_banner_headline', true)) : '') ?: 'BLOGS',
            'section_heading' => ($blogs_id ? trim((string) get_post_meta($blogs_id, 'blogs_banner_section_heading', true)) : '') ?: 'Latest News',
        ],
    ];
    $banners['img'] = [
        ['#menu-hero .page-hero__bg-img', $banners['menu']['banner']],
        ['#events-hero .page-hero__bg-img', $banners['events']['banner']],
        ['.upcoming-banner__bg img', $banners['events']['upcoming_bg']],
        ['#blogs-hero .page-hero__bg-img', $banners['blogs']['banner']],
    ];
    $banners['txt'] = [
        ['#menu-hero .page-hero__title', $banners['menu']['headline']],
        ['#events-hero .page-hero__title', $banners['events']['headline']],
        ['.upcoming-banner__title', $banners['events']['upcoming_heading']],
        ['#blogs-hero .page-hero__title', $banners['blogs']['headline']],
        ['#blogs-listing .section-gold-heading', $banners['blogs']['section_heading']],
    ];

    $blog_posts = get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'has_password' => false,
        'numberposts' => 36,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
    $articles = array_map('irori_format_article_summary', $blog_posts);

    $payload = [
        'version' => 3,
        'generatedAt' => gmdate('c'),
        'settings' => $settings,
        'welcome' => $welcome,
        'banners' => $banners,
        'mainMenus' => $main_menus,
        'occasionalMenus' => $occasional_menus,
        'upcomingSlides' => $upcoming_slides,
        'events' => $events,
        'reviews' => $reviews,
        'articles' => $articles,
    ];

    set_transient(IRORI_CMS_CACHE_KEY, $payload, 10 * MINUTE_IN_SECONDS);
    return $payload;
}

add_action('rest_api_init', function (): void {
    register_rest_route('irori/v1', '/bootstrap', [
        'methods' => \WP_REST_Server::READABLE,
        'permission_callback' => '__return_true',
        'callback' => function (): \WP_REST_Response {
            $res = new \WP_REST_Response(irori_build_bootstrap_payload(), 200);
            $res->header('Cache-Control', 'public, max-age=30, stale-while-revalidate=300');
            return $res;
        },
    ]);

    register_rest_route('irori/v1', '/article/(?P<slug>[a-zA-Z0-9_%-]+)', [
        'methods' => \WP_REST_Server::READABLE,
        'permission_callback' => '__return_true',
        'callback' => function (\WP_REST_Request $req) {
            $slug = sanitize_title(rawurldecode((string) $req->get_param('slug')));
            $rev = (int) get_option('irori_cache_rev', 1);
            $ckey = 'irori_art_v' . $rev . '_' . md5($slug);
            $cached = get_transient($ckey);
            if (is_array($cached)) {
                $res = new \WP_REST_Response($cached, 200);
                $res->header('Cache-Control', 'public, max-age=30, stale-while-revalidate=300');
                return $res;
            }
            $posts = get_posts($slug === 'latest'
                ? ['post_type' => 'post', 'post_status' => 'publish', 'has_password' => false, 'numberposts' => 1, 'orderby' => 'date', 'order' => 'DESC']
                : ['name' => $slug, 'post_type' => 'post', 'post_status' => 'publish', 'has_password' => false, 'numberposts' => 1]
            );
            if (empty($posts) || post_password_required($posts[0]) || $posts[0]->post_password !== '') {
                return new \WP_Error('not_found', 'Article not found', ['status' => 404]);
            }
            $detail = irori_format_article_detail($posts[0]);
            set_transient($ckey, $detail, 10 * MINUTE_IN_SECONDS);
            $res = new \WP_REST_Response($detail, 200);
            $res->header('Cache-Control', 'public, max-age=30, stale-while-revalidate=300');
            return $res;
        },
    ]);
});
