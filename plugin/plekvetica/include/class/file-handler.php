<?php

/**
 * This class does everything related to files. Uploads, delete, transform ect.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlekFileHandler
{

    protected $image_options = array();
    public $errors = null;

    public function __construct()
    {
        $this-> errors = new WP_Error();
    }
    public function upload_image()
    {
    }

    /**
     * Moves the file to the WP media library. Tries to resize images.
     * Set the $image_options before in order to resize.
     *
     * @param string $fieldname
     * @param string $file_title - The Title of the file
     * @return int|false - False on error, attachment_id on success.
     */
    public function handle_uploaded_file(string $fieldname, $file_title)
    {
        global $plek_ajax_errors;

        $uploaded_file = $_FILES[$fieldname];
        $file_name = $_FILES[$fieldname]['tmp_name'];


        $upload = wp_handle_upload($uploaded_file, array('test_form' => false));

        if (isset($upload['error'])) {
            $plek_ajax_errors->add('upload_file', $upload['error']);
            return false;
        }


        $uploaded_file = $upload['file'];
        $attachment_id = wp_insert_attachment(array(
            'guid' => $upload['url'],
            'post_title' => sanitize_text_field($file_title),
            'post_mime_type' => $upload['type'],
        ), $uploaded_file);

        if (!$attachment_id) {
            $plek_ajax_errors->add('upload_file', __('Error adding the image to the database.', 'plekvetica'));
        }

        //Resize the file
        $resize = $this->resize_uploaded_image($uploaded_file);
        if (is_string($resize)) {
            $plek_ajax_errors->add('upload_file', sprintf(__('Error while resizing the image: %s.', 'plekvetica'), $resize));
        }

        wp_generate_attachment_metadata($attachment_id, $uploaded_file);
        return $attachment_id;
    }

    /**
     * Resizes a uploaded image
     * 
     *
     * @param string $uploaded_file - Path to file
     * @return mixed  Null if no dimensions set, string on error, array on success.
     */
    public function resize_uploaded_image($uploaded_file)
    {
        $max_width = (isset($this->image_options['max_width'])) ? $this->image_options['max_width'] : 0;
        $max_height = (isset($this->image_options['max_height'])) ? $this->image_options['max_height'] : 0;
        $file_type = (isset($this->image_options['type'])) ? $this->image_options['type'] : 'jpeg';
        $quality = (isset($this->image_options['quality'])) ? $this->image_options['quality'] : 80;

        //Return null if no image dimensions set.
        if ($max_width === 0 and $max_height === 0) {
            return null;
        }

        list($old_width, $old_height) = getimagesize($uploaded_file);

        if ($max_width === 0 and $max_height === 0) {
            //Do not do any resize
            $new_width = $old_width;
            $new_height = $old_height;
        } else {
            $ratio = $old_width / $old_height;
            if ($ratio > 1) {
                $new_width = $max_width;
                $new_height = $max_width / $ratio;
            } else {
                $new_width = $max_height * $ratio;
                $new_height = $max_height;
            }
        }

        //Resize the file
        $image = wp_get_image_editor($uploaded_file);
        if (!is_wp_error($image)) {
            $image->resize($new_width, $new_height, true);
            return $image->save($uploaded_file);
        }
        return $image->get_error_message();
    }

    /**
     * Creates a new image with a watermark on top.
     *
     * @param string $image_path - The Path to the base image
     * @param string $watermark_path - Watermark file to use. Has to be a PNG file
     * @param string $new_image_path - Path to the new image
     * @return bool True on success, false on error
     */
    public function create_watermarked_image($image_path, $watermark_path, $new_image_path)
    {
        if (!file_exists($image_path) or !file_exists($watermark_path)) {
            $this->errors->add('create_watermarked_image_no_image', __('No Image found', 'plekvetica') . ' Image: ' . $image_path . ' Watermark:' . $watermark_path);
            return false;
        }
        $orig_image = getimagesize($image_path);
        list($width, $height) = $orig_image;
        $mime = (isset($orig_image['mime'])) ? $orig_image['mime'] : 'null';

        //Set the new Image
        $new_img = imagecreatetruecolor($width, $height);
        $watermark_img = imagecreatefrompng($watermark_path);
        switch ($mime) {
            case 'image/jpeg':
                $image_base = imagecreatefromjpeg($image_path);
                break;
            case 'png': //@todo Not working. 
                $image_base = imagecreatefrompng($image_path);
                break;

            default:
                $this->errors->add('create_watermarked_image_unsupported', __('Filetype not supported', 'plekvetica'));
                return false;
                break;
        }
        //Enable blend mode and save alpha channel
        imagealphablending($new_img, true);
        imagesavealpha($new_img, true);

        //Adjust the watermark image to fit the base image
        $watermark_orig_width = imagesx($watermark_img);
        $watermark_orig_height = imagesY($watermark_img);
        $watermark_new_height = ($width / $watermark_orig_width * $watermark_orig_height);

        $watermark_img_scaled = imagescale($watermark_img, $width,$watermark_new_height); 

        $watermark_height = imagesy($watermark_img_scaled);
        $watermark_y_pos = ($height - $watermark_height) / 2; //Align to the center

        //Copy the images
        imagecopy($new_img, $image_base, 0, 0, 0, 0, $width, $height);
        imagecopy($new_img, $watermark_img_scaled, 0, $watermark_y_pos, 0, 0, $width, $width);

        //Save the new image
        return imagejpeg($new_img, $new_image_path);

    }

    /**
     * Returns the path to the watermark file.
     *
     * @param string $condition - Supported are 22, 12
     * @return string|false False if $condition not supported
     */
    public function get_watermak_file($condition){
        switch ($condition) {
            case '22':
                $watermark = PLEK_PATH.'images/watermarks/ticketraffle-2-2.png';
                break;
            case '12':
                $watermark = PLEK_PATH.'images/watermarks/ticketraffle-1-2.png';
                break;
            case '11':
                $watermark = PLEK_PATH.'images/watermarks/ticketraffle-1-1.png';
                break;
            
            default:
                return false;
                break;
        }
        return $watermark;
    }
    /**
     * Sets the Options for the image
     * If this options are set, uploaded files will may be resized on upload.
     *
     * @param int $max_width
     * @param int $max_height
     * @param string $file_type - The type to save. Default is jpeg.
     * @return void
     */
    public function set_image_options(int $max_width = 0, int $max_height = 0, string $file_type = 'jpeg', $quality = 80)
    {
        $this->image_options['max_width'] = (int) $max_width;
        $this->image_options['max_height'] = (int) $max_height;
        $this->image_options['type'] = $file_type;
        $this->image_options['quality'] = $quality;
    }


    /**
     * Converts a absolute path to a valid url
     *
     * @param string $path
     * @return string the URL
     */
    public function get_url_from_path($path){
        $path = str_replace('\\', '/', $path);
        $base_path = get_home_path(); 
        $base_url = get_home_url(). '/';
        return str_replace($base_path, $base_url, $path );
    }
}
