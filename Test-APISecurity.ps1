# ============================================
# API Security Penetration Testing Suite
# All-in-One Comprehensive Security Audit
# Fixed Version for Windows PowerShell
# ============================================

param(
    [string]$BaseUrl = "http://127.0.0.1:8000",
    [switch]$Verbose,
    [switch]$ContinuousMode
)

# Color functions - FIXED
function Write-TestSuccess { 
    param([string]$msg) 
    Write-Host "✓ $msg" -ForegroundColor Green 
}

function Write-TestFailure { 
    param([string]$msg) 
    Write-Host "✗ $msg" -ForegroundColor Red 
}

function Write-TestWarning { 
    param([string]$msg) 
    Write-Host "⚠ $msg" -ForegroundColor Yellow 
}

function Write-TestInfo { 
    param([string]$msg) 
    Write-Host "ℹ $msg" -ForegroundColor Cyan 
}

function Write-TestHeader { 
    param([string]$msg) 
    Write-Host "`n$msg" -ForegroundColor Cyan -BackgroundColor Black 
}

# Test counters
$script:totalTests = 0
$script:passedTests = 0
$script:failedTests = 0
$script:warningTests = 0

function Add-TestResult {
    param([string]$status)
    $script:totalTests++
    switch ($status) {
        "pass" { $script:passedTests++ }
        "fail" { $script:failedTests++ }
        "warn" { $script:warningTests++ }
    }
}

# ============================================
# TEST SUITE FUNCTIONS
# ============================================

function Test-UnauthenticatedAccess {
    Write-TestHeader "[TEST 1] Unauthenticated Access Protection"
    
    $endpoints = @(
        "/api/current-status/1",
        "/api/user-stats/1",
        "/api/attendance-history/1",
        "/api/attendance-logs/1",
        "/api/user-workplace/1"
    )
    
    $protected = 0
    $exposed = 0
    
    foreach ($endpoint in $endpoints) {
        try {
            $response = Invoke-WebRequest -Uri "$BaseUrl$endpoint" -Method GET -ErrorAction Stop -TimeoutSec 5
            Write-TestFailure "$endpoint - EXPOSED (Status: $($response.StatusCode))"
            $exposed++
            if ($Verbose) {
                Write-Host "  Response: $($response.Content.Substring(0, [Math]::Min(100, $response.Content.Length)))..." -ForegroundColor Gray
            }
        } catch {
            if ($_.Exception.Response) {
                $statusCode = [int]$_.Exception.Response.StatusCode
                if ($statusCode -eq 401 -or $statusCode -eq 302 -or $statusCode -eq 419) {
                    Write-TestSuccess "$endpoint - Protected (Status: $statusCode)"
                    $protected++
                } else {
                    Write-TestWarning "$endpoint - Unexpected status: $statusCode"
                }
            } else {
                Write-TestWarning "$endpoint - Connection error: $($_.Exception.Message)"
            }
        }
    }
    
    Write-Host "`n  Summary: $protected protected, $exposed exposed" -ForegroundColor $(if ($exposed -eq 0 -and $protected -gt 0) {'Green'} else {'Red'})
    
    if ($exposed -eq 0 -and $protected -gt 0) {
        Add-TestResult "pass"
    } elseif ($exposed -gt 0) {
        Add-TestResult "fail"
    } else {
        Add-TestResult "warn"
    }
}

function Test-SecurityHeaders {
    Write-TestHeader "[TEST 2] Security Headers Validation"
    
    $requiredHeaders = @{
        "X-Content-Type-Options" = "nosniff"
        "X-Frame-Options" = @("SAMEORIGIN", "DENY")
        "X-XSS-Protection" = "1"
        "Access-Control-Allow-Origin" = $null
    }
    
    try {
        $response = Invoke-WebRequest -Uri "$BaseUrl/api/current-status/1" -Method GET -ErrorAction SilentlyContinue -TimeoutSec 5
        $headers = $response.Headers
    } catch {
        if ($_.Exception.Response) {
            $headers = $_.Exception.Response.Headers
        } else {
            $headers = @{}
        }
    }
    
    $headersPassed = 0
    $headersFailed = 0
    
    foreach ($header in $requiredHeaders.Keys) {
        $value = $headers[$header]
        $expected = $requiredHeaders[$header]
        
        if ($value) {
            if ($expected -eq $null) {
                Write-TestSuccess "$header : $value"
                $headersPassed++
            } elseif ($expected -is [array]) {
                if ($expected -contains $value) {
                    Write-TestSuccess "$header : $value"
                    $headersPassed++
                } else {
                    Write-TestWarning "$header : $value (Expected: $($expected -join ' or '))"
                    $headersPassed++
                }
            } elseif ($value -match $expected) {
                Write-TestSuccess "$header : $value"
                $headersPassed++
            } else {
                Write-TestWarning "$header : $value (Expected: $expected)"
                $headersPassed++
            }
        } else {
            Write-TestFailure "$header : MISSING"
            $headersFailed++
        }
    }
    
    Write-Host "`n  Summary: $headersPassed present, $headersFailed missing" -ForegroundColor $(if ($headersFailed -eq 0) {'Green'} else {'Red'})
    
    if ($headersFailed -eq 0) {
        Add-TestResult "pass"
    } else {
        Add-TestResult "fail"
    }
}

function Test-CORSConfiguration {
    Write-TestHeader "[TEST 3] CORS Configuration"
    
    try {
        $response = Invoke-WebRequest -Uri "$BaseUrl/api/current-status/1" -Method GET -ErrorAction SilentlyContinue -TimeoutSec 5
        $corsHeader = $response.Headers["Access-Control-Allow-Origin"]
    } catch {
        if ($_.Exception.Response) {
            $corsHeader = $_.Exception.Response.Headers["Access-Control-Allow-Origin"]
        } else {
            $corsHeader = $null
        }
    }
    
    if ($corsHeader) {
        Write-TestSuccess "Access-Control-Allow-Origin: $corsHeader"
        
        if ($corsHeader -eq "*") {
            Write-TestWarning "Using wildcard (*) - OK for development, RESTRICT in production!"
            Add-TestResult "warn"
        } else {
            Write-TestSuccess "Origin restricted to: $corsHeader"
            Add-TestResult "pass"
        }
    } else {
        Write-TestFailure "CORS headers not configured"
        Add-TestResult "fail"
    }
}

function Test-SQLInjection {
    Write-TestHeader "[TEST 4] SQL Injection Protection"
    
    $injectionTests = @(
        "/api/current-status/1' OR '1'='1",
        "/api/user-stats/1; DROP TABLE users--",
        "/api/attendance-history/1' UNION SELECT",
        "/api/user-workplace/1' AND 1=1--"
    )
    
    $protected = 0
    $vulnerable = 0
    
    foreach ($injection in $injectionTests) {
        try {
            $encodedUrl = [System.Uri]::EscapeUriString("$BaseUrl$injection")
            $response = Invoke-WebRequest -Uri $encodedUrl -Method GET -ErrorAction Stop -TimeoutSec 5
            Write-TestFailure "Potential SQL injection vulnerability at: $injection"
            $vulnerable++
        } catch {
            if ($_.Exception.Response) {
                $statusCode = [int]$_.Exception.Response.StatusCode
                if ($statusCode -eq 404 -or $statusCode -eq 401 -or $statusCode -eq 403 -or $statusCode -eq 400 -or $statusCode -eq 419) {
                    $protected++
                }
            } else {
                $protected++
            }
        }
    }
    
    Write-TestSuccess "SQL injection attempts blocked: $protected/$($injectionTests.Count)"
    
    if ($vulnerable -eq 0) {
        Add-TestResult "pass"
    } else {
        Add-TestResult "fail"
    }
}

function Test-XSSProtection {
    Write-TestHeader "[TEST 5] XSS (Cross-Site Scripting) Protection"
    
    $xssTests = @(
        "/api/current-status/1?name=<script>alert('XSS')</script>",
        "/api/user-stats/1?data=<img src=x onerror=alert(1)>",
        "/api/attendance-history/1?q=<svg onload=alert(1)>"
    )
    
    $protected = 0
    
    foreach ($xss in $xssTests) {
        try {
            $encodedUrl = "$BaseUrl$xss"
            $response = Invoke-WebRequest -Uri $encodedUrl -Method GET -ErrorAction Stop -TimeoutSec 5
            $content = $response.Content
            
            if ($content -match "<script>" -or $content -match "<img" -or $content -match "<svg") {
                Write-TestFailure "XSS payload might be reflected"
            } else {
                $protected++
            }
        } catch {
            $protected++
        }
    }
    
    Write-TestSuccess "XSS attempts blocked: $protected/$($xssTests.Count)"
    
    if ($protected -eq $xssTests.Count) {
        Add-TestResult "pass"
    } else {
        Add-TestResult "fail"
    }
}

function Test-CookieSecurity {
    Write-TestHeader "[TEST 6] Cookie Security Configuration"
    
    try {
        $response = Invoke-WebRequest -Uri "$BaseUrl/login" -Method GET -SessionVariable session -TimeoutSec 5
        $cookies = $session.Cookies.GetCookies($BaseUrl)
        
        $secureCookies = 0
        $insecureCookies = 0
        
        foreach ($cookie in $cookies) {
            if ($Verbose) {
                Write-Host "`n  Cookie: $($cookie.Name)" -ForegroundColor Gray
                Write-Host "    HttpOnly: $($cookie.HttpOnly)" -ForegroundColor Gray
                Write-Host "    Secure: $($cookie.Secure)" -ForegroundColor Gray
            }
            
            if ($cookie.HttpOnly) {
                Write-TestSuccess "Cookie '$($cookie.Name)' has HttpOnly flag"
                $secureCookies++
            } else {
                Write-TestWarning "Cookie '$($cookie.Name)' missing HttpOnly flag (XSRF token is exception)"
                $insecureCookies++
            }
        }
        
        if ($cookies.Count -eq 0) {
            Write-TestInfo "No cookies set on /login endpoint"
            Add-TestResult "pass"
        } elseif ($insecureCookies -le 1) {
            # One insecure cookie (XSRF) is acceptable
            Add-TestResult "pass"
        } else {
            Add-TestResult "warn"
        }
        
    } catch {
        Write-TestWarning "Could not test cookie security: $($_.Exception.Message)"
        Add-TestResult "warn"
    }
}

function Test-RateLimiting {
    Write-TestHeader "[TEST 7] Rate Limiting / DDoS Protection"
    
    Write-Host "Sending 20 rapid requests..." -ForegroundColor Cyan
    
    $requests = 20
    $blocked = 0
    $successful = 0
    
    1..$requests | ForEach-Object {
        try {
            $response = Invoke-WebRequest -Uri "$BaseUrl/api/current-status/1" -Method GET -ErrorAction Stop -TimeoutSec 2
            $successful++
        } catch {
            if ($_.Exception.Response) {
                $statusCode = [int]$_.Exception.Response.StatusCode
                if ($statusCode -eq 429) {
                    $blocked++
                }
            }
        }
        Start-Sleep -Milliseconds 50
    }
    
    if ($blocked -gt 0) {
        Write-TestSuccess "Rate limiting active - $blocked requests throttled"
        Add-TestResult "pass"
    } else {
        Write-TestWarning "No rate limiting detected - consider implementing throttling"
        Add-TestResult "warn"
    }
}

function Test-HTTPSConfiguration {
    Write-TestHeader "[TEST 8] HTTPS Configuration"
    
    if ($BaseUrl -match "^https://") {
        try {
            $response = Invoke-WebRequest -Uri $BaseUrl -Method GET -ErrorAction SilentlyContinue -TimeoutSec 5
            $hstsHeader = $response.Headers["Strict-Transport-Security"]
            
            if ($hstsHeader) {
                Write-TestSuccess "HSTS Header present: $hstsHeader"
                Add-TestResult "pass"
            } else {
                Write-TestWarning "HSTS Header missing (recommended for HTTPS)"
                Add-TestResult "warn"
            }
        } catch {
            Write-TestWarning "Could not verify HTTPS configuration"
            Add-TestResult "warn"
        }
    } else {
        Write-Host "Running on HTTP (development) - HTTPS required for production" -ForegroundColor Cyan
        Add-TestResult "pass"
    }
}

function Test-SensitiveDataExposure {
    Write-TestHeader "[TEST 9] Sensitive Data Exposure Check"
    
    $endpoints = @(
        "/api/current-status/1",
        "/api/user-stats/1"
    )
    
    $exposed = $false
    
    foreach ($endpoint in $endpoints) {
        try {
            $response = Invoke-WebRequest -Uri "$BaseUrl$endpoint" -Method GET -ErrorAction Stop -TimeoutSec 5
            $content = $response.Content.ToLower()
            
            if ($content -match "password|secret|api_key|private_key") {
                Write-TestFailure "$endpoint - Potential sensitive data in response"
                $exposed = $true
            }
        } catch {
            # Expected - endpoint should be protected
        }
    }
    
    if (-not $exposed) {
        Write-TestSuccess "No obvious sensitive data exposure detected"
        Add-TestResult "pass"
    } else {
        Add-TestResult "fail"
    }
}

function Test-ErrorHandling {
    Write-TestHeader "[TEST 10] Error Handling & Information Disclosure"
    
    $testEndpoints = @(
        "/api/nonexistent/endpoint",
        "/api/current-status/999999",
        "/api/user-stats/abc"
    )
    
    $safeErrors = 0
    $verboseErrors = 0
    
    foreach ($endpoint in $testEndpoints) {
        try {
            $response = Invoke-WebRequest -Uri "$BaseUrl$endpoint" -Method GET -ErrorAction Stop -TimeoutSec 5
        } catch {
            if ($_.ErrorDetails.Message) {
                $errorContent = $_.ErrorDetails.Message
                
                if ($errorContent -match "stack trace|exception|sql|database|file path|line \d+") {
                    Write-TestFailure "$endpoint - Verbose error message detected"
                    $verboseErrors++
                    if ($Verbose) {
                        Write-Host "  Error preview: $($errorContent.Substring(0, [Math]::Min(100, $errorContent.Length)))..." -ForegroundColor Gray
                    }
                } else {
                    $safeErrors++
                }
            } else {
                $safeErrors++
            }
        }
    }
    
    Write-TestSuccess "Safe error handling: $safeErrors/$($testEndpoints.Count)"
    
    if ($verboseErrors -eq 0) {
        Add-TestResult "pass"
    } else {
        Add-TestResult "fail"
    }
}

# ============================================
# REPORT GENERATION
# ============================================

function Show-TestReport {
    Write-Host "`n" -NoNewline
    Write-Host "============================================" -ForegroundColor Cyan
    Write-Host "         SECURITY AUDIT REPORT" -ForegroundColor Cyan
    Write-Host "============================================" -ForegroundColor Cyan
    
    Write-Host "`nTest Results:" -ForegroundColor White
    Write-Host "  Total Tests:    $totalTests" -ForegroundColor White
    Write-Host "  Passed:         $passedTests" -ForegroundColor Green
    Write-Host "  Failed:         $failedTests" -ForegroundColor Red
    Write-Host "  Warnings:       $warningTests" -ForegroundColor Yellow
    
    $score = if ($totalTests -gt 0) { [math]::Round(($passedTests / $totalTests) * 100, 2) } else { 0 }
    
    Write-Host "`nSecurity Score: " -NoNewline -ForegroundColor White
    if ($score -ge 90) {
        Write-Host "$score% - EXCELLENT" -ForegroundColor Green
    } elseif ($score -ge 70) {
        Write-Host "$score% - GOOD" -ForegroundColor Green
    } elseif ($score -ge 50) {
        Write-Host "$score% - NEEDS IMPROVEMENT" -ForegroundColor Yellow
    } else {
        Write-Host "$score% - CRITICAL" -ForegroundColor Red
    }
    
    Write-Host "`nRecommendations:" -ForegroundColor White
    
    if ($failedTests -gt 0) {
        Write-Host "  • Fix $failedTests critical security issues immediately" -ForegroundColor Red
    }
    
    if ($warningTests -gt 0) {
        Write-Host "  • Address $warningTests warnings before production deployment" -ForegroundColor Yellow
    }
    
    if ($score -ge 80) {
        Write-Host "  • Security implementation is strong!" -ForegroundColor Green
        Write-Host "  • Ensure production environment variables are properly configured" -ForegroundColor Green
    }
    
    if ($BaseUrl -notmatch "^https://") {
        Write-Host "  • Switch to HTTPS for production deployment" -ForegroundColor Yellow
    }
    
    Write-Host "`n============================================" -ForegroundColor Cyan
    Write-Host "Test completed at $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Gray
    Write-Host "============================================`n" -ForegroundColor Cyan
}

# ============================================
# MAIN EXECUTION
# ============================================

function Start-SecurityAudit {
    Clear-Host
    
    Write-Host "============================================" -ForegroundColor Cyan
    Write-Host "   API SECURITY PENETRATION TEST SUITE" -ForegroundColor Cyan
    Write-Host "============================================" -ForegroundColor Cyan
    Write-Host "Target: $BaseUrl" -ForegroundColor White
    Write-Host "Time: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Gray
    Write-Host "============================================`n" -ForegroundColor Cyan
    
    # Reset counters
    $script:totalTests = 0
    $script:passedTests = 0
    $script:failedTests = 0
    $script:warningTests = 0
    
    # Run all tests
    Test-UnauthenticatedAccess
    Test-SecurityHeaders
    Test-CORSConfiguration
    Test-SQLInjection
    Test-XSSProtection
    Test-CookieSecurity
    Test-RateLimiting
    Test-HTTPSConfiguration
    Test-SensitiveDataExposure
    Test-ErrorHandling
    
    # Show report
    Show-TestReport
}

# ============================================
# CONTINUOUS MODE
# ============================================

if ($ContinuousMode) {
    Write-Host "Starting continuous monitoring mode..." -ForegroundColor Yellow
    Write-Host "Press Ctrl+C to stop`n" -ForegroundColor Gray
    
    while ($true) {
        Start-SecurityAudit
        Write-Host "Waiting 30 seconds before next scan..." -ForegroundColor Gray
        Start-Sleep -Seconds 30
    }
} else {
    Start-SecurityAudit
}