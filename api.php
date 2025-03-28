<?php //cutehack
header('Content-Type: application/json');

if (isset($_GET['uid']) && isset($_GET['server_name'])) {
    $uid = $_GET['uid'];
    $server_name = $_GET['server_name'];

    $token_url = "http://teamxcutehack.serv00.net/like/token_ind.json";
    $ffinfo_url = "https://infoff.vercel.app/info?region={$server_name}&uid={$uid}";
    $like_api_url = "https://likeapiff.thory.in/like?uid={$uid}&server_name={$server_name}&token_url=" . urlencode($token_url);

    $ffinfo_response = @file_get_contents($ffinfo_url);

    if ($ffinfo_response === FALSE || empty($ffinfo_response)) {
        echo json_encode(["error" => "Failed to fetch data from ffinfo API or empty response"]);
        exit;
    }

    $ffinfo_data = json_decode($ffinfo_response, true);

    if ($ffinfo_data === NULL) {
        echo json_encode(["error" => "Invalid JSON format from ffinfo API", "raw_response" => $ffinfo_response]);
        exit;
    }

    if (!isset($ffinfo_data["basicInfo"]["liked"]) || !isset($ffinfo_data["basicInfo"]["nickname"])) {
        echo json_encode(["error" => "Missing 'liked' or 'nickname' in response", "parsed_data" => $ffinfo_data]);
        exit;
    }

    $likes_before = (int) str_replace(',', '', $ffinfo_data["basicInfo"]["liked"]);
    $player_nickname = $ffinfo_data["basicInfo"]["nickname"];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $like_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $like_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        echo json_encode(["error" => "Like API call failed with status code $http_code"]);
        exit;
    }

    $like_data = json_decode($like_response, true);
    if ($like_data === NULL || !isset($like_data["LikesafterCommand"])) {
        echo json_encode(["error" => "Invalid JSON from Like API"]);
        exit;
    }

    $likes_after = (int) $like_data["LikesafterCommand"];
    $likes_sended = $likes_after - $likes_before;

    $final_response = [
        "PlayerNickname" => $player_nickname,
        "LikesbeforeCommand" => $likes_before,
        "LikesafterCommand" => $likes_after,
        "likeSended" => $likes_sended,
        "UID" => $uid,
        "credit" => "@thoryxff",
        "status" => 1,
        "thanks" => "super thanks to thoryxff for providing this like source code!",
        "owner" => "cutehack Chx 💀"
    ];

    echo json_encode($final_response, JSON_PRETTY_PRINT);
} else {
    echo json_encode(["error" => "Missing required parameters: uid or server_name"]);
}
?>