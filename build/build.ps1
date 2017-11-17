$ScriptDir = (Split-Path -Path $MyInvocation.MyCommand.Definition -Parent)
$WorkDir = (Get-Item $ScriptDir).parent.FullName
Push-Location $WorkDir
Remove-Item launcher.exe 
Copy-Item "launcher\windows-launcher\bin\Release\Z-BlogPHP App Validator Launcher.exe" "launcher.exe"
Pop-Location