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

$score = $band_class -> update_band_score($band_object -> get_id());

$follow_text = (!$band_object->user_is_follower())?__('Follow','pleklang'):__('Unfollow','pleklang');
$follower_count = $band_object -> get_follower_count();
?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Details', 'pleklang')); ?>
<div class="meta-content">
  <dl class='event-details-container'>
    <?php if (!empty($country)) : ?>
      <dt><?php echo __('Origin','pleklang'); ?></dt>
      <dd class="band-flag"><?php echo $band_object->get_flag_formated(); ?> <?php echo $country; ?></dd>
    <?php endif; ?>
    <dt><?php echo __('Social & Links','pleklang'); ?></dt>
    <dd class="event-links">
      <span>
        <?php if (!empty($facebook)) : ?>
          <a href="<?php echo $facebook; ?>" target="_blank" title="<?php echo sprintf(__('Zur Facebook Seite von %s', 'pleklang'), $name); ?>"><i class='fab fa-facebook-square'></i></a>
        <?php endif; ?>
      </span>
      <span>
        <?php if (!empty($insta)) : ?>
          <a href="<?php echo $insta; ?>" target="_blank" title="<?php echo sprintf(__('Zur Instagram Seite von %s', 'pleklang'), $name); ?>"><i class="fab fa-instagram"></i></a>
        <?php endif; ?>
      </span>
      <span>
        <?php if (!empty($website)) : ?>
          <a href="<?php echo $website; ?>" target="_blank" title="<?php echo sprintf(__('Zur Website von %s', 'pleklang'), $name); ?>"><i class='fas fa-globe'></i></a>
        <?php endif; ?>
      </span>
    </dd>
    <?php if ($user -> user_is_in_team()) : ?>
      <dt><?php echo __('Score','pleklang'); ?></dt>
      <dd class="band-score"><?php echo $score; ?></dd>
    <?php endif; ?>
  </dl>
  <div class="band-follow-button"><?php PlekTemplateHandler::load_template('button-counter', 'components', $follower_count, $follow_text, '' ,'plek-follow-band-btn'); ?></div>
</div>
<?php