<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
global $plek_handler;

//Disable query monitor on mobile
if(wp_is_mobile()){
    deactivate_plugins( '/query-monitor/query-monitor.php' );
	echo 'Query Monitor disabled';
}else{
    activate_plugins( '/query-monitor/query-monitor.php' );
	echo 'Query Monitor enabled';

}


$pn = new PlekNotificationHandler;
$pe = new PlekEvents;
$pf = new PlekFileHandler;
$psm = new plekSocialMedia;

$orig_photo = ABSPATH.'wp-content\uploads\2022\Plakat_GNP_OBSCURA_420x594_web.jpg';
$save_path = ABSPATH.'wp-content\uploads\2022\Plakat_GNP_OBSCURA_420x594_web_marked.jpg';
$save_url = 'https://localhost/plekvetica/wp-content/uploads/2022/Plakat_GNP_OBSCURA_420x594_web_marked.jpg';
$watermark = PLEK_PATH.'images\watermarks\ticketraffle-2-2.png';

/*if(!$pf -> create_watermarked_image($orig_photo, $watermark, $save_path)){
	s($pf->errors->get_error_messages());
}else{

	echo '<img src="'.$save_url.'"/>';
}*/
$event_id = 78471;

$string = <<<EOT
<div class="xjyslct xjbqb8w x972fbf xcfux6l x1qhh985 xm0m39n x9f619 x1rg5ohu xdj266r x11i5rnm xat24cr x1mh8g0r xexx8yu x4uap5 x18d9i69 xkhd6sd x1n2onr6 x16tdsg8 xh8yej3" style="margin: 0px; text-align: start; padding: 0px; position: relative; border-width: 0px; display: inline-block; box-sizing: border-box; width: 680px; background-color: #242526; appearance: none; font-family: 'Segoe UI Historic', 'Segoe UI', Helvetica, Arial, sans-serif; color: #1c1e21; font-size: 12px; font-style: normal; font-variant-ligatures: normal; font-variant-caps: normal; font-weight: 400; letter-spacing: normal; orphans: 2; text-indent: 0px; text-transform: none; white-space: normal; widows: 2; word-spacing: 0px; -webkit-text-stroke-width: 0px; text-decoration-thickness: initial; text-decoration-style: initial; text-decoration-color: initial;">
<div class="x78zum5 xdt5ytf xwib8y2 x1y1aw1k" style="padding-top: 8px; display: flex; flex-direction: column; padding-bottom: 8px; font-family: inherit;">
<div class="x9f619 x1n2onr6 x1ja2u2z x78zum5 x2lah0s x1nhvcw1 x1cy8zhl xozqiw3 x1q0g3np x1pi30zi x1swvt13 xexx8yu xykv574 xbmpl8g x4cne27 xifccgj" style="align-items: flex-start; z-index: 0; position: relative; justify-content: flex-start; padding-right: 16px; flex-flow: row nowrap; padding-left: 16px; flex-shrink: 0; margin: -6px; display: flex; box-sizing: border-box; padding-top: 0px; font-family: inherit;">
<div class="x9f619 x1n2onr6 x1ja2u2z x78zum5 xdt5ytf x193iq5w xeuugli x1r8uery x1iyjqo2 xs83m0k xamitd3 xsyo7zv x16hj40l x10b6aqq x1yrsyyn" style="padding: 6px; max-width: 100%; flex: 1 1 0px; z-index: 0; position: relative; display: flex; box-sizing: border-box; align-self: center; flex-direction: column; min-width: 0px; font-family: inherit;">
<div class="x78zum5 xdt5ytf xz62fqu x16ldp7u" style="margin-top: -5px; display: flex; flex-direction: column; margin-bottom: -5px; font-family: inherit;">
<div class="xu06os2 x1ok221b" style="margin-top: 5px; margin-bottom: 5px; font-family: inherit;"><span class="x193iq5w xeuugli x13faqbe x1vvkbs x1xmvt09 x1lliihq x1s928wv xhkezso x1gmr53x x1cpjm7i x1fgarty x1943h6x xudqn12 x3x7a5m x6prxxf xvq8zen xo1l8bm xzsf02u x1yc453h" dir="auto" style="word-break: break-word; max-width: 100%; display: block; overflow-wrap: break-word; font-family: inherit; text-align: left; font-size: 0.9375rem; min-width: 0px; font-weight: 400; line-height: 1.3333; color: var(--primary-text);">Veranstaltung von <strong style="font-weight: 600;"><a class="x1i10hfl xjbqb8w x6umtig x1b1mbwd xaqea5y xav7gou x9f619 x1ypdohk xt0psk2 xe8uvvx xdj266r x11i5rnm xat24cr x1mh8g0r xexx8yu x4uap5 x18d9i69 xkhd6sd x16tdsg8 x1hl2dhg xggy1nq x1a2a7pz xt0b8zv xzsf02u x1s688f" style="color: var(--primary-text); cursor: pointer; text-decoration: none; margin: 0px; text-align: inherit; padding: 0px; outline: none; -webkit-tap-highlight-color: transparent; font-weight: 600; box-sizing: border-box; list-style: none; touch-action: manipulation; background-color: transparent; display: inline; font-family: inherit; border: 0px initial initial;" tabindex="0" role="link" href="https://www.facebook.com/MetBar.Lenzburg">Met-Bar Lenzburg</a></strong></span></div>
</div>
</div>
</div>
</div>
</div>
<div class="xjyslct xjbqb8w x972fbf xcfux6l x1qhh985 xm0m39n x9f619 x1rg5ohu xdj266r x11i5rnm xat24cr x1mh8g0r xexx8yu x4uap5 x18d9i69 xkhd6sd x1n2onr6 x16tdsg8 xh8yej3" style="margin: 0px; text-align: start; padding: 0px; position: relative; border-width: 0px; display: inline-block; box-sizing: border-box; width: 680px; background-color: #242526; appearance: none; font-family: 'Segoe UI Historic', 'Segoe UI', Helvetica, Arial, sans-serif; color: #1c1e21; font-size: 12px; font-style: normal; font-variant-ligatures: normal; font-variant-caps: normal; font-weight: 400; letter-spacing: normal; orphans: 2; text-indent: 0px; text-transform: none; white-space: normal; widows: 2; word-spacing: 0px; -webkit-text-stroke-width: 0px; text-decoration-thickness: initial; text-decoration-style: initial; text-decoration-color: initial;">
<div class="x78zum5 xdt5ytf xwib8y2 x1y1aw1k" style="padding-top: 8px; display: flex; flex-direction: column; padding-bottom: 8px; font-family: inherit;">
<div class="x9f619 x1n2onr6 x1ja2u2z x78zum5 x2lah0s x1nhvcw1 x1cy8zhl xozqiw3 x1q0g3np x1pi30zi x1swvt13 xexx8yu xykv574 xbmpl8g x4cne27 xifccgj" style="align-items: flex-start; z-index: 0; position: relative; justify-content: flex-start; padding-right: 16px; flex-flow: row nowrap; padding-left: 16px; flex-shrink: 0; margin: -6px; display: flex; box-sizing: border-box; padding-top: 0px; font-family: inherit;">
<div class="x9f619 x1n2onr6 x1ja2u2z x78zum5 xdt5ytf x2lah0s x193iq5w xeuugli xsyo7zv x16hj40l x10b6aqq x1yrsyyn" style="padding: 6px; max-width: 100%; z-index: 0; position: relative; flex-shrink: 0; display: flex; box-sizing: border-box; flex-direction: column; min-width: 0px; font-family: inherit;"> </div>
<div class="x9f619 x1n2onr6 x1ja2u2z x78zum5 xdt5ytf x193iq5w xeuugli x1r8uery x1iyjqo2 xs83m0k xamitd3 xsyo7zv x16hj40l x10b6aqq x1yrsyyn" style="padding: 6px; max-width: 100%; flex: 1 1 0px; z-index: 0; position: relative; display: flex; box-sizing: border-box; align-self: center; flex-direction: column; min-width: 0px; font-family: inherit;">
<div class="x78zum5 xdt5ytf xz62fqu x16ldp7u" style="margin-top: -5px; display: flex; flex-direction: column; margin-bottom: -5px; font-family: inherit;">
<div class="xu06os2 x1ok221b" style="margin-top: 5px; margin-bottom: 5px; font-family: inherit;"><span class="x193iq5w xeuugli x13faqbe x1vvkbs x1xmvt09 x1lliihq x1s928wv xhkezso x1gmr53x x1cpjm7i x1fgarty x1943h6x xudqn12 x3x7a5m x6prxxf xvq8zen xo1l8bm xzsf02u x1yc453h" dir="auto" style="word-break: break-word; max-width: 100%; display: block; overflow-wrap: break-word; font-family: inherit; text-align: left; font-size: 0.9375rem; min-width: 0px; font-weight: 400; line-height: 1.3333; color: var(--primary-text);"><a class="x1i10hfl xjbqb8w x6umtig x1b1mbwd xaqea5y xav7gou x9f619 x1ypdohk xt0psk2 xe8uvvx xdj266r x11i5rnm xat24cr x1mh8g0r xexx8yu x4uap5 x18d9i69 xkhd6sd x16tdsg8 x1hl2dhg xggy1nq x1a2a7pz xt0b8zv x1fey0fg" style="color: var(--blue-link); cursor: pointer; text-decoration: none; margin: 0px; text-align: inherit; padding: 0px; outline: none; -webkit-tap-highlight-color: transparent; box-sizing: border-box; list-style: none; touch-action: manipulation; background-color: transparent; display: inline; font-family: inherit; border: 0px initial initial;" tabindex="0" role="link" href="https://facebook.com/MetBar.Lenzburg" target="_blank" rel="noopener">Met-Bar Lenzburg</a></span></div>
</div>
</div>
</div>
</div>
</div>
<div class="xjyslct xjbqb8w x972fbf xcfux6l x1qhh985 xm0m39n x9f619 x1rg5ohu xdj266r x11i5rnm xat24cr x1mh8g0r xexx8yu x4uap5 x18d9i69 xkhd6sd x1n2onr6 x16tdsg8 xh8yej3" style="margin: 0px; text-align: start; padding: 0px; position: relative; border-width: 0px; display: inline-block; box-sizing: border-box; width: 680px; background-color: #242526; appearance: none; font-family: 'Segoe UI Historic', 'Segoe UI', Helvetica, Arial, sans-serif; color: #1c1e21; font-size: 12px; font-style: normal; font-variant-ligatures: normal; font-variant-caps: normal; font-weight: 400; letter-spacing: normal; orphans: 2; text-indent: 0px; text-transform: none; white-space: normal; widows: 2; word-spacing: 0px; -webkit-text-stroke-width: 0px; text-decoration-thickness: initial; text-decoration-style: initial; text-decoration-color: initial;">
<div class="x78zum5 xdt5ytf xwib8y2 x1y1aw1k" style="padding-top: 8px; display: flex; flex-direction: column; padding-bottom: 8px; font-family: inherit;">
<div class="x9f619 x1n2onr6 x1ja2u2z x78zum5 x2lah0s x1nhvcw1 x1cy8zhl xozqiw3 x1q0g3np x1pi30zi x1swvt13 xexx8yu xykv574 xbmpl8g x4cne27 xifccgj" style="align-items: flex-start; z-index: 0; position: relative; justify-content: flex-start; padding-right: 16px; flex-flow: row nowrap; padding-left: 16px; flex-shrink: 0; margin: -6px; display: flex; box-sizing: border-box; padding-top: 0px; font-family: inherit;">
<div class="x9f619 x1n2onr6 x1ja2u2z x78zum5 xdt5ytf x2lah0s x193iq5w xeuugli xsyo7zv x16hj40l x10b6aqq x1yrsyyn" style="padding: 6px; max-width: 100%; z-index: 0; position: relative; flex-shrink: 0; display: flex; box-sizing: border-box; flex-direction: column; min-width: 0px; font-family: inherit;"> </div>
<div class="x9f619 x1n2onr6 x1ja2u2z x78zum5 xdt5ytf x193iq5w xeuugli x1r8uery x1iyjqo2 xs83m0k xamitd3 xsyo7zv x16hj40l x10b6aqq x1yrsyyn" style="padding: 6px; max-width: 100%; flex: 1 1 0px; z-index: 0; position: relative; display: flex; box-sizing: border-box; align-self: center; flex-direction: column; min-width: 0px; font-family: inherit;">
<div class="x78zum5 xdt5ytf xz62fqu x16ldp7u" style="margin-top: -5px; display: flex; flex-direction: column; margin-bottom: -5px; font-family: inherit;">
<div class="xu06os2 x1ok221b" style="margin-top: 5px; margin-bottom: 5px; font-family: inherit;"><span class="x193iq5w xeuugli x13faqbe x1vvkbs x1xmvt09 x1lliihq x1s928wv xhkezso x1gmr53x x1cpjm7i x1fgarty x1943h6x xudqn12 x3x7a5m x6prxxf xvq8zen xo1l8bm xzsf02u x1yc453h" dir="auto" style="word-break: break-word; max-width: 100%; display: block; overflow-wrap: break-word; font-family: inherit; text-align: left; font-size: 0.9375rem; min-width: 0px; font-weight: 400; line-height: 1.3333; color: var(--primary-text);">Tickets</span></div>
<div class="xu06os2 x1ok221b" style="margin-top: 5px; margin-bottom: 5px; font-family: inherit;"><span class="x193iq5w xeuugli x13faqbe x1vvkbs x1xmvt09 x1lliihq x1s928wv xhkezso x1gmr53x x1cpjm7i x1fgarty x1943h6x x4zkp8e x676frb x1nxh6w3 x1sibtaa xo1l8bm xi81zsa x1yc453h" dir="auto" style="word-break: break-word; max-width: 100%; display: block; font-size: 0.8125rem; line-height: 1.2308; overflow-wrap: break-word; font-family: inherit; text-align: left; min-width: 0px; color: var(--secondary-text); font-weight: 400;">eventfrog.ch/07012023</span></div>
</div>
</div>
</div>
</div>
</div>
<div class="xjyslct xjbqb8w x972fbf xcfux6l x1qhh985 xm0m39n x9f619 x1rg5ohu xdj266r x11i5rnm xat24cr x1mh8g0r xexx8yu x4uap5 x18d9i69 xkhd6sd x1n2onr6 x16tdsg8 xh8yej3" style="margin: 0px; text-align: start; padding: 0px; position: relative; border-width: 0px; display: inline-block; box-sizing: border-box; width: 680px; background-color: #242526; appearance: none; font-family: 'Segoe UI Historic', 'Segoe UI', Helvetica, Arial, sans-serif; color: #1c1e21; font-size: 12px; font-style: normal; font-variant-ligatures: normal; font-variant-caps: normal; font-weight: 400; letter-spacing: normal; orphans: 2; text-indent: 0px; text-transform: none; white-space: normal; widows: 2; word-spacing: 0px; -webkit-text-stroke-width: 0px; text-decoration-thickness: initial; text-decoration-style: initial; text-decoration-color: initial;">
<div class="x78zum5 xdt5ytf xwib8y2 x1y1aw1k" style="padding-top: 8px; display: flex; flex-direction: column; padding-bottom: 8px; font-family: inherit;">
<div class="x9f619 x1n2onr6 x1ja2u2z x78zum5 x2lah0s x1nhvcw1 x1cy8zhl xozqiw3 x1q0g3np x1pi30zi x1swvt13 xexx8yu xykv574 xbmpl8g x4cne27 xifccgj" style="align-items: flex-start; z-index: 0; position: relative; justify-content: flex-start; padding-right: 16px; flex-flow: row nowrap; padding-left: 16px; flex-shrink: 0; margin: -6px; display: flex; box-sizing: border-box; padding-top: 0px; font-family: inherit;">
<div class="x9f619 x1n2onr6 x1ja2u2z x78zum5 xdt5ytf x2lah0s x193iq5w xeuugli xsyo7zv x16hj40l x10b6aqq x1yrsyyn" style="padding: 6px; max-width: 100%; z-index: 0; position: relative; flex-shrink: 0; display: flex; box-sizing: border-box; flex-direction: column; min-width: 0px; font-family: inherit;"> </div>
<div class="x9f619 x1n2onr6 x1ja2u2z x78zum5 xdt5ytf x193iq5w xeuugli x1r8uery x1iyjqo2 xs83m0k xamitd3 xsyo7zv x16hj40l x10b6aqq x1yrsyyn" style="padding: 6px; max-width: 100%; flex: 1 1 0px; z-index: 0; position: relative; display: flex; box-sizing: border-box; align-self: center; flex-direction: column; min-width: 0px; font-family: inherit;">
<div class="x78zum5 xdt5ytf xz62fqu x16ldp7u" style="margin-top: -5px; display: flex; flex-direction: column; margin-bottom: -5px; font-family: inherit;">
<div class="xu06os2 x1ok221b" style="margin-top: 5px; margin-bottom: 5px; font-family: inherit;"><span class="x193iq5w xeuugli x13faqbe x1vvkbs x1xmvt09 x1lliihq x1s928wv xhkezso x1gmr53x x1cpjm7i x1fgarty x1943h6x xudqn12 x3x7a5m x6prxxf xvq8zen xo1l8bm xzsf02u x1yc453h" dir="auto" style="word-break: break-word; max-width: 100%; display: block; overflow-wrap: break-word; font-family: inherit; text-align: left; font-size: 0.9375rem; min-width: 0px; font-weight: 400; line-height: 1.3333; color: var(--primary-text);">Öffentlich<span style="font-family: inherit;"><span class="xzpqnlu xjm9jq1 x6ikm8r x10wlt62 x10l6tqk x1i1rx1s" style="position: absolute; overflow: hidden; width: 1px; height: 1px; clip: rect(0px, 0px, 0px, 0px); font-family: inherit;"> </span><span style="font-family: inherit;" aria-hidden="true"> · </span></span>Jeder auf und außerhalb von Facebook</span></div>
</div>
</div>
</div>
</div>
</div>
<div class="x1pi30zi x1swvt13" style="padding-right: 16px; padding-left: 16px; font-family: 'Segoe UI Historic', 'Segoe UI', Helvetica, Arial, sans-serif; color: #1c1e21; font-size: 12px; font-style: normal; font-variant-ligatures: normal; font-variant-caps: normal; font-weight: 400; letter-spacing: normal; orphans: 2; text-align: start; text-indent: 0px; text-transform: none; white-space: normal; widows: 2; word-spacing: 0px; -webkit-text-stroke-width: 0px; background-color: #242526; text-decoration-thickness: initial; text-decoration-style: initial; text-decoration-color: initial;">
<div class="x1ni5s2d" style="font-family: inherit;">
<div class="x11i5rnm xat24cr x1mh8g0r x1vvkbs xdj266r" style="margin: 0px; overflow-wrap: break-word; font-family: inherit;">Wurde auch langsam mal Zeit, dass HAK wieder die Met-Bar durchlüftet! Begleitet werden die Schweizer Mundartcore-Metaller von den Musikern von The Kate Effect, die leider nichts mit der britischen Monarchie zu tun haben.</div>
<div class="x11i5rnm xat24cr x1mh8g0r x1vvkbs xtlvy1s" style="margin: 0.5em 0px 0px; overflow-wrap: break-word; font-family: inherit;">Eintritt: 15.00 CHF<br />Doors: 20.00 Uhr<br />Konzertende: ca. 23.30 Uhr</div>
<div class="x11i5rnm xat24cr x1mh8g0r x1vvkbs xtlvy1s" style="margin: 0.5em 0px 0px; overflow-wrap: break-word; font-family: inherit;">Der Vorverkauf läuft bis 19.00 Uhr am Konzerttag. Die restlichen Tickets werden an der Abendkasse verkauft. Reservationen nehmen wir aus organisatorischen Gründen nicht mehr entgegen.</div>
<div class="x11i5rnm xat24cr x1mh8g0r x1vvkbs xtlvy1s" style="margin: 0.5em 0px 0px; overflow-wrap: break-word; font-family: inherit;">HAK<br />Wer harte Klänge in der Mundartszene bis jetzt vermisst hat kann sich freuen! HAK hatte die Idee für berner Mundartmetal und veröffentlichte 2006 das Debütalbum «Dräiät Dürä». Der Titel ist Programm, kompromisslos und hart die Musik, provokativ die Texte.</div>
<div class="x11i5rnm xat24cr x1mh8g0r x1vvkbs xtlvy1s" style="margin: 0.5em 0px 0px; overflow-wrap: break-word; font-family: inherit;">2007 formierte sich die komplette Band mit HAK (Sänger/Shouter), Dänu und Räffu (beide Gitarre), Shady (Drums) und Cami (Bass). Mit den folgenden Auftritten konnte HAK zahlreiche Hörer der harten Musik erreichen und ihre Musik auch über die Kantonsgrenze hinaus tragen. Berndeutsche Musik dieser Art gab es bisher nicht, fette Metal- und Hardcoreriffs treffen auf Reime, Gesangslinien und Schreiattacken im breitesten Berndeutsch.</div>
<div class="x11i5rnm xat24cr x1mh8g0r x1vvkbs xtlvy1s" style="margin: 0.5em 0px 0px; overflow-wrap: break-word; font-family: inherit;">THE KATE EFFECT<br />Obwohl sie oft mit einem britischen Modephänomen verwechselt werden, ist The Kate Effect in Tat<br />und Wahrheit eine Band. Das Schweizer Quartett spielt eine Kombination aus Melodic Death- und<br />Thrash Metal mit einem kleinen Schuss Hardcore. Nach ihrer Gründung 2011 gab sich die Band ihren<br />sowohl von Ihrer Königlichen Hoheit der Gräfin von Cambridge als auch von einer ziemlichen Portion<br />Schwachsinn inspirierten Namen und veröffentlichte 2017 ihre Debut-EP «Make Metal Kate Again».<br />Aktuell sind Lukas Villiger (Gesang), Martin „März“ Kubli (Gitarre), Marco „Rada“ Radamonti (Gitarre)<br />und Pascal Maurer (Drums) in den Vorbereitungen für den Release ihrer zweiten EP im Frühling 2023. 
<div class="x1i10hfl xjbqb8w x6umtig x1b1mbwd xaqea5y xav7gou x9f619 x1ypdohk xt0psk2 xe8uvvx xdj266r x11i5rnm xat24cr x1mh8g0r xexx8yu x4uap5 x18d9i69 xkhd6sd x16tdsg8 x1hl2dhg xggy1nq x1a2a7pz xt0b8zv xzsf02u x1s688f" style="margin: 0px; text-align: inherit; padding: 0px; outline: none; text-decoration: none; -webkit-tap-highlight-color: transparent; font-weight: 600; cursor: pointer; box-sizing: border-box; list-style: none; touch-action: manipulation; background-color: transparent; display: inline; color: var(--primary-text); font-family: inherit; border: 0px initial initial;" tabindex="0" role="button">Weniger anzeigen</div>
</div>
</div>
</div>
<p> </p>
EOT;
$stripped = $plek_handler->remove_tags($string, $plek_handler -> get_forbidden_tags('textarea'), 'textarea');
$stripped = $plek_handler->strip_tags($stripped, $plek_handler->get_allowed_tags('textarea'));
s($string);
s($stripped);
echo $string;
echo "<hr/>";
echo $stripped;

?>
