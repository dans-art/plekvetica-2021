<?php
extract(get_defined_vars());

$user = (isset($template_args[0])) ? $template_args[0] : ''; //the current user object
$akkredi_posts = (isset($template_args[1])) ? $template_args[1] : ''; //the current user object

?>

<?php if(!empty($akkredi_posts)): ?>
    Deine Akkreditieren Events:
    <?php foreach($akkredi_posts as $event): ?>
        <?php 
            $startdate = date('d m Y',strtotime($event -> startdate));    
        ?>
        <div><a href="<?php get_permalink($event -> ID); ?>"><?php echo $startdate ?> - <?php echo $event -> post_title; ?> (<?php echo $event -> akk_status; ?>)</a></div>
    <?php endforeach; ?>
<?php endif; ?>