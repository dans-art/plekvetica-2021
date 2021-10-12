<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
  }

 /**
  * Manages the Template Blocks for the my_Plekvetica page.
  */
class PlekEventBlocks extends PlekEvents
{

    //Get user blocks
    protected $display_type = 'event-item-compact'; //event-item-compact, event-list-item
    protected $number_of_posts = 5; //Number of posts to get
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
        $user = new PlekUserHandler;
        $today = date('Y-m-d 00:00:00');
        $today_ms = strtotime($today);
        $next_week = date('Y-m-d 23:59:59', strtotime('+7 days', $today_ms));

        switch ($block_id) {
            case 'my_week':
                //Load the data
                if ($user->user_is_in_team()) {
                    $dat = $this->get_user_akkredi_event($today, $next_week);
                } else {
                    $dat = $this->get_user_events($today, $next_week);
                }
                break;
            case 'my_events':
                //Load the data
                if ($user->user_is_in_team()) {
                    $dat = $this->get_user_akkredi_event();
                    $this -> block_total_posts = (isset($this -> total_posts['get_user_akkredi_event']))?$this -> total_posts['get_user_akkredi_event']:0;
                } else {
                    $dat = $this->get_user_events();
                    $this -> block_total_posts = (isset($this -> total_posts['get_user_events']))?$this -> total_posts['get_user_events']:0;
                }
                break;

            default:
                # code...
                break;
        }
        return $dat;
    }


    /**
     * Loads the Data of a block_id.
     * Adds a "Load more" button
     * Define set_display_type() first to change the template to use for each event.
     * Available are: my_events, my_week
     * @todo: my_watchlist, my_reviews
     *
     * @param string $block_id
     * @param array $data 
     * @return string HTML formated Event list
     */
    public function get_block(string $block_id, array $data = array())
    {
        $page_obj = $this -> get_pages_object();
        $content = $this->load_block($block_id, $data);
        $html = "";
        if (is_array($content) and !empty($content)) {
            foreach ($content as $index => $content_data) {
                $html .= PlekTemplateHandler::load_template_to_var($this->display_type, 'event', $content_data, $index);
            }
            if($this -> block_total_posts !== null){
                $html .= $this -> get_pages_count_formated($this -> block_total_posts);
                if($this -> display_more_events_button($this -> block_total_posts)){
                    $html .= PlekTemplateHandler::load_template_to_var('button', 'components', get_pagenum_link($page_obj -> page + 1), __('Weitere Events laden','pleklang'), '_self', 'load_more_reviews', 'ajax-loader-button');
                }
            }
            return $html;
        }
        return false;
    }
}
