# Genera deploy/42mentors.zip con solo lo que se sube al servidor:
#   42mentors/{public, src, sql, cache/.htaccess, .env.example}
# Nunca incluye .env, API-Docs/ ni hosting-tests/. Las rutas del ZIP usan "/" para que cPanel las extraiga bien.
# Uso (desde la raíz del proyecto, en Windows):  powershell -ExecutionPolicy Bypass -File tools\build-deploy.ps1
Add-Type -AssemblyName System.IO.Compression, System.IO.Compression.FileSystem

$project = Split-Path -Parent $PSScriptRoot
$base    = Join-Path ([System.IO.Path]::GetTempPath()) '42mentors-deploy'
$stage   = Join-Path $base '42mentors'
$zipPath = Join-Path $project 'deploy\42mentors.zip'

if (Test-Path -LiteralPath $base) { Remove-Item -LiteralPath $base -Recurse -Force -Confirm:$false }
New-Item -ItemType Directory -Force -Path $stage | Out-Null
foreach ($dir in 'public', 'src', 'sql') {
    Copy-Item -LiteralPath (Join-Path $project $dir) -Destination $stage -Recurse
}
New-Item -ItemType Directory -Force -Path (Join-Path $stage 'cache') | Out-Null
Copy-Item -LiteralPath (Join-Path $project 'cache\.htaccess') -Destination (Join-Path $stage 'cache')
Copy-Item -LiteralPath (Join-Path $project '.env.example') -Destination $stage

New-Item -ItemType Directory -Force -Path (Split-Path $zipPath) | Out-Null
if (Test-Path -LiteralPath $zipPath) { Remove-Item -LiteralPath $zipPath -Force -Confirm:$false }
$zip = [System.IO.Compression.ZipFile]::Open($zipPath, 'Create')
Get-ChildItem -LiteralPath $base -Recurse -File -Force | ForEach-Object {
    $entry = $_.FullName.Substring($base.Length + 1) -replace '\\', '/'
    [void][System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $_.FullName, $entry)
}
$zip.Dispose()
Remove-Item -LiteralPath $base -Recurse -Force -Confirm:$false

Write-Output "Generado $zipPath"
