Set WshShell = CreateObject("WScript.Shell")
WshShell.CurrentDirectory = "C:\xampp\htdocs\food\realtime"
WshShell.Run "C:\Program Files\nodejs\node.exe server.js", 0, False 