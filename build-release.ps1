$Root = $PSScriptRoot
$BuildRoot = Join-Path $Root "build"
$PackageRoot = Join-Path $BuildRoot "mixpack-bundles"
$Zip = Join-Path $BuildRoot "mixpack-bundles-1.0.0.zip"

if (Test-Path $BuildRoot) {
    Remove-Item $BuildRoot -Recurse -Force
}

New-Item -ItemType Directory -Path $PackageRoot | Out-Null

$ExcludeDirectories = @(
    ".git",
    "build",
    "docs",
    "tests"
)

$ExcludeFiles = @(
    ".gitignore",
    ".editorconfig",
    "phpcs.xml.dist",
    "build-release.ps1",
    ".gitkeep"
)

Get-ChildItem $Root -Force | ForEach-Object {
    if ($ExcludeDirectories -contains $_.Name) {
        return
    }

    if ($ExcludeFiles -contains $_.Name) {
        return
    }

    if ($_.PSIsContainer) {
        Copy-Item $_.FullName $PackageRoot -Recurse -Force
    } else {
        Copy-Item $_.FullName $PackageRoot -Force
    }
}

Get-ChildItem $PackageRoot -Recurse -Force |
    Where-Object { $ExcludeFiles -contains $_.Name } |
    Remove-Item -Force

Compress-Archive `
    -Path $PackageRoot `
    -DestinationPath $Zip `
    -CompressionLevel Optimal

Write-Host ""
Write-Host "Release created:"
Write-Host $Zip