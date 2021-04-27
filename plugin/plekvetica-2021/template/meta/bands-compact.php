<?php 

extract(get_defined_vars());
$bands = $template_args[0];
//s($bands);
?>

<?php foreach($bands as $id => $band):  ?>

    <span class="band band-<?php echo $id; ?>">
    <?php 
    echo $band['flag'] . ' ' . $band['name']; ?>
    </span>
    <?php if(next($bands) == true){echo ', ';}?>

<?php endforeach; ?>