<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class plekGenres
{

    /**
     * The available genres
     *
     * @var array ['name'] => 'displayName'
     */
    public $genres = [
        'alternative-rock' => 'Alternative Rock',
        'black-metal' => 'Black Metal',
        'brutal-slam-death-metal-grindcore' => 'Brutal / Slam Death Metal',
        'celtic-rock' => 'Celtic Rock',
        'crossover' => 'Crossover',
        'dark-metal' => 'Dark Metal',
        'death-core' => 'Death Core',
        'death-metal' => 'Death Metal',
        'djent' => 'Djent',
        'doom-metal' => 'Doom Metal',
        'folk-pagan-metal' => 'Folk/Pagan Metal',
        'folk-rock' => 'Folk Rock',
        'funk' => 'Funk',
        'glam-metal' => 'Glam Metal',
        'gothic-symphonic-metal' => 'Gothic / Symphonic Metal',
        'hard-rock' => 'Hard Rock',
        'hardcore' => 'Hardcore',
        'heavy-power-metal' => 'Heavy Metal',
        'hip-hop' => 'Hip Hop',
        'industrial-metal' => 'Industrial Metal',
        'irish-folk' => 'Irish Folk',
        'medieval-rock' => 'Medieval Rock',
        'melodic-black-metal' => 'Melodic Black Metal',
        'melodic-death-metal' => 'Melodic Death Metal',
        'melodic-metal' => 'Melodic Metal',
        'melodic-thrash-metal' => 'Melodic Thrash Metal',
        'metal' => 'Metal',
        'metalcore-hardcore' => 'Metalcore',
        'nu-metal' => 'Nu Metal',
        'oriental-metal' => 'Oriental Metal',
        'pirate-metal' => 'Pirate Metal',
        'post-metal-rock-punk' => 'Post Metal/Rock/Punk',
        'progressive-metal' => 'Progressive Metal',
        'progressive-rock' => 'Progressive Rock',
        'punk' => 'Punk',
        'rap' => 'Rap',
        'rock' => 'Rock',
        'rock-n-roll' => 'Rock ‚N‘ Roll',
        'stoner-metal' => 'Stoner Metal',
        'symphonic-black-metal' => 'Symphonic Black Metal',
        'thrash-metal' => 'Thrash Metal',
        //Update 2.1.1
        'viking-metal' => 'Viking Metal',
        'tec-death-metal' => 'Technical Death Metal',
        'indian-metal' => 'Indian Metal'
    ];
    public $errors = array(); //A collection of errors since initialization
    public $changes = array(); //The changes made since initialization

    /**
     * Updates all the genres. 
     *
     * @return bool|array True on success, Array with errors on error.
     */
    public function update_genres()
    {
        if (!$this->update_acf_choices() or !$this->update_category_genres() or !$this->genre_category_cleanup()) {
            return $this->errors;
        }
        return true;
    }
    /**
     * Insert or updates the genres / categories.
     *
     * @return bool True on success, false on errors
     */
    public function update_category_genres()
    {
        $plek_band = new PlekBandHandler;
        $category_genres = $plek_band->get_all_genres(true);
        $this->errors['update_genres'] = [];

        foreach ($this->genres as $slug => $name) {
            if (!isset($category_genres[$slug])) {
                //Category does not exist, insert
                $insert = wp_insert_term($name, 'tribe_events_cat', ['slug' => $slug]);
                if (is_wp_error($insert)) {
                    $this->errors['update_genres'][$slug] = $insert;
                } else {
                    $this->changes['update_genres'][] = $name . ' added in Categories';
                }
            } else {
                //Get the existing category and update the name if missmatch
                $cats = get_terms(['slug' => $slug, 'taxonomy' => 'tribe_events_cat', 'hide_empty' => false]);
                if (isset($cats[0]->name) and $name !== $cats[0]->name) {
                    //Name missmatch
                    $id = $cats[0]->term_id;
                    $update = wp_update_term($id, 'tribe_events_cat', ['slug' => $slug, 'name' => $name]);
                    if (is_wp_error($update)) {
                        $this->errors['update_genres'][$slug] = $update;
                    } else {
                        $this->changes['update_genres'][] = $name . ' updated in Categories';
                    }
                }
            }
        }
        return (empty($this->errors['update_genres'])) ? true : false;
    }

    /**
     * Updates the ACF Choices
     *
     * @return bool True on success, false on errors
     */
    public function update_acf_choices()
    {
        $afc_content = null;
        $afc_id = null;
        $original_genres_count = 0;
        $this->errors['update_acf'] = [];
        $item = get_posts([
            'post_type' => 'acf-field',
            'title' => 'Genre',
        ]);
        //make sure we got the right one
        foreach ($item as $genre) {
            if ($genre->post_excerpt === 'band_genre') {
                //This is the one
                $afc_id = $genre->ID;
                $content = maybe_unserialize($genre->post_content);
                if (!isset($content['choices'])) {
                    $this->errors['update_acf'][] = 'No Choices found.';
                    return false;
                } else {
                    $original_genres_count = count($content['choices']);
                }
                $afc_content = $content;
            }
        }
        //Set the new choices
        $afc_content['choices'] = $this->genres;

        //sanatize the data
        $content = maybe_serialize($afc_content);

        //save to the db
        $update = wp_update_post(['ID' => $afc_id, 'post_content' => $content], true);
        if (is_wp_error($update)) {
            $this->errors['update_acf'][] = $update;
        } else {
            $this->changes['update_acf'][] = 'ACF updated. From ' . $original_genres_count . ' to ' . count($this->genres) . ' Genres';
        }
        return (empty($this->errors['update_acf'])) ? true : false;
    }

    /**
     * Checks if all the ACF Genres are set as Categories and vice versa.
     * It compares the saved genres with the genres defined in this class
     *
     * @return bool|string true if everything is ok, string if there are missing genres.
     */
    public function check_genres()
    {
        $plek_band = new PlekBandHandler;
        $acf_genres = $plek_band->get_acf_band_genres();
        $category_genres = $plek_band->get_all_genres(true);

        $genres_to_check = $this->genres;
        $errors = [];
        foreach ($genres_to_check as $slug => $name) {
            $error = false;
            //ACF
            if (!isset($acf_genres[$slug])) {
                $errors[] = 'ACF Genre not found: ' . $slug;
                $error = true;
            }
            if (isset($acf_genres[$slug]) and $acf_genres[$slug] !== $name) {
                $errors[] = 'ACF Genre Name missmatch: ' . $slug;
                $error = true;
            }
            //Category
            if (!isset($category_genres[$slug])) {
                $errors[] = 'Category Genre not found: ' . $slug;
                $error = true;
            }
            if (isset($category_genres[$slug]) and $category_genres[$slug] !== $name) {
                $errors[] = 'Category Genre Name missmatch: ' . $slug;
                $error = true;
            }
            if (!$error) {
                unset($genres_to_check[$slug]);
            }
            unset($acf_genres[$slug]);
            unset($category_genres[$slug]);
        }

        $acf_leftover = implode(', ', $acf_genres);
        $category_leftover = implode(', ', $category_genres);
        $genres_leftover = implode(', ', $genres_to_check);

        if (empty($acf_leftover) and empty($category_leftover) and empty($genres_to_check)) {
            return true;
        }
        return "ACF Only: $acf_leftover <br/> Category Only: $category_leftover <br/> Not in DB: $genres_leftover<br/>" . implode('<br/>', $errors);
    }

    /**
     * Removes all the categories which are not defined in the $this->genres array.
     *
     * @return bool True on success, false on error
     */
    public function genre_category_cleanup()
    {
        $plek_band = new PlekBandHandler;
        $this->errors['remove_cat'] = [];

        $category_genres = $plek_band->get_all_genres();
        foreach ($category_genres as $term_object) {
            if (!isset($this->genres[$term_object->slug])) {
                //Genre / category does not exist in the array. Remove it.
                $id = $term_object->term_id;
                $remove = wp_delete_term($id, 'tribe_events_cat');
                //save to the db
                if (is_wp_error($remove)) {
                    $this->errors['remove_cat'][] = $remove;
                } else {
                    $this->changes['remove_cat'][] = $term_object->name . ' removed from Categories';
                }
            }
        }
        return (empty($this->errors['remove_cat'])) ? true : false;
    }
}
