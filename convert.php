<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Convert URL</title>
</head>
<body>
<form action="convert.php" method="POST">
    Nhập link docln.net: <input type="text" name="link"><br>
    <input type="submit">
</form>

<?php
include_once 'HTML_TO_DOC.class.php';
$htd = new HTML_TO_DOC();

if (isset($_POST['link']) && !empty($_POST['link'])) {
    $url = $_POST['link'];

    // Kiểm tra URL trước khi sử dụng file_get_contents
    if (!empty($url)) {
        $html = file_get_contents($url);
        if ($html === FALSE) {
            echo "Không thể tải nội dung từ URL.";
            exit;
        }

        preg_match('/<title>(.*?)<\/title>/', $html, $matches);
        $title = isset($matches[1]) ? $matches[1] : 'Untitled';

        // Bước 1: Khởi tạo CURL và thiết lập URL của trang web
        $ch = curl_init();

        // Bước 2: Thiết lập các tuỳ chọn CURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Nếu cần bỏ qua kiểm tra chứng chỉ SSL

        // Bước 3: Thực hiện yêu cầu CURL và lưu phản hồi vào biến $response
        $response = curl_exec($ch);

        // Kiểm tra lỗi CURL (nếu có)
        if (curl_errno($ch)) {
            echo 'Lỗi CURL: ' . curl_error($ch);
        } else {
            // Bước 4: Đóng phiên CURL
            curl_close($ch);

            // Bước 5: Phân tích nội dung HTML và trích xuất các phần tử có id='chapter-content'
            $dom = new DOMDocument();
            @$dom->loadHTML($response);

            // Tìm phần tử có id='chapter-content'
            $element = $dom->getElementById('chapter-content');

            // Mảng để lưu nội dung của phần tử
            $textContent = "";
            $innerHTML = "";

            if ($element) {
                $textContent = $element->textContent;
                $innerHTML = $dom->saveHTML($element);
            }

            // Thay thế img bằng link src
            $new_element = preg_replace('/<img[^>]*src=["\']([^"\']+)["\'][^>]*>/', '$1', $innerHTML);


            // Thay thế <p thành \\p<p để xuống dòng sau khi strips tag
            $new_element = str_replace("<p", "\\p<p", $new_element);


            $new_element = strip_tags($new_element);


            $new_element = str_replace("\\p", "</p><p>", $new_element);



            $title = str_replace(" - Cổng Light Novel - Đọc Light Novel", "", $title);
            $title = str_replace("Đọc", "", $title);
//            echo $title;

            $htd->createDoc($new_element, $title,1);

            // Bước 6: Ghi nội dung vào file TXT
            // $file = 'output_chapter_content.txt';
            // file_put_contents($file, "Text Content:\n" . $textContent . "\n\nHTML Content:\n" . $new_element);
            // echo "Đã ghi nội dung vào file output_chapter_content.txt";
        }
    } else {
        echo "URL không hợp lệ.";
    }
} else {
    echo "Không có link nào được cung cấp.";
}
?>
</body>
</html>
