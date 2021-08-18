<?php

extract(get_defined_vars());
$event_class = $template_args[0]; //Plek_events_form Object

?>
<div class="plek-add-band">
	<form name="add_event_basic" id="add_band" action="" method="post">


		<div class="submit plek-button">
			<input type="submit" name="plek-band-submit" id="plek-band-submit" value="<?php echo __('Band eintragen','pleklang');?>">
		</div>
	</form>
</div>