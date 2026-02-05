import sys
import base64
import cv2
import numpy as np
from skimage.metrics import structural_similarity as ssim

def decode_base64_image(data_url):
    if data_url.startswith('data:image'):
        header, encoded = data_url.split(',', 1)
    else:
        encoded = data_url
    img_data = base64.b64decode(encoded)
    arr = np.frombuffer(img_data, np.uint8)
    img = cv2.imdecode(arr, cv2.IMREAD_GRAYSCALE)
    return img

def compare_images(img1, img2):
    # Resize to same shape
    h = min(img1.shape[0], img2.shape[0])
    w = min(img1.shape[1], img2.shape[1])
    img1 = cv2.resize(img1, (w, h))
    img2 = cv2.resize(img2, (w, h))
    score, _ = ssim(img1, img2, full=True)
    return score

def main():
    # Arguments: img1_base64 img2_base64
    if len(sys.argv) != 3:
        print('Usage: python biometric_match.py <img1_base64> <img2_base64>')
        sys.exit(1)
    img1 = decode_base64_image(sys.argv[1])
    img2 = decode_base64_image(sys.argv[2])
    score = compare_images(img1, img2)
    print(f'{score*100:.2f}')

if __name__ == '__main__':
    main()
