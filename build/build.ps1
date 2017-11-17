$ScriptDir = (Split-Path -Path $MyInvocation.MyCommand.Definition -Parent)
$WorkDir = (Get-Item $ScriptDir).parent.FullName
Push-Location $WorkDir
Remove-Item launcher.exe 
Remove-Item dist.7z
Copy-Item "launcher\windows-launcher\bin\Release\Z-BlogPHP App Validator Launcher.exe" "launcher.exe"
7z a dist.7z * -mm=lzma -mf -mmt -ms -r -x@"build\ignoreList.txt" -x!"*.md"
Pop-Location