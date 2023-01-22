<?php

//extract(get_defined_vars());
//$event_object = $template_args[0]; //Plek_events object
$perks_normal = array();
$perks_normal[] = __('Overview of all your events','plekvetica');
$perks_normal[] = __('Added events are published / approved automatically','plekvetica');
$perks_normal[] = __('Editing function for your events','plekvetica');
$perks_normal[] = __('Follow / watch bands and events and never miss a Event again','plekvetica');
$perks_normal[] = __('Notifications of changes to followed bands / events (feature coming soon)','plekvetica');
$perks_normal[] = __('Personal suggestions from bands and events that you might like (feature coming soon)','plekvetica');

$perks_normal[] = __('[BAND] Management of your Band(s)','plekvetica');
$perks_normal[] = __('[BAND] Add videos, logo and description to your Band','plekvetica');
$perks_normal[] = __('[BAND] Overview of all Band events','plekvetica');
?>
    <div id="plek-event-saved-info">
        <?php echo __('Event saved, thanks a lot! Our Eventmanager will check and publish the entry.','plekvetica'); ?>
        <?php echo __('You can login or sign up in order to edit the event afterwards.','plekvetica'); ?>
    </div>
    <div id="plek-memeber-perks">
		<ul>
			<?php foreach($perks_normal as $perk): ?>
				<li><?php echo $perk; ?></li>
			<?php endforeach;?>
		</ul>
    </div>