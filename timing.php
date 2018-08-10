<?php

$current = file_get_contents("timing.json");
$current=json_decode($current,true);
$current['time']=microtime(true); // server time (sec)
echo json_encode($current);

?>