<?php
/**
 * This class does everything related to files. Uploads, delete, transform ect.
 */
class PlekFileHandler
{

    protected $image_options = array();

    public function upload_image(){

    }

    public function resize_uploaded_image(string $fieldname, $file_title){
        global $plek_ajax_errors;

        $uploaded_file = $_FILES[$fieldname];
        $file_name = $_FILES[$fieldname]['tmp_name'];
        $max_width = (isset($this -> image_options['max_width'])) ? $this -> image_options['max_width']: 0;
        $max_height = (isset($this -> image_options['max_height'])) ? $this -> image_options['max_height']: 0;
        $file_type = (isset($this -> image_options['type'])) ? $this -> image_options['type']: 'jpeg';
        $quality = (isset($this -> image_options['quality'])) ? $this -> image_options['quality']: 80;
        
        list($old_width, $old_height) = getimagesize( $file_name );

        if($max_width === 0 AND $max_height === 0){
            //Do not do any resize
            $new_width = $old_width;
            $new_height = $old_height;
        }else{
            $ratio = $old_width / $old_height;
            if($ratio > 1){
                $new_width = $max_width;
                $new_height = $max_width / $ratio;
            }else{
                $new_width = $max_height * $ratio;
                $new_height = $max_height;
            }
        }

        $upload = wp_handle_upload($uploaded_file, array('test_form' => false));

        if(isset($upload['error'])){
            $plek_ajax_errors -> add('upload_file', $upload['error']);
            return false;
        }
 
        
        $uploaded_file = $upload['file'];
        $attachment_id = wp_insert_attachment(array(
            'guid' => $upload['url'],
            'post_title' => sanitize_text_field( $file_title ),
            'post_mime_type' => $upload['type'],
        ), $uploaded_file);

        if(!$attachment_id){
            $plek_ajax_errors -> add('upload_file', __('Fehler bei hinzufügen des Bildes in die Datenbank.','pleklang'));
        }

        //Resize the file
        $image = wp_get_image_editor( $uploaded_file );
        if ( ! is_wp_error( $image ) ) {
            $image->resize( $new_width, $new_height, true );
            $image->save(  $uploaded_file );
        }
        return $attachment_id;
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
    public function set_image_options(int $max_width = 0, int $max_height = 0, string $file_type = 'jpeg', $quality = 80){
        $this -> image_options['max_width'] = (int) $max_width;
        $this -> image_options['max_height'] = (int) $max_height;
        $this -> image_options['type'] = $file_type;
        $this -> image_options['quality'] = $quality;
    }
    
}
