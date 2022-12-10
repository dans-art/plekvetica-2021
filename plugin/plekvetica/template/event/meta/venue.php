<?php
$venue_id = tribe_get_venue_id();

if (!$venue_id) {
    return;
}

$map_link = tribe_get_map_link();
$website = tribe_get_venue_website_url();
$phone = tribe_get_phone();
?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Location', 'pleklang')); ?>

<div class="meta-content">
    <div id="venue-container">
        <div class='venue-name'><?php echo tribe_get_venue(); ?></div>
        <div class='address'>
            <div class='address-line-street'><?php echo tribe_get_address( $venue_id ); ?></div>
            <div class='address-line-city'><?php echo tribe_get_zip( $venue_id ); ?><span class="plek-space-delimiter"><?php echo tribe_get_city( $venue_id ); ?></div>
            <div class='address-line-country'>
           <?php if ( tribe_get_country( $venue_id ) AND tribe_get_country( $venue_id ) !== __('Switzerland', 'pleklang') ) : ?>
            	<?php echo tribe_get_country( $venue_id ); ?>
            <?php endif; ?>
            </div>
        </div>

        <div class='venue-contact'>
            <span class='phone'>
                <?php if ($phone) : ?>
                    <a href="tel:<?php echo esc_html($phone); ?>" target="_blank"><i class="fas fa-phone-alt"></i></a>
                <?php endif; ?>
            </span>
            <span class='map'>
                <?php if ($map_link) : ?>
                    <a href="<?php echo esc_html($map_link); ?>" target="_blank"><i class="fas fa-map-marked-alt"></i></a>
                <?php endif; ?>
            </span>
            <span class='homepage'>
                <?php if ($website) : ?>
                    <a href="<?php echo esc_html($website); ?>" target="_blank"><i class="fas fa-globe"></i></a>
                <?php endif; ?>
            </span>

        </div>
    </div>
</div>