<?php

class plekGalleryHandler
{


    public function __construct()
    {
    }

    public static function is_gallery()
    {
        if (strpos($_SERVER['REQUEST_URI'], '/maf/galleries/') > 0) {
            return true;
        }
        return false;
    }

    /**
     * Gets the Gallery ID from the gallery slug in the url (maf/galleries/name_of_the_gallery)
     *
     * @return mixed gallery object on success, string on error.
     */
    public static function get_gallery_from_url()
    {
        global $wpdb;
        $gallery_slug = preg_match('/(\/maf\/galleries\/)([^\/]*)(\/){0,1}/',$_SERVER['REQUEST_URI'], $linkparts);
        if(isset($linkparts[2])){
            $decoded = urldecode($linkparts[2]);
            $gallery_escaped_slug = htmlspecialchars($decoded);
            //Search in DB for the ID
            $query = $wpdb->prepare("  SELECT `gid`, `title`, `galdesc` FROM `".$wpdb->prefix."ngg_gallery` WHERE `slug` = '%s'", $gallery_escaped_slug);
            $db_result = $wpdb->get_results($query);
        }

        if (!isset($db_result) OR empty($db_result[0])) {
            return __("Es wurde keine Gallerie gefunden. Womöglich wurde diese gelöscht oder verschoben.", "pleklang");
        }

        return $db_result[0];
    }


    public function display_album(int $id = null)
    {
        global $nggdb;
        $galleries = array();

        if (!isset($nggdb)) {
            return __('Error: Plugin Next Gen Gallery is not active');
        }
        $album = $nggdb->find_album($id);
        if (!$album) {
            return sprintf(__('Keine Album mit der ID %s gefunden.'), $id);
        }
        if (empty($album->gallery_ids)) {
            return sprintf(__('Keine Gallerien in dem Album mit der ID: %s'), $id);
        }
        $galleries = $this -> get_galleries($album -> gallery_ids);
        
        return PlekTemplateHandler::load_template_to_var('album-container', 'gallery', $galleries);
    }
    /**
     * Loads the galleries object
     *
     * @param [type] $ids Comma separated string or array with gallery ids
     * @return array Array with ngg gallery objects
     */
    public function get_galleries($ids){
        if(is_string($ids)){
            $ids = explode(',',$ids);
        }
        $galleries = array();
        foreach ($ids as $gall_id) {
            $galleries[] = $this->get_gallery($gall_id);
        }
        return $galleries;
    }

    /**
     * Get the Gallery details by ID.
     * 
     *
     * @param integer $gallery_id
     * @return object ngg gallery object 
     * @todo: Move nggdb check to another function / constructor function. Or check at the start of the plugin.
     */
    public function get_gallery(int $gallery_id)
    {
        global $nggdb;

        if (!isset($nggdb)) {
            return __('Error: Plugin Next Gen Gallery is not active');
        }

        $gallery =  $nggdb->find_gallery($gallery_id);
        if (!$gallery) {
            return sprintf(__('Keine Gallerie mit der ID %s gefunden.'), $gallery_id);
        }
        return $gallery;
    }

    public static function get_gallery_image_object(object $gallery_object)
    {
        global $nggdb;
        $id = (!empty($gallery_object->previewpic)) ? $gallery_object->previewpic : false;
        $image = $nggdb->find_image($gallery_object->previewpic);
        if (!$image) {
            $image = new stdClass();
            $image->thumbnailURL = '';
            $image->alttext = 'IMAGE NOT FOUND!';
            $image->meta_data['thumbnail']["width"] = '320';
            $image->meta_data['thumbnail']["height"] = '240';
        }
        return $image;
    }

    public static function get_gallery_link(object $gallery_object, $page_id = null)
    {
        if (!isset($gallery_object->slug)) {
            return false;
        }
        if ($page_id === null) {
            $page_id = get_the_ID();
        }

        return get_permalink($page_id) . 'maf/galleries/' . $gallery_object->slug;
    }
}
