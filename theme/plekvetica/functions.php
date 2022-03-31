<?php

$plek_theme = new plekTheme;

add_action('after_setup_theme', [$plek_theme, 'plek_theme_setup']);

add_filter('generate_copyright', [$plek_theme, 'plek_footer_creds_text']);
add_filter('wp_nav_menu_items', [$plek_theme, 'plek_nav_items'], 10, 2);

add_filter('disable_wp_rest_api_post_var', [$plek_theme, 'disable_wp_rest_api_post_var'], 10, 1);

add_filter('auth_cookie_expiration', [$plek_theme, 'keep_me_logged_in']);



class plekTheme
{

    public function plek_nav_items($items, $args)
    {
        if ($args->theme_location === 'primary') {

            //Replace the sub-menu
            $items = str_replace('class="sub-menu"', 'class="plek-sub-menu"', $items);
            //Add Search Bar
            $placeholder = __('Search...', 'pleklang');;
            $items .= "<li class='plek-menu-search'>
            <a class='icon' href='#'><i class='fas fa-search'></i></a>
            <ul>
            <li class='input'><input type='text' placeholder='{$placeholder}'/></li>
            </ul>
            </li>";
        }
        return $items;
    }

    /**
     * Remove Powered by Generatepress
     *
     * @return void
     */
    public function plek_footer_creds_text()
    {
        $copyright = '<div class="creds">Copyright © ' . date('Y') . ' by <a href="https://' . site_url() . '">Plekvetica</a></div>';
        return $copyright;
    }

    /**
     * Adds editor style and theme support
     *
     * @return void
     */
    public function plek_theme_setup()
    {
        add_theme_support('editor-styles');
        add_editor_style('css/backend-style.css');
        return;
    }

    /**
     * Enables the REST API for Contact Form 7 if the API is disabled by Disable WP REST API Plugin 
     *
     * @param mixed $var
     * @return string
     */
    public function disable_wp_rest_api_post_var($var)
    {
        return '_wpcf7';
    }

    public function keep_me_logged_in($expirein)
    {
        return 10368000; // 120 Days
    }
}
