<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

extract(get_defined_vars());
$subject = (isset($template_args[0])) ? $template_args[0] : ''; //the subject
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title><?php echo $subject; ?></title>
</head>

<body>
    <style type="text/css">
        <?php
        include(PLEK_PATH . 'css/email-style.min.css');
        ?>
    </style>
    <div id="header">
        Header
    </div>