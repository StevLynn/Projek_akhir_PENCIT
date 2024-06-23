<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pengolahan Citra</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Hasil Pengolahan Citra</h1>
        <div class="image-container">
            <div class="upload">
                <h2>Gambar Asli</h2>
                <?php
                if (isset($_GET['original_image'])) {
                    $originalImage = $_GET['original_image'];
                    echo "<img src='$originalImage' alt='Gambar Asli'>";
                }
                ?>
            </div>
            <div class="output">
                <h2>Gambar Hasil Pengolahan</h2>
                <?php
                if (isset($_GET['resized_image'])) {
                    $resizedImage = $_GET['resized_image'];
                    echo "<img src='$resizedImage' alt='Hasil Pengolahan'>";
                } elseif (isset($_GET['thresholded_image'])) {
                    $thresholdedImage = $_GET['thresholded_image'];
                    echo "<img src='$thresholdedImage' alt='Hasil Thresholding'>";
                } elseif (isset($_GET['negative_image'])) {
                    $negativeImage = $_GET['negative_image'];
                    echo "<img src='$negativeImage' alt='Hasil Proses Citra Negatif'>";
                } elseif (isset($_GET['enhanced_image'])) {
                    $enhancedImage = $_GET['enhanced_image'];
                    echo "<img src='$enhancedImage' alt='Hasil Perbaikan Citra'>";
                } elseif (isset($_GET['sobel_image'])) {
                    $sobelImage = $_GET['sobel_image'];
                    echo "<img src='$sobelImage' alt='Hasil Deteksi Tepi (Sobel)'>";
                }
                ?>
            </div>
        </div>
        <div class="bottom">
            <a href="index.php">Kembali ke halaman utama</a>
        </div>
    </div>
</body>
</html>
