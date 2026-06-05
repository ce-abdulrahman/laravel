import os
import urllib.request
import ssl

ssl._create_default_https_context = ssl._create_unverified_context

fonts = {
    'AmiriQuran-Regular.ttf': 'https://github.com/google/fonts/raw/main/ofl/amiriquran/AmiriQuran-Regular.ttf'
}

dest_dir = r'c:\Users\kurdn\Desktop\my-quran\laravel\public\fonts\ar'
os.makedirs(dest_dir, exist_ok=True)

headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3'
}

for name, url in fonts.items():
    dest_path = os.path.join(dest_dir, name)
    print(f"Downloading {name} from {url}...")
    try:
        req = urllib.request.Request(url, headers=headers)
        with urllib.request.urlopen(req) as response:
            with open(dest_path, 'wb') as out_file:
                out_file.write(response.read())
        print(f"Successfully downloaded {name}")
    except Exception as e:
        print(f"Failed to download {name}: {e}")

print("Font download process completed.")
