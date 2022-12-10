<?php

extract(get_defined_vars());
$event_object = $template_args[0]; //Plek_events object

?>
<div id="plek-event-guest-login-form-container">
    <div class="event-name-container">
    		<label for="guest_name"><?php echo __('Your Name','pleklang'); ?></label>
    		<input type="text" name="guest_name" id="guest_name" class="input" value="" />
    	</div>
        <div class="event-email-container">
            <label for="guest_email"><?php echo __('Your Email','pleklang'); ?></label>
    		<input type="text" name="guest_email" id="guest_email" class="input" value="" />
    	</div>
</div>