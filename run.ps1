$phpDir = 'C:\Users\Polmush\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe'
$env:Path = "$phpDir;$env:Path"
Set-Location 'D:\Docs\Code\kurs'

if ($args.Count -eq 0) {
    Write-Host "Usage: .\run.ps1 <command>"
    Write-Host "  .\run.ps1 artisan migrate"
    Write-Host "  .\run.ps1 composer require package"
    Write-Host "  .\run.ps1 serve"
    exit
}

if ($args[0] -eq 'serve') {
    php artisan serve
} elseif ($args[0] -eq 'artisan') {
    php @args
} elseif ($args[0] -eq 'composer') {
    $remaining = $args[1..($args.Count-1)]
    php "$phpDir\composer.phar" @remaining
} else {
    php @args
}
