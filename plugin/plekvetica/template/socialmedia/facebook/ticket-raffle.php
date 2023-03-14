<?php

/**
 * This is the Template for the Ticket Raffle Text for Facebook.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
extract(get_defined_vars());

//This Template has no args
$event = (isset($template_args[0])) ? $template_args[0] : new PlekEvents; //The Event object
$organizer = $event->get_organizers_as_facebook_id();
$win_conditions = $event->get_field('win_conditions');
$start_date = $event->get_start_date('', true);
//Get the Organizer ID as facebook link
array_walk($organizer, function (&$val, $key) {
    //If the length of the key is bigger than 2, the key is the facebook page id. Otherwise it is the index of the array
    if(strlen($key) > 2){
        $val =  '@[' . $key . ']';
    }
}); 
?>

Mitmachen und Gewinnen! 🤩
Wir verlosen zusammen mit <?php echo implode(', ', $organizer); ?> <?php echo $win_conditions; ?> Tickets für <?php echo $event->get_name(); ?>!
Was müsst ihr dafür tun? Ganz einfach:
1. Plekvetica liken
2. Schreibt einen Kommentar mit wem ihr an den Event wollt.
Und schon seid ihr im Los-Kessel. 🤘😀🤘

Teilnahmeschluss ist der <?php echo date('d M Y', $start_date - $event -> ticket_raffle_due_time); ?> um 12:12Uhr
Die Gewinner werden über den Facebook-Messenger von uns spätestens 2 Tage vor dem Event benachrichtigt.

Viel Glück! 🙂
Teilnahmebedingungen:
--------------------------
Member von Plekvetica sind vom Gewinnspiel ausgenommen. Keine Barauszahlung, die Gewinner werden per Zufall ausgelost. Facebook hat mit diesem Gewinnspiel nichts zu tun.