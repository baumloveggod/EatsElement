<?php
$today = date("Y-m-d");
header("Location: rezept_detail.php?datum=" . urlencode($today));
?>