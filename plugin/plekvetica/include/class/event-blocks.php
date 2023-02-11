<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Manages the Template Blocks for the my_Plekvetica page.
 * @todo: creat setter function type, dir and container
 */
class PlekEventBlocks extends PlekEvents
{

    //Get user blocks
    protected $display_type = 'event-item-compact'; //event-item-compact, event-list-item
    protected $template_dir = 'event'; //Default: event
    protected $template_container = 'block-container'; //Default: block-container
    protected $number_of_posts = 10; //Number of posts to get
    protected $add_current_date_separator = true;
    protected $block_total_posts = null; //Number of posts last fetched
    protected $original_paged = 0; //Original Paged, get stored after set the paged with set_block_paged()
    protected $separate_by = null; //Choose how to set the separator. Currently only "month" is supported

    /**
     * Set the current template to use
     *
     * @param string $file
     * @param string $dir
     * @param string $container
     * @return void
     */
    public function set_template(string $file = "", string $dir = "", string $container = "")
    {
        if (!empty($file)) {
            $this->set_display_type($file);
        }
        if (!empty($dir)) {
            $this->set_template_dir($dir);
        }
        if (!empty($container)) {
            $this->set_template_container($container);
        }
        return;
    }
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
     * Sets the relative dir where the template is located.
     * Default: event
     * Call this before get_block method.
     * 
     * @param string $dir - Name of the directory
     * @return void
     */
    public function set_template_dir(string $dir)
    {
        $this->template_dir = $dir;
    }

    /**
     * Sets the template to use for the container
     * Default: event
     * Call this before get_block method.
     * 
     * @param string $template - Name of the template file
     * @return void
     */
    public function set_template_container(string $template)
    {
        $this->template_container = $template;
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
     * Sets the way, how to separate the posts.
     *
     * @param string $sep - Separator type
     * @return void
     */
    public function set_separate_by(string $sep)
    {
        $this->separate_by = $sep;
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
        $ret = array('data' => '', 'error' => false, 'post_type' => 'events');
        $limit = $this->number_of_posts;

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
                    //Loads all events. The one the user created and the ones he is accreditaded
                    $ret['data'] = $this->get_team_user_events(null, null, $limit);
                    $this->block_total_posts = (isset($this->total_posts['get_user_akkredi_event'])) ? $this->total_posts['get_user_akkredi_event'] : 0;
                } else {
                    $ret['data'] = $this->get_user_events();
                    $this->block_total_posts = (isset($this->total_posts['get_user_events'])) ? $this->total_posts['get_user_events'] : 0;
                }
                break;

            case 'my_missing_reviews':
                //Load the data
                if ($user->user_is_in_team()) {
                    $ret['data'] =  $plek_event->get_user_missing_review_events();
                } else {
                    $ret['data'] = __('This Data can only be displayed to team members', 'plekvetica');
                    $ret['error'] = true;
                }
                break;

            case 'band_events':
                $band_id = (isset($data['band_id'])) ? $data['band_id'] : 0;
                $ret['data'] =  $this->get_events_of_band($band_id, '', '', $limit);
                $this->block_total_posts = (isset($this->total_posts['get_band_events'])) ? $this->total_posts['get_band_events'] : 0;
                break;

            case 'my_band_follows':
                $band_handler = new PlekBandHandler;
                $ret['data'] =  $band_handler->get_all_bands_followed_by_user(null, $limit);
                $ret['post_type'] = 'bands';
                $this->block_total_posts = (isset($band_handler->total_posts['get_followed_bands'])) ? $band_handler->total_posts['get_followed_bands'] : 0;
                break;

            case 'my_event_watchlist':
                $ret['template'] = 'event-item-watchlist';
                //$band_id = (isset($data['band_id']))?$data['band_id']:0;
                $ret['data'] =  $this->plek_get_all_watchlisted_events_by_user(null, true, $limit);
                $this->block_total_posts = (isset($this->total_posts['get_user_watchlist_events'])) ? $this->total_posts['get_user_watchlist_events'] : 0;
                break;

            case 'bands':
                $band_handler = new PlekBandHandler;
                $ret['data'] = $band_handler->get_bands($this->number_of_posts);
                $ret['template'] = '';
                $ret['template_dir'] = '';
                $ret['post_type'] = 'bands';
                $this->block_total_posts = (isset($band_handler->total_posts['get_bands'])) ? $band_handler->total_posts['get_bands'] : 0;
                break;

            case 'search_events':
                global $plek_search_handler;
                $query = isset($_REQUEST['s']) ? $_REQUEST['s'] : null;
                if (empty($query)) {
                    $ret['data'] = "";
                    break;
                }
                $ret['data'] =  $plek_search_handler->search_tribe_events($query);
                $this->block_total_posts = (isset($plek_event->total_posts['search_tribe_events'])) ? $plek_event->total_posts['search_tribe_events'] : 0;
                break;
            case 'all_reviews':
                $this->display_type = 'event-list-item-review';
                $ret['data'] =  $plek_event->load_all_reviews();
                $this->block_total_posts = (isset($plek_event->total_posts['all_review_events'])) ? $plek_event->total_posts['all_review_events'] : 0;
                break;

            case 'search_events_review':
                $this->display_type = 'event-list-item-review';
                global $plek_search_handler;
                $search_string = isset($_REQUEST['search_reviews']) ? $_REQUEST['search_reviews'] : null;
                $post_ids = $plek_search_handler->search_tribe_events($search_string, true);
                if (!is_array($post_ids)) {
                    $ret['data'] = array();
                }
                $ret['data'] = $plek_search_handler->load_tribe_events_from_ids($post_ids);
                $this->block_total_posts = (isset($plek_event->total_posts['search_tribe_events'])) ? $plek_event->total_posts['search_tribe_events'] : 0;
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
     * @todo: Support for Ajax band posts
     *
     * @param string $block_id
     * @param array $data 
     * @return string HTML formated Event list
     */
    public function get_block(string $block_id, array $data = array())
    {

        $this->set_block_paged($block_id);
        $page_obj = $this->get_pages_object($this->number_of_posts);
        // Loading the data for the block
        $load = $this->load_block($block_id, $data);
        $total_posts = $this->block_total_posts;
        $this->block_total_posts = null; //Reset the total posts
        $last_events_date = '';
        $posts_type = (!empty($load['post_type'])) ? $load['post_type'] : 'events';

        if ($load['error'] !== false) {
            return $load['data'];
        } else {
            $content = $load['data'];
        }
        $html = "";
        if (empty($content)) {
            return "";
        }
        if (is_array($content)) {
            foreach ($content as $index => $content_data) {
                if ($block_id === 'my_events' and $this->add_current_date_separator === true) {
                    $html .= (isset($content_data->startdate)) ? $this->show_date_separator($last_events_date, $content_data->startdate) : '';
                }
                $html .= PlekTemplateHandler::load_template_to_var($this->display_type, $this->template_dir, $content_data, $index, $this->separate_by, $last_events_date);
                $last_events_date = (isset($content_data->startdate)) ? $content_data->startdate : '';
            }
            $html .= PlekTemplateHandler::load_template_to_var('pagination-buttons', 'components', $total_posts, $this->number_of_posts, 'ajax-block-loader-button', $block_id, $posts_type);

            $html_data = $this->get_block_container_html_data($data, $block_id, $page_obj->page, $this->number_of_posts);
            $this->reset_paged();
            return PlekTemplateHandler::load_template_to_var($this->template_container, $this->template_dir, $block_id, $html_data, $html);
        }
        $this->reset_paged();
        return false;
    }

    /**
     * Returns the HTML data attributes
     * e.g. data-page-id=2 data-block-id=events ...
     *
     * @param array $data
     * @param string $block_id
     * @param int|string $current_page
     * @param int|string $posts_per_page
     * @return string The HTML data attributes as a string
     */
    public function get_block_container_html_data($data, $block_id, $current_page, $posts_per_page)
    {
        $page_id = get_the_ID();
        $html_data = "";
        if (!is_array($data)) {
            $data = array();
        }
        $data['display_type'] = $this->display_type;
        $data['template_dir'] = $this->template_dir;
        $data['template_container'] = $this->template_container;
        $data['number_of_posts'] = $this->number_of_posts;
        $data['separate_by'] = $this->separate_by;

        foreach ($data as $key => $value) {
            $html_data .= "data-{$key}='{$value}' ";
        }
        return $html_data .= "data-page_id='{$page_id}' data-block_id='{$block_id}' data-paged='{$current_page}' data-ppp='{$posts_per_page}'";
    }
    /**
     * Loads a block from ajax request. Required $_REQUEST data:
     * block_id, ppp, paged
     *
     * @return string|false HTML content on success, false on error
     */
    public function get_block_from_ajax()
    {
        global $plek_ajax_handler;
        $block_id = $plek_ajax_handler->get_ajax_data('block_id');
        $posts_per_page = (int) $plek_ajax_handler->get_ajax_data('ppp');
        $paged = (int) $plek_ajax_handler->get_ajax_data('paged');
        $this->set_number_of_posts($posts_per_page);
        $this->set_display_type($plek_ajax_handler->get_ajax_data('display_type'));
        $this->set_template_dir($plek_ajax_handler->get_ajax_data('template_dir'));
        $this->set_template_container($plek_ajax_handler->get_ajax_data('template_container'));
        $this->set_separate_by($plek_ajax_handler->get_ajax_data('separate_by'));

        $ajax_data = $plek_ajax_handler->get_all_ajax_data();
        //Set the correct Request URI
        if (isset($ajax_data['page_id'])) {
            $_SERVER['REQUEST_URI'] = str_replace(home_url(), '', get_permalink($ajax_data['page_id']));
        }

        set_query_var('paged', $paged);
        return $this->get_block($block_id, $ajax_data);
    }

    /**
     * Set the current Page.
     * If the block_id is not like the given block_id, the paged will be reset to 1.
     *
     * @param string $block_id
     * @return void
     */
    public function set_block_paged(string $block_id)
    {
        $this->original_paged = get_query_var('paged');
        //Get Block_id from URL
        $block_id_request = (isset($_REQUEST['block_id'])) ? $_REQUEST['block_id'] : null;
        if ($block_id_request !== null and $block_id_request !== $block_id) {
            //Reset the Paged if current block ist not the block from the url
            set_query_var('paged', 1);
        } else {
            if (!isset($_REQUEST['paged'])) {
                $paged = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
            } else {
                $paged = (isset($_REQUEST['paged'])) ? $_REQUEST['paged'] : 0;
            }
            set_query_var('paged', $paged);
        }
    }

    /**
     * Reset the original paged. This function should always be used, after using set_block_paged()
     *
     * @return void
     */
    public function reset_paged()
    {
        set_query_var('paged', $this->original_paged);
        return;
    }
}
