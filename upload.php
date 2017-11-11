<?php
// PARAMETERS EXPECTED
// $_POST["deviceId"];
// $_POST["videoTitle"];
// $_POST["totalStreamlets"]
// $_POST["streamletNo"];
// $_FILES["fileToUpload"]["name"])

if (empty($_POST["deviceId"]) || empty($_POST["videoTitle"] || empty($_POST["totalStreamlets"] || empty($_POST["streamletNo"])))) {
    exit("Expecting parameters deviceId, videoTitle, totalStreamlets, streamletNo");
}

//header('Content-type: application/json');
define ('SITE_ROOT', realpath(dirname(__FILE__)));
$servername = "localhost";
$username = "team07";
$password = "cs5248team07";
$db = "team07";

$myfile = fopen("uploadphp.log", "w+") or die("Unable to open uploadphp.log!");
fwrite($myfile, "deviceId: ".$_POST["deviceId"]."\n");
fwrite($myfile, "videoTitle: ".$_POST["videoTitle"]."\n");
fwrite($myfile, "Total streamlets: ".$_POST["totalStreamlets"]."\n");
fwrite($myfile, "Streamlet no: ".$_POST["streamletNo"]."\n");

// Create connection
$conn = mysqli_connect($servername, $username, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    error_log("SQL connection failed", 0);
    fwrite($myfile, "SQL connection failed (Line " . __LINE__ . ")\n");
} else {
    echo "Connected to DB.<br/>";
}

$videoTitle =  $_POST["videoTitle"];

$target_dir = "video_repo/";
$target_file = $target_dir . $_POST["videoTitle"] . "/" . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file ($target_file) already exists.<br/>";
    error_log("Failed - file already exists", 0);
    fwrite($myfile, "Failed - file already exists (Line " . __LINE__ . ")\n");

    $uploadOk = 0;
}

// Allow certain file formats
if($imageFileType == "mp4" || $imageFileType == "m4s" || $imageFileType = "ts") {
    echo "File type: " . $imageFileType . "<br/>";
} else {
    echo "Sorry, only MP4/M4S/TS file types are allowed.<br/>";
    error_log("Failed - file format invalid", 0); 
    fwrite($myfile, "Failed - file format invalid (Line " . __LINE__ . ")\n");

    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.<br/>";
    error_log("Failed - file not uploaded", 0);
        fwrite($myfile, "Failed - file not uploaded (Line " . __LINE__ . ")\n");

// if everything is ok, try to upload file
} else {
    $file_dir = SITE_ROOT."/video_repo/" . $_POST["videoTitle"];
    if (!file_exists($file_dir)) {
        shell_exec ("mkdir " . $file_dir);
        shell_exec ("chmod 777 " . $file_dir);

        shell_exec ("mkdir " . $file_dir . "/240p");
        shell_exec ("chmod 777 " . $file_dir . "/240p");

        shell_exec ("mkdir " . $file_dir . "/360p");
        shell_exec ("chmod 777 " . $file_dir . "/360p");

        shell_exec ("mkdir " . $file_dir . "/480p");
        shell_exec ("chmod 777 " . $file_dir . "/480p");
    }
    $moveResult = move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], SITE_ROOT."/".$target_file);
    if ($moveResult) {
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.<br/>";
    } else {
        echo "Sorry, there was an error uploading your file.<br/>";
        error_log("Failed - error moving uploaded file.", 0);
        fwrite($myfile, "Failed - error moving uploaded file (Line " . __LINE__ . ")\n");
    }
}

// get the size of the output
$outputSize = ob_get_length();

// send headers to tell the browser to close the connection
header("Content-Length: $outputSize");
header('Connection: close');

// Flush all output
ob_end_flush();
ob_flush();
flush();

// close current session
if (session_id()) session_write_close();

// Start background processes

$findQuery = "SELECT * FROM UPLOAD_VIDEO WHERE VIDEO_TITLE = '" . $_POST["videoTitle"]. "';";

$uploadedNumberOfStreamlets = 0;
$newNumberOfStreamlets = 0;

$insertQuery = "INSERT INTO UPLOAD_VIDEO (`IDX`, `UPLOAD_DEVICE_ID`, `VIDEO_TITLE`, `TOTAL_NUMBER_OF_STREAMLETS`, `UPLOADED_NUMBER_OF_STREAMLETS`, `LAST_UPLOAD_TIME`, `LAST_TRANSCODED_STREAMLET_240P`, `LAST_TRANSCODED_STREAMLET_360P`, `LAST_TRANSCODED_STREAMLET_480P`) VALUES (NULL, '" . $_POST["deviceId"] . "', '" . $_POST["videoTitle"] . "', " . $_POST["totalStreamlets"] . ", '1', CURRENT_TIMESTAMP, '0', '0', '0');";

$findResult = $conn->query($findQuery);
if ($findResult->num_rows > 0) {
    // Existing record in database, update number of streamlets.
    $row = $findResult->fetch_assoc();
    $idx = $row["IDX"];
    $uploadedNumberOfStreamlets = $row["UPLOADED_NUMBER_OF_STREAMLETS"];
    $newNumberOfStreamlets = $uploadedNumberOfStreamlets + 1;

    $updateQuery = "UPDATE UPLOAD_VIDEO SET `UPLOADED_NUMBER_OF_STREAMLETS` = ". $newNumberOfStreamlets  ." WHERE IDX = ". $idx .";" ;
    $updateResult = $conn->query($updateQuery);

    fwrite($myfile, "Performing update query...\n");
    if (!$updateResult) {
        error_log("Failed - update query", 0);
        fwrite($myfile, "Failed - update query. Reason: ". $conn->error. " (Line " . __LINE__ . ")\n");
        fwrite($myfile, $updateQuery."\n)");
    } else {
        fwrite($myfile, "Updated SQL record successfully.\n");
    }
} else {
    // No existing records in database, new video with first streamlet to be stored.
    $insertResult = $conn->query($insertQuery);
    if (!$insertResult) {
        fwrite($myfile, "Failed - insert query. Reason: ".$conn->error. " (Line " . __LINE__ . ")\n");
        fwrite($myfile, $insertQuery."\n)");

    } else {
        fwrite($myfile, "Added SQL record successfully.\n");
    }
}

$initialAvailableVideos = 0;
// Retrieve initial count of AVAILABLE_VIDEOS
$initialAvailableResult = $conn->query("SELECT COUNT(*) FROM AVAILABLE_VIDEOS;");
if (!$initialAvailableResult) {
    fwrite($myfile, "Query for inital number of available videos failed.\n");
} else {
    while ($row = $initialAvailableResult->fetch_assoc()) {
        fwrite($myfile, "Total number of available videos: ". $row["COUNT(*)"] . "\n");
        $initialAvailableVideos = $row["COUNT(*)"];
    }
}

$transcodeCommand240p = SITE_ROOT."/transcode.sh " . $target_file . " 240p ". $file_dir . " ". $_POST["streamletNo"]; 
$transcodeCommand360p = SITE_ROOT."/transcode.sh " . $target_file . " 360p ". $file_dir . " ". $_POST["streamletNo"];
$transcodeCommand480p = SITE_ROOT."/transcode.sh " . $target_file . " 480p ". $file_dir . " ". $_POST["streamletNo"];

fwrite($myfile, "Transcode command: ". $transcodeCommand240p . "\n");
exec($transcodeCommand240p, $output240p, $status240p);
fwrite($myfile, "Transcode 240p exit status ".$status240p.", output: ".$output240p[0]."\n");
if (end($output240p) == "[0][0]") {
    $quality = "240p";
    include("updateSqlTranscodeStatus.php");
}

exec($transcodeCommand360p, $output360p, $status360p);
fwrite($myfile, "Transcode 360p exit status ".$status360p.", output: ".$output360p[0]."\n");
if (end($output360p) == "[0][0]") {
    $quality = "360p";
    include("updateSqlTranscodeStatus.php");
}
exec($transcodeCommand480p, $output480p, $status480p);

fwrite($myfile, "Transcode 480p exit status ".$status480p.", output: ".$output480p[0]."\n");
if (end($output480p) == "[0][0]") {
    $quality = "480p";
    include("updateSqlTranscodeStatus.php");
}

$finalAvailableVideos = 0;
// Retrieve final count of AVAILABLE_VIDEOS
$finalAvailableResult = $conn->query("SELECT COUNT(*) FROM AVAILABLE_VIDEOS;");
if (!$finalAvailableResult) {
    fwrite($myfile, "Query for final number of available videos failed.\n");
} else {
    while ($row = $finalAvailableResult->fetch_assoc()) {
        fwrite($myfile, "Total number of available videos: ". $row["COUNT(*)"] . "\n");
        $finalAvailableVideos = $row["COUNT(*)"];
    }
}

if ($finalAvailableVideos - $initialAvailableVideos == 0) {
    fwrite($myfile, "No change in total number of available videos.\n");
} else {
    fwrite($myfile, "New available video. To create playlists...\n");
    createM3U8VariantPlaylist($videoTitle);
    createMpdPlaylist($videoTitle);
}

function createM3U8VariantPlaylist($videoName) {
    global $myfile;
    $videoDir =  "video_repo/". $videoName;
    $playlist480pDir = $videoDir . "/480p/480p.m3u8";
    $playlist360pDir = $videoDir . "/360p/360p.m3u8";
    $playlist240pDir = $videoDir . "/240p/240p.m3u8";
    $m3u8_480p = fopen($playlist480pDir, "a+") or die("Unable to open $playlist480pDir");
    $m3u8_360p = fopen($playlist360pDir, "a+") or die("Unable to open $playlist360pDir");
    $m3u8_240p = fopen($playlist240pDir, "a+") or die("Unable to open $playlist240pDir");
    fwrite($m3u8_480p, "#EXT-X-ENDLIST");
    fwrite($m3u8_360p, "#EXT-X-ENDLIST");
    fwrite($m3u8_240p, "#EXT-X-ENDLIST");
    fwrite($myfile, "Completed quality playlists.");
    fclose($m3u8_480p);
    fclose($m3u8_360p);
    fclose($m3u8_240p);

    $videoM3U8Dir = $videoDir."/".$videoName.".m3u8";
    $videoM3U8File = fopen($videoM3U8Dir, "w+") or die ("Unable to create $videoM3U8File");
    fwrite($videoM3U8File, "#EXTM3U\n");

    fwrite($videoM3U8File, "#EXT-X-STREAM:INF:PROGRAM-ID=1,BANDWIDTH=2800000,CODECS=\"avc1.64001F,mp4a.40.2\"\n");
    fwrite($videoM3U8File, "http://monterosa.d2.comp.nus.edu.sg/~team07/". $playlist480pDir . "\n");

    fwrite($videoM3U8File, "#EXT-X-STREAM:INF:PROGRAM-ID=1,BANDWIDTH=2000000,CODECS=\"avc1.64001F,mp4a.40.2\"\n");
    fwrite($videoM3U8File, "http://monterosa.d2.comp.nus.edu.sg/~team07/". $playlist360pDir . "\n");

    fwrite($videoM3U8File, "#EXT-X-STREAM:INF:PROGRAM-ID=1,BANDWIDTH=900000,CODECS=\"avc1.64001F,mp4a.40.2\"\n");
    fwrite($videoM3U8File, "http://monterosa.d2.comp.nus.edu.sg/~team07/". $playlist240pDir . "\n");

    fclose($videoM3U8File);

    // Add to src/list.media.json
    $fullVideoUri = "http://monterosa.d2.comp.nus.edu.sg/~team07/video_repo/$videoName/$videoName.m3u8";

    $json = file_get_contents("src/list.media.json");
    $originalJsonDecode = json_decode($json, JSON_OBJECT_AS_ARRAY);
    fwrite($myfile, "Existing size in JSON list: " . sizeof($originalJsonDecode) ."\n");

    $newJsonEntry = array(
        "name" => $videoName." (M3U8)",
        "uri" => $fullVideoUri
    );

    array_unshift($originalJsonDecode, $newJsonEntry);
    fwrite($myfile, "JSON entry prepared.\n");

    // $newJsonStr = substr(stripcslashes(json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)), 1, -1);
    $newJsonStr = json_encode($originalJsonDecode, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    // include_once("formatJson.php");
    // fwrite($myfile, "Indenting JSON file...\n");
    // $indentJson = indent($newJsonStr);

    fwrite($myfile, "New Json Str: " . $newJsonStr);
    $newJsonFile = fopen("src/list.media.json", "w+") or die("Unable to open list.media.json!");
    fwrite($newJsonFile, $newJsonStr);
    fclose($newJsonFile);
}

function createMpdPlaylist($videoName) {
    global $myfile;

}

mysqli_close($conn);
fclose($myfile);

?>