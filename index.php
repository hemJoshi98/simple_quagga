<!DOCTYPE html>
<html lang="en">

<head>
    <title></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* In order to place the tracking correctly */
        canvas.drawing, canvas.drawingBuffer {
            position: absolute;
            left: 0;
            top: 0;
        }
        div {
            height: 200px;
            width: 400px;

            position: fixed;
            top: 50%;
            left: 50%;
            margin-top: -100px;
            margin-left: -200px;
        }

        textarea{
            position: fixed;
            top: 40%;
            left: 50%;
            margin-top: -100px;
            margin-left: -100px;
        }

        .scan{
            position: fixed;
            top: 20%;
            left: 50%;
            margin-top: -100px;
            margin-left: -100px;
        }
    </style>
</head>

<body>
    <!-- Div to show the scanner -->
    <div id="scanner-container"> </div>
    <input class = "scan" type="button" id="btn" value="Start/Stop the scanner" />


    <!-- Include the image-diff library -->
    <script src="https://cdn.rawgit.com/serratus/quaggaJS/0420d5e0/dist/quagga.min.js"></script>

    <script>
        var _scannerIsRunning = false;

        function order_by_occurance(arr){
            var counts = {};
            arr.forEach(function(value){
                if(!counts[value]){
                    counts[value] = 0;
                }
                counts[value]++;
            });
            return Object.keys(counts).sort(function(currkey, nextkey){
                return counts[currkey]<counts[nextkey];
            });
        }

        function startScanner() {
            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: document.querySelector('#scanner-container'),
                    constraints: {
                        width: 640,
                        height: 320,
                        facingMode: "environment"
                    },
                },
                decoder: {
                    readers: [
                        "code_128_reader",
                        // "ean_reader",
                        // "ean_8_reader",
                        // "code_39_reader",
                        // "code_39_vin_reader",
                        // "codabar_reader",
                        // "upc_reader",
                        // "upc_e_reader",
                        // "i2of5_reader"
                    ],
                    debug: {
                        showCanvas: true,
                        showPatches: true,
                        showFoundPatches: true,
                        showSkeleton: true,
                        showLabels: true,
                        showPatchLabels: true,
                        showRemainingPatchLabels: true,
                        boxFromPatches: {
                            showTransformed: true,
                            showTransformedBox: true,
                            showBB: true
                        }
                    }
                },

            }, function (err) {
                if (err) {
                    console.log(err);
                    return
                }

                console.log("Initialization finished. Ready to start");
                Quagga.start();

                // Set flag to is running
                _scannerIsRunning = true;
            });

            Quagga.onProcessed(function (result) {
                var drawingCtx = Quagga.canvas.ctx.overlay,
                drawingCanvas = Quagga.canvas.dom.overlay;

                if (result) {
                    if (result.boxes) {
                        drawingCtx.clearRect(0, 0, parseInt(drawingCanvas.getAttribute("width")), parseInt(drawingCanvas.getAttribute("height")));
                        result.boxes.filter(function (box) {
                            return box !== result.box;
                        }).forEach(function (box) {
                            Quagga.ImageDebug.drawPath(box, { x: 0, y: 1 }, drawingCtx, { color: "green", lineWidth: 2 });
                        });
                    }

                    if (result.box) {
                        Quagga.ImageDebug.drawPath(result.box, { x: 0, y: 1 }, drawingCtx, { color: "#00F", lineWidth: 2 });
                    }

                    if (result.codeResult && result.codeResult.code) {
                        Quagga.ImageDebug.drawPath(result.line, { x: 'x', y: 'y' }, drawingCtx, { color: 'red', lineWidth: 3 });
                    }
                }
            });

            var last_result = [];
            Quagga.onDetected(function (result) {
                var last_code = result.codeResult.code;
                last_result.push(last_code);
                // console.log(last_result);
                if (last_result.length>30){
                    code = order_by_occurance(last_result)[0];
                    last_result = []
                    console.log("Barcode detected and processed : [" + code + "]", result);
                    document.getElementById("code_submit").value = code;
                    document.getElementById('jsform').submit();
                }
            });
        }


        // Start/stop scanner
        document.getElementById("btn").addEventListener("click", function () {
            if (_scannerIsRunning) {
                Quagga.stop();
            } else {
                startScanner();
            }
        }, false);
    </script>

    <div>
        <textarea id="textareabox" name="textarea1" placeholder="No barcode found"></textarea>
    </div>
    <form id = "jsform" action="./db.php" method="post">
        Code: <input id = "code_submit" type="text" name = "code_128" value=""><br>
    </form>
</body>

</html>