<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Play sync music</title>



  <style>
  .musicName {
    color: #fff;
    font-size: 62px;
    text-align: center;
    margin: 30px 0 0 0;
  }
  .musicTime {
    color: #555;
    font-size: 32px;
    text-align: center;
    margin: 20px;
  }
  .status {
  font-size: 12px;
    color: #777;
    text-align: center;
  }

  #canvas {
    position: fixed;
    z-index: -1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
  }

  #music {
    display: none;
  }
  </style>
</head>
<body>

  <canvas id="canvas" onclick="musicElem.play();"></canvas>

  <h1 id="name" class="musicName"></h1>
  <h2 id="time" class="musicTime" ></h2>
  <h3 id="status" class="status" ></h2>

  <audio id="music">
    <source id="musicSource" src="" type="audio/mpeg">
  </audio>
  

  <script>
    var playing = {};
    var nameElem = document.getElementById("name");
    var timeElem = document.getElementById("time");
    var musicElem = document.getElementById("music");
    var statusElem = document.getElementById("status");
    var musicSrcElem = document.getElementById('musicSource');

    function setStatus(str) {
      statusElem.innerHTML = str;
      console.log(str);
    }

    setInterval(() => {
      if(playing.time) {
        timeElem.innerHTML = calcMusicTime().toFixed(0);
      }
    }, 1000);

    setInterval(() => {
      // auto synchronize
      syncWithServer();
    }, 3000);

    function localTime(){
      return Date.now()/1000;
    }
    function calcMusicTime(){
      return localTime() + playing.diff - playing.start;
    }
    function syncWithServer()
    {

        // get music
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.open( "GET", "./timing.php", false ); // false for synchronous request
        xmlHttp.send( null );
        var res = xmlHttp.responseText;
        playing = JSON.parse(res);


        // sync server time
        var reqDelay = Date.now();
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.open( "GET", "./serverTime.php", false ); // false for synchronous request
        xmlHttp.send( null );
        playing.time = parseFloat(xmlHttp.responseText);
        reqDelay = Date.now() - reqDelay;
        reqDelay = reqDelay/1000 /2; // sec, one side
        setStatus("reqDelay: "+reqDelay.toFixed(3));

        playing.diff = playing.time - reqDelay - localTime();
        nameElem.innerHTML = playing.name;


        var timeDiff = musicElem.currentTime-calcMusicTime();
        if(Math.abs(timeDiff)>1) {
          setStatus("Changing music: "+timeDiff.toFixed(3));
          musicSrcElem.src = playing.file;
          musicElem.load();
          musicElem.currentTime = calcMusicTime();
          musicElem.currentSrc = playing.file;
          if(musicElem.currentTime>=0) {
            musicElem.play();
            animateMusic();
          }
        } else {

          if(Math.abs(timeDiff)>0.05) {
            setStatus("sync just time diff: "+timeDiff.toFixed(3));
            // musicElem.pause();
            // setTimeout(() => {
              musicElem.currentTime = calcMusicTime();
              // musicElem.play();              
            // }, 50);
            
          } else {
            setStatus("time diff is small: "+timeDiff.toFixed(3));
          }
        }
    }


  // audio animation
  function animateMusic() {
    var context = new AudioContext();
    var src = context.createMediaElementSource(musicElem);
    var analyser = context.createAnalyser();

    var canvas = document.getElementById("canvas");
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    var ctx = canvas.getContext("2d");

    src.connect(analyser);
    analyser.connect(context.destination);

    analyser.fftSize = 256;

    var bufferLength = analyser.frequencyBinCount;
    console.log(bufferLength);

    var dataArray = new Uint8Array(bufferLength);

    var WIDTH = canvas.width;
    var HEIGHT = canvas.height;

    var barWidth = (WIDTH / bufferLength) * 2.5;
    var barHeight;
    var x = 0;

    function renderFrame() {

      x = 0;

      analyser.getByteFrequencyData(dataArray);

      ctx.fillStyle = "#000";
      ctx.fillRect(0, 0, WIDTH, HEIGHT);

      // return;

      for (var i = 0; i < bufferLength; i++) {
        barHeight = dataArray[i];
        
        var r = barHeight + (25 * (i/bufferLength));
        var g = 250 * (i/bufferLength);
        var b = 50;

        ctx.fillStyle = "rgb(" + r + "," + g + "," + b + ")";
        ctx.fillRect(x, HEIGHT - barHeight, barWidth, barHeight);

        x += barWidth + 1;
      }
      requestAnimationFrame(renderFrame);
    }

    renderFrame();
  };




    syncWithServer();

  </script>
</body>
</html>