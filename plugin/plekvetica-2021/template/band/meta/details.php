<?php

/**
 * @todo Check if user is logged in before showing the follow button!?
 */
extract(get_defined_vars());
$band_object = isset($template_args[0]) ? $template_args[0] : null;
$band_class = new PlekBandHandler;
$user = new PlekUserHandler;
$name = $band_object->get_name();
$genres = $band_object->get_genres();
$country = $band_object->get_country_name();
$facebook = $band_object->get_facebook_link();
$insta = $band_object->get_instagram_link();
$website = $band_object->get_website_link();

$score = $band_class->update_band_score($band_object->get_id());

$follow_text = (!$band_object->user_is_follower()) ? __('Follow', 'pleklang') : __('Unfollow', 'pleklang');
$follower_count = $band_object->get_follower_count();
?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Details', 'pleklang')); ?>
<div class="meta-content">
  <dl class='event-details-container'>
    <?php if (!empty($country)) : ?>
      <dt><?php echo __('Origin', 'pleklang'); ?></dt>
      <dd class="band-flag"><?php echo $band_object->get_flag_formated(); ?> <?php echo $country; ?></dd>
    <?php endif; ?>
    <dt><?php echo __('Social & Links', 'pleklang'); ?></dt>
    <dd class="event-links">
      <?php foreach ($band_class->social_media as $slug => $attr) : ?>
        <?php
        $link_name = (isset($attr['name'])) ? $attr['name'] : 'Undefined';
        $fa_class = (isset($attr['fa_class'])) ? $attr['fa_class'] : '';
        $acf_id = (isset($attr['acf_id'])) ? $attr['acf_id'] : '';
        $social_link =  $band_class->get_social_link($acf_id);
        ?>
        <span>
          <?php if (!empty($social_link)) : ?>
            <a href="<?php echo $social_link; ?>" target="_blank" title="<?php echo sprintf(__('To the %s page of %s', 'pleklang'), $link_name, $name); ?>"><i class="<?php echo $fa_class; ?>"></i></a>
          <?php endif; ?>
        </span>
      <?php endforeach; ?>
    </dd>
    <?php if ($user->user_is_in_team()) : ?>
      <dt><?php echo __('Score', 'pleklang'); ?></dt>
      <dd class="band-score"><?php echo $score; ?> Spotify: <?php echo $band_class->get_spotify_data('popularity'); ?></dd>
    <?php endif; ?>
  </dl>
  <div class="band-follow-button"><?php PlekTemplateHandler::load_template('button-counter', 'components', $follower_count, $follow_text, '', 'plek-follow-band-btn'); ?></div>
</div>