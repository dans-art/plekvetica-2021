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
        $errors = new WP_Error();
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
            $plek_ajax_errors->add('upload_file', __('Error adding the image to the database.', 'pleklang'));
        }

        //Resize the file
        $resize = $this->resize_uploaded_image($uploaded_file);
        if (is_string($resize)) {
            $plek_ajax_errors->add('upload_file', sprintf(__('Error while resizing the image: %s.', 'pleklang'), $resize));
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
     * @param [type] $image_path
     * @param [type] $watermark_path
     * @param [type] $new_image_path
     * @return void
     */
    public function create_watermarked_image($image_path, $watermark_path, $new_image_path)
    {
        if (!file_exists($image_path) or !file_exists($watermark_path)) {
            $this->error->add('create_watermarked_image_no_image', __('No Image found', 'pleklang') . ' Image: ' . $image_path . ' Watermark:' . $watermark_path);
            return false;
        }
        $orig_image = getimagesize($image_path);
        list($width, $height, $type) = $orig_image;
        $mime = (isset($orig_image['mime'])) ? $orig_image['mime'] : 'null';

        //Set the new Image
        $new_img = imagecreatetruecolor($width, $height);
        $watermark_img = imagecreatefrompng($watermark_path);
        switch ($mime) {
            case 'image/jpeg':
                $image_base = imagecreatefromjpeg($image_path);
                break;

            default:
                $this->error->add('create_watermarked_image_unsupported', __('Filetype not supported', 'pleklang'));
                return false;
                break;
        }
        //Enable blend mode and save alpha channel
        imagealphablending($new_img, true);
        imagesavealpha($new_img, true);

        //Copy the images
        imagecopymerge($new_img, $image_base, 0, 0, 0, 0, $width, $height, 100);
        imagecopymerge($new_img, $watermark_img, 0, 0, 0, 0, $width, $height, 50);

        //Save the new image
        return imagejpeg($new_img, $new_image_path);

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
}
