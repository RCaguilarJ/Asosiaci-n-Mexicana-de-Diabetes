<!DOCTYPE html>
<script>
window.addEventListener('unhandledrejection', function(event) {
    if (event.reason && event.reason.message && 
        event.reason.message.includes('message channel closed')) {
        event.preventDefault(); // Suprimir error de extensión
    }
});
</script>

<?php
header("Location: views/index.php");
exit;
?>