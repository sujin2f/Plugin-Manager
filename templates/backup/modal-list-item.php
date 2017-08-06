<?php
/**
 * Grouping Modal : List Item
 *
 * @package     WordPress
 * @subpackage  Plugin Manager PRO
 * @since       0.0.1
 * @author      Sujin 수진 Choi http://www.sujinc.com/
*/

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}
?>

<li>
	<label for="<?php echo $group_id ?>">
		<input id="<?php echo $group_id ?>" type="checkbox" data-id="<?php echo $group_id ?>" data-name="<?php echo $group_name ?>" />
		<?php echo $group_name ?>
	</label>

	<input type="text" value="<?php echo $colour ?>" class="group-colour-picker" data-id="<?php echo $group_id ?>" />
</li>
