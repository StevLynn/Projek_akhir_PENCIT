<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Pengolahan Citra Digital Sederhana</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/coco-ssd"></script>
    <style>
        #processedImage {
            max-width: 50%;
            height: auto;
            display: block;
            margin: 20px auto;
        }
    </style>
    <script>
        function toggleThresholdValue() {
            var action = document.getElementById('action').value;
            var thresholdValueField = document.getElementById('threshold_value_field');
            var enhanceOptions = document.getElementById('enhanceOptions');

            if (action === 'threshold') {
                thresholdValueField.style.display = 'block';
                enhanceOptions.style.display = 'none';
            } else if (action === 'enhance') {
                thresholdValueField.style.display = 'none';
                enhanceOptions.style.display = 'block';
            } else {
                thresholdValueField.style.display = 'none';
                enhanceOptions.style.display = 'none';
            }
        }

        let model;

        async function loadModel() {
            model = await cocoSsd.load();
            console.log("Model loaded");
        }

        loadModel();

        async function detectObjects(imageElement) {
            const predictions = await model.detect(imageElement);
            console.log("Predictions: ", predictions);
            return predictions;
        }

        async function handleObjectDetection(imageFile) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();
            img.src = URL.createObjectURL(imageFile);
            
            img.onload = async () => {
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
                
                const predictions = await detectObjects(canvas);
                predictions.forEach(prediction => {
                    ctx.beginPath();
                    ctx.rect(...prediction.bbox);
                    ctx.lineWidth = 2;
                    ctx.strokeStyle = 'red';
                    ctx.fillStyle = 'red';
                    ctx.stroke();
                    ctx.fillText(
                        prediction.class + ' - ' + Math.round(prediction.score * 100) + '%',
                        prediction.bbox[0],
                        prediction.bbox[1] > 10 ? prediction.bbox[1] - 5 : 10
                    );
                });

                const dataUrl = canvas.toDataURL();
                document.getElementById('processedImage').src = dataUrl;
                document.getElementById('processedImage').style.display = 'block';
            };
        }

        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function () {
                var output = document.getElementById('processedImage');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        function handleObjectDetectionEvent(event) {
            event.preventDefault();
            const fileInput = document.querySelector('input[type="file"]');
            const selectedFile = fileInput.files[0];
            if (selectedFile && document.getElementById('action').value === 'objek') {
                handleObjectDetection(selectedFile);
            } else {
                document.querySelector('form').submit();
            }
        }
    </script>
</head>
<body>
    <div class="header">
        <h1>Aplikasi Pengolahan Citra Digital Sederhana</h1>
    </div>
    <div class="container">
        <form class="form-container" action="process.php" method="post" enctype="multipart/form-data">
            <h2>Silahkan Lakukan Pengolahan Citra</h2>
            <input type="file" name="file" accept="image/*" onchange="previewImage(event)">
            <br>
            <select name="action" id="action" onchange="toggleThresholdValue()">
                <option value="none" selected disabled>Pilih Tindakan</option>
                <option value="upload">Upload</option>
                <option value="resize">Resize</option>
                <option value="negative">Proses Citra Negatif</option>
                <option value="enhance">Perbaikan Citra</option>
                <option value="threshold">Thresholding</option>
                <option value="sobel">Deteksi Tepi (Sobel)</option>
                <option value="objek">Pendeteksian Objek</option>
            </select>
            <br>
            <div id="threshold_value_field" style="display: none;">
                <label for="threshold_value">Nilai Ambang Batas:</label>
                <select name="threshold_value" id="threshold_value">
                    <option value="70">70</option>
                    <option value="100">100</option>
                    <option value="127">127</option>
                    <option value="190">190</option>
                </select>
            </div>
            <div id="enhanceOptions" style="display: none;">
                <label for="brightness">Kecerahan:</label>
                <input type="number" name="brightness" id="brightness" value="0" min="-255" max="255">
                <br>
                <label for="contrast">Kontras:</label>
                <input type="number" name="contrast" id="contrast" value="0" min="-100" max="100">
            </div>
            <br><br>
            <button type="submit" onclick="handleObjectDetectionEvent(event)">Eksekusi</button>
        </form>
        <br>
        <img id="processedImage" style="display:none;" />
    </div>
</body>
</html>
