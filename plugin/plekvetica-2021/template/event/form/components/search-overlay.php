<?php 
extract(get_defined_vars());
$title = $template_args[0]; //Titel
?>
<div class="plek-search-overlay" style="display: none;">
    <div class="title"><?php echo $title; ?></div>
    <div class="search-result">
        <div class="item">
            <div class="item-title">%%title%%</div>
            <div class="item-subtitle">%%subtitle%%</div>
        </div>
    </div>
</div>