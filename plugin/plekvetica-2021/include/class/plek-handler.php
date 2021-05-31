<?php

class PlekHandler
{

    public function get_plek_option(string $options_name = ''){
        $options = get_option('plek_general_options');
        if(empty($options_name)){return null;}
        if(empty($options[$options_name])){return null;}
        return $options[$options_name];

    }

    public function print_url(string $url){
        $new_url = $url;
        $parse_url = parse_url($url);
        if(empty($parse_url['scheme'])){
            $new_url = 'http://'.$url;
        }
        return $new_url;
    }

    public function text_bar_from_shortcode($attr){
        $attributes = shortcode_atts( array(
            'text' => 'Add Text...',
            ), $attr );
        return PlekTemplateHandler::load_template_to_var('text-bar', 'components', $attributes['text']);
    }

    public function plek_text_two_line_title_from_shortcode($attr){
        $attributes = shortcode_atts( array(
            'line1' => 'Add Text...',
            'line2' => 'Add Subtext',
            ), $attr );
        return PlekTemplateHandler::load_template_to_var('text_two_line', 'components', $attributes['line1'], $attributes['line2']);
    }

    public function get_acf_choices(string $field_name, string $type, int $page_id){
        switch ($type) {
            case 'term':
                $page = 'term_'.$page_id;
                break;
            
            default:
                # code...
                break;
        }
        $acf = get_field_object($field_name, $page);
        if(!isset($acf['choices'])){
            return false;
        }
        return $acf['choices'];
        
    }

    public function plek_get_team_shortcode(){
        $authors_handler = new PlekAuthorHandler;
        $authors = $authors_handler -> get_all_team_authors();
        return PlekTemplateHandler::load_template_to_var('author-post-items','posts',$authors);
    }

    public function wp_get_nav_menu_items_filter($items, $menu, $args){
        if ($menu->slug === 'oberes-menue') {
            foreach($items as $index => $nav){
                if($nav -> post_name === 'login-logout'){
                    if(is_user_logged_in()){
                        $items[$index] -> title = __('Mein Plekvetica','pleklang'); 
                        $items[$index] -> classes[] = 'member-area-nav'; 
                    }
                    else{
                        $items[$index] -> title = __('Login','pleklang');
                        $items[$index] -> classes[] = 'not-logged-in-nav'; 
                    }
                }
            }
        }
        return $items;
    }

}
