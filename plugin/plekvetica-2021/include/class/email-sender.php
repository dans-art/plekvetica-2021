<?php
/**
 * Class to send emails
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlekEmailSender 
{

    protected $to = array();
    protected $subject = "";
    protected $message = "";
    protected $headers = array();
    protected $attachments = "";

    /**
     * Sets the default values for the from field, content type and charset
     *
     * @return void
     */
    public function set_default(){
        $this -> set_from('info@plekvetica.ch', 'Plekvetica');
        $this -> set_type();
        return;
    }

    /**
     * Sets the To address
     * If BCC and CC are empty, they get ignored.
     * This function can be called multiple times. Addresses will be added.
     *
     * @param string $to
     * @param string $bcc_mail
     * @param string $cc_mail
     * @param string $bcc_name
     * @param string $cc_name
     * @return void
     */
    public function set_to(string $to,  string $bcc_mail = "", string $cc_mail = "", string $bcc_name = "", string $cc_name = ""){
        $this -> to[] = $to;
        if(!empty($bcc_mail)){
            $this -> headers[] = "BCC: {$bcc_name} <{$bcc_mail}>";
        }
        if(!empty($cc_mail)){
            $this -> headers[] = "CC: {$cc_name} <{$cc_mail}>";
        }
        return;
    }

    public function set_subject(string $subject){
        $this -> subject = $subject;
        return;
    }

    public function set_message(string $message = ""){
       $this -> message = $message;
       return;
    }
    
    /**
     * Loads the template with the given arguments
     * Template has to be within the template/email folder
     *
     * @param string $template - The Template to use. e.g. user/new-user
     * @param string $subject - Subject and title of the email
     * @param mixed ...$args - Arguments for the template 
     * @return void
     */
    public function set_message_from_template(string $template, string $subject = '', ...$args){
       $this -> message = PlekTemplateHandler::load_template_to_var($template, 'email', $subject, $args);
       return;
    }

    public function set_from($email, $name = ''){
        $this -> headers[] = "From: {$name} <{$email}>";
        return;

    }

    public function set_reply_to($email, $name = ''){
        $this -> headers[] = "Reply-To: {$name} <{$email}>";
        return;

    }

    public function set_type($content_type = 'text/html', $charset = 'UTF-8'){
        $this -> headers[] = "Content-Type: {$content_type}; charset={$charset}";
        return;
    }

    public function set_attachments($attachments = null){

    }

    public function send_mail(){
        return wp_mail( $this -> to, $this -> subject, $this -> message, $this -> headers, $this -> attachments );
    }
    
}
