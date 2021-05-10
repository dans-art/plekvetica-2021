<?php 

extract(get_defined_vars());
$event_class = $template_args[0];

$authors =  $event_class -> get_event_authors();
if(!$authors){
    echo ('No Authors found!');
    return;
}
?>

<dl class='event-author-container'>
<?php foreach($authors as $name => $role):?>
  <dt><?php echo $name;?></dt>
  <dd><?php echo $role;?></dd>
  <?php endforeach;?>
</dl>

