var test = null;

var state = document.getElementById('content-capture');

// Reader device identifier selected for acquisition
var myVal = "";

// We always acquire in PNG format in the simplified UI
var currentFormat = Fingerprint.SampleFormat.PngImage;

// For biometric_register.php we keep track of up to 3 fingerprint samples
var fingerprintAttempts = [];

var FingerprintSdkTest = (function () {
    function FingerprintSdkTest() {
        var _instance = this;
        this.operationToRestart = null;
        this.acquisitionStarted = false;
        this.sdk = new Fingerprint.WebApi;
        this.sdk.onDeviceConnected = function (e) {
            // Detects if the deveice is connected for which acquisition started
            showMessage("Scan your finger");
        };
        this.sdk.onDeviceDisconnected = function (e) {
            // Detects if device gets disconnected - provides deviceUid of disconnected device
            showMessage("Device disconnected");
        };
        this.sdk.onCommunicationFailed = function (e) {
            // Detects if there is a failure in communicating with U.R.U web SDK
            showMessage("Communinication Failed")
        };
        this.sdk.onSamplesAcquired = function (s) {
            // Sample acquired event triggers this function
            sampleAcquired(s);
        };
    }

    FingerprintSdkTest.prototype.startCapture = function () {
        if (this.acquisitionStarted) // Monitoring if already started capturing
            return;
        var _instance = this;
        showMessage("");
        this.operationToRestart = this.startCapture;
        this.sdk.startAcquisition(currentFormat, myVal).then(function () {
            _instance.acquisitionStarted = true;
        }, function (error) {
            showMessage(error.message);
        });
    };
    FingerprintSdkTest.prototype.stopCapture = function () {
        if (!this.acquisitionStarted) //Monitor if already stopped capturing
            return;
        var _instance = this;
        showMessage("");
        this.sdk.stopAcquisition().then(function () {
            _instance.acquisitionStarted = false;
        }, function (error) {
            showMessage(error.message);
        });
    };
    FingerprintSdkTest.prototype.getInfo = function () {
        var _instance = this;
        return this.sdk.enumerateDevices();
    };

    return FingerprintSdkTest;
})();

function showMessage(message){
    var x = state.querySelectorAll("#status");
    if(x.length != 0){
        x[0].innerHTML = message;
    }
}

window.onload = function () {
    localStorage.clear();
    fingerprintAttempts = [];
    test = new FingerprintSdkTest();

    // In the simplified UI, auto-select the first available reader (if any)
    // and start capture.
    test.getInfo().then(function (sucessObj) {
        if (sucessObj && sucessObj.length > 0) {
            myVal = sucessObj[0];
            onStart();
        } else {
            showMessage("No reader detected. Please connect a reader.");
        }
    }, function (error) {
        showMessage(error.message);
    });
};

function onStart() {
    // Always capture in PNG format in this simplified version
    currentFormat = Fingerprint.SampleFormat.PngImage;
    test.startCapture();
}

function onStop() {
    test.stopCapture();
}

function sampleAcquired(s){   
    // We only support PNG image samples in this simplified version.
    if(currentFormat == Fingerprint.SampleFormat.PngImage){   
        // If sample acquired format is PNG- perform following call on object received 
        // Get samples from the object - get 0th element of samples as base 64 encoded PNG image
        localStorage.setItem("imageSrc", "");
        var samples = JSON.parse(s.samples);
        var imgSrc = "data:image/png;base64," + Fingerprint.b64UrlTo64(samples[0]);
        localStorage.setItem("imageSrc", imgSrc);

        var vDiv = document.getElementById('imagediv');
        vDiv.innerHTML = "";
        var image = document.createElement("img");
        image.id = "image";
        image.src = imgSrc;
        vDiv.appendChild(image);

        // If we're on biometric_register.php, collect up to 3 attempts
        var attemptsInput = document.getElementById('fingerprint_attempts');
        var attemptInfo = document.getElementById('attemptInfo');
        if (attemptsInput) {
            if (fingerprintAttempts.length < 3) {
                fingerprintAttempts.push(imgSrc);
                attemptsInput.value = JSON.stringify(fingerprintAttempts);
                if (attemptInfo) {
                    let msg = 'Captured ' + fingerprintAttempts.length + ' of 3 samples.';
                    // Show percentage similarity
                    if (fingerprintAttempts.length > 1) {
                        function base64Similarity(a, b) {
                            var len = Math.min(a.length, b.length);
                            var same = 0;
                            for (var i = 0; i < len; i++) {
                                if (a[i] === b[i]) same++;
                            }
                            return len > 0 ? (same / len * 100).toFixed(2) : 0;
                        }
                        if (fingerprintAttempts.length === 2) {
                            let pct = base64Similarity(fingerprintAttempts[0], fingerprintAttempts[1]);
                            msg += `\nSimilarity 1 vs 2: ${pct}%`;
                        } else if (fingerprintAttempts.length === 3) {
                            let pct1 = base64Similarity(fingerprintAttempts[0], fingerprintAttempts[1]);
                            let pct2 = base64Similarity(fingerprintAttempts[0], fingerprintAttempts[2]);
                            let pct3 = base64Similarity(fingerprintAttempts[1], fingerprintAttempts[2]);
                            msg += `\nSimilarity 1 vs 2: ${pct1}%`;
                            msg += `\nSimilarity 1 vs 3: ${pct2}%`;
                            msg += `\nSimilarity 2 vs 3: ${pct3}%`;
                        }
                    }
                    attemptInfo.textContent = msg;
                }
            }
        }

        // Remove empty-state styling once a real fingerprint image is shown
        if (vDiv.classList && vDiv.classList.contains('empty-state')) {
            vDiv.classList.remove('empty-state');
        }

        // Remember when this sample was rendered so we can safely clear it later
        var stamp = Date.now().toString();
        if (vDiv.dataset) {
            vDiv.dataset.lastSampleTs = stamp;
        }

        // Automatically clear the fingerprint image after 5 seconds
        setTimeout(function () {
            if (!vDiv.dataset || vDiv.dataset.lastSampleTs !== stamp) {
                return; // a newer sample has been rendered; do not clear
            }
            vDiv.innerHTML = "";
            if (vDiv.classList && !vDiv.classList.contains('empty-state')) {
                vDiv.classList.add('empty-state');
                vDiv.classList.remove('empty-state');
            }

            // Remember when this sample was rendered so we can safely clear it later
            var stamp2 = Date.now().toString();
            if (vDiv.dataset) {
                vDiv.dataset.lastSampleTs = stamp2;
            }

            // Automatically clear the fingerprint image after 5 seconds
            setTimeout(function () {
                if (!vDiv.dataset || vDiv.dataset.lastSampleTs !== stamp2) {
                    return; // a newer sample has been rendered; do not clear
                }
                vDiv.innerHTML = "";
                if (vDiv.classList && !vDiv.classList.contains('empty-state')) {
                    vDiv.classList.add('empty-state');
                }
            }, 2000);
        }, 5000);
    }
}