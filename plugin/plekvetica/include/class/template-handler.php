<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlekTemplateHandler
{

    /**
     * Undocumented function
     *
     * @param string $template_name
     * @param string $subfolder
     * @return void
     */

    public static function get_template_path(string $template_name = '', string $subfolder = '')
    {
        $args = get_defined_vars();

        $args = wp_parse_args(
            $args,
            [
                'template_name'          => 'default-template',
                'subfolder'        => ''
            ]
        );
        /**
         * @param string $template_name
         * @param string $subfolder
         */
        extract($args);

        if (substr($template_name, -4) !== '.php') {
            $template_name .= '.php';
        }

        if (!empty($subfolder) && substr($subfolder, -1) !== '/') {
            $subfolder .= '/';
        }
        //Check if it exists in Template or Stylesheet dir   
        $file = locate_template(PLEK_THEME_TEMPLATE_PATH . $subfolder . $template_name, false);
        if ($file) {
            return $file;
        }

        $file = PLEK_PATH . 'template/' . $subfolder . $template_name;
        if (file_exists($file)) {
            return $file;
        }

        return false;
    }

    /**
     * Loads a template file and passes arguments
     *
     * @param string $template_name - Name of the file without extension
     * @param string $subfolder - Subfolder if any
     * @param mixed ...$template_args - Arguments of any type to pass to the template.
     * @return void
     * 
     * Template arguments:
     * - components/button
     * --link
     * --label
     * --target
     * --id
     * --class
     * -- data
     * - components/text-bar
     * -- text
     */
    public static function load_template(string $template_name = '', string $subfolder = '', ...$template_args)
    {
        $args = get_defined_vars();
        $path = PlekTemplateHandler::get_template_path($template_name, $subfolder);
        if ($path) {
            ob_start();

            /*if ( is_array( $data ) ) {
                extract( $data );
            }*/


            include($path);

            $html = ob_get_clean();
            echo $html;
            return;
        }

        echo sprintf(__('Template "%s" not found!', 'plek'), $template_name);
        return;
    }
    public static function load_template_to_var(string $template_name = '', string $subfolder = '', ...$template_args)
    {
        $args = get_defined_vars();
        $path = PlekTemplateHandler::get_template_path($template_name, $subfolder);

        if ($path) {
            ob_start();
            include($path);
            $output_string = ob_get_contents();
            ob_end_clean();
            wp_reset_postdata();
            return $output_string;
        }

        return sprintf(__('Template "%s" not found!', 'plek'), $template_name);
    }

    /**
     * Get the dropdown options for all the countries
     *
     * @param string $current_country- Country as name or country-code. E.g: CH
     * @return string|false HTML Options on success, false on error
     */
    public static function get_countries_dropdown_options($current_country = null)
    {
        global $plek_handler;
        $countries = $plek_handler->get_all_countries();
        if (!is_array($countries)) {
            return false;
        }
        $return_options = "<option value='null' >".__('Select Country','plekvetica')."</option>";
        foreach ($countries as $code => $name) {
            $selected = ($current_country === $code OR $current_country === $name) ? 'selected' : '';
            $return_options .= "<option value='{$code}' {$selected}>{$name}</option>";
        }
        return $return_options;
    }
}
