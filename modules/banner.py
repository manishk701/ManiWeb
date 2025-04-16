from colorama import Fore,Back,Style
import platform,os

OsName = platform.uname()[0]

def banner():
    if OsName == "Windows":
      os.system("cls")
    else:
      os.system("clear")
    print(Fore.LIGHTWHITE_EX+" (               )   (       *                (                     )       (")   
    print(Fore.LIGHTWHITE_EX+r" )\ )  *   )  ( /(   )\ )  (  `           (   )\ )       (       ( /( ")
    print(Fore.LIGHTWHITE_EX+r"(()/(` )  /(  )\()) (()/(  )\))(        ( )\ (()/( (     )\      )\()) (   (()/( ")
    print(Fore.LIGHTWHITE_EX+r"/(_))( )(_))((_)\   /(_)_)()\       ___  )((_) /(_)))\ ((((_)(  |((_)\  )\   /(_)) ")
    print(Fore.CYAN+r" (_)) (_(_())   ((_) (_))  (_()((_)|___|((_)_ (_)) ((_) )\ _ )\ |_ ((_)((_) (_)) ")
    print(Fore.CYAN+r"/ __||_   _|  / _ \ | _ \ |  \/  |      | _ )| _ \| __|(_)_\(_)| |/ / | __|| _ \ ")
    print(Fore.CYAN+r"\__ \  | |   | (_) ||   / | |\/| |      | _ \|   /| _|  / _ \    ' <  | _| |   / ")
    print(Fore.CYAN+r"|___/  |_|    \___/ |_|_\ |_|  |_|      |___/|_|_\|___|/_/ \_\  _|\_\ |___||_|_\ ")
    print(Style.RESET_ALL)

banner()