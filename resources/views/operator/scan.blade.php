@extends('layouts.app')

@section('title', 'Scan Tickets')

@section('content')
@if(auth()->user()->isOperator())
    <!-- Operator-only notice -->
    <div class="alert alert-info mb-4">
        <i data-lucide="info" class="me-1"></i>
        <strong>Operator Mode:</strong> You have access to the ticket scanning system only. Use the scanner below to validate tickets at the event entrance.
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i data-lucide="scan-line" class="me-2"></i>Ticket Scanner
                </h4>
                <small class="text-muted">Scan QR codes to validate entry</small>
            </div>
            <div class="card-body">
                <!-- Scanner Interface -->
                <div class="text-center mb-4">
                    <!-- Camera Access Notice -->
                    <div class="alert alert-info mb-3">
                        <i data-lucide="info" class="me-1"></i>
                        <strong>Camera Access Required:</strong> This page needs camera permission to scan QR codes.
                        If using Chrome, the site must be accessed via HTTPS or localhost for camera access.
                        <br><small>Make sure to allow camera access when prompted by your browser.</small>
                    </div>

                    <div id="scanner-container" class="position-relative">
                        <div class="video-container position-relative">
                            <video id="scanner-video" class="rounded border"
                                   style="max-width: 100%; height: 300px; object-fit: cover;"
                                   autoplay muted playsinline></video>
                            <div id="scanner-overlay" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="pointer-events: none;">
                                <div class="scanner-frame"></div>
                            </div>
                        </div>

                        <!-- Camera Controls -->
                        <div class="mt-3">
                            <button id="start-scanner" class="btn btn-success me-2">
                                <i data-lucide="camera" class="me-1"></i>Start Scanner
                            </button>
                            <button id="stop-scanner" class="btn btn-danger me-2" style="display: none;">
                                <i data-lucide="camera-off" class="me-1"></i>Stop Scanner
                            </button>
                            <button id="toggle-camera" class="btn btn-outline-secondary me-2" style="display: none;">
                                <i data-lucide="refresh-ccw" class="me-1"></i>Switch Camera
                            </button>
                            <button id="test-camera" class="btn btn-outline-info">
                                <i data-lucide="aperture" class="me-1"></i>Test Camera
                            </button>
                        </div>
                    </div>

                    <!-- Manual Entry Option -->
                    <div class="mt-4">
                        <hr>
                        <h6>Manual Entry</h6>
                        <div class="input-group">
                            <input type="text" id="manual-code" class="form-control" placeholder="Enter ticket number or QR code manually">
                            <button class="btn btn-outline-primary" type="button" onclick="validateManualCode()">
                                <i data-lucide="search" class="me-1"></i>Validate
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Scan Results -->
                <div id="scan-results" class="mt-4" style="display: none;">
                    <hr>
                    <h6>Scan Result</h6>
                    <div id="result-content"></div>
                </div>

                <!-- Recent Scans -->
                <div id="recent-scans" class="mt-4">
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Recent Scans</h6>
                        <button class="btn btn-outline-secondary btn-sm" onclick="refreshRecentScans()">
                            <i data-lucide="rotate-ccw" class="me-1"></i>Refresh
                        </button>
                    </div>
                    <div id="recent-scans-list">
                        <div class="text-center text-muted py-3">
                            <i data-lucide="history"></i>
                            <p class="mb-0">No recent scans</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scan Result Modal -->
<div class="modal fade" id="scanResultModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scanResultTitle">Scan Result</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="scanResultBody">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/@zxing/library@0.20.0/umd/index.min.js"></script>
<script>
    // Check if ZXing loaded properly with more detailed checks
    if (typeof ZXing === 'undefined') {
        console.error('ZXing library failed to load from CDN');
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger';
            alert.innerHTML = `
                <strong>QR Scanner Library Error:</strong> The QR code scanning library failed to load.
                This might be due to network issues. Please refresh the page or use manual entry.
            `;
            document.querySelector('.card-body').insertBefore(alert, document.querySelector('.card-body').firstChild);
        });
    } else {
        console.log('ZXing available:', ZXing);
        console.log('BrowserQRCodeReader available:', ZXing.BrowserQRCodeReader);
    }
</script>
<script>
    let codeReader = null;
    let selectedDeviceId = null;
    let currentTicket = null;

    // Initialize scanner
    document.addEventListener('DOMContentLoaded', function() {
        loadRecentScans();

        // Initialize code reader
        try {
            codeReader = new ZXing.BrowserQRCodeReader();
            console.log('ZXing library loaded successfully');
            console.log('CodeReader instance:', codeReader);
            console.log('Available methods:', Object.getOwnPropertyNames(Object.getPrototypeOf(codeReader)));
            console.log('ZXing version info:', ZXing);

            // Test if key methods exist
            console.log('Has listVideoInputDevices:', typeof codeReader.listVideoInputDevices === 'function');
            console.log('Has decodeFromVideoDevice:', typeof codeReader.decodeFromVideoDevice === 'function');
        } catch (error) {
            console.error('Error initializing ZXing:', error);
            showAlert('QR scanner library failed to load. Please refresh the page.', 'danger');
            return;
        }

        // Get available cameras
        codeReader.listVideoInputDevices().then((videoInputDevices) => {
            console.log('Available cameras:', videoInputDevices);
            if (videoInputDevices.length > 0) {
                // Handle undefined deviceId by using null (default camera)
                selectedDeviceId = videoInputDevices[0].deviceId || null;
                console.log('Selected camera ID:', selectedDeviceId);

                // If deviceId is undefined, try to get camera permissions first
                if (selectedDeviceId === null || selectedDeviceId === undefined) {
                    console.log('Device ID is undefined, requesting camera permissions...');
                    navigator.mediaDevices.getUserMedia({ video: true })
                        .then(stream => {
                            console.log('Camera permission granted');
                            // Stop the stream and try to get devices again
                            stream.getTracks().forEach(track => track.stop());
                            return codeReader.listVideoInputDevices();
                        })
                        .then(devices => {
                            console.log('Devices after permission:', devices);
                            if (devices.length > 0 && devices[0].deviceId) {
                                selectedDeviceId = devices[0].deviceId;
                                console.log('Updated camera ID:', selectedDeviceId);
                            } else {
                                selectedDeviceId = null; // Use default camera
                            }
                            showAlert('Camera ready. Click "Start Scanner" to begin.', 'success');
                        })
                        .catch(err => {
                            console.error('Permission request failed:', err);
                            showAlert('Camera permission denied. Please allow camera access in your browser settings.', 'warning');
                        });
                } else {
                    showAlert('Camera detected. Click "Start Scanner" to begin.', 'info');
                }

                if (videoInputDevices.length > 1) {
                    document.getElementById('toggle-camera').style.display = 'inline-block';
                }
            } else {
                console.warn('No cameras found');
                showAlert('No cameras found. Please ensure camera permissions are granted and try manual entry.', 'warning');
            }
        }).catch((err) => {
            console.error('Camera access error:', err);
            showAlert(`Error accessing cameras: ${err.message}. Please ensure camera permissions are granted or use manual entry.`, 'danger');
        });
    });

    // Start scanner
    document.getElementById('start-scanner').addEventListener('click', function() {
        console.log('=== START SCANNER BUTTON CLICKED ===');
        console.log('Start scanner clicked, selectedDeviceId:', selectedDeviceId);
        console.log('selectedDeviceId type:', typeof selectedDeviceId);
        console.log('selectedDeviceId !== undefined:', selectedDeviceId !== undefined);

        // Allow null or undefined deviceId (will use default camera)
        if (selectedDeviceId !== undefined) {
            console.log('Device ID is valid, calling startScanning()...');
            startScanning();
        } else {
            console.log('Device ID is undefined, showing error...');
            showAlert('No camera available. Please refresh the page and grant camera permissions.', 'warning');
        }
    });

    // Stop scanner
    document.getElementById('stop-scanner').addEventListener('click', function() {
        stopScanning();
    });

    // Test camera button
    document.getElementById('test-camera').addEventListener('click', function() {
        console.log('=== TEST CAMERA CLICKED ===');
        const videoElement = document.getElementById('scanner-video');

        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                console.log('Camera stream obtained:', stream);
                videoElement.srcObject = stream;
                videoElement.play();
                showAlert('Camera test successful! You should see video feed.', 'success');

                // Stop stream after 5 seconds
                setTimeout(() => {
                    stream.getTracks().forEach(track => track.stop());
                    videoElement.srcObject = null;
                    showAlert('Camera test finished.', 'info');
                }, 5000);
            })
            .catch(err => {
                console.error('Camera test failed:', err);
                showAlert(`Camera test failed: ${err.message}`, 'danger');
            });
    });

    // Toggle camera
    document.getElementById('toggle-camera').addEventListener('click', function() {
        codeReader.listVideoInputDevices().then((videoInputDevices) => {
            const currentIndex = videoInputDevices.findIndex(device => device.deviceId === selectedDeviceId);
            const nextIndex = (currentIndex + 1) % videoInputDevices.length;
            selectedDeviceId = videoInputDevices[nextIndex].deviceId;

            if (document.getElementById('start-scanner').style.display === 'none') {
                stopScanning();
                setTimeout(startScanning, 100);
            }
        });
    });

    function startScanning() {
        console.log('=== START SCANNING FUNCTION CALLED ===');
        const videoElement = document.getElementById('scanner-video');
        const deviceId = selectedDeviceId || null; // Use null for default camera if no specific device
        console.log('Video element:', videoElement);
        console.log('Starting camera scan with device:', deviceId);
        console.log('CodeReader instance:', codeReader);

        // Show loading state
        showAlert('Starting camera...', 'info');

        console.log('About to call decodeFromVideoDevice...');

        // Set video constraints to help with exposure
        const constraints = {
            video: {
                deviceId: deviceId ? { exact: deviceId } : undefined,
                width: { ideal: 640 },
                height: { ideal: 480 },
                facingMode: 'environment', // Use back camera if available
                focusMode: 'continuous',
                exposureMode: 'continuous',
                whiteBalanceMode: 'continuous'
            }
        };

        const decodePromise = codeReader.decodeFromVideoDevice(deviceId, videoElement, (result, err) => {
            console.log('=== DECODE CALLBACK ===');
            if (result) {
                console.log('QR Code scanned successfully:', result.text);
                handleScanResult(result.text);
            }
            if (err && !(err instanceof ZXing.NotFoundException)) {
                console.error('Scanning error in callback:', err);
            }
        });

        console.log('Decode promise:', decodePromise);

        decodePromise.then(() => {
            console.log('=== CAMERA STARTED SUCCESSFULLY ===');
            showAlert('Camera started. Position QR code in the frame.', 'success');
            document.getElementById('start-scanner').style.display = 'none';
            document.getElementById('stop-scanner').style.display = 'inline-block';
            document.getElementById('toggle-camera').style.display = 'inline-block';

            // Apply additional video properties to help with brightness issues
            const stream = videoElement.srcObject;
            if (stream && stream.getVideoTracks) {
                const videoTracks = stream.getVideoTracks();
                if (videoTracks.length > 0) {
                    const track = videoTracks[0];
                    console.log('Video track capabilities:', track.getCapabilities ? track.getCapabilities() : 'Not supported');

                    // Try to apply settings to prevent auto-exposure issues
                    if (track.applyConstraints) {
                        track.applyConstraints({
                            exposureMode: 'manual',
                            exposureCompensation: 0,
                            brightness: 0.5,
                            contrast: 1.0
                        }).catch(err => {
                            console.log('Could not apply manual exposure constraints:', err);
                            // Fallback to continuous mode
                            track.applyConstraints({
                                exposureMode: 'continuous'
                            }).catch(e => console.log('Fallback constraints failed:', e));
                        });
                    }
                }
            }
        }).catch((err) => {
            console.error('=== CAMERA START FAILED ===');
            console.error('Failed to start camera:', err);
            console.error('Error name:', err.name);
            console.error('Error message:', err.message);
            console.error('Error stack:', err.stack);
            showAlert(`Failed to start camera: ${err.message}. Please check camera permissions and try again.`, 'danger');
        });
    }

    function stopScanning() {
        console.log('Stopping scanner...');

        if (codeReader) {
            codeReader.reset();
        }

        // Properly stop all video streams
        const videoElement = document.getElementById('scanner-video');
        if (videoElement && videoElement.srcObject) {
            const stream = videoElement.srcObject;
            if (stream && stream.getTracks) {
                const tracks = stream.getTracks();
                tracks.forEach(track => {
                    console.log('Stopping track:', track);
                    track.stop();
                });
            }
            videoElement.srcObject = null;
        }

        document.getElementById('start-scanner').style.display = 'inline-block';
        document.getElementById('stop-scanner').style.display = 'none';
        document.getElementById('toggle-camera').style.display = 'none';

        console.log('Scanner stopped successfully');
    }

    function validateManualCode() {
        const code = document.getElementById('manual-code').value.trim();
        if (code) {
            handleScanResult(code);
            document.getElementById('manual-code').value = '';
        }
    }

    // Handle scan result
    function handleScanResult(qrCode) {
        console.log('Handling scan result:', qrCode);

        // Temporarily pause scanning to prevent multiple scans
        const isScanning = document.getElementById('start-scanner').style.display === 'none';
        if (isScanning) {
            // Stop the current scan temporarily
            if (codeReader) {
                codeReader.reset();
            }
        }

        // Make AJAX request to validate ticket using the correct route
        fetch(`{{ route("tickets.validate", ":qr_code") }}`.replace(':qr_code', encodeURIComponent(qrCode)), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showValidTicket(data.ticket);
            } else {
                showInvalidTicket(data.message);
            }
            addToRecentScans(data);

            // After showing result, restart scanning if it was active
            if (isScanning) {
                setTimeout(() => {
                    console.log('Restarting scanner after successful scan...');
                    startScanning();
                }, 2000); // Wait 2 seconds before restarting
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error validating ticket', 'danger');

            // Restart scanning even on error
            if (isScanning) {
                setTimeout(() => {
                    console.log('Restarting scanner after error...');
                    startScanning();
                }, 1000);
            }
        });
    }

    function showValidTicket(ticket) {
        currentTicket = ticket;

        const modalTitle = document.getElementById('scanResultTitle');
        const modalBody = document.getElementById('scanResultBody');

        modalTitle.textContent = 'Valid Ticket';
        modalTitle.className = 'modal-title text-success';

        modalBody.innerHTML = `
            <div class="alert alert-success">
                <h6><i data-lucide="check-circle" class="me-1"></i>Ticket Validated Successfully</h6>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <p><strong>Ticket:</strong> ${ticket.ticket_number}</p>
                    <p><strong>Holder:</strong> ${ticket.user_name}</p>
                    <p><strong>Email:</strong> ${ticket.user_email}</p>
                </div>
                <div class="col-sm-6">
                    <p><strong>Event:</strong> ${ticket.event_title}</p>
                    <p><strong>Date:</strong> ${ticket.event_date}</p>
                    <p><strong>Time:</strong> ${ticket.event_time}</p>
                </div>
            </div>
            <p><strong>Validated by:</strong> ${ticket.validated_by}</p>
            <p><strong>Validated at:</strong> ${ticket.validated_at}</p>
        `;

        const modal = new bootstrap.Modal(document.getElementById('scanResultModal'));
        modal.show();

        // Reset camera when modal is hidden
        document.getElementById('scanResultModal').addEventListener('hidden.bs.modal', function() {
            console.log('Modal closed, refreshing camera stream...');
            const videoElement = document.getElementById('scanner-video');
            if (videoElement && videoElement.srcObject) {
                // Force refresh the video stream to reset exposure
                const stream = videoElement.srcObject;
                videoElement.srcObject = null;
                setTimeout(() => {
                    videoElement.srcObject = stream;
                }, 100);
            }
        }, { once: true }); // Use once: true to prevent multiple event listeners
    }

    function showInvalidTicket(message) {
        const modalTitle = document.getElementById('scanResultTitle');
        const modalBody = document.getElementById('scanResultBody');

        modalTitle.textContent = 'Invalid Ticket';
        modalTitle.className = 'modal-title text-danger';

        modalBody.innerHTML = `
            <div class="alert alert-danger">
                <h6><i data-lucide="x-circle" class="me-1"></i>Invalid Ticket</h6>
                <p class="mb-0">${message}</p>
            </div>
        `;

        new bootstrap.Modal(document.getElementById('scanResultModal')).show();
    }

    function addToRecentScans(scanData) {
        const recentList = document.getElementById('recent-scans-list');
        const scanTime = new Date().toLocaleTimeString();

        const scanItem = document.createElement('div');
        scanItem.className = 'border-bottom py-2';
        scanItem.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>${scanData.success ? scanData.ticket.ticket_number : 'Invalid Code'}</strong>
                    <br><small class="text-muted">${scanTime}</small>
                </div>
                <span class="badge ${scanData.success ? 'bg-success' : 'bg-danger'}">
                    ${scanData.success ? 'Valid' : 'Invalid'}
                </span>
            </div>
        `;

        recentList.insertBefore(scanItem, recentList.firstChild);

        // Keep only last 10 scans
        const scans = recentList.children;
        if (scans.length > 10) {
            recentList.removeChild(scans[scans.length - 1]);
        }
    }

    function loadRecentScans() {
        // In a real implementation, this would load from the server
        // For now, we'll just keep local session data
    }

    function refreshRecentScans() {
        loadRecentScans();
        showAlert('Recent scans refreshed', 'info');
    }

    function showAlert(message, type) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        const container = document.querySelector('.card-body');
        container.insertBefore(alert, container.firstChild);

        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }

    // Enter key for manual entry
    document.getElementById('manual-code').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            validateManualCode();
        }
    });
</script>

<style>
    .scanner-frame {
        width: 200px;
        height: 200px;
        border: 3px solid #28a745;
        border-radius: 10px;
        position: relative;
    }

    .scanner-frame::before,
    .scanner-frame::after {
        content: '';
        position: absolute;
        width: 30px;
        height: 30px;
        border: 3px solid #28a745;
    }

    .scanner-frame::before {
        top: -3px;
        left: -3px;
        border-right: none;
        border-bottom: none;
    }

    .scanner-frame::after {
        bottom: -3px;
        right: -3px;
        border-left: none;
        border-top: none;
    }

    #scanner-video {
        background: #000;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
</style>
@endpush
@endsection
