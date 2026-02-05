Python Biometric Matching Integration Setup
===========================================

Follow these steps to enable Python-based fingerprint image matching for this project:

1. Install Python 3.x
---------------------
- Download and install Python 3 from https://www.python.org/downloads/
- During install, check the box to "Add Python to PATH"

2. Install Required Python Packages
-----------------------------------
- Open Command Prompt (Win+R, type `cmd`, press Enter)
- Navigate to your project folder:
    cd c:\xampp\htdocs\samples\fingerprint-recognition
- Install dependencies:
    pip install -r requirements.txt

3. Test the Python Script
-------------------------
- You can test the script manually:
    python biometric_match.py <base64_img1> <base64_img2>
- It should print a similarity percentage (0-100).

4. Ensure PHP Can Call Python
-----------------------------
- The PHP code uses `shell_exec('python biometric_match.py ...')`.
- Make sure `python` is callable from the command line (type `python --version`).
- If not, add Python to your system PATH or use the full path in the PHP command (e.g., `C:\Python39\python.exe biometric_match.py ...`).

5. Troubleshooting
------------------
- If you get errors about missing modules, rerun `pip install -r requirements.txt`.
- If you see "python not found", check your PATH or specify the full Python path in PHP.
- For image errors, ensure the input is a valid base64 PNG data URL.

6. (Optional) Virtual Environment
---------------------------------
- You can use a virtualenv for isolation:
    python -m venv venv
    venv\Scripts\activate
    pip install -r requirements.txt

After setup, registering biometrics in the web app will use real image similarity for matching percentages.
