<!DOCTYPE html>
<?php
// handles data transfer and authenication

//pull in info
$session = $_GET['session'];
    $ka = $_GET['ka'];
    $kb = $_GET['kb'];
    $db = $_GET['db'];
    $row = $_GET['row'];
    $token = $_GET['token'];
    $exit = $_GET['exit'];
    $dat = $_GET['dat'];

if ($_GET['mode'] !='FILE')
{
    $session = $_GET['session'];
    $ka = $_GET['ka'];
    $kb = $_GET['kb'];
    $db = $_GET['db'];
    $row = $_GET['row'];
    $token = $_GET['token'];
    $exit = $_GET['exit'];
    $dat = $_GET['dat'];

    //build urls
    $saveurl = 'resource/datahandling/submit.php?stage=3&session='.$session.'&ka='.$ka.'&kb='.$kb.
    '&token='.$token.'&act=QUERY&db='.$db;

    $query = 'q=UPDATE`orokonui`.`'.$db."`SET`html`='"."|DATA|"."'WHERE`".$db.'`.`id`='."'".$row."'";
    $exiturl = 'rules.php?session='.$session.'&ka='.$ka.'&kb='.$kb.'&token='.$token.'&redirect='.$exit;
}else{
    if (file_exists($_GET['f']))
    {
        $f = fopen($_GET['f'], "r");
        $dat = fread($f, filesize($_GET['f']));
        fclose($f);
    }
    $savefileurl = 'resource/datahandling/submit.php?stage=3&session='.$session.'&ka='.$ka.'&kb='.$kb.
    '&token='.$token.'&act=WRITE_FILE&f='.$_GET['f'];
}
?>
<html>
  <head>
    <meta charset="utf-8">
     <link href="https://fonts.googleapis.com/css?family=Space+Mono" rel="stylesheet">
  </head>
  <body>
    <div id='toolbar'>
      <h5>HTML/JS In-Browser Editor
      <input type='submit' id='exit' value='Exit' onclick="exit()"></input>
      <input type='submit' id='save' value='Save' onclick="save()"></input>
      <input type='submit' id='preview' value='Preview' onClick="genPre()"></input>
    </h5>
    </div>
    <div id='editordiv'>
      <textarea cols='100' rows='35' id='editor'><?php echo $dat; ?></textarea>
      <!--<canvas height='500' width='5000' id='editor'></canvas>
      <script>
        var text = ""
        var editor = document.getElementById('editor');
        var ctx = editor.getContext('2d');
        var col = 1;
        var lettersize = 11;
        document.onkeypress = function(evt)
        {
          console.log(String.fromCharCode(evt.which));
          text += String.fromCharCode(evt.which);
          atext = text.split("");
          ctx.font="12px 'Space Mono', monospace";
          ctx.fillStyle ='white';
          ctx.fillRect(0,0,500,500);
          for (var i=0;i<atext.length;i++)
          {
            if (atext[i] == 'var')
            {
              ctx.fillStyle='#cb37d3';
              ctx.fillText(atext[i],i*lettersize,col*lettersize);
            }
            else
            {
              ctx.fillStyle='#000000';
              ctx.fillText(atext[i],i*lettersize,col*lettersize);
            }
          }
        }
      </script>-->
    </div>
    <hr>
    <div id='previewdiv'>
      <p>Click the preview button to generate a preview of how it will look down here, note that variable subsitution is not supported in the preview</p>
    </div>
    <script>
    var div = document.createElement("div");
    function save()
    {
      if ('<?php echo $_GET['mode'];?>' == 'FILE')
      {
        // save to file, through submit.php
	var doc = document.getElementById('editor');
        var save = new XMLHttpRequest();
        var savestr = "<?php echo $savefileurl;?>";
        var q = "contents="+doc.value;
        encodeURI(q);
        savestr +="&"+q;
        save.open("GET", savestr);
        save.send();
        console.log(save.responseText);
      }
      else
      {
        var doc = document.getElementById('editor');
        var save = new XMLHttpRequest();
        var savestr = "<?php echo $saveurl;?>";
        var q = "<?php echo $query; ?>".split("|");
        q = q[0] + doc.value + q[2];
        encodeURI(q);
        savestr +="&"+q;
        save.open("GET", savestr);
        save.send();
        console.log(save.responseText);
      }
    }
    function exit()
    {
      if ('<?php echo $_GET['exit'];?>' != 'close')
      {
        window.location = '<?php echo $exiturl; ?>';
      }
      else
      {
        window.close();
        // if this fail due to security setting, etc... redirect to a blank page.
        window.location = 'about:blank';
      }
    }
    function genPre()
    {
      // delete old preview
      var pre = document.getElementById('previewdiv');
      var doc = document.getElementById('editor');
      div.style.width = "100%";
      div.style.height = "500px";
      div.id="preview";
      div.innerHTML = doc.value;
      pre.appendChild(div);
      //pre.removeChild(document.getElementById('preview'));
      //alert(pre.innerHTML);
    }
    </script>
  </body>
</html>
