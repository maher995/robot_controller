<?php
// بيانات الاتصال بقاعدة البيانات - غيّرها ببياناتك من InfinityFree
$host = "sql301.infinityfree.com";      // اسم السيرفر (Hostname)
$user = "if0_42364660";                // اسم المستخدم
$pass = "IA7WSssljPPL";           // كلمة المرور
$dbname = "if0_42364660_myDatabase";   // اسم قاعدة البيانات

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "فشل الاتصال: " . $conn->connect_error]));
}
?>
