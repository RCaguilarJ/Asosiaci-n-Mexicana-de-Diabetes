param(
    [string]$ProjectRoot = (Split-Path -Parent $PSScriptRoot)
)

Write-Host "Project root: $ProjectRoot"

$envLocal = Join-Path $ProjectRoot ".env.local"
$envDefault = Join-Path $ProjectRoot ".env"

if (-not (Test-Path $envLocal) -and (Test-Path $envDefault)) {
    Copy-Item $envDefault $envLocal
    Write-Host "Created .env.local from .env"
}

Write-Host "PHP version:"
php -v

Write-Host "Composer version:"
composer --version

Write-Host "Generating autoload files..."
composer dump-autoload

Write-Host "Done. Update .env.local if needed."
