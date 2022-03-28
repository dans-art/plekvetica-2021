<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlekGalleryHandler
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
        $gallery_slug = preg_match('/(\/maf\/galleries\/)([^\/]*)(\/){0,1}/', $_SERVER['REQUEST_URI'], $linkparts);
        if (isset($linkparts[2])) {
            $decoded = urldecode($linkparts[2]);
            $gallery_escaped_slug = htmlspecialchars($decoded);
            //Search in DB for the ID
            $query = $wpdb->prepare("  SELECT `gid`, `title`, `galdesc` FROM `" . $wpdb->prefix . "ngg_gallery` WHERE `slug` = '%s'", $gallery_escaped_slug);
            $db_result = $wpdb->get_results($query);
        }

        if (!isset($db_result) or empty($db_result[0])) {
            return __("No gallery was found. It may have been deleted or moved.", "pleklang");
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
            return sprintf(__('No album found with ID: %s.'), $id);
        }
        if (empty($album->gallery_ids)) {
            return sprintf(__('No gallery found with ID: %s'), $id);
        }
        $galleries = $this->get_galleries($album->gallery_ids);

        return PlekTemplateHandler::load_template_to_var('album-container', 'gallery', $galleries);
    }
    /**
     * Loads the galleries object
     *
     * @param [type] $ids Comma separated string or array with gallery ids
     * @return array Array with ngg gallery objects
     */
    public function get_galleries($ids)
    {
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $galleries = array();
        foreach ($ids as $gall_id) {
            if (!empty($gall_id)) {
                $galleries[] = $this->get_gallery($gall_id);
            }
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
            return sprintf(__('No gallery found with ID: %s'), $gallery_id);
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

    /**
     * Gets the image count on the given gallery.
     * @param int $gallery_id - The Gallery to get the count from.
     * @return int Amount of pictures in the gallery
     */
    public function get_gallery_images_count($gallery_id)
    {
        global $nggdb;
        return count($nggdb->get_ids_from_gallery($gallery_id));
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

    public static function get_band_name(object $gallery_object)
    {
        global $wpdb;
        $gall_id = (string) $gallery_object->gid;
        $wild = '%';
        if (!is_integer($gallery_object->gid)) {
            return (isset($gallery_object->name)) ? $gallery_object->name : 'No Bandname found!';
        }
        $like = $wild . $wpdb->esc_like($gallery_object->gid) . $wild;

        $query = $wpdb->prepare("SELECT meta.meta_value, terms.name  
        FROM `{$wpdb->prefix}termmeta` as meta
        LEFT JOIN {$wpdb->prefix}terms as terms
        ON terms.term_id = meta.term_id
        WHERE `meta_key` LIKE 'band_galleries' AND meta_value LIKE '%s'", $like);

        $db_result = $wpdb->get_results($query);
        if (count($db_result) > 1) {
            //Search for the real term
            foreach ($db_result as $item) {
                $galls = explode(',', $item->meta_value);
                if (array_search($gall_id, $galls, true) !== false) {
                    return $item->name;
                }
            }
        }
        //DB Result was only one entry
        $meta = (isset($db_result[0]->meta_value)) ? $db_result[0]->meta_value : null;
        $galls = explode(',', $meta);
        //Make sure the number is in the meta_value from the DB
        if (isset($db_result[0]->name) and (array_search($gall_id, $galls, true) !== false)) {
            return $db_result[0]->name;
        }
        return $gallery_object->title;
    }

    /**
     * 
     *
     * @return void
     */
    public function plek_get_ngg_Albums_shortcode($attr)
    {
        $attributes = shortcode_atts(array(
            'albumid' => '',
        ), $attr);
        $album_id = $attributes['albumid'];
        return $this->display_album($album_id);
    }

    /**
     * Creates a new Album. 
     *
     * @param string $album_name - The Name of the new Album
     * @return string|int String on with Error message on error, true on success
     */
    public function create_album($album_name = null)
    {
        if (empty($album_name)) {
            return __('No Album name provided', 'pleklang');
        }

        //Check if the album name already exists
        $existing = $this->get_album_by_name($album_name, 'id');
        if ($existing) {
            return intval($existing);
        }

        $album = new stdClass();
        $album->name = $album_name;
        $new_album = C_Album_Mapper::get_instance()->save($album);
        if (is_int($new_album)) {
            return $new_album;
        }
        return __('Error while saving the new Album.', 'pleklang');
    }

    /**
     * Creates a new Gallery. 
     *
     * @todo: Make sure that the path does not exist!
     * @todo: add author
     * @param string $album_name - The Name of the new Album
     * @return string|int String on with Error message on error, true on success
     */
    public function create_gallery($gallery_name = null, $description = '')
    {
        global $nggdb;
        if (empty($gallery_name)) {
            return __('No Gallery name provided', 'pleklang');
        }
        $path = '/wp-content/gallery/' . date('Y') . '/' . sanitize_title($gallery_name) . '/';
        $author = get_current_user_id();
        $new_gallery = $nggdb->add_gallery($gallery_name, $path, $description, 0, 0, $author);

        if (is_int($new_gallery)) {
            return $new_gallery;
        }
        return __('Error while saving the new Gallery.', 'pleklang');
    }

    /**
     * Uploads a image an assign to the gallery in $_POST['gallery_id']
     *
     * @return string|int Image ID on success, error message on failure.
     */
    public function upload_image()
    {
        require(NGG_MODULE_DIR . '/ngglegacy/admin/functions.php');
        global $plek_ajax_handler, $nggdb, $ngg;

        $file = $plek_ajax_handler->get_ajax_files_data('file_data');
        $gallery_id = $plek_ajax_handler->get_ajax_data('gallery_id');

        // Images must be an array
        $imageslist = array();

        // get selected gallery
        if (empty($gallery_id)) {
            return __('No gallery selected !', 'nggallery');
        }

        // get the path to the gallery	
        $gallery = $nggdb->find_gallery($gallery_id);

        if (empty($gallery->path)) {
            return __('Failure in database, no gallery path set !', 'nggallery');
        }

        $base_dir = get_home_path();
        $gallery_path = $base_dir . $gallery->path;


        if ($file['error'] !== 0) {
            return __('Error uploading file', 'pleklang');
        }

        $temp_file = $file['tmp_name'];
        $file_name = $file['name'];

        $filepart = self::fileinfo($file['name']);
        $filename = $filepart['basename'];

        if ($filepart['extension'] !== 'jpg' || !@getimagesize($temp_file)) {
            return __('File is not a valid Image. Only JPG\'s are allowed!.', 'pleklang');
        }
        //check for existing files
        $filename = $this->get_unique_file_name($gallery_path, $file_name);

        //Create dir if not exists
        if (!file_exists($gallery_path)) {
            if (!wp_mkdir_p($gallery_path)) {
                return __('Failed to create new gallery dir: ', 'pleklang') . esc_html($gallery_path);
            }
        }
        //Check if the dir is writable.
        if (!is_writeable($gallery_path)) {
            return sprintf(__('Unable to write to directory %s. Is this directory writable by the server?', 'nggallery'), esc_html($gallery_path));
        }

        if (!@move_uploaded_file($temp_file, $gallery_path . $filename)) {
            return __('Error, the file could not be moved to : ', 'nggallery') . esc_html($gallery_path);
        }
        
        $image_id = nggAdmin::add_Images($gallery_id, array($filename));

        //Resize the original image to the max image size set in the ngg options
        nggAdmin::resize_image($image_id[0]);

        //create thumbnails
        nggAdmin::create_thumbnail($image_id[0]);
        //nggAdmin::do_ajax_operation( 'create_thumbnail' , $image_ids, __('Create new thumbnails','nggallery') );

        //add the preview image if needed
        nggAdmin::set_gallery_preview($gallery_id);

        //Remove the headers to avoid cookies to be created
        header_remove();

        return isset($image_id[0]) ? $image_id[0] : 0;
    }


    /**
     * Adds the gallery Id's to a album
     *
     * @param int $album_id - The ID of the album
     * @param array $galleries - A array with the gallery id's : array("1648","1649")
     * @return string|true String on with Error message on error, true on success
     */
    public function add_gallery_to_album($album_id, $galleries = array())
    {
        if (empty($album_id) or $album_id === 0 or empty($galleries)) {
            return __('No Album ID or Gallery IDs provided', 'pleklang');
        }
        $album_mapper = C_Album_Mapper::get_instance();
        $album = $album_mapper->find($album_id);
        if ($album === null) {
            return __('Album not found', 'pleklang');
        }
        $album->sortorder = array_merge($album->sortorder, $galleries);
        if (is_int(C_Album_Mapper::get_instance()->save($album))) {
            return true;
        }
        return __('Could not save the albums galleries.', 'pleklang');
    }

    /**
     * Creates the fileparts out of a filename and renames jpeg to jpg.
     *
     * @param string $name The original file name
     * @return array Information about the file
     */
    static function fileinfo($name)
    {

        //Sanitizes a filename replacing whitespace with dashes
        $name = sanitize_file_name($name);

        //get the parts of the name
        $filepart = pathinfo(strtolower($name));

        if (empty($filepart))
            return false;

        // required until PHP 5.2.0
        if (empty($filepart['filename']))
            $filepart['filename'] = substr($filepart['basename'], 0, strlen($filepart['basename']) - (strlen($filepart['extension']) + 1));

        $filepart['filename'] = sanitize_title_with_dashes($filepart['filename']);

        //extension jpeg will not be recognized by the slideshow, so we rename it
        $filepart['extension'] = ($filepart['extension'] == 'jpeg') ? 'jpg' : $filepart['extension'];

        //combine the new file name
        $filepart['basename'] = $filepart['filename'] . '.' . $filepart['extension'];

        return $filepart;
    }

    /**
     * Creates an unique filename.
     *
     * @param string $path_to_file - Path to the file with tailing slash. 
     * @param string $filename - Filename with extension.
     * @return string The new Filename.
     */
    public function get_unique_file_name($path_to_file, $filename)
    {
        $filename_parts = explode('.', $filename);
        $extension = end($filename_parts);
        //remove the extension 
        unset($filename_parts[array_key_last($filename_parts)]);

        $filename_no_ex = implode('.', $filename_parts);

        $count = 0;
        while (file_exists($path_to_file . $filename_no_ex . $count . '.' . $extension)) {
            $count = (!is_int($count)) ? 0 : $count + 1;
            $filename =  $filename_no_ex . $count . '.' . $extension;
        }
        return $filename;
    }

    /**
     * Finds a album by name.
     *
     * @param string $name - The name of the album to find
     * @param string $field - the field to return. Default: all.
     * @return mixed false if no album found, null if field not found, object, string or int on success.
     */
    public function get_album_by_name($name, $field = 'all')
    {
        global $wpdb;
        $prep = $wpdb->prepare("SELECT * FROM $wpdb->nggalbum WHERE name = %s", $name);
        $found = $wpdb->get_row($prep);
        if (!$found) {
            return false;
        }
        if ($field === 'all') {
            return $found;
        }
        if (isset($found->{$field})) {
            return $found->{$field};
        } else {
            return null;
        }
    }

    /**
     * Get the day of the album according to the date of the album
     *
     * @param int $album_id
     * @return string The name of the weekday.
     */
    public function get_album_day($album_id)
    {
        //Get the album Title
        $album_mapper = C_Album_Mapper::get_instance();
        $album = $album_mapper->find($album_id);
        if (empty($album->name)) {
            return null;
        }
        $date = preg_match('/[0-9]{4}.[0-9]{2}.[0-9]{2}/', $album->name, $match);
        if (!$date) {
            return null;
        }
        $date = (isset($match[0])) ? str_replace('.', '-', $match[0]) : "";
        $time = strtotime($date);
        return date_i18n('l', $time);
    }
}
