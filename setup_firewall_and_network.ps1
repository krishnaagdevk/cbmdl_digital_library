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

# 1. Automate Windows Firewall Rules for Apache (Ports 80 & 8080)
Write-Host "[1/3] Setting up Windows Firewall rules for Apache (Ports 80 & 8080)..." -ForegroundColor Green

$rules = @(
    @{ Name = "CBMDL_Apache_80"; Port = 80; Desc = "Allow local LAN computers to access CBMDL Digital Library server on port 80" },
    @{ Name = "CBMDL_Apache_8080"; Port = 8080; Desc = "Allow local LAN computers to access CBMDL Digital Library server on port 8080" }
)

foreach ($r in $rules) {
    $existingRule = Get-NetFirewallRule -DisplayName $r.Name -ErrorAction SilentlyContinue
    if ($existingRule) {
        Write-Host "      Firewall rule '$($r.Name)' already exists." -ForegroundColor Gray
    } else {
        New-NetFirewallRule -DisplayName $r.Name `
                            -Direction Inbound `
                            -LocalPort $r.Port `
                            -Protocol TCP `
                            -Action Allow `
                            -Profile Any `
                            -Description $r.Desc | Out-Null
        Write-Host "      SUCCESS: Inbound Firewall Rule for port $($r.Port) created!" -ForegroundColor Green
    }
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

# Detect active Apache port (80 or 8080)
$apachePort = 80
$conn8080 = Get-NetTCPConnection -LocalPort 8080 -State Listen -ErrorAction SilentlyContinue
if ($conn8080) {
    $apachePort = 8080
}

$urlPortSuffix = if ($apachePort -eq 80) { "" } else { ":$apachePort" }
$fullServerUrl = "http://${currentIP}${urlPortSuffix}/cbmdl"

# 3. Create Desktop Launcher Shortcut
Write-Host ""
Write-Host "[3/3] Creating Desktop Shortcut for easy access..." -ForegroundColor Green

$desktopPath = [System.IO.Path]::Combine([System.Environment]::GetFolderPath("Desktop"), "CBMDL Library Server.url")
$urlContent = "[InternetShortcut]`r`nURL=http://localhost${urlPortSuffix}/cbmdl/`r`nIconIndex=0`r`nIconFile=C:\xampp\htdocs\cbmdl\images\favicon.ico"
[System.IO.File]::WriteAllText($desktopPath, $urlContent)

Write-Host "      SUCCESS: Desktop shortcut 'CBMDL Library Server' created!" -ForegroundColor Green

Write-Host ""
Write-Host "====================================================" -ForegroundColor Cyan
Write-Host "                  SETUP COMPLETE!                   " -ForegroundColor Cyan
Write-Host "====================================================" -ForegroundColor Cyan
Write-Host "Tell client PCs on your network to open this URL:" -ForegroundColor White
Write-Host "   $fullServerUrl" -ForegroundColor Yellow
Write-Host "====================================================" -ForegroundColor Cyan
Write-Host ""
Read-Host "Press Enter to exit..."
