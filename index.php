<?php
$tmpdir   = "/tmp";
$audiodir = "./audio";

if (isset($_REQUEST["speech"]) && !empty($_REQUEST['speech'])) {
$speech      = stripslashes(trim($_POST["speech"]));
$filename    = uniqid();
$speech_file = "{$tmpdir}/{$filename}";
$wave_file   = "{$audiodir}/{$filename}.wav";
$mp3_file    = "{$audiodir}/{$filename}.mp3";
$from    = $_REQUEST['from'];
$number  = $_REQUEST['number'];

$uname = "**TEXTLOCAL USERNAME**";
$pword = "**TEXTLOCAL PASSWORD**";

if(isset($_REQUEST['comments'])) {
	$speech=explode(' ',$comments,2);
	$number=$speech[0];
	$speech=stripslashes(trim($speech[1]));
}


exec("pico2wave -w " . $wave_file . " '" . addslashes($speech) . "'");

$lame_cmd = sprintf("lame %s %s", $wave_file, $mp3_file);
exec($lame_cmd);
unlink($wave_file);
$listen_file = basename($mp3_file);
$manifest    = '#' . date('D M d H:i:s e Y') . "n" . $listen_file . "=audio/mp3";
file_put_contents('../tmp/manifest', $manifest);
copy($mp3_file, '../tmp/' . basename($mp3_file));
$zipfile = $filename . '.zip';
system('zip -j /var/www/vhosts/api/zips/' . $zipfile . ' ../tmp/manifest ../tmp/' . basename($mp3_file).' >/dev/null');

if (isset($_REQUEST['mms'])) {
$url     = "http://api.dixon.io/zips/" . $zipfile;
$subject = $_REQUEST['subject'];
$subject = urlencode($subject); //encode special characters (e.g. .,& etc)
$data    = "uname=" . $uname . "&pword=" . $pword . "&url=" . $url . "&from=" . $from;
$data .= "&number=" . $number . "&subject=" . $subject;
$ch = curl_init('http://www.txtlocal.com/sendmmspost.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch); //This is the result from Textlocal
echo $result;
curl_close($ch);
}
} else {
// default values
$speech = "Hello there!";
}
?>
<html>
<head>
<title>Text to Voice MMS</title>
</head>
<body>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
Message:<br />
<textarea name="speech" wrap="VIRTUAL" style="width:350px;height:100px;"><?php echo $speech;?></textarea>
<br />
<input name='mms' type='checkbox' value='1' <?php if (isset($_REQUEST['mms'])) echo 'checked';?>>
MMS to <input type='text' name='number' value='<?php if (isset($_REQUEST['number'])) echo $_REQUEST['number'];?>'>
<br />
From: <input type='text' name='from' value='447516055755'><br />
Subject: <input type='text' name='subject' value='Text to Speech'><br />
<input name="go" type="submit" value="Go">
<?php
if (isset($listen_file)) {
?>
                <a href="audio/<?php echo $listen_file;?>">Listen</a>
<?php
}
?>
        </form>
</body>
</html>
