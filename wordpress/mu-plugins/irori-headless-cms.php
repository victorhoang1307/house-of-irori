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

define('IRORI_CMS_CACHE_KEY', 'irori_bootstrap_payload_v1');

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
 * 2. Register Custom Post Types (Occasional Menus, Events, Google Reviews)
 */
add_action('init', function (): void {
    add_theme_support('post-thumbnails');

    register_post_type('irori_occasional', [
        'labels' => [
            'name' => 'Occasional Menus',
            'singular_name' => 'Occasional Menu',
            'add_new_item' => 'Add New Occasional Menu',
            'edit_item' => 'Edit Occasional Menu',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-food',
        'menu_position' => 21,
        'supports' => ['title', 'editor', 'thumbnail', 'page-attributes'],
    ]);

    register_post_type('irori_event', [
        'labels' => [
            'name' => 'Events',
            'singular_name' => 'Event',
            'add_new_item' => 'Add New Event',
            'edit_item' => 'Edit Event',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-calendar-alt',
        'menu_position' => 22,
        'supports' => ['title', 'editor', 'thumbnail', 'page-attributes'],
    ]);

    register_post_type('irori_review', [
        'labels' => [
            'name' => 'Google Reviews',
            'singular_name' => 'Google Review',
            'add_new_item' => 'Add New Review',
            'edit_item' => 'Edit Review',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-star-filled',
        'menu_position' => 23,
        'supports' => ['title', 'editor', 'page-attributes'],
    ]);
});

/**
 * 3. Custom Meta Boxes for Posts, Occasional Menus, Events, and Reviews
 */
add_action('add_meta_boxes', function (): void {
    add_meta_box('irori_post_meta', 'Article Display Options', 'irori_render_post_meta_box', 'post', 'normal', 'high');
    add_meta_box('irori_occasional_meta', 'Occasional Menu Details', 'irori_render_item_meta_box', 'irori_occasional', 'normal', 'high');
    add_meta_box('irori_event_meta', 'Event Schedule & Placement', 'irori_render_event_meta_box', 'irori_event', 'normal', 'high');
    add_meta_box('irori_review_meta', 'Review Metadata', 'irori_render_review_meta_box', 'irori_review', 'normal', 'high');
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

function irori_render_item_meta_box(\WP_Post $post): void {
    wp_nonce_field('irori_save_meta', 'irori_meta_nonce');
    $iso = get_post_meta($post->ID, '_irori_date_iso', true);
    $label = get_post_meta($post->ID, '_irori_date_label', true);
    $fb = get_post_meta($post->ID, '_irori_fallback_img', true);
    ?>
    <p>
        <label for="irori_date_iso"><strong>Event/Menu Date (YYYY-MM-DD):</strong></label><br>
        <input type="date" id="irori_date_iso" name="irori_date_iso" value="<?php echo esc_attr($iso); ?>">
    </p>
    <p>
        <label for="irori_date_label"><strong>Display Date Label:</strong></label><br>
        <input type="text" id="irori_date_label" name="irori_date_label" value="<?php echo esc_attr($label); ?>" class="regular-text" placeholder="16th May 2025">
    </p>
    <p>
        <label for="irori_fallback_img"><strong>Fallback Image Path / URL (used if Featured Image is empty):</strong></label><br>
        <input type="text" id="irori_fallback_img" name="irori_fallback_img" value="<?php echo esc_attr($fb); ?>" class="large-text" placeholder="assets/images/event-wild-turkey.jpg">
    </p>
    <?php
}

function irori_render_event_meta_box(\WP_Post $post): void {
    irori_render_item_meta_box($post);
    $placement = get_post_meta($post->ID, '_irori_placement', true) ?: 'occasional';
    ?>
    <p>
        <label for="irori_placement"><strong>Display Section on Events Page:</strong></label><br>
        <select id="irori_placement" name="irori_placement">
            <option value="occasional" <?php selected($placement, 'occasional'); ?>>OCCASIONAL Grid (Bottom 2x2 Grid)</option>
            <option value="upcoming" <?php selected($placement, 'upcoming'); ?>>UP COMING Carousel (Top Featured Banner)</option>
            <option value="both" <?php selected($placement, 'both'); ?>>Both UP COMING &amp; OCCASIONAL</option>
        </select>
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
        'irori_date_iso' => '_irori_date_iso',
        'irori_date_label' => '_irori_date_label',
        'irori_fallback_img' => '_irori_fallback_img',
        'irori_time_label' => '_irori_time_label',
        'irori_initial' => '_irori_initial',
    ];
    foreach ($text_fields as $input => $meta_key) {
        if (isset($_POST[$input])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$input])));
        }
    }
    if (isset($_POST['irori_placement'])) {
        $pl = sanitize_key(wp_unslash($_POST['irori_placement']));
        if (in_array($pl, ['occasional', 'upcoming', 'both'], true)) {
            update_post_meta($post_id, '_irori_placement', $pl);
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

add_action('clean_post_cache', function (int $post_id): void {
    if (!wp_is_post_revision($post_id) && !wp_is_post_autosave($post_id)) {
        irori_bust_cms_cache();
    }
});

foreach (['deleted_post', 'trashed_post', 'untrashed_post', 'edited_term', 'create_term'] as $hook) {
    add_action($hook, 'irori_bust_cms_cache');
}

/**
 * 4. Default Data Definitions (Matches Approved Figma Design 1:1)
 */
function irori_default_settings(): array {
    return [
        'email' => 'houseofirori@gmail.com',
        'phone_display' => '+84 97 141 90 66',
        'phone_tel' => '+84971419066',
        'reservations_url' => '#footer',
        'address_line1' => 'Level 3, 17/3 Nguyen Thi Minh Khai,',
        'address_line2' => 'Sai Gon Ward, HCMC, Vietnam',
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

function irori_default_menu_book(): array {
    return [
        'cocktail_image' => 'assets/images/menu-showcase-1.jpg',
        'cocktail_alt' => 'House of IRORI Signature Cocktail Menu',
        'left_eyebrow' => 'HOUSE OF IRORI • HEARTH KITCHEN',
        'left_heading' => 'Wood-Fired Pizza',
        'left_sub' => '72-hour fermented sourdough crust blistered over binchotan & oak',
        'left_items' => [
            ['name' => 'Miso Burrata & Smoked Scallion', 'price' => '295', 'desc' => 'San Marzano tomato, creamy burrata, white miso glaze, charred negi, cold-pressed olive oil.'],
            ['name' => 'Kurobuta Chorizo & Yuzu Honey', 'price' => '320', 'desc' => 'Fior di latte, spiced Berkshire pork sausage, pickled jalapeño, wild yuzu blossom honey.'],
            ['name' => 'Wild Mushroom & Truffle Dashi', 'price' => '310', 'desc' => 'Roasted maitake & shiitake, aged Parmigiano, black truffle kombu cream, crispy sage.'],
            ['name' => 'Charred Hokkaido Corn & Nori', 'price' => '280', 'desc' => 'Smoked mozzarella, sweet Hokkaido corn, brown butter shoyu, toasted Ariake nori.'],
        ],
        'right_eyebrow' => 'LATE NIGHT • COUNTER PLATES',
        'right_heading' => 'Side Dishes & Bites',
        'right_sub' => 'Crafted to accompany whisky highballs & signature cocktails',
        'right_note' => 'All prices in thousands VND • Subject to 5% service charge & applicable VAT',
        'right_items' => [
            ['name' => 'Binchotan Chicken Karaage', 'price' => '185', 'desc' => 'Free-range thigh marinated in ginger & sake lees, black garlic kewpie, grilled sudachi.'],
            ['name' => 'Wagyu Tataki Brioche Toast', 'price' => '360', 'desc' => 'Seared Miyazaki striploin, smoked bone-marrow butter, pickled myoga shallot, shiso cress.'],
            ['name' => 'Smoked Edamame & Sansho Salt', 'price' => '125', 'desc' => 'Wok-charred young soybeans tossed in sakura-wood smoke oil and green sansho pepper.'],
            ['name' => 'Yuzu Kosho Hand-Cut Fries', 'price' => '145', 'desc' => 'Triple-cooked russet potatoes, fermented citrus chili salt, whipped dashi aioli.'],
        ],
    ];
}

/**
 * 5. Admin Pages: House of IRORI -> Site Settings & Menu Book
 */
add_action('admin_menu', function (): void {
    add_menu_page(
        'House of IRORI',
        'House of IRORI',
        'manage_options',
        'irori-settings',
        'irori_render_settings_page',
        'dashicons-store',
        20
    );
    add_submenu_page(
        'irori-settings',
        'Site & Contact Settings',
        'Site Settings',
        'manage_options',
        'irori-settings',
        'irori_render_settings_page'
    );
    add_submenu_page(
        'irori-settings',
        'Cocktail & Food Menu Book',
        'Menu Book',
        'manage_options',
        'irori-menu-book',
        'irori_render_menu_book_page'
    );
});

add_action('admin_enqueue_scripts', function (string $hook): void {
    if (strpos($hook, 'irori-') !== false) {
        wp_enqueue_media();
    }
});

function irori_render_settings_page(): void {
    if (!current_user_can('manage_options')) {
        return;
    }
    $saved = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['irori_settings_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['irori_settings_nonce'])), 'irori_save_settings')) {
        $defaults = irori_default_settings();
        $clean = [];
        foreach ($defaults as $k => $v) {
            $raw = isset($_POST[$k]) ? wp_unslash($_POST[$k]) : $v;
            if ($k === 'email') {
                $clean[$k] = sanitize_email($raw);
            } elseif (strpos($k, '_url') !== false && strpos($raw, '#') !== 0) {
                $clean[$k] = esc_url_raw($raw);
            } else {
                $clean[$k] = sanitize_text_field($raw);
            }
        }
        update_option('irori_site_settings', $clean);
        irori_bust_cms_cache();
        $saved = true;
    }
    $s = wp_parse_args(get_option('irori_site_settings', []), irori_default_settings());
    ?>
    <div class="wrap">
        <h1>House of IRORI — Site &amp; Contact Settings</h1>
        <?php if ($saved): ?><div class="notice notice-success is-dismissible"><p>Settings saved and live cache cleared.</p></div><?php endif; ?>
        <form method="post">
            <?php wp_nonce_field('irori_save_settings', 'irori_settings_nonce'); ?>
            <table class="form-table" role="presentation">
                <?php
                $labels = [
                    'email' => 'Contact Email',
                    'phone_display' => 'Phone (Display)',
                    'phone_tel' => 'Phone (tel: link)',
                    'reservations_url' => 'Reservations Button Link',
                    'address_line1' => 'Address Line 1',
                    'address_line2' => 'Address Line 2',
                    'rating_score' => 'Google Rating Summary',
                    'map_lat' => 'Map Latitude',
                    'map_lng' => 'Map Longitude',
                    'map_zoom' => 'Map Default Zoom (13–19)',
                    'google_maps_url' => 'Google Maps External URL',
                    'facebook_url' => 'Facebook URL',
                    'instagram_url' => 'Instagram URL',
                    'tiktok_url' => 'TikTok URL',
                    'threads_url' => 'Threads URL',
                ];
                foreach ($labels as $key => $label): ?>
                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th>
                        <td><input type="text" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($s[$key]); ?>" class="regular-text"></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php submit_button('Save Site Settings'); ?>
        </form>
    </div>
    <?php
}

function irori_sanitize_dish_rows($rows): array {
    if (!is_array($rows)) {
        return [];
    }
    $out = [];
    foreach ($rows as $r) {
        $name = sanitize_text_field($r['name'] ?? '');
        $price = sanitize_text_field($r['price'] ?? '');
        $desc = sanitize_textarea_field($r['desc'] ?? '');
        if ($name !== '') {
            $out[] = ['name' => $name, 'price' => $price, 'desc' => $desc];
        }
    }
    return $out;
}

function irori_render_menu_book_page(): void {
    if (!current_user_can('manage_options')) {
        return;
    }
    $saved = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['irori_menu_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['irori_menu_nonce'])), 'irori_save_menu_book')) {
        $d = irori_default_menu_book();
        $clean = [
            'cocktail_image' => sanitize_text_field(wp_unslash($_POST['cocktail_image'] ?? $d['cocktail_image'])),
            'cocktail_alt' => sanitize_text_field(wp_unslash($_POST['cocktail_alt'] ?? $d['cocktail_alt'])),
            'left_eyebrow' => sanitize_text_field(wp_unslash($_POST['left_eyebrow'] ?? $d['left_eyebrow'])),
            'left_heading' => sanitize_text_field(wp_unslash($_POST['left_heading'] ?? $d['left_heading'])),
            'left_sub' => sanitize_text_field(wp_unslash($_POST['left_sub'] ?? $d['left_sub'])),
            'left_items' => irori_sanitize_dish_rows(wp_unslash($_POST['left_items'] ?? [])),
            'right_eyebrow' => sanitize_text_field(wp_unslash($_POST['right_eyebrow'] ?? $d['right_eyebrow'])),
            'right_heading' => sanitize_text_field(wp_unslash($_POST['right_heading'] ?? $d['right_heading'])),
            'right_sub' => sanitize_text_field(wp_unslash($_POST['right_sub'] ?? $d['right_sub'])),
            'right_note' => sanitize_text_field(wp_unslash($_POST['right_note'] ?? $d['right_note'])),
            'right_items' => irori_sanitize_dish_rows(wp_unslash($_POST['right_items'] ?? [])),
        ];
        update_option('irori_menu_book', $clean);
        irori_bust_cms_cache();
        $saved = true;
    }
    $m = wp_parse_args(get_option('irori_menu_book', []), irori_default_menu_book());
    ?>
    <div class="wrap">
        <h1>House of IRORI — Cocktail &amp; Food Menu Book</h1>
        <?php if ($saved): ?><div class="notice notice-success is-dismissible"><p>Menu Book updated and live cache cleared.</p></div><?php endif; ?>
        <form method="post">
            <?php wp_nonce_field('irori_save_menu_book', 'irori_menu_nonce'); ?>
            <h2>1. Cocktail Tab ("Our top cocktail")</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="cocktail_image">Cocktail Menu Image URL</label></th>
                    <td>
                        <input type="text" id="cocktail_image" name="cocktail_image" value="<?php echo esc_attr($m['cocktail_image']); ?>" class="large-text">
                        <button type="button" class="button" id="irori-pick-cocktail-img">Select from Media Library</button>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cocktail_alt">Image Alt Text</label></th>
                    <td><input type="text" id="cocktail_alt" name="cocktail_alt" value="<?php echo esc_attr($m['cocktail_alt']); ?>" class="regular-text"></td>
                </tr>
            </table>

            <h2>2. Food Tab — Left Page (Wood-Fired Pizza)</h2>
            <p>
                <input type="text" name="left_eyebrow" value="<?php echo esc_attr($m['left_eyebrow']); ?>" class="regular-text" placeholder="Eyebrow">
                <input type="text" name="left_heading" value="<?php echo esc_attr($m['left_heading']); ?>" class="regular-text" placeholder="Heading">
                <input type="text" name="left_sub" value="<?php echo esc_attr($m['left_sub']); ?>" class="large-text" placeholder="Subtitle" style="margin-top:6px;">
            </p>
            <table class="widefat striped" id="irori-left-table">
                <thead><tr><th style="width:28%">Dish Name</th><th style="width:12%">Price</th><th>Description</th><th style="width:60px"></th></tr></thead>
                <tbody>
                <?php foreach ($m['left_items'] as $idx => $it): ?>
                    <tr>
                        <td><input type="text" name="left_items[<?php echo (int) $idx; ?>][name]" value="<?php echo esc_attr($it['name']); ?>" class="widefat"></td>
                        <td><input type="text" name="left_items[<?php echo (int) $idx; ?>][price]" value="<?php echo esc_attr($it['price']); ?>" class="widefat"></td>
                        <td><input type="text" name="left_items[<?php echo (int) $idx; ?>][desc]" value="<?php echo esc_attr($it['desc']); ?>" class="widefat"></td>
                        <td><button type="button" class="button irori-del-row">&times;</button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><button type="button" class="button" data-add-menu="left_items" data-target="#irori-left-table tbody">+ Add Left Page Dish</button></p>

            <h2>3. Food Tab — Right Page (Side Dishes &amp; Bites)</h2>
            <p>
                <input type="text" name="right_eyebrow" value="<?php echo esc_attr($m['right_eyebrow']); ?>" class="regular-text" placeholder="Eyebrow">
                <input type="text" name="right_heading" value="<?php echo esc_attr($m['right_heading']); ?>" class="regular-text" placeholder="Heading">
                <input type="text" name="right_sub" value="<?php echo esc_attr($m['right_sub']); ?>" class="large-text" placeholder="Subtitle" style="margin-top:6px;">
                <input type="text" name="right_note" value="<?php echo esc_attr($m['right_note']); ?>" class="large-text" placeholder="Footer Price/VAT Note" style="margin-top:6px;">
            </p>
            <table class="widefat striped" id="irori-right-table">
                <thead><tr><th style="width:28%">Dish Name</th><th style="width:12%">Price</th><th>Description</th><th style="width:60px"></th></tr></thead>
                <tbody>
                <?php foreach ($m['right_items'] as $idx => $it): ?>
                    <tr>
                        <td><input type="text" name="right_items[<?php echo (int) $idx; ?>][name]" value="<?php echo esc_attr($it['name']); ?>" class="widefat"></td>
                        <td><input type="text" name="right_items[<?php echo (int) $idx; ?>][price]" value="<?php echo esc_attr($it['price']); ?>" class="widefat"></td>
                        <td><input type="text" name="right_items[<?php echo (int) $idx; ?>][desc]" value="<?php echo esc_attr($it['desc']); ?>" class="widefat"></td>
                        <td><button type="button" class="button irori-del-row">&times;</button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><button type="button" class="button" data-add-menu="right_items" data-target="#irori-right-table tbody">+ Add Right Page Dish</button></p>

            <?php submit_button('Save Menu Book'); ?>
        </form>
    </div>
    <script>
    (function(){
      var pickBtn = document.getElementById('irori-pick-cocktail-img');
      if (pickBtn && window.wp && wp.media) {
        pickBtn.addEventListener('click', function(e){
          e.preventDefault();
          var frame = wp.media({ title: 'Select Cocktail Menu Image', multiple: false });
          frame.on('select', function(){
            var att = frame.state().get('selection').first().toJSON();
            document.getElementById('cocktail_image').value = att.url;
          });
          frame.open();
        });
      }
      document.addEventListener('click', function(e){
        if (e.target.classList.contains('irori-del-row')) {
          e.target.closest('tr').remove();
        }
        var addBtn = e.target.closest('[data-add-menu]');
        if (addBtn) {
          var key = addBtn.getAttribute('data-add-menu');
          var tbody = document.querySelector(addBtn.getAttribute('data-target'));
          var idx = Date.now();
          var tr = document.createElement('tr');
          ['name','price','desc'].forEach(function(field){
            var td = document.createElement('td');
            var inp = document.createElement('input');
            inp.type = 'text';
            inp.name = key + '[' + idx + '][' + field + ']';
            inp.className = 'widefat';
            td.appendChild(inp);
            tr.appendChild(td);
          });
          var tdDel = document.createElement('td');
          var btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'button irori-del-row';
          btn.textContent = '\u00d7';
          tdDel.appendChild(btn);
          tr.appendChild(tdDel);
          tbody.appendChild(tr);
        }
      });
    })();
    </script>
    <?php
}

/**
 * 6. First-Boot Idempotent Seeder (Populates All Figma Default Content)
 */
add_action('init', 'irori_maybe_seed_defaults', 30);

function irori_maybe_seed_defaults(): void {
    if (get_option('irori_seeded_v1')) {
        return;
    }
    $lock = (int) get_option('irori_seed_lock_v1', 0);
    if ($lock > 0 && (time() - $lock) < 300) {
        return;
    }
    if ($lock > 0) {
        delete_option('irori_seed_lock_v1');
    }
    if (!add_option('irori_seed_lock_v1', time(), '', 'no')) {
        return;
    }

    // Remove WP boilerplate "Hello world!" if untouched
    $hello = get_page_by_path('hello-world', OBJECT, 'post');
    if ($hello) {
        wp_delete_post($hello->ID, true);
    }

    update_option('irori_site_settings', irori_default_settings());
    update_option('irori_menu_book', irori_default_menu_book());

    // Create Blog Categories matching Figma pills (#interior, #Concept, #Club)
    $cats = [
        'interior' => wp_insert_term('interior', 'category', ['slug' => 'interior']),
        'concept' => wp_insert_term('Concept', 'category', ['slug' => 'concept']),
        'club' => wp_insert_term('Club', 'category', ['slug' => 'club']),
    ];
    $cat_ids = [];
    foreach (['interior', 'concept', 'club'] as $slug) {
        $term = get_term_by('slug', $slug, 'category');
        if ($term) {
            $cat_ids[$slug] = (int) $term->term_id;
        }
    }

    // Seed Blog Articles (9 items matching blogs.html 6 visible + 3 Load More, with full editorial prose)
    $elle_prose = '<p>Được khởi xướng vào 3 năm trước với mong muốn đem đến cho công chúng yêu nghệ thuật và thiết kế một trải nghiệm thưởng lãm mới mẻ, ELLE Decoration Pop-Up Office dần tạo được dấu ấn trong tâm trí cộng đồng sáng tạo. Sự trở lại của Pop-Up Office với chủ đề “The Making of Joy” - Khởi Tạo Niềm Vui đánh dấu chuỗi hoạt động trong vòng 3 tuần từ 20/12/2024 đến 10/01/2025, tại TĐX Ice Factory quận 1, TP.HCM.</p>'
        . '<p>Về chuỗi hoạt động “Văn phòng Sáng Tạo” Pop-Up Office</p>'
        . '<p>Pop-Up là một hình mẫu chương trình do ELLE Decoration khởi xướng từ năm 2022, với mong muốn tạo ra một không gian kết nối cộng đồng yêu nghệ thuật, thiết kế, văn hoá và phong cách sống.</p>'
        . '<p>Cốt lõi của chuỗi hoạt động chính là không gian văn phòng của đội ngũ tòa soạn, nơi làm việc, hội họp, phỏng vấn, sản xuất hình ảnh, tiếp đãi đối tác và trò chuyện cùng các nhân vật của ấn phẩm. Với Pop-Up Office, đội ngũ ELLE Decoration được toàn quyền cải tạo và biến đổi một không gian có sẵn thành văn phòng của mình, với sự góp sức của các đối tác về sản phẩm nội thất, vật liệu, chiếu sáng, công nghệ, vv.. nhằm tạo ra một trải nghiệm tổng hòa giữa nội thất, kiến trúc, nghệ thuật và văn hoá.</p>';

    $articles_seed = [
        ['title' => 'Why We Work with Spirit, Not Against It', 'slug' => 'why-we-work-with-spirit-not-against-it', 'cat' => 'interior', 'date' => '2026-04-14 18:00:00', 'img' => 'assets/images/blog-thumb-1.jpg', 'sub' => 'Why We Work with Spirit, Not Against It', 'excerpt' => 'A conversation about respect, slow curation, and why every ingredient has a story to tell…'],
        ['title' => 'ELLE Decoration PopUp Office - Dự Án Văn Phòng Sáng Tạo Dành Cho Cộng Đồng', 'slug' => 'elle-decoration-popup-office', 'cat' => 'concept', 'date' => '2026-04-12 18:00:00', 'img' => 'assets/images/blog-thumb-1.jpg', 'sub' => 'Why We Work with Spirit, Not Against It', 'excerpt' => 'A conversation about respect, slow curation, and why every ingredient has a story to tell…'],
        ['title' => 'Inside Level Two: Listening Culture & Hand-Carved Ice', 'slug' => 'inside-level-two-listening-culture', 'cat' => 'club', 'date' => '2026-04-10 18:00:00', 'img' => 'assets/images/blog-thumb-1.jpg', 'sub' => 'Craftsmanship Behind the Upstairs Counter', 'excerpt' => 'A conversation about respect, slow curation, and why every ingredient has a story to tell…'],
        ['title' => 'Showa Timber & Warm Brass: Designing the Alleyway', 'slug' => 'showa-timber-and-warm-brass', 'cat' => 'interior', 'date' => '2026-04-08 18:00:00', 'img' => 'assets/images/blog-thumb-1.jpg', 'sub' => 'Two Floors, One Cohesive Philosophy', 'excerpt' => 'A conversation about respect, slow curation, and why every ingredient has a story to tell…'],
        ['title' => 'Binchotan & Fermented Dough: Hearth Notes from Level One', 'slug' => 'binchotan-and-fermented-dough', 'cat' => 'concept', 'date' => '2026-04-05 18:00:00', 'img' => 'assets/images/blog-thumb-1.jpg', 'sub' => 'Where Artisanal Sourdough Meets Izakaya Warmth', 'excerpt' => 'A conversation about respect, slow curation, and why every ingredient has a story to tell…'],
        ['title' => 'Late-Night Vinyl Pressings from 1970s Tokyo', 'slug' => 'late-night-vinyl-pressings-tokyo', 'cat' => 'club', 'date' => '2026-04-02 18:00:00', 'img' => 'assets/images/blog-thumb-1.jpg', 'sub' => 'Analog Soundscapes After Midnight', 'excerpt' => 'A conversation about respect, slow curation, and why every ingredient has a story to tell…'],
        ['title' => 'Quiet Omotenashi in the Heart of Saigon', 'slug' => 'quiet-omotenashi-in-saigon', 'cat' => 'interior', 'date' => '2026-03-28 18:00:00', 'img' => 'assets/images/level1-alley.jpg', 'sub' => 'Hospitality Without Performance', 'excerpt' => 'How spatial restraint and warm cedar lighting shape an unhurried evening on Nguyen Thi Minh Khai…'],
        ['title' => 'Seasonal Botanicals & House-Aged Infusions', 'slug' => 'seasonal-botanicals-house-infusions', 'cat' => 'concept', 'date' => '2026-03-22 18:00:00', 'img' => 'assets/images/level2-bar.jpg', 'sub' => 'Slow Extraction & Citrus Balance', 'excerpt' => 'Exploring yuzu kosho, roasted barley, and Japanese single-malt pairings across changing Saigon seasons…'],
        ['title' => 'Guestshift Chronicles: Regional Bartenders at IRORI', 'slug' => 'guestshift-chronicles-at-irori', 'cat' => 'club', 'date' => '2026-03-15 18:00:00', 'img' => 'assets/images/event-wild-turkey.jpg', 'sub' => 'One-Night Counter Collaborations', 'excerpt' => 'Highlights from intimate takeover nights bringing regional craft bartenders behind our upstairs counter…'],
    ];

    foreach ($articles_seed as $a) {
        $pid = wp_insert_post([
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
        }
    }

    // Seed Occasional Menus (4 items on menu.html: 2 initial + 2 Load More)
    $occ_menus = [
        ['title' => 'Guestshift - Wild Turkey 8ys Takeover', 'iso' => '2025-05-16', 'label' => '16th May 2025', 'img' => 'assets/images/event-wild-turkey.jpg', 'desc' => 'Beat the sweltering heat with Lunazul tequila. House of IRORI created La Familia Es Todo – refreshing, balanced, and invigorating. A harmonious blend of 100% agave tequila, sweet and sour fruit flavors, and cool mint, offering a moment of complete relaxation. 📍Enjoy this special cocktail at House of IRORI this April.'],
        ['title' => 'Guestshift - Wild Turkey 8ys Takeover', 'iso' => '2025-05-16', 'label' => '16th May 2025', 'img' => 'assets/images/event-wild-turkey.jpg', 'desc' => 'Beat the sweltering heat with Lunazul tequila. House of IRORI created La Familia Es Todo – refreshing, balanced, and invigorating. A harmonious blend of 100% agave tequila, sweet and sour fruit flavors, and cool mint, offering a moment of complete relaxation. 📍Enjoy this special cocktail at House of IRORI this April.'],
        ['title' => 'Seasonal Tasting - Nikka From The Barrel Flight', 'iso' => '2025-06-08', 'label' => '8th June 2025', 'img' => 'assets/images/level2-bar.jpg', 'desc' => 'An intimate counter-side tasting exploring smoked Japanese malt pairings, hand-carved crystal block ice, and seasonal citrus infusions curated by House of IRORI’s head bartender.'],
        ['title' => 'Okinawa Awamori & Charcoal Izakaya Pairing', 'iso' => '2025-06-27', 'label' => '27th June 2025', 'img' => 'assets/images/level1-alley.jpg', 'desc' => 'Discover aged island rice spirits paired with binchotan-kissed small plates and wood-fired artisanal flatbreads across Level One and Level Two at House of IRORI.'],
    ];
    foreach ($occ_menus as $i => $m) {
        $pid = wp_insert_post([
            'post_type' => 'irori_occasional',
            'post_status' => 'publish',
            'post_title' => $m['title'],
            'post_content' => $m['desc'],
            'menu_order' => $i + 1,
        ]);
        if ($pid && !is_wp_error($pid)) {
            update_post_meta($pid, '_irori_date_iso', $m['iso']);
            update_post_meta($pid, '_irori_date_label', $m['label']);
            update_post_meta($pid, '_irori_fallback_img', $m['img']);
        }
    }

    // Seed Events: 3 Upcoming carousel slides + 6 Occasional grid items
    $upcoming_seed = [
        ['title' => 'Featured Upcoming Guestshift at House of IRORI', 'img' => 'assets/images/event-upcoming-featured.jpg'],
        ['title' => 'Wild Turkey 8ys Takeover Night at House of IRORI', 'img' => 'assets/images/event-wild-turkey.jpg'],
        ['title' => 'Intimate Japanese Spirits Tasting at Level Two Counter', 'img' => 'assets/images/level2-bar.jpg'],
    ];
    foreach ($upcoming_seed as $i => $u) {
        $pid = wp_insert_post([
            'post_type' => 'irori_event',
            'post_status' => 'publish',
            'post_title' => $u['title'],
            'post_content' => '',
            'menu_order' => $i + 1,
        ]);
        if ($pid && !is_wp_error($pid)) {
            update_post_meta($pid, '_irori_placement', 'upcoming');
            update_post_meta($pid, '_irori_fallback_img', $u['img']);
        }
    }

    $events_seed = [
        ['title' => 'Guestshift - Wild Turkey 8ys Takeover', 'iso' => '2025-05-16', 'label' => '16th May 2025', 'img' => 'assets/images/event-wild-turkey.jpg', 'desc' => 'Beat the sweltering heat with Lunazul tequila. House of IRORI created La Familia Es Todo – refreshing, balanced, and invigorating. A harmonious blend of 100% agave tequila, sweet and sour fruit flavors, and cool mint, offering a moment of complete relaxation. 📍Enjoy this special cocktail at House of IRORI this April.'],
        ['title' => 'Guestshift - Wild Turkey 8ys Takeover', 'iso' => '2025-05-16', 'label' => '16th May 2025', 'img' => 'assets/images/event-wild-turkey.jpg', 'desc' => 'Beat the sweltering heat with Lunazul tequila. House of IRORI created La Familia Es Todo – refreshing, balanced, and invigorating. A harmonious blend of 100% agave tequila, sweet and sour fruit flavors, and cool mint, offering a moment of complete relaxation. 📍Enjoy this special cocktail at House of IRORI this April.'],
        ['title' => 'Guestshift - Wild Turkey 8ys Takeover', 'iso' => '2025-05-16', 'label' => '16th May 2025', 'img' => 'assets/images/event-wild-turkey.jpg', 'desc' => 'Beat the sweltering heat with Lunazul tequila. House of IRORI created La Familia Es Todo – refreshing, balanced, and invigorating. A harmonious blend of 100% agave tequila, sweet and sour fruit flavors, and cool mint, offering a moment of complete relaxation. 📍Enjoy this special cocktail at House of IRORI this April.'],
        ['title' => 'Guestshift - Wild Turkey 8ys Takeover', 'iso' => '2025-05-16', 'label' => '16th May 2025', 'img' => 'assets/images/event-wild-turkey.jpg', 'desc' => 'Beat the sweltering heat with Lunazul tequila. House of IRORI created La Familia Es Todo – refreshing, balanced, and invigorating. A harmonious blend of 100% agave tequila, sweet and sour fruit flavors, and cool mint, offering a moment of complete relaxation. 📍Enjoy this special cocktail at House of IRORI this April.'],
        ['title' => 'Midnight Vinyl & Rare Malt Listening Session', 'iso' => '2025-06-14', 'label' => '14th June 2025', 'img' => 'assets/images/event-upcoming-featured.jpg', 'desc' => 'An unhurried late-evening session on Level Two featuring 1970s Japanese jazz pressings spun on analog turntables alongside rare single-cask pours from our reserve shelf.'],
        ['title' => 'Tokyo Counter Takeover - Craft Highball Night', 'iso' => '2025-06-28', 'label' => '28th June 2025', 'img' => 'assets/images/level2-bar.jpg', 'desc' => 'Precision carbonation, hand-chiseled ice spears, and bespoke botanical cordials in a one-night guest bartender showcase celebrating modern Ginza highball culture at House of IRORI.'],
    ];
    foreach ($events_seed as $i => $ev) {
        $pid = wp_insert_post([
            'post_type' => 'irori_event',
            'post_status' => 'publish',
            'post_title' => $ev['title'],
            'post_content' => $ev['desc'],
            'menu_order' => 10 + $i,
        ]);
        if ($pid && !is_wp_error($pid)) {
            update_post_meta($pid, '_irori_placement', 'occasional');
            update_post_meta($pid, '_irori_date_iso', $ev['iso']);
            update_post_meta($pid, '_irori_date_label', $ev['label']);
            update_post_meta($pid, '_irori_fallback_img', $ev['img']);
        }
    }

    // Seed Google Reviews (5 reviews on index.html)
    $reviews_seed = [
        ['name' => 'Kenji Takahashi', 'initial' => 'K', 'iso' => '2026-09-01', 'time' => '1 month ago', 'text' => 'Stepping through the narrow 70s Tokyo alley into the upstairs counter feels like entering another world. The smoked Old Fashioned and quiet omotenashi hospitality are unmatched in Saigon.'],
        ['name' => 'Minh Anh Nguyen', 'initial' => 'M', 'iso' => '2026-08-15', 'time' => '2 months ago', 'text' => 'Warm tungsten light on dark timber, hand-carved ice, and bartenders who treat every pour like a conversation. Easily our favorite hidden sanctuary on Nguyen Thi Minh Khai.'],
        ['name' => 'David Laurent', 'initial' => 'D', 'iso' => '2026-07-20', 'time' => '3 months ago', 'text' => 'Two floors with one cohesive philosophy. Downstairs feels like a classic Showa-era cinema set, and Level Two is an intimate cocktail bar with extraordinary Japanese spirits.'],
        ['name' => 'Sora Watanabe', 'initial' => 'S', 'iso' => '2026-06-18', 'time' => '4 months ago', 'text' => 'Impeccable balance in every signature cocktail. The yuzu highball and charcoal-aged negroni pair effortlessly with the quiet jazz and warm cedar woodwork.'],
        ['name' => 'Hoang Nam Tran', 'initial' => 'H', 'iso' => '2026-05-29', 'time' => '5 months ago', 'text' => 'A rare place in Saigon where conversation comes first. Thoughtful service, artisanal pizza downstairs, and world-class bespoke pours at the upstairs bar.'],
    ];
    foreach ($reviews_seed as $i => $rv) {
        $pid = wp_insert_post([
            'post_type' => 'irori_review',
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

    update_option('irori_seeded_v1', 1);
    delete_transient(IRORI_CMS_CACHE_KEY);
}

/**
 * 7. REST API Endpoints (/wp-json/irori/v1/bootstrap & /wp-json/irori/v1/article/<slug>)
 */
function irori_resolve_post_image(int $post_id, string $default = 'assets/images/blog-thumb-1.jpg'): array {
    $thumb_id = get_post_thumbnail_id($post_id);
    if ($thumb_id) {
        $url = wp_get_attachment_image_url($thumb_id, 'large');
        $alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
        if ($url) {
            return ['url' => $url, 'alt' => $alt ?: get_the_title($post_id)];
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
        'title' => html_entity_decode(get_the_title($p), ENT_QUOTES, 'UTF-8'),
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

function irori_build_bootstrap_payload(): array {
    $cached = get_transient(IRORI_CMS_CACHE_KEY);
    if (is_array($cached)) {
        return $cached;
    }

    $settings = wp_parse_args(get_option('irori_site_settings', []), irori_default_settings());
    $menu_book = wp_parse_args(get_option('irori_menu_book', []), irori_default_menu_book());

    // Occasional Menus
    $occ_posts = get_posts([
        'post_type' => 'irori_occasional',
        'post_status' => 'publish',
        'has_password' => false,
        'numberposts' => 30,
        'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC'],
    ]);
    $occasional_menus = [];
    foreach ($occ_posts as $p) {
        $img = irori_resolve_post_image($p->ID, 'assets/images/event-wild-turkey.jpg');
        $occasional_menus[] = [
            'id' => (int) $p->ID,
            'title' => html_entity_decode(get_the_title($p), ENT_QUOTES, 'UTF-8'),
            'dateIso' => get_post_meta($p->ID, '_irori_date_iso', true) ?: gmdate('Y-m-d', strtotime($p->post_date)),
            'dateLabel' => get_post_meta($p->ID, '_irori_date_label', true) ?: gmdate('jS F Y', strtotime($p->post_date)),
            'desc' => html_entity_decode(wp_strip_all_tags($p->post_content), ENT_QUOTES, 'UTF-8'),
            'image' => $img['url'],
            'alt' => html_entity_decode($img['alt'], ENT_QUOTES, 'UTF-8'),
        ];
    }

    // Events (Upcoming carousel + Occasional grid)
    $ev_posts = get_posts([
        'post_type' => 'irori_event',
        'post_status' => 'publish',
        'has_password' => false,
        'numberposts' => 50,
        'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC'],
    ]);
    $upcoming_slides = [];
    $events = [];
    foreach ($ev_posts as $p) {
        $placement = get_post_meta($p->ID, '_irori_placement', true) ?: 'occasional';
        $img = irori_resolve_post_image($p->ID, 'assets/images/event-wild-turkey.jpg');
        $title = html_entity_decode(get_the_title($p), ENT_QUOTES, 'UTF-8');
        if ($placement === 'upcoming' || $placement === 'both') {
            $upcoming_slides[] = [
                'id' => (int) $p->ID,
                'title' => $title,
                'image' => $img['url'],
                'alt' => html_entity_decode($img['alt'], ENT_QUOTES, 'UTF-8'),
            ];
        }
        if ($placement === 'occasional' || $placement === 'both') {
            $events[] = [
                'id' => (int) $p->ID,
                'title' => $title,
                'dateIso' => get_post_meta($p->ID, '_irori_date_iso', true) ?: gmdate('Y-m-d', strtotime($p->post_date)),
                'dateLabel' => get_post_meta($p->ID, '_irori_date_label', true) ?: gmdate('jS F Y', strtotime($p->post_date)),
                'desc' => html_entity_decode(wp_strip_all_tags($p->post_content), ENT_QUOTES, 'UTF-8'),
                'image' => $img['url'],
                'alt' => html_entity_decode($img['alt'], ENT_QUOTES, 'UTF-8'),
            ];
        }
    }

    // Reviews
    $rv_posts = get_posts([
        'post_type' => 'irori_review',
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

    // Blog Articles
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
        'version' => 1,
        'generatedAt' => gmdate('c'),
        'settings' => $settings,
        'menuBook' => $menu_book,
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
