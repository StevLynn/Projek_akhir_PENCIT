<?php
include 'koneksi.php'; // Sertakan file koneksi ke database

// Fungsi untuk memeriksa jenis file yang diunggah
function isAllowedFileType($file) {
    $allowedTypes = ['image/jpeg', 'image/png'];
    $fileInfo = getimagesize($file);
    $fileType = $fileInfo['mime'];
    return in_array($fileType, $allowedTypes);
}

// Fungsi untuk melakukan resize gambar
function resizeImage($sourceFile, $targetWidth, $targetHeight, $targetFile) {
    if (empty($sourceFile) || !file_exists($sourceFile)) {
        throw new Exception('Path file tidak valid atau kosong');
    }

    if (!isAllowedFileType($sourceFile)) {
        throw new Exception('File yang diupload harus berformat JPEG atau PNG.');
    }

    // Membuat gambar sumber dari file yang diupload
    $imageInfo = getimagesize($sourceFile);
    list($width, $height) = $imageInfo;
    $src = ($imageInfo[2] === IMAGETYPE_JPEG) ? imagecreatefromjpeg($sourceFile) : imagecreatefrompng($sourceFile);
    $dst = imagecreatetruecolor($targetWidth, $targetHeight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

    // Menyimpan gambar yang sudah diresize ke file
    if ($imageInfo[2] === IMAGETYPE_JPEG) {
        imagejpeg($dst, $targetFile);
    } else {
        imagepng($dst, $targetFile);
    }
    imagedestroy($dst);
    return $targetFile; // Mengembalikan path gambar hasil resize
}

// Fungsi untuk melakukan thresholding pada gambar dengan nilai ambang batas tertentu
function thresholdImage($sourceFile, $thresholdValue, $targetFile) {
    if (empty($sourceFile) || !file_exists($sourceFile)) {
        throw new Exception('Path file tidak valid atau kosong');
    }

    if (!isAllowedFileType($sourceFile)) {
        throw new Exception('File yang diupload harus berformat JPEG atau PNG.');
    }

    // Membuat gambar sumber dari file yang diupload
    $imageInfo = getimagesize($sourceFile);
    list($width, $height) = $imageInfo;
    $src = ($imageInfo[2] === IMAGETYPE_JPEG) ? imagecreatefromjpeg($sourceFile) : imagecreatefrompng($sourceFile);
    $dst = imagecreatetruecolor($width, $height);

    // Melakukan thresholding pada gambar
    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $rgb = imagecolorat($src, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $grayValue = round(($r + $g + $b) / 3); // Menghitung nilai rata-rata warna grayscale

            // Melakukan thresholding dengan nilai ambang batas
            $color = ($grayValue > $thresholdValue) ? 255 : 0;
            $newColor = imagecolorallocate($dst, $color, $color, $color);
            imagesetpixel($dst, $x, $y, $newColor);
        }
    }

    // Menyimpan gambar hasil thresholding ke file
    imagepng($dst, $targetFile); // Simpan sebagai file PNG
    imagedestroy($dst);
    return $targetFile; // Mengembalikan path gambar hasil thresholding
}

// Fungsi untuk menghasilkan citra negatif
function negativeImage($sourceFile, $targetFile) {
    if (empty($sourceFile) || !file_exists($sourceFile)) {
        throw new Exception('Path file tidak valid atau kosong');
    }

    if (!isAllowedFileType($sourceFile)) {
        throw new Exception('File yang diupload harus berformat JPEG atau PNG.');
    }

    // Membuat gambar sumber dari file yang diupload
    $imageInfo = getimagesize($sourceFile);
    list($width, $height) = $imageInfo;
    $src = ($imageInfo[2] === IMAGETYPE_JPEG) ? imagecreatefromjpeg($sourceFile) : imagecreatefrompng($sourceFile);

    // Membuat gambar negatif
    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $rgb = imagecolorat($src, $x, $y);
            $r = 255 - (($rgb >> 16) & 0xFF);
            $g = 255 - (($rgb >> 8) & 0xFF);
            $b = 255 - ($rgb & 0xFF);
            $newColor = imagecolorallocate($src, $r, $g, $b);
            imagesetpixel($src, $x, $y, $newColor);
        }
    }

    // Menyimpan gambar negatif ke file
    if ($imageInfo[2] === IMAGETYPE_JPEG) {
        imagejpeg($src, $targetFile);
    } else {
        imagepng($src, $targetFile);
    }
    imagedestroy($src);
    return $targetFile; // Mengembalikan path gambar negatif
}

// Fungsi untuk perbaikan citra (penyesuaian kecerahan dan kontras)
function enhanceImage($sourceFile, $brightness, $contrast, $targetFile) {
    if (empty($sourceFile) || !file_exists($sourceFile)) {
        throw new Exception('Path file tidak valid atau kosong');
    }

    if (!isAllowedFileType($sourceFile)) {
        throw new Exception('File yang diupload harus berformat JPEG atau PNG.');
    }

    // Membuat gambar sumber dari file yang diupload
    $imageInfo = getimagesize($sourceFile);
    $src = ($imageInfo[2] === IMAGETYPE_JPEG) ? imagecreatefromjpeg($sourceFile) : imagecreatefrompng($sourceFile);

    // Menyesuaikan kecerahan dan kontras
    imagefilter($src, IMG_FILTER_BRIGHTNESS, $brightness);
    imagefilter($src, IMG_FILTER_CONTRAST, -$contrast); // Nilai negatif untuk meningkatkan kontras

    // Menyimpan gambar hasil perbaikan ke file
    if ($imageInfo[2] === IMAGETYPE_JPEG) {
        imagejpeg($src, $targetFile);
    } else {
        imagepng($src, $targetFile);
    }
    imagedestroy($src);
    return $targetFile; // Mengembalikan path gambar hasil perbaikan
}

// Fungsi untuk melakukan deteksi tepi menggunakan Sobel
function sobelEdgeDetection($sourceFile, $targetFile) {
    if (empty($sourceFile) || !file_exists($sourceFile)) {
        throw new Exception('Path file tidak valid atau kosong');
    }

    if (!isAllowedFileType($sourceFile)) {
        throw new Exception('File yang diupload harus berformat JPEG atau PNG.');
    }

    // Membuat gambar sumber dari file yang diupload
    $imageInfo = getimagesize($sourceFile);
    list($width, $height) = $imageInfo;
    $src = ($imageInfo[2] === IMAGETYPE_JPEG) ? imagecreatefromjpeg($sourceFile) : imagecreatefrompng($sourceFile);
    $dst = imagecreatetruecolor($width, $height);

    // Kernel Sobel
    $gx = [
        [-1, 0, 1],
        [-2, 0, 2],
        [-1, 0, 1]
    ];

    $gy = [
        [-1, -2, -1],
        [0, 0, 0],
        [1, 2, 1]
    ];

    // Deteksi tepi menggunakan kernel Sobel
    for ($x = 1; $x < $width - 1; $x++) {
        for ($y = 1; $y < $height - 1; $y++) {
            $sumX = $sumY = 0;
            for ($i = -1; $i <= 1; $i++) {
                for ($j = -1; $j <= 1; $j++) {
                    $rgb = imagecolorat($src, $x + $i, $y + $j);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;
                    $gray = ($r + $g + $b) / 3;

                    $sumX += $gx[$i + 1][$j + 1] * $gray;
                    $sumY += $gy[$i + 1][$j + 1] * $gray;
                }
            }
            $magnitude = sqrt($sumX * $sumX + $sumY * $sumY);
            $color = ($magnitude > 255) ? 255 : $magnitude;
            $newColor = imagecolorallocate($dst, $color, $color, $color);
            imagesetpixel($dst, $x, $y, $newColor);
        }
    }

    // Menyimpan gambar hasil deteksi tepi ke file
    imagepng($dst, $targetFile); // Simpan sebagai file PNG
    imagedestroy($dst);
    return $targetFile; // Mengembalikan path gambar hasil deteksi tepi
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'uploads/';

    // Memastikan bahwa ada file yang dipilih
    if (!empty($_FILES['file']['name'])) {
        $uploadFile = $uploadDir . basename($_FILES['file']['name']);
        $namaFileAsli = $_FILES['file']['name'];
        $namaFileBersih = preg_replace('/[^\w\d\.]/', '_', $namaFileAsli); // Mengganti karakter non-alphanumeric dengan garis bawah
        $uploadFile = $uploadDir . basename($namaFileBersih);

        // Pindahkan file yang diupload ke direktori uploads
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
            if ($_POST['action'] === 'resize') {
                try {
                    $resizedImagePath = resizeImage($uploadFile, 200, 200, $uploadDir . 'resized_' . $namaFileBersih); // Mengubah ukuran menjadi 200x200 px

                    // Simpan data ke database
                    $query = "INSERT INTO images (original_image, processed_image) VALUES ('$uploadFile', '$resizedImagePath')";
                    mysqli_query($koneksi, $query);

                    header("Location: hasil.php?resized_image=$resizedImagePath&original_image=$uploadFile"); // Redirect ke halaman utama dengan parameter gambar hasil resize dan gambar asli
                    exit(); // Keluar dari skrip setelah redirect
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
            } elseif ($_POST['action'] === 'negative') {
                try {
                    $negativeImagePath = negativeImage($uploadFile, $uploadDir . 'negative_' . $namaFileBersih);

                    // Simpan data ke database
                    $query = "INSERT INTO images (original_image, processed_image) VALUES ('$uploadFile', '$negativeImagePath')";
                    mysqli_query($koneksi, $query);

                    header("Location: hasil.php?negative_image=$negativeImagePath&original_image=$uploadFile");
                    exit();
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
            } elseif ($_POST['action'] === 'threshold') {
                try {
                    $thresholdValue = $_POST['threshold_value']; // Ambil nilai ambang batas dari form
                    $thresholdedImagePath = thresholdImage($uploadFile, $thresholdValue, $uploadDir . 'thresholded_' . $namaFileBersih . '.png'); // Simpan sebagai file PNG

                    // Simpan data ke database atau lakukan operasi lainnya sesuai kebutuhan
                    $query = "INSERT INTO images (original_image, processed_image) VALUES ('$uploadFile', '$thresholdedImagePath')";
                    mysqli_query($koneksi, $query);

                    header("Location: hasil.php?thresholded_image=$thresholdedImagePath&original_image=$uploadFile"); // Redirect ke halaman utama dengan parameter gambar hasil thresholding dan gambar asli
                    exit(); // Keluar dari skrip setelah redirect
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
            } elseif ($_POST['action'] === 'enhance') {
                try {
                    $brightness = $_POST['brightness']; // Ambil nilai kecerahan dari form
                    $contrast = $_POST['contrast']; // Ambil nilai kontras dari form
                    $enhancedImagePath = enhanceImage($uploadFile, $brightness, $contrast, $uploadDir . 'enhanced_' . $namaFileBersih);

                    // Simpan data ke database
                    $query = "INSERT INTO images (original_image, processed_image) VALUES ('$uploadFile', '$enhancedImagePath')";
                    mysqli_query($koneksi, $query);

                    header("Location: hasil.php?enhanced_image=$enhancedImagePath&original_image=$uploadFile");
                    exit();
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
            } elseif ($_POST['action'] === 'sobel') {
                try {
                    $sobelImagePath = sobelEdgeDetection($uploadFile, $uploadDir . 'sobel_' . $namaFileBersih . '.png');

                    // Simpan data ke database
                    $query = "INSERT INTO images (original_image, processed_image) VALUES ('$uploadFile', '$sobelImagePath')";
                    mysqli_query($koneksi, $query);

                    header("Location: hasil.php?sobel_image=$sobelImagePath&original_image=$uploadFile");
                    exit();
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
            } else {
                echo "Tindakan yang dipilih tidak valid.";
            }
        } else {
            echo "Gagal mengunggah file.";
        }
    } else {
        echo "Silakan pilih file terlebih dahulu.";
    }
}
?>
