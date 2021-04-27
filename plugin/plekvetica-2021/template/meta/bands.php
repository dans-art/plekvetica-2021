<?php 

extract(get_defined_vars());
$bands = $template_args[0];
//s($bands);
?>

<?php foreach($bands as $id => $band):  ?>

    <div class="band band-<?php echo $id; ?>">
    <h3><?php echo $band['flag'] . ' ' .  $band['name']; ?></h3>
    <?php if(!empty($band['website_link'])):?>
        <a href="<?php echo $band['website_link']; ?>">Bandweb</a>
    <?php endif;?>
    <div><?php echo $band['videos']; ?></div>
    </div>

<?php endforeach; ?>