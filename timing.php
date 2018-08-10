<?php

$current = file_get_contents("timing.json");
$current=json_decode($current,true);
echo json_encode($current);

?>