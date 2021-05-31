<?php
/**
 * View: Events Bar Views List
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/events-bar/views/list.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.0.0
 *
 * @var array $public_views Array of data of the public views, with the slug as the key.
 */
//s($public_views);
$public_views['add'] = new stdClass;
$public_views['add'] -> view_class = 'Tribe\Events\Views\V2\Views\List_View';
$public_views['add'] -> view_url = site_url( 'event-eintragen' );
$public_views['add'] -> view_label = __('Event hinzufügen','pleklang');

?>
<div
	class="tribe-events-c-view-selector__content"
	id="tribe-events-view-selector-content"
	data-js="tribe-events-view-selector-list-container"
>
	<ul class="tribe-events-c-view-selector__list">
		<?php foreach ( $public_views as $public_view_slug => $public_view_data ) : ?>
			<?php $this->template(
				'components/events-bar/views/list/item',
				[ 'public_view_slug' => $public_view_slug, 'public_view_data' => $public_view_data ]
			); ?>
		<?php endforeach; ?>
	</ul>
</div>
