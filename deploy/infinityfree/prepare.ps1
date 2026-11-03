$ErrorActionPreference = 'Stop'
$root = Split-Path (Split-Path $PSScriptRoot -Parent) -Parent
$stage = Join-Path $PSScriptRoot 'upload'
if (Test-Path $stage) {
    throw 'Upload directory already exists. Use the existing package rather than overwriting it.'
}
$private = Join-Path $stage '_app'
New-Item -ItemType Directory -Path $private -Force | Out-Null
foreach ($folder in @('config', 'src', 'views', 'ml', 'public')) {
    Copy-Item -LiteralPath (Join-Path $root $folder) -Destination $private -Recurse
}
New-Item -ItemType Directory -Path (Join-Path $private 'database/sessions') -Force | Out-Null
foreach ($file in @('seed.php', 'schema_mysql.sql')) {
    Copy-Item -LiteralPath (Join-Path $root "database/$file") -Destination (Join-Path $private 'database')
}
Copy-Item -LiteralPath (Join-Path $root 'public/assets') -Destination $stage -Recurse
Copy-Item -LiteralPath (Join-Path $PSScriptRoot 'templates/index.php') -Destination $stage
Copy-Item -LiteralPath (Join-Path $PSScriptRoot 'templates/database.php') -Destination (Join-Path $private 'config/database.php')
Copy-Item -LiteralPath (Join-Path $PSScriptRoot 'templates/private.htaccess') -Destination (Join-Path $private '.htaccess')
Copy-Item -LiteralPath (Join-Path $PSScriptRoot 'templates/public.htaccess') -Destination (Join-Path $stage '.htaccess')
Write-Output "Staging files prepared at $stage. Review before creating ZIP."
