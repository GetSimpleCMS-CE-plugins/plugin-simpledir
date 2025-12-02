<?php
// Admin panel functions
function simpledir_admin_message($status, $message) {
	// Sanitize the message for JavaScript output
	$safe_message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
	$status_class = ($status === 'error') ? 'error' : 'updated';
?>
	<script type="text/javascript">
		$(function() {
			var msg = <?php echo json_encode($safe_message, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
			var statusClass = <?php echo json_encode($status_class, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
			$("div.bodycontent").before(
				"<div class=\"" + statusClass + "\" style=\"display:block;\">" + msg + "</div>");
			$(".updated, .error").fadeOut(500).fadeIn(500);
		});
	</script>
<?php
}