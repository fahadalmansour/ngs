<?php
/**
 * Toast Notification Component
 *
 * Empty container for JavaScript-populated toast notifications.
 * Included in footer.php for global availability.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ngs-toast-container" aria-live="assertive" aria-atomic="true" role="alert">
	<!-- Toast messages are dynamically inserted here by assets/js/components/toast.js -->
</div>
