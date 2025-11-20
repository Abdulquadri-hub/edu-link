 = 'app/Models/ParentAssignment.php'
 = [System.IO.File]::ReadAllBytes()
if (.Length -gt 3 -and [0] -eq 0xEF -and [1] -eq 0xBB -and [2] -eq 0xBF) {
     = .Length - 3
     = New-Object byte[] 
    [System.Array]::Copy(, 3, , 0, )
    [System.IO.File]::WriteAllBytes(, )
    Write-Output 'Removed BOM from ' + 
} else {
    Write-Output 'No BOM present'
}
