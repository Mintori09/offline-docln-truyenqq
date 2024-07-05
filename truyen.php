<form action="image.php" method="POST">
    <label>Nhập link truyện qq: </label><br>
    <input type="text" name="LINK"><br>
        <input type="submit">
</form>
<?php
// Function to fetch the HTML content of a webpage
function fetchWebPage($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

// Function to extract image URLs from HTML content
function extractImageUrls($html, $baseUrl) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $imageTags = $dom->getElementsByTagName('img');
    $imageUrls = [];
    foreach ($imageTags as $img) {
        $src = $img->getAttribute('src');
        if (!filter_var($src, FILTER_VALIDATE_URL)) {
            // If the src is a relative URL, convert it to an absolute URL
            $src = $baseUrl . '/' . ltrim($src, '/');
        }
        $imageUrls[] = $src;
    }
    return $imageUrls;
}

// Function to download and save images using cURL with headers
function downloadImages($imageUrls, $saveDir) {
    foreach ($imageUrls as $url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        curl_setopt($ch, CURLOPT_REFERER, 'https://truyenqqviet.com'); // Adjust the referer as needed

        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($imageData !== false && $httpCode == 200) {
            $imagePath = $saveDir . '/' . basename(parse_url($url, PHP_URL_PATH));
            file_put_contents($imagePath, $imageData);
            echo "Saved image: $imagePath\n";
        } else {
            echo "Failed to download image: $url\n";
        }
    }
}

// Main script
$url = $_POST["LINK"]; // Replace with the target URL
$saveDir = 'images'; // Directory to save images

// Create the save directory if it doesn't exist
if (!file_exists($saveDir)) {
    mkdir($saveDir, 0777, true);
}

// Fetch the webpage content
$html = fetchWebPage($url);

// Extract image URLs
$imageUrls = extractImageUrls($html, $url);

// Download and save images
downloadImages($imageUrls, $saveDir);

echo "Done!";
?>
