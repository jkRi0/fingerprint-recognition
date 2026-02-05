<?php
// biometric_register.php: Register biometrics and save to a JSON file
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $fingerprintAttemptsJson = $_POST['fingerprint_attempts'] ?? '';
    $attempts = json_decode($fingerprintAttemptsJson, true);

    if ($username === '' || !is_array($attempts) || count($attempts) < 3) {
        $error = 'Please provide a username and capture 3 fingerprint samples.';
    } else {
        // NOTE: Image samples from the same finger are rarely byte-identical.
        // For now we trust the device and just require 3 captured samples.
        // Use the first sample as the canonical fingerprint and store all attempts.
        $fingerprint = $attempts[0];
        $dataFile = __DIR__ . '/biometric_data.json';
        $allData = [];
        if (file_exists($dataFile)) {
            $json = file_get_contents($dataFile);
            $allData = json_decode($json, true) ?: [];
        }
        $allData[] = [
            'username' => $username,
            'fingerprint' => $fingerprint,
            'attempts' => $attempts,
            'registered_at' => date('c')
        ];
        file_put_contents($dataFile, json_encode($allData, JSON_PRETTY_PRINT));

        // Calculate similarity between each pair using Python biometric_match.py
        $pct = [];
        if (count($attempts) === 3) {
            foreach ([[0,1],[0,2],[1,2]] as $idx => $pair) {
                $a = escapeshellarg($attempts[$pair[0]]);
                $b = escapeshellarg($attempts[$pair[1]]);
                $cmd = "python biometric_match.py $a $b";
                $output = shell_exec($cmd);
                $pct[$idx] = is_numeric(trim($output)) ? round(floatval($output),2) : 0;
            }
            $_SESSION['match_pct'] = $pct;
        }

        // Redirect after successful POST to avoid duplicate writes on refresh
        header('Location: biometric_register.php?success=1');
        exit;
    }
}

// Handle success message on GET after redirect
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['success']) && $_GET['success'] == '1') {
    $success = 'Biometric registration successful!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Biometric Registration - Fingerprint Recognition</title>
    <link rel="stylesheet" href="css/bootstrap-min.css">
    <link rel="stylesheet" href="app.css">
</head>
<body>
    <div class="app-root">
        <div class="app-card shadow-sm">
            <header class="app-header">
                <h1 class="app-title">Biometric Registration</h1>
                <p class="app-subtitle">Associate fingerprint data with a user account.</p>
            </header>
            <main class="app-main">
                <div id="content-capture" class="capture-layout">
                    <div class="capture-preview">
                        <div id="imagediv" class="fingerprint-preview empty-state"></div>
                    </div>
                    <div class="capture-status">
                        <div id="status" class="status-text">Place your finger on the reader to capture.</div>
                        <div class="status-helper">After a fingerprint is captured, it will be linked to the username below and stored as biometric data.</div>
                        <?php if ($error): ?>
                            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="success-msg"><?php echo htmlspecialchars($success); ?></div>
                            <?php if (!empty($_SESSION['match_pct'])): ?>
                                <div class="status-helper" style="margin-bottom:10px;">
                                    Similarity between attempts:<br>
                                    Attempt 1 vs 2: <b><?php echo $_SESSION['match_pct'][0]; ?>%</b><br>
                                    Attempt 1 vs 3: <b><?php echo $_SESSION['match_pct'][1]; ?>%</b><br>
                                    Attempt 2 vs 3: <b><?php echo $_SESSION['match_pct'][2]; ?>%</b>
                                </div>
                                <?php unset($_SESSION['match_pct']); ?>
                            <?php endif; ?>
                        <?php endif; ?>
                        <form method="post" autocomplete="off">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" name="username" id="username" class="form-control" required>
                            </div>
                            <p id="attemptInfo" class="status-helper" style="margin-top:4px;">Captured 0 of 3 samples.</p>
                            <!-- Hidden field that will be filled with JSON array of fingerprint samples by app.js -->
                            <input type="hidden" name="fingerprint_attempts" id="fingerprint_attempts" required>
                            <button type="submit" class="btn btn-primary" style="margin-top:10px;">Register Biometrics</button>
                        </form>
                        <a href="index.html" class="btn btn-ghost" style="margin-top:12px;">Back to Main Page</a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="lib/jquery.min.js"></script>
    <script src="lib/bootstrap.min.js"></script>
    <script src="scripts/es6-shim.js"></script>
    <script src="scripts/websdk.client.bundle.min.js"></script>
    <script src="scripts/fingerprint.sdk.min.js"></script>
    <script src="app.js"></script>
</body>
</html>
