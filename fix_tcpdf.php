<?php
$file = 'vendor/tecnickcom/_tcpdf.0.002/tcpdf.php';
$content = file_get_contents($file);

// Mencari dan mengganti pola kurung kurawal untuk akses array
$pattern = '/(\$[a-zA-Z0-9_]+){(\$[a-zA-Z0-9_]+)}/';
$replacement = '$1[$2]';
$content = preg_replace($pattern, $replacement, $content);

// Mencari dan mengganti pola lain yang mungkin ada
$pattern2 = '/(\$[a-zA-Z0-9_]+){([0-9]+)}/';
$replacement2 = '$1[$2]';
$content = preg_replace($pattern2, $replacement2, $content);

file_put_contents($file, $content);
echo "File telah diperbaiki!";
?>