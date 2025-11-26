<?php
// test_api.php
// Yeh script check karega ki PHP Python se baat kar pa raha hai ya nahi

$url = 'http://127.0.0.1:5000/predict_disease';

echo "<h2>🔍 Connection Test Debugger</h2>";
echo "Testing connection to: <b>$url</b><hr>";

// Dummy Data for testing
$data = json_encode(['symptoms' => 'chest pain']);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 seconds timeout

$response = curl_exec($ch);
$error_msg = curl_error($ch); // Asli Error yahan milega
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($error_msg) {
    echo "<h3 style='color:red'>❌ CONNECTION FAILED</h3>";
    echo "<b>Technical Error:</b> " . $error_msg . "<br><br>";
    echo "<strong>Possible Solutions:</strong><ul>";
    echo "<li>Agar error 'Connection refused' hai -> Python Server band hai.</li>";
    echo "<li>Agar error 'Timeout' hai -> Firewall abhi bhi rok raha hai.</li>";
    echo "</ul>";
} else {
    echo "<h3 style='color:green'>✅ SUCCESS! Connection Established</h3>";
    echo "<b>HTTP Status Code:</b> " . $http_code . "<br>";
    echo "<b>Server Response:</b> <pre>" . htmlspecialchars($response) . "</pre>";
    echo "<br>Agar yahan Success dikh raha hai, to aapka main code bhi chalna chahiye.";
}
?>