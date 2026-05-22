<?php
/**
 * Manual Installation Helper for Resume Parser Dependencies
 * Use this if Composer is not available
 */

echo "<h2>Manual Dependency Installation Helper</h2>";
echo "<p>This script helps you install PDF and DOCX parsing libraries manually.</p>";

// Check if vendor directory exists
$vendorDir = __DIR__ . '/vendor';
$autoloadFile = $vendorDir . '/autoload.php';

if (file_exists($autoloadFile)) {
    echo "<div style='background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>✓ Dependencies already installed!</strong><br>";
    echo "The vendor/autoload.php file exists. Resume parser should work.";
    echo "</div>";
    exit;
}

echo "<h3>Installation Options:</h3>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #ffc107;'>";
echo "<strong>Option 1: Install Composer (Recommended)</strong><br>";
echo "1. Download Composer from: <a href='https://getcomposer.org/download/' target='_blank'>https://getcomposer.org/download/</a><br>";
echo "2. Run: <code>composer install</code> in the project directory<br>";
echo "</div>";

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #0c5460;'>";
echo "<strong>Option 2: Use DOCX Files Instead (No Installation Needed)</strong><br>";
echo "DOCX files work without any external libraries!<br>";
echo "Just convert your PDF resume to DOCX format and upload it.<br>";
echo "The resume parser will automatically extract text from DOCX files.";
echo "</div>";

echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #721c24;'>";
echo "<strong>Current Status:</strong><br>";
echo "PDF Parser Library: " . (class_exists('Smalot\PdfParser\Parser') ? "✓ Installed" : "✗ Not Installed") . "<br>";
echo "DOCX Parser Library: " . (class_exists('PhpOffice\PhpWord\IOFactory') ? "✓ Installed" : "✗ Not Installed") . "<br>";
echo "ZipArchive Extension: " . (class_exists('ZipArchive') ? "✓ Available" : "✗ Not Available") . "<br>";
echo "</div>";

// Check if ZipArchive is available (needed for DOCX fallback)
if (class_exists('ZipArchive')) {
    echo "<div style='background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Good News!</strong> DOCX parsing will work even without external libraries.<br>";
    echo "You can upload DOCX files right now without any installation.";
    echo "</div>";
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Warning:</strong> ZipArchive extension is not enabled.<br>";
    echo "Please enable <code>php_zip.dll</code> in your php.ini file and restart Apache.";
    echo "</div>";
}

echo "<hr>";
echo "<h3>Quick Test:</h3>";
echo "<p>Try uploading a DOCX resume file - it should work without any installation!</p>";
?>

