<?php

class PlekEvents extends PlekEventHandler
{

    protected $event = array();
    protected $band_terms = array('website_link', 'herkunft', 'videos', 'bandpic');
    public string $poster_size = 'medium';

    protected $errors = array();



    /**
     * Returns the previous loaded event
     * If no Event is found, event array will be returned.
     *
     * @param integer $event_id
     * @return void
     */
    public function get_event()
    {
        return $this->event;
    }

    public function get_field(string $name = 'post_title', string $template = null)
    {
        switch ($name) {
            case 'bands':
                return $this->format_bands($this->event['bands']);
                break;
            case 'date':
                return $this->format_date();
                break;
            case 'venue_short':
                return tribe_get_venue($this->event['meta']['_EventVenueID']->meta_value);
                break;
            case 'genres':
            case 'datetime':
            case 'price_links':
            case 'authors':
            case 'videos':
            case 'details':
                return PlekTemplateHandler::load_template($name,'meta', $this);
                break;
            default:
                $val = null;
                if (isset($this->event[0]->{$name})) {
                    $val = nl2br($this->event[0]->{$name});
                }
                else if (isset($this->event['meta'][$name]->meta_value)) {
                    $val = $this->event['meta'][$name]->meta_value;
                }
                else{
                    $val = "Field '$name' not Found";
                }
                return ($template === null)?$val:PlekTemplateHandler::load_template($template,'meta', $val);
                break;
        }
        return;
    }


    public function load_event(int $event_id = null, string $status = 'publish')
    {
        global $wpdb;

        if (empty($event_id)) {
            if (($event_id = get_the_ID()) === false) {
                return false;
            }
        }

        $query = "SELECT
        `posts`.`ID`, 
        `posts`.`post_author`,
        `posts`.`post_title`,
        `posts`.`post_content`
        FROM `" . $wpdb->prefix . "posts` `posts`
        WHERE `posts`.ID = '$event_id' AND `posts`.`post_type` = 'tribe_events' 
        AND `posts`.`post_status` = '$status'
        ORDER BY `posts`.`post_date` DESC";

        $db_result = $wpdb->get_results($query);
        if (empty($db_result)) {
            $this->errors[$event_id] = __('No Event found', 'plek');
            return false;
        }
        $this->event = $db_result;
        $this->load_event_terms($event_id);
        $this->load_event_meta($event_id);

        return true;
    }

    public function load_event_terms(int $event_id)
    {
        global $wpdb;

        $select = function () {
            $sel = '';
            foreach ($this->band_terms as $term_name) {
                $sel .= ", $term_name.meta_value AS '$term_name'";
            }
            return $sel;
        };
        $left_join = function () use ($wpdb) {
            $join = '';
            foreach ($this->band_terms as $term_name) {
                $join .= "LEFT JOIN " . $wpdb->prefix . "termmeta $term_name ON wt.term_id = $term_name.term_id AND $term_name.meta_key = '$term_name'";
            }
            return $join;
        };
        $query = "SELECT wt.name, wt.term_id, wt.slug, p.ID AS 'post_id', wtt.taxonomy, user.display_name
        " . $select() . "
        FROM " . $wpdb->prefix . "terms wt
        INNER JOIN " . $wpdb->prefix . "term_taxonomy wtt ON wt.term_id = wtt.term_id
        INNER JOIN " . $wpdb->prefix . "term_relationships wtr ON wtt.term_taxonomy_id = wtr.term_taxonomy_id
        INNER JOIN " . $wpdb->prefix . "posts p ON wtr.object_id = p.ID
        " . $left_join() . "
        LEFT JOIN " . $wpdb->prefix . "users user ON wt.name = user.user_login
        WHERE p.post_type = 'tribe_events' AND p.ID = '$event_id'";

        $db_result = $wpdb->get_results($query);
        if (empty($db_result)) {
            $this->errors[$event_id] = __('No Terms found', 'plek');
            return false;
        }

        //$this -> events[$event_id]['terms'] = $db_result;
        $this->process_terms($db_result);
    }

    public function load_event_meta(int $event_id)
    {
        global $wpdb;
        $query = "SELECT postmeta.meta_key, postmeta.meta_value
        FROM " . $wpdb->prefix . "postmeta postmeta
        WHERE postmeta.post_id = '$event_id'
        GROUP BY postmeta.meta_key";

        $db_result = $wpdb->get_results($query, 'OBJECT_K');
        if (empty($db_result)) {
            $this->errors[$event_id] = __('No Terms found', 'plek');
            return false;
        }

        $this->event['meta'] = $db_result;
        //$this -> process_terms($db_result);
    }

    private function process_terms(array $terms)
    {
        if (empty($terms)) {
            return false;
        }
        foreach ($terms as $line) {
            switch ($line->taxonomy) {
                case 'post_tag':
                    $band_class = new plekBandHandler;
                    $band = array();
                    $band['name'] = $line->name;
                    $band['slug'] = $line->slug;
                    $band['link'] = $band_class -> get_band_link($line -> slug);
                    $band['bandpage'] = $line->slug;
                    $band['flag'] = (isset($line->{'herkunft'})) ? $band_class->get_flag_formated($line->{'herkunft'}) : '';
                    foreach ($this->band_terms as $term_name) {
                        $band[$term_name] = $line->{$term_name};
                    }
                    $this->event['bands'][$line->term_id] = $band;
                    break;

                case 'author':
                    $user = array("name" => $line->name, 'display_name' => $line->display_name);
                    $this->event['author'][$line->term_id] = $user;

                    break;

                case 'tribe_events_cat':
                    $this->event['genres'][$line->slug] = $line->name;
                    break;
            }
        }
    }

    /**
 * Inject the Band infos into the Tribe Events result
 *
 * @param [type] $post
 * @return void
 */
function plek_tribe_get_event($post)
{
    $this -> load_event_terms($post -> ID);
    $bands = $this -> get_event(); 
    $post -> terms = $bands;
    return $post;
}

public function plek_get_featured_startpage(){
    //load from cache?

    s(tribe_get_events());
    return "Hello";
}
}
