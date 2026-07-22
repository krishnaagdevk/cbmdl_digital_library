# Self-elevate the script if not running as Administrator
if (-Not ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Host "Requesting Administrator rights..." -ForegroundColor Yellow
    Start-Process powershell.exe -ArgumentList "-NoProfile -ExecutionPolicy Bypass -File `"$PSCommandPath`"" -Verb RunAs
    exit
}

Write-Host "====================================================" -ForegroundColor Cyan
Write-Host "  CBMDL Local Library - Automatic Server Setup Tool  " -ForegroundColor Cyan
Write-Host "====================================================" -ForegroundColor Cyan
Write-Host ""

# 1. Automate Windows Firewall Rule for Apache Port 8080
Write-Host "[1/3] Setting up Windows Firewall rule for Apache (Port 8080)..." -ForegroundColor Green
$ruleName = "CBMDL_Apache_8080"
$existingRule = Get-NetFirewallRule -DisplayName $ruleName -ErrorAction SilentlyContinue

if ($existingRule) {
    Write-Host "      Firewall rule '$ruleName' already exists." -ForegroundColor Gray
} else {
    New-NetFirewallRule -DisplayName $ruleName `
                        -Direction Inbound `
                        -LocalPort 8080 `
                        -Protocol TCP `
                        -Action Allow `
                        -Profile Any `
                        -Description "Allow local LAN computers to access CBMDL Digital Library server on port 8080" | Out-Null
    Write-Host "      SUCCESS: Inbound Firewall Rule for port 8080 created!" -ForegroundColor Green
}

# 2. Get current LAN IP Address (Filter out virtual adapters like WSL/Hyper-V/VirtualBox)
$netAdapter = Get-NetIPAddress -AddressFamily IPv4 | Where-Object {
    $_.IPAddress -notlike "127.*" -and 
    $_.IPAddress -notlike "169.254.*" -and 
    $_.IPAddress -notlike "172.26.*" -and 
    $_.IPAddress -notlike "172.17.*" -and 
    $_.IPAddress -notlike "172.18.*" -and 
    $_.IPAddress -notlike "172.19.*" -and 
    $_.IPAddress -notlike "172.20.*" -and 
    $_.IPAddress -notlike "172.21.*" -and 
    $_.IPAddress -notlike "172.22.*" -and 
    $_.IPAddress -notlike "172.23.*" -and 
    $_.IPAddress -notlike "172.24.*" -and 
    $_.IPAddress -notlike "172.25.*" -and 
    $_.IPAddress -notlike "172.27.*" -and 
    $_.IPAddress -notlike "172.28.*" -and 
    $_.IPAddress -notlike "172.29.*" -and 
    $_.IPAddress -notlike "172.30.*" -and 
    $_.IPAddress -notlike "172.31.*"
} | Select-Object -First 1

if (-not $netAdapter) {
    $netAdapter = Get-NetIPAddress -AddressFamily IPv4 | Where-Object {$_.IPAddress -like "192.168.*"} | Select-Object -First 1
}

$currentIP = $netAdapter.IPAddress

Write-Host ""
Write-Host "[2/3] Detecting Current LAN IP Address..." -ForegroundColor Green
Write-Host "      Server Local IP Address: $currentIP" -ForegroundColor Yellow

# 3. Create Desktop Launcher Shortcut
Write-Host ""
Write-Host "[3/3] Creating Desktop Shortcut for easy access..." -ForegroundColor Green

$desktopPath = [System.IO.Path]::Combine([System.Environment]::GetFolderPath("Desktop"), "CBMDL Library Server.url")
$urlContent = "[InternetShortcut]`r`nURL=http://localhost:8080/cbmdl/`r`nIconIndex=0`r`nIconFile=C:\xampp\htdocs\cbmdl\images\favicon.ico"
[System.IO.File]::WriteAllText($desktopPath, $urlContent)

Write-Host "      SUCCESS: Desktop shortcut 'CBMDL Library Server' created!" -ForegroundColor Green

Write-Host ""
Write-Host "====================================================" -ForegroundColor Cyan
Write-Host "                  SETUP COMPLETE!                   " -ForegroundColor Cyan
Write-Host "====================================================" -ForegroundColor Cyan
Write-Host "Tell your 18 client PCs to open this URL in browser:" -ForegroundColor White
Write-Host "   http://${currentIP}:8080/cbmdl" -ForegroundColor Yellow
Write-Host "====================================================" -ForegroundColor Cyan
Write-Host ""
Read-Host "Press Enter to exit..."
