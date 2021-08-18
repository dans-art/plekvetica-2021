<?php

extract(get_defined_vars());
$bands = $template_args[0];
//s($bands);
?>

<?php foreach ($bands as $id => $band) :  ?>

    <span class="band band-<?php echo $id; ?>">
        <span class="flag"><?php echo $band['flag']; ?></span>
        <span class="name"><?php echo "<a href='" . $band['link'] . "' title='Bandpage von " . $band['name'] . "'>" . $band['name'] . "</a>"; ?></span>
    </span>

<?php endforeach; ?>