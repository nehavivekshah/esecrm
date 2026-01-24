<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="shortcut icon" href="{{ asset('public/assets/images/favicon.ico') }}" type="image/x-icon">
    
        <title>@yield('title', 'Customer Relationship Management')</title>
        
        <!-- Bootstrap 5.3.3 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- Bootstrap-Select CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
        
        <!-- DataTables Bootstrap 5 CSS -->
        <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        
        @include('inc.loginHeadLink')
        
        <style>
            /* Loader Styles */
            #page-loader {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.9);
                z-index: 9999;
                display: flex;
                justify-content: center;
                align-items: center;
            }
    
            .spinner {
                width: 50px;
                height: 50px;
                border: 6px solid #ccc;
                border-top: 6px solid #3498db;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
    
            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }
                100% {
                    transform: rotate(360deg);
                }
            }
    
            /* Hide the page content until the loader is gone */
            body {
                overflow: hidden;
            }
    
            body.loaded #page-loader {
                display: none;
            }
    
            body.loaded {
                overflow: auto;
            }
        </style>
        
        <script src="https://cdn.tiny.cloud/1/wa5nrulndxu7i9yumfv1j52xb09r488mk492qb9qku6w4zvp/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
    </head>
    <body class="bg-light" onload="loadSharedPrefData()">
        
        <!-- Loader -->
        <div id="page-loader">
            <div class="spinner"></div>
        </div>
        
        @if(Auth::check())
        
        @include('inc.sidebar')
            
        @endif
        
        @yield('content')
        
        @if (Session::has('success'))
        <div class="response-msg auto-hide">
            <div class="alert alert-success shadow alert-dismissible" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                {{ Session::get('success') }}
            </div>
        </div>
        @elseif (Session::has('error'))
        <div class="response-msg auto-hide">
            <div class="alert alert-danger shadow alert-dismissible" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                {{ Session::get('error') }}
            </div>
        </div>
        @endif
        
        <!-- Js Library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
        
        <script>
            // Wait for the page to fully load
            window.addEventListener("load", function () {
                // Remove loader and show the page content
                document.body.classList.add("loaded");
                //document.querySelector(".content").style.display = "block";
            });
        </script>
    
        @include('inc.script')

        <script>
            $(document).ready(function() {
                
                $('#lists').DataTable({
                "pageLength": 50,
                    "order": [],
                });
                
                $("#leadslists_filter label input").attr("placeholder", "Search..");
                $("#lists_filter label input").attr("placeholder", "Search..");
                
                $('.selectpicker').selectpicker();
                
            });
            
            // Enable Bootstrap Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        </script>
        
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const path = window.location.pathname.toLowerCase();
                let module = "unknown";
                let subjectId = 0;
            
                // ---------- Detect module and subject ----------
                if (path.match(/leads\/\d+/)) { module="lead"; subjectId = path.split("/").pop(); }
                else if (path.match(/tasks\/\d+/)) { module="task"; subjectId = path.split("/").pop(); }
                else if (path.match(/proposals\/\d+/)) { module="proposal"; subjectId = path.split("/").pop(); }
                else if (path.match(/invoices\/\d+/)) { module="invoice"; subjectId = path.split("/").pop(); }
                else if (path.includes("/leads")) module="lead";
                else if (path.includes("/tasks")) module="task";
                else if (path.includes("/dashboard")) module="dashboard";
            
                // ---------- Log page view ----------
                if (module !== "unknown") {
                    sendActivity(module + "_view", subjectId, module.charAt(0).toUpperCase() + module.slice(1) + " page opened");
                }
            
                // ---------- Log clicks globally ----------
                document.body.addEventListener("click", function(e) {
                    let target = e.target.closest("[data-track-type]");
                    if (!target) return;
            
                    let type = target.getAttribute("data-track-type");
                    let value = target.getAttribute("data-track-value") || null;
            
                    sendActivity(type, subjectId, type + " clicked", value);
                });
            
                // ---------- Function to send activity ----------
                function sendActivity(type, subject_id, description, value = null) {
                    fetch("/activities/store", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            type: type,
                            subject_id: subject_id,
                            description: description,
                            value: value
                        })
                    }).then(res => res.json()).then(console.log).catch(console.error);
                }
            });
            </script>

        
    </body>

</html>