<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Sync music Admin panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" type="text/css" media="screen" href="main.css" />
</head>
<body>

<pre>
<?php

// if we submiting a music request
print_r($_POST);
if(array_key_exists("submit",$_POST)) {
  $current = array(
    "file"=>"./musics/".$_POST['submit'],
    "name"=>$_POST['submit'],
    "start"=>microtime(true)+(int)$_POST['delay']
  );
  file_put_contents("timing.json",json_encode($current));
}

?>
</pre>

  <form method="post">
  <div>
    <input type="submit" name="submit" value="stop">
  </div>
  <div>
    delay: <input type="number" name="delay" value="0" />
  </div>
  <ul>
    <?php
      $files = scandir('./musics/');
      foreach($files as $file) {
    ?>  
      <li>
        <input type="submit" name="submit" value="<?php echo $file; ?>">
      </li>
    <?php  } ?>
  </ul>
  </form>

  
</body>
</html>