from subprocess import getoutput
import requests, json
import os
from modules import control

def dependency():
    check_php = getoutput("php -v")
    if "not found" in check_php:
        exit("Please install PHP\nCommand: sudo apt install php")

    try:
        from colorama import Fore, Style
        import requests, psutil
    except ImportError:
        exit("Please install required libraries\nCommand: python3 -m pip install -r requirements.txt")

def check_started():
    settings_path = os.path.join("mani-web", "Settings.json")
    try:
        with open(settings_path, "r") as jsonFile:
            data = json.load(jsonFile)

        if data["is_start"] == False:
            data["is_start"] = True
            with open(settings_path, "w") as jsonFile:
                json.dump(data, jsonFile)
        elif data["is_start"] == True:
            control.kill_php_proc()
    except FileNotFoundError:
        print(f"Error: {settings_path} not found")
        exit(1)
    except json.JSONDecodeError:
        print(f"Error: Invalid JSON in {settings_path}")
        exit(1)

def check_update():
    try:
        http = requests.get("https://raw.githubusercontent.com/manishk701/ManiWeb.git/main/Settings.json").text
        http_json = json.loads(http)

        settings_path = os.path.join("mani-web", "Settings.json")
        with open(settings_path, "r") as jsonFile:
            data = json.load(jsonFile)
            
        if data['version'] < http_json['version']:
            exit("Please Update Tool")
    except requests.RequestException as e:
        print(f"Error checking for updates: {str(e)}")
    except json.JSONDecodeError:
        print("Error: Invalid JSON response from update check")
    except FileNotFoundError:
        print(f"Error: {settings_path} not found")
    except Exception as e:
        print(f"An unexpected error occurred: {str(e)}")
