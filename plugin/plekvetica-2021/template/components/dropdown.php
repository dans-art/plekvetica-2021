<?php
global $plek_event;
global $plek_handler;
extract(get_defined_vars());

$id = (isset($template_args[0])) ? $template_args[0] : ''; //Id and Name of the Select Item
$data = (isset($template_args[1])) ? $template_args[1] : array(); //Data to convert to Options
$selected = (isset($template_args[2])) ? $template_args[2] : array(); //Values to be selected
$multiple = (isset($template_args[3]) AND $template_args[3] === true) ? 'multiple' : ''; //Defines if multiple selections are possible / allowed
$name_mod = ($multiple)? $id.'[]': $id;
if(empty($data)){
	echo __('Fehler: Keine Optionen gefunden!','pleklang');
	return;
}

?>
<select id="<?php echo $id; ?>" name="<?php echo $name_mod; ?>" <?php echo $multiple; ?>>
	<?php foreach ($data as $key => $value) : ?>
		<?php 
			if(is_object($value)){
				$key = $value -> slug;
				$value = $value -> name;
			}
			?>
		<option value='<?php echo $key; ?>' <?php echo (isset($selected[$key])) ? "selected" : ""; ?>>
			<?php echo $value; ?>
		</option>
	<?php endforeach; ?>
</select>