<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
  }

 /**
  * Manages the Template Blocks for the my_Plekvetica page.
  * @todo: Add Blocks for following Bands and Events
  */
class PlekEventBlocks extends PlekEvents
{

    //Get user blocks
    protected $display_type = 'event-item-compact'; //event-item-compact, event-list-item
    protected $number_of_posts = 10; //Number of posts to get
    protected $add_current_date_separator = true; 
    protected $block_total_posts = null; //Number of posts last fetched

    /**
     * Sets the display type, aka. Name of the template to use.
     * You can set your custom template in the template/event/ 
     * Available are: event-item-compact, event-list-item
     * Call this before get_block method.
     * 
     * @param string $type - Name of the template file
     * @return void
     */
    public function set_display_type(string $type)
    {
        $this->display_type = $type;
    }

    /**
     * Defines if a "current date" separator should be shown.
     * Default is on. This will only be shown when the block "my_events" is used.
     *
     * @param boolean $seperator_on
     * @return void
     */
    public function set_add_current_date_separator(bool $seperator_on)
    {
        $this->add_current_date_separator = $seperator_on;
    }

    /**
     * Sets the number of posts to get.
     * @todo: this function is not in use yet...
     *
     * @param integer $posts
     * @return void
     */
    public function set_number_of_posts(int $posts)
    {
        $this->number_of_posts = $posts;
    }

    /**
     * Loads the event data. Use this for getting the raw data.
     * Usually, this function is called by the get_block method. 
     *
     * @param string|null $block_id
     * @param array $data
     * @return array The fetched post as an array
     */
    public function load_block(string $block_id = null, array $data)
    {
        global $plek_event;
        $user = new PlekUserHandler;
        $today = date('Y-m-d 00:00:00');
        $today_ms = strtotime($today);
        $next_week = date('Y-m-d 23:59:59', strtotime('+7 days', $today_ms));
        $ret = array('data' => '', 'error' => false);
        $limit = $this -> number_of_posts;

        switch ($block_id) {
            case 'my_week':
                //Load the data
                if ($user->user_is_in_team()) {
                    $ret['data'] = $this->get_user_akkredi_event($today, $next_week, $limit);
                } else {
                    $ret['data'] = $this->get_user_events($today, $next_week, $limit);
                }
                break;
            case 'my_events':
                //Load the data
                if ($user->user_is_in_team()) {
                    $ret['data'] = $this->get_user_akkredi_event(null, null, $limit);
                    $this -> block_total_posts = (isset($this -> total_posts['get_user_akkredi_event']))?$this -> total_posts['get_user_akkredi_event']:0;
                } else {
                    $ret['data'] = $this->get_user_events();
                    $this -> block_total_posts = (isset($this -> total_posts['get_user_events']))?$this -> total_posts['get_user_events']:0;
                }
                break;

             case 'my_missing_reviews':
                //Load the data
                if ($user->user_is_in_team()) {
                    $ret['data'] =  $plek_event->get_user_missing_review_events();
                } else {
                    $ret['data'] = __('This Data can only be displayed to team members','pleklang');
                    $ret['error'] = true;
                }
                break;
                
                case 'band_events':
                    $band_id = (isset($data['band_id']))?$data['band_id']:0;
                    $ret['data'] =  $this->get_events_of_band($band_id, '', '', $limit);
                    $this -> block_total_posts = (isset($this -> total_posts['get_band_events']))?$this -> total_posts['get_band_events']:0;
                break;
                
                case 'my_band_follows':
                    $band_id = (isset($data['band_id']))?$data['band_id']:0;
                    //$ret['data'] =  $this->get_events_of_band($band_id, '', '', $limit);
                    //$this -> block_total_posts = (isset($this -> total_posts['get_band_events']))?$this -> total_posts['get_band_events']:0;
                break;

                case 'my_event_watchlist':
                    //$band_id = (isset($data['band_id']))?$data['band_id']:0;
                    //$ret['data'] =  $this->get_events_of_band($band_id, '', '', $limit);
                    //$this -> block_total_posts = (isset($this -> total_posts['get_band_events']))?$this -> total_posts['get_band_events']:0;
                break;

            default:
                # code...
                break;
        }
        return $ret;
    }


    /**
     * Loads the Data of a block_id.
     * Adds a "Load more" button
     * Define set_display_type() first to change the template to use for each event.
     * Available are: my_events, my_week
     * @todo: my_watchlist
     * @todo: cache block content
     * @todo: Reset the total posts to the pages object default.
     *
     * @param string $block_id
     * @param array $data 
     * @return string HTML formated Event list
     */
    public function get_block(string $block_id, array $data = array())
    {
        $page_obj = $this -> get_pages_object($this -> number_of_posts);
        $load = $this->load_block($block_id, $data);
        $total_posts = $this -> block_total_posts;
        $this -> block_total_posts = null; //Reset the total posts
        $last_events_date = '';

        if($load['error'] !== false){
            return $load['data'];
        }else{
            $content = $load['data'];
        }
        $html = "";
        if (is_array($content) and !empty($content)) {
            foreach ($content as $index => $content_data) {
                if($block_id === 'my_events' AND $this -> add_current_date_separator === true){
                   $html .= $this -> show_date_separator($last_events_date, $content_data -> startdate);
                   $last_events_date = $content_data -> startdate;
                }
                $html .= PlekTemplateHandler::load_template_to_var($this->display_type, 'event', $content_data, $index);
            }
            $html .= PlekTemplateHandler::load_template_to_var('pagination-buttons', 'components', $total_posts, $this -> number_of_posts, 'ajax-loader-button');
            if($this->display_more_events_button($total_posts, $this -> number_of_posts)){
                //$html .= $this -> get_pages_count_formated($total_posts, $this -> number_of_posts);
            }
            return $html;
        }
        return false;
    }
}
