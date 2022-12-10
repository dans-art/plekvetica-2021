<?php

global $plek_handler;

$organizer_ids = tribe_get_organizer_ids();
if (empty($organizer_ids)) {
    return;
}

?>

<?php PlekTemplateHandler::load_template('text-bar', 'components', __('Organizer', 'pleklang')); ?>
<div class="meta-content">
    <div id="organizer-container">
        <?php foreach ($organizer_ids as $organi) : ?>
            <?php
            $phone = tribe_get_organizer_phone($organi);
            $email = tribe_get_organizer_email($organi);
            $website = tribe_get_event_meta($organi, '_OrganizerWebsite', true);
            ?>
            <div class='plek-organizer'>
                <div class='organi-name'>
                    <?php if (!empty($website)) : ?>
                        <a href="<?php echo $plek_handler->print_url($website); ?>" target="_blank"><?php echo tribe_get_organizer($organi); ?></a>
                    <?php else : ?>
                        <?php echo tribe_get_organizer($organi); ?>
                    <?php endif; ?>

                </div>
                <?php if (count($organizer_ids) <= 2) : ?>
                    <div class='organi-contact'>
                        <?php if (!empty($phone)) : ?>
                            <span class="plek-organizer-phone">
                                <a href="tel:<?php echo esc_html($phone); ?>"><i class="fas fa-phone-alt"></i></a>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($email)) : ?>
                            <span class="plek-organizer-email">
                                <a href="mailto:<?php echo esc_html($email); ?>"><i class="far fa-envelope"></i></a>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($website)) : ?>
                            <span class="plek-organizer-web">
                                <a href="<?php echo $plek_handler->print_url($website); ?>"><i class="fas fa-globe"></i></a>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            </dl>

        <?php endforeach; ?>

    </div>
</div>