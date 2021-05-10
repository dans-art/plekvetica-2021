<?php

class PlekHandler
{

    public function get_plek_option(string $options_name = ''){
        $options = get_option('plek_general_options');
        if(empty($options_name)){return null;}
        if(empty($options[$options_name])){return null;}
        return $options[$options_name];

    }
}
