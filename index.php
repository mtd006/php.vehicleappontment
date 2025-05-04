<?php
// Start session MUST be the very first thing
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Basic check if user is logged in (more robust check done via JS calling API)
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO Meta Tags -->
    <title>Vehicle Appointment System for Warehouse Unloading | Global Standards</title>
    <meta name="description" content="Schedule and manage vehicle unloading appointments using our global-compliant, AI-assisted vehicle appointment system for transporters and vendors.">
    <meta name="keywords" content="Vehicle Appointment, Transport Scheduler, Warehouse Unloading, PO Tracker, Global Logistics, Transport Management System">

    <link rel="icon" href="favicon.ico" type="image/x-icon"> <!-- Optional: Add a favicon -->

    <style>
        /* --- CSS - Keep this section well-organized --- */
        :root {
            --primary-color: #4a6d7c; /* Muted Blue/Green */
            --secondary-color: #8b7e75; /* Earthy Brown/Beige */
            --accent-color: #bfae9e; /* Lighter Sand */
            --background-color: #f4f1ea; /* Very Light Beige/Off-white */
            --text-color: #333333;
            --card-bg: #ffffff;
            --border-color: #dcdcdc;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --scheduled-color: #17a2b8; /* Info */
            --arrived-color: #ffc107;   /* Warning */
            --unloading-color: #007bff; /* Primary Blue */
            --delayed-color: #dc3545;   /* Danger */
            --completed-color: #28a745; /* Success */
            --cancelled-color: #6c757d; /* Secondary Gray */

            --font-family-serif: Georgia, 'Times New Roman', Times, serif;
            --font-family-sans: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Fallback sans-serif */

            --box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            --border-radius: 5px;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-family-sans);
            line-height: 1.6;
            background-color: var(--background-color);
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            font-size: 16px; /* Base font size */
        }

        .app-container {
            display: flex;
            flex-grow: 1;
        }

        /* --- Login/Register Styles --- */
        .auth-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
        }

        .auth-container h2 {
            margin-bottom: 25px;
            color: var(--primary-color);
            font-family: var(--font-family-serif);
        }

        .auth-container .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .auth-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--secondary-color);
        }

        .auth-container input[type="text"],
        .auth-container input[type="password"],
        .auth-container input[type="email"], /* Added for consistency */
        .auth-container input[type="tel"] { /* Added for consistency */
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
        }

        .auth-container .btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }

        .auth-container .btn:hover {
            background-color: #3a5764; /* Darker shade */
        }

        .auth-container .toggle-link {
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .auth-container .toggle-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: bold;
        }
        .auth-container .toggle-link a:hover {
            text-decoration: underline;
        }

        /* --- Main Application Styles (Dashboard) --- */
        #main-app {
            display: flex;
            width: 100%;
            transition: opacity 0.5s ease-in-out;
        }

        .sidebar {
            width: 240px;
            background-color: var(--secondary-color);
            color: white;
            padding: 20px 0;
            flex-shrink: 0;
            transition: width 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .sidebar .logo {
             text-align: center;
             margin-bottom: 30px;
             padding: 0 15px;
        }
         .sidebar .logo h1 {
             font-family: var(--font-family-serif);
             font-size: 1.5rem;
             margin: 0;
             color: var(--background-color);
         }

        .sidebar nav {
            flex-grow: 1; /* Pushes logout to bottom */
        }
        .sidebar nav ul {
            list-style: none;
        }

        .sidebar nav ul li a,
        .sidebar nav ul li button { /* Treat buttons like links */
            display: block;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            transition: background-color 0.3s ease, padding-left 0.3s ease;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            font-size: 1rem;
            cursor: pointer;
        }
         /* Style for icons (if added later) */
         .sidebar nav ul li a i,
         .sidebar nav ul li button i {
             margin-right: 10px;
             width: 1.2em; /* Align text */
             text-align: center;
         }

        .sidebar nav ul li a:hover,
        .sidebar nav ul li button:hover,
        .sidebar nav ul li a.active {
            background-color: rgba(255, 255, 255, 0.1);
            padding-left: 25px; /* Indent on hover/active */
        }

        .sidebar .logout-section {
            margin-top: auto; /* Pushes to bottom */
            padding: 0 20px 20px 20px; /* Padding for logout button */
        }
        .sidebar .logout-btn {
            background-color: var(--danger-color);
            border-radius: var(--border-radius);
            text-align: center;
            width: 100%; /* Make button full width */
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }
         .sidebar .logout-btn:hover {
             background-color: #c82333; /* Darker red */
             padding-left: 15px; /* Reset indent */
         }
         #username-display {
             font-size: 0.8em;
             opacity: 0.8;
             display: block; /* Ensure it takes space */
             margin-top: 2px;
         }

        main.content {
            flex-grow: 1;
            padding: 25px;
            overflow-y: auto; /* Allow content scrolling */
            background-color: var(--background-color);
        }

        .page {
            display: none; /* Hide pages by default */
            animation: fadeIn 0.5s ease-in-out;
        }

        .page.active {
            display: block; /* Show active page */
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2.page-title {
            font-family: var(--font-family-serif);
            color: var(--primary-color);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 10px;
            font-size: 1.8rem;
        }

        /* --- Card Styles --- */
        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 20px;
            padding: 20px;
            transition: box-shadow 0.3s ease;
        }
        .card:hover {
             box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
            flex-wrap: wrap; /* Allow wrapping */
            gap: 10px; /* Space between items if wrapped */
        }

        .card-header h3 {
            margin: 0;
            font-size: 1.2rem; /* Slightly larger */
            color: var(--primary-color);
            font-weight: bold;
            flex-grow: 1; /* Allow title to take space */
        }

        .status-badge {
            padding: 5px 12px; /* Slightly more padding */
            border-radius: 15px; /* Pill shape */
            font-size: 0.8rem;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            white-space: nowrap;
            line-height: 1.5; /* Better vertical alignment */
        }

        .status-Scheduled { background-color: var(--scheduled-color); }
        .status-Arrived { background-color: var(--arrived-color); color: #333; } /* Darker text on yellow */
        .status-Unloading { background-color: var(--unloading-color); }
        .status-Delayed { background-color: var(--delayed-color); }
        .status-Completed { background-color: var(--completed-color); }
        .status-Cancelled { background-color: var(--cancelled-color); }

        .card-content p, .card-content div {
            margin-bottom: 10px; /* More spacing */
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .card-content strong {
            color: var(--secondary-color);
            margin-right: 8px; /* Slightly more space */
             display: inline-block;
             min-width: 110px; /* Align labels */
             font-weight: 600;
        }
         .collapsible-details {
             margin-top: 15px;
             padding-top: 10px;
             border-top: 1px dashed var(--border-color);
             font-size: 0.9rem;
             background-color: #fdfcf9; /* Very light bg for details */
             padding: 10px;
             border-radius: var(--border-radius);
         }
          .collapsible-details p { margin-bottom: 6px; }
          .collapsible-details strong { min-width: 100px;}


        .card-actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
            text-align: right;
        }
         .card-actions .btn {
             margin-left: 10px; /* Spacing between action buttons */
         }

        /* --- Form Styles --- */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Responsive grid */
            gap: 20px 25px; /* Row and column gap */
        }

        .form-group {
            margin-bottom: 15px; /* Default spacing */
            display: flex;
            flex-direction: column; /* Stack label and input */
        }

        .form-group label {
            margin-bottom: 6px;
            font-weight: bold;
            color: var(--secondary-color);
             font-size: 0.9rem;
        }
        .form-group label .required-star { color: var(--danger-color); }


        .form-group input[type="text"],
        .form-group input[type="datetime-local"],
        .form-group input[type="date"],
        .form-group input[type="number"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 0.95rem;
             background-color: #fff;
             transition: border-color 0.2s ease;
        }
         .form-group input:focus,
         .form-group select:focus,
         .form-group textarea:focus {
             border-color: var(--primary-color);
             outline: none;
             box-shadow: 0 0 0 2px rgba(74, 109, 124, 0.2);
         }

        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }

         .form-group input[type="checkbox"] {
             width: auto; /* Checkboxes don't need full width */
             margin-right: 8px; /* Space between checkbox and label text */
             vertical-align: middle;
         }
         .form-group label.checkbox-label { /* Style label next to checkbox */
             display: inline-block;
             margin-bottom: 0;
             font-weight: normal;
             color: var(--text-color);
             vertical-align: middle;
         }
         /* Wrap checkbox and its label together */
         .checkbox-group {
            display: flex;
            align-items: center;
         }

        /* --- Buttons --- */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500; /* Slightly bolder */
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.1s ease;
             margin-right: 10px; /* Spacing between buttons */
             margin-bottom: 5px; /* Spacing if they wrap */
             vertical-align: middle; /* Align with text/inputs */
        }
        .btn:last-child {
            margin-right: 0;
        }
         .btn:active {
             transform: translateY(1px); /* Subtle press effect */
         }

        .btn-primary { background-color: var(--primary-color); color: white; }
        .btn-primary:hover { background-color: #3a5764; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }

        .btn-secondary { background-color: var(--secondary-color); color: white; }
         .btn-secondary:hover { background-color: #7a6e65; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }

        .btn-success { background-color: var(--success-color); color: white; }
        .btn-success:hover { background-color: #218838; }

        .btn-warning { background-color: var(--warning-color); color: #333; }
        .btn-warning:hover { background-color: #e0a800; }

        .btn-danger { background-color: var(--danger-color); color: white; }
        .btn-danger:hover { background-color: #c82333; }

        .btn-info { background-color: var(--info-color); color: white; }
        .btn-info:hover { background-color: #138496; }

        .btn-sm {
             padding: 6px 12px;
             font-size: 0.85rem;
        }
         /* Button group for actions */
         .action-buttons {
            white-space: nowrap; /* Prevent wrapping */
         }
          .action-buttons .btn { margin-right: 5px; }
          .action-buttons .btn:last-child { margin-right: 0; }


        /* --- Modals --- */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.5); /* Black w/ opacity */
            animation: fadeInModal 0.3s ease-in-out;
            padding: 20px; /* Add padding for smaller screens */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; /* Adjusted margin for better centering */
            padding: 30px;
            border: 1px solid #888;
            width: 80%;
            max-width: 700px; /* Default max-width */
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
             position: relative;
        }
         /* Wider modal for manage data */
         #manage-data-modal .modal-content {
             max-width: 850px;
         }

        .close-btn {
            color: #aaa;
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1; /* Ensure consistent positioning */
        }

        .close-btn:hover,
        .close-btn:focus {
            color: black;
            text-decoration: none;
        }

         .modal h3 {
             margin-top: 0;
             margin-bottom: 25px; /* More space */
             color: var(--primary-color);
             font-family: var(--font-family-serif);
             font-size: 1.5rem;
             border-bottom: 1px solid var(--border-color);
             padding-bottom: 10px;
         }

         @keyframes fadeInModal {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
         }

        /* --- Tabs --- */
        .tab-controls {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            flex-wrap: wrap; /* Allow tabs to wrap */
        }

        .tab-controls button {
            padding: 10px 20px;
            cursor: pointer;
            border: none;
            background-color: transparent;
            border-bottom: 3px solid transparent;
            margin-bottom: -1px; /* Overlap border */
            transition: border-color 0.3s ease, color 0.3s ease;
            font-size: 1rem;
            color: var(--secondary-color);
             margin-right: 5px; /* Space between tabs */
             white-space: nowrap;
        }
        .tab-controls button:last-child { margin-right: 0; }

        .tab-controls button.active {
            border-bottom-color: var(--primary-color);
            font-weight: bold;
            color: var(--primary-color);
        }
         .tab-controls button:hover {
             color: var(--primary-color);
         }

        /* --- Reports Section --- */
        #report-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #fff; /* Use card bg */
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
             align-items: flex-end; /* Align items bottom */
        }
        #report-filters .form-group {
            margin-bottom: 0; /* Remove default bottom margin */
            flex: 1 1 180px; /* Adjust flex basis */
        }
         #report-filters .form-group label {
             font-size: 0.85rem;
             margin-bottom: 4px;
         }
         #report-filters .form-group input,
         #report-filters .form-group select {
             padding: 8px;
             font-size: 0.9rem;
         }
         #report-filters .filter-actions {
             display: flex;
             gap: 10px; /* Space between buttons */
             flex-basis: auto; /* Let buttons size naturally */
             margin-left: auto; /* Push buttons to the right if space allows */
             padding-bottom: 0; /* Align with bottom of inputs */
         }

        #report-results {
             margin-top: 20px;
         }
        .table-responsive {
            overflow-x: auto; /* Enable horizontal scroll on tables */
            width: 100%;
        }
         .report-table {
             width: 100%;
             border-collapse: collapse;
             margin-top: 15px;
             background-color: #fff;
             box-shadow: var(--box-shadow);
             border-radius: var(--border-radius);
             overflow: hidden; /* Clip shadow */
         }
         .report-table th, .report-table td {
             border: 1px solid var(--border-color);
             padding: 10px 12px; /* More padding */
             text-align: left;
              font-size: 0.9rem;
             vertical-align: middle;
             border-bottom-width: 1px;
             border-top: none;
             border-left: none;
              border-right: none;
         }
          .report-table td { border-color: #eee; } /* Lighter internal borders */
         .report-table th {
             background-color: var(--accent-color);
             color: var(--text-color);
             font-weight: 600; /* Bolder headers */
             white-space: nowrap; /* Prevent header wrapping */
             border-bottom-width: 2px;
             border-color: var(--border-color);
         }
         .report-table tbody tr:nth-child(even) {
             background-color: #f9f9f9; /* Subtle striping */
         }
          .report-table tbody tr:hover {
             background-color: #f1f1f1; /* Hover effect */
         }

        /* --- Manage Data Page Styles --- */
        .manage-data-tabs button {
            font-size: 0.95rem;
        }

        .manage-data-content {
            display: none; /* Hide content sections by default */
            margin-top: 20px;
            animation: fadeIn 0.5s ease-in-out;
        }

        .manage-data-content.active {
            display: block; /* Show active content */
        }

        /* Use report-table styles for manage data tables */
        .manage-data-content .report-table td,
        .manage-data-content .report-table th {
            padding: 8px 10px;
            vertical-align: middle;
        }
         /* Adjust action button width if needed */
         .manage-data-content .report-table th:last-child,
         .manage-data-content .report-table td:last-child {
             text-align: right;
             width: 150px; /* Ensure enough space for buttons */
         }


        /* --- Utility Classes --- */
        .hidden { display: none !important; }
        .message { padding: 8px; border-radius: var(--border-radius); font-size: 0.9rem; }
        .error-message { color: var(--danger-color); background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .success-message { color: var(--success-color); background-color: #d4edda; border: 1px solid #c3e6cb;}
        .text-center { text-align: center; }
        .mt-1 { margin-top: 0.5rem; }
        .mt-2 { margin-top: 1rem; }
        .mb-1 { margin-bottom: 0.5rem; }
        .mb-2 { margin-bottom: 1rem; }
        .currency::before { content: '?'; margin-right: 2px; } /* Indian Rupee Symbol */
        .loading-placeholder td {
            color: #aaa;
            font-style: italic;
            text-align: center !important;
        }

        /* --- Responsive Design --- */
        @media (max-width: 992px) {
             .form-grid {
                 grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); /* Adjust min size */
             }
             #report-filters {
                 flex-direction: column;
                 align-items: stretch;
             }
              #report-filters .filter-actions {
                 margin-left: 0; /* Align left on smaller screens */
                 justify-content: flex-start;
                 margin-top: 10px;
             }
             .modal-content {
                 width: 90%;
             }
             h2.page-title { font-size: 1.6rem; }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 65px; /* Slightly wider collapsed */
                overflow: hidden;
                position: absolute; /* Allow content to flow underneath when closed */
                height: 100%;
                z-index: 10; /* Keep above content */
            }
            .sidebar:hover {
                width: 240px;
                position: relative; /* Restore normal flow on hover */
            }
            main.content {
                padding-left: 80px; /* Space for collapsed sidebar */
                transition: padding-left 0.3s ease;
            }
             .sidebar:hover ~ main.content {
                  padding-left: 25px; /* Normal padding when expanded */
             }

            .sidebar .logo h1 {
                font-size: 1rem;
                 opacity: 0;
                 transition: opacity 0.2s ease;
            }
             .sidebar:hover .logo h1 { opacity: 1; }

            .sidebar nav ul li a span,
            .sidebar .logout-btn span#username-display {
                 display: none;
                 opacity: 0;
                  transition: opacity 0.2s 0.1s ease; /* Delay text fade in */
            }
             .sidebar:hover nav ul li a span,
             .sidebar:hover .logout-btn span#username-display {
                 display: inline; /* Or block for username */
                 opacity: 1;
             }
             /* Add icons here eventually using <i> tags */
            .sidebar nav ul li a, .sidebar .logout-btn {
                 text-align: center;
                 padding-left: 0;
                 padding-right: 0;
            }
             .sidebar:hover nav ul li a {
                 text-align: left;
                 padding-left: 20px;
                 padding-right: 20px;
            }
            .sidebar:hover .logout-btn {
                text-align: center; /* Keep logout centered? Or left? */
                padding-left: 15px;
                padding-right: 15px;
            }
             .sidebar:hover .logout-section {
                 padding: 0 20px 20px 20px;
             }

            main.content { padding: 15px; }
            .card-header { flex-direction: column; align-items: flex-start; }
            .status-badge { margin-top: 5px; }
            .modal-content { width: 95%; margin: 10% auto; padding: 20px; }
            .tab-controls button { padding: 8px 10px; font-size: 0.9rem; }
        }
         @media (max-width: 480px) {
              body { font-size: 14px; }
              .auth-container { margin: 20px auto; padding: 20px; }
              .btn { padding: 8px 15px; font-size: 0.9rem; }
              .card-content strong { min-width: 90px; }
              .page-title { font-size: 1.4rem; }
               main.content { padding-left: 15px; } /* Reset padding when sidebar fully collapsed */
               .sidebar { width: 0; overflow: hidden; position: absolute; } /* Fully hide */
               /* Add a hamburger menu toggle button later */
         }

    </style>
</head>
<body>

    <div id="auth-section" class="<?php echo $is_logged_in ? 'hidden' : ''; ?>">
        <!-- Login Form -->
        <div class="auth-container" id="login-container">
            <h2>Login</h2>
            <form id="login-form" novalidate>
                <div class="form-group">
                    <label for="login-username">Username</label>
                    <input type="text" id="login-username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" required>
                </div>
                <button type="submit" class="btn">Login</button>
                <p id="login-error" class="message error-message hidden"></p>
                <p class="toggle-link">Don't have an account? <a href="#" id="show-register">Register here</a></p>
            </form>
        </div>

        <!-- Registration Form -->
        <div class="auth-container hidden" id="register-container">
            <h2>Register</h2>
            <form id="register-form" novalidate>
                <div class="form-group">
                    <label for="register-username">Username</label>
                    <input type="text" id="register-username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="register-password">Password (min 6 chars)</label>
                    <input type="password" id="register-password" name="password" required minlength="6">
                </div>
                 <div class="form-group">
                    <label for="register-confirm-password">Confirm Password</label>
                    <input type="password" id="register-confirm-password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn">Register</button>
                <p id="register-error" class="message error-message hidden"></p>
                <p id="register-success" class="message success-message hidden"></p>
                <p class="toggle-link">Already have an account? <a href="#" id="show-login">Login here</a></p>
            </form>
        </div>
    </div>

    <div id="main-app" class="app-container <?php echo !$is_logged_in ? 'hidden' : ''; ?>">
        <!-- Sidebar -->
        <aside class="sidebar">
             <div class="logo">
                 <h1>VAMS</h1> <!-- Vehicle Appointment Mgt System -->
             </div>
            <nav>
                <ul>
                    <!-- Add icons later e.g., <i class="fas fa-tachometer-alt"></i> -->
                    <li><a href="#dashboard" class="nav-link active" data-page="dashboard"><span>Dashboard</span></a></li>
                    <li><a href="#create-appointment" class="nav-link" data-page="create-appointment"><span>New Appointment</span></a></li>
                    <li><a href="#manage-data" class="nav-link" data-page="manage-data"><span>Manage Data</span></a></li>
                    <li><a href="#reports" class="nav-link" data-page="reports"><span>Reports</span></a></li>
                    <li><a href="#import" class="nav-link" data-page="import"><span>Import Data</span></a></li>
                </ul>
            </nav>
            <div class="logout-section">
                <button id="logout-btn" class="logout-btn">Logout <span id="username-display"></span></button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="content">
            <!-- Page: Dashboard -->
            <div id="dashboard" class="page active">
                <h2 class="page-title">Dashboard - Live Status</h2>
                 <div class="card">
                     <div class="card-header">
                         <h3>Filters</h3>
                         <div> <!-- Wrapper for buttons -->
                             <button id="apply-dash-filters" class="btn btn-primary btn-sm">Apply Filters</button>
                             <button id="reset-dash-filters" class="btn btn-secondary btn-sm">Reset</button>
                         </div>
                     </div>
                     <div id="dashboard-filters" class="form-grid">
                         <div class="form-group">
                             <label for="dash-filter-status">Status</label>
                             <select id="dash-filter-status">
                                <option value="">All Statuses</option>
                                <option value="Scheduled">Scheduled</option>
                                <option value="Arrived">Arrived</option>
                                <option value="Unloading">Unloading</option>
                                <option value="Delayed">Delayed</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                         </div>
                         <div class="form-group">
                            <label for="dash-filter-warehouse">Warehouse</label>
                            <select id="dash-filter-warehouse" class="warehouse-dropdown">
                                <option value="">All Warehouses</option>
                                <!-- Options loaded by JS -->
                            </select>
                        </div>
                         <div class="form-group">
                            <label for="dash-filter-date">Date</label>
                            <input type="date" id="dash-filter-date">
                        </div>
                     </div>
                 </div>
                <div id="appointment-list" class="mt-2">
                    <!-- Appointments loaded here by JS -->
                    <p>Loading appointments...</p>
                </div>
            </div>

            <!-- Page: Create Appointment -->
            <div id="create-appointment" class="page">
                <h2 class="page-title">Schedule New Appointment</h2>
                <div class="card">
                     <form id="appointment-form" novalidate>
                         <div class="form-grid">
                             <div class="form-group">
                                 <label for="app-warehouse">Warehouse <span class="required-star">*</span></label>
                                 <select id="app-warehouse" name="warehouse_id" class="warehouse-dropdown" required>
                                     <option value="">Select Warehouse</option>
                                 </select>
                             </div>
                             <div class="form-group">
                                 <label for="app-datetime">Appointment Date & Time <span class="required-star">*</span></label>
                                 <input type="datetime-local" id="app-datetime" name="appointment_datetime" required>
                             </div>
                            <div class="form-group">
                                 <label for="app-transporter">Transporter <span class="required-star">*</span></label>
                                 <select id="app-transporter" name="transporter_id" class="transporter-dropdown" required>
                                     <option value="">Select Transporter</option>
                                 </select>
                             </div>
                              <div class="form-group">
                                 <label for="app-vehicle">Vehicle <span class="required-star">*</span></label>
                                 <select id="app-vehicle" name="vehicle_id" class="vehicle-dropdown" required>
                                     <option value="">Select Vehicle</option>
                                     <!-- Filter by transporter later -->
                                 </select>
                             </div>
                             <div class="form-group">
                                 <label for="app-driver">Driver <span class="required-star">*</span></label>
                                 <select id="app-driver" name="driver_id" class="driver-dropdown" required>
                                     <option value="">Select Driver</option>
                                      <!-- Filter by transporter later -->
                                 </select>
                             </div>
                            <div class="form-group">
                                <label for="app-cargo-type">Cargo Type <span class="required-star">*</span></label>
                                <select id="app-cargo-type" name="cargo_type_id" class="cargo_type-dropdown" required>
                                    <option value="">Select Cargo Type</option>
                                </select>
                            </div>
                             <div class="form-group">
                                 <label for="app-po">Purchase Order (Optional)</label>
                                 <select id="app-po" name="po_id" class="purchase_order-dropdown">
                                     <option value="">Select PO</option>
                                 </select>
                             </div>
                              <div class="form-group">
                                 <label for="app-duration">Est. Duration (mins)</label>
                                 <input type="number" id="app-duration" name="estimated_duration_mins" value="60" min="15" step="15">
                             </div>
                         </div>
                         <div class="form-group mt-2">
                             <label for="app-cargo-details">Cargo Details <span class="required-star">*</span> (Items, Quantity, Weight etc.)</label>
                             <textarea id="app-cargo-details" name="cargo_details" rows="3" required></textarea>
                         </div>
                          <div class="form-group">
                             <label for="app-special-instructions">Special Instructions (Optional)</label>
                             <textarea id="app-special-instructions" name="special_instructions" rows="2"></textarea>
                         </div>
                         <div class="text-center mt-2">
                             <button type="submit" class="btn btn-primary">Schedule Appointment</button>
                             <p id="appointment-form-message" class="message mt-1 hidden"></p>
                         </div>
                     </form>
                </div>
            </div>

            <!-- Page: Manage Data -->
            <div id="manage-data" class="page">
                <h2 class="page-title">Manage Core Data</h2>

                <!-- Tabs to switch between data types -->
                <div class="tab-controls manage-data-tabs">
                    <button class="tab-link active" data-target="manage-warehouses">Warehouses</button>
                    <button class="tab-link" data-target="manage-transporters">Transporters</button>
                    <button class="tab-link" data-target="manage-vehicles">Vehicles</button>
                    <button class="tab-link" data-target="manage-drivers">Drivers</button>
                    <button class="tab-link" data-target="manage-cargo_types">Cargo Types</button>
                    <button class="tab-link" data-target="manage-purchase_orders">Purchase Orders</button>
                </div>

                <!-- Container for each data type's content -->
                <div class="manage-data-content-container">

                    <!-- Warehouses Section -->
                    <div id="manage-warehouses" class="manage-data-content active">
                        <div class="card">
                             <div class="card-header">
                                <h3>Warehouses</h3>
                                <button class="btn btn-sm btn-primary add-new-btn" data-type="warehouse">Add New Warehouse</button>
                             </div>
                             <div class="table-responsive mt-2">
                                 <table class="report-table" id="warehouse-table">
                                     <thead>
                                         <tr>
                                             <th>ID</th>
                                             <th>Name</th>
                                             <th>Location Code</th>
                                             <th>Address</th>
                                             <th>Contact</th>
                                             <th>Email</th>
                                             <th>Phone</th>
                                             <th>Actions</th>
                                         </tr>
                                     </thead>
                                     <tbody><tr class="loading-placeholder"><td colspan="8">Loading...</td></tr></tbody>
                                 </table>
                             </div>
                        </div>
                    </div>

                    <!-- Transporters Section -->
                    <div id="manage-transporters" class="manage-data-content">
                         <div class="card">
                             <div class="card-header">
                                <h3>Transporters</h3>
                                <button class="btn btn-sm btn-primary add-new-btn" data-type="transporter">Add New Transporter</button>
                             </div>
                             <div class="table-responsive mt-2">
                                 <table class="report-table" id="transporter-table">
                                     <thead>
                                         <tr>
                                             <th>ID</th>
                                             <th>Name</th>
                                             <th>Contact Person</th>
                                             <th>Email</th>
                                             <th>Phone</th>
                                             <th>Actions</th>
                                         </tr>
                                     </thead>
                                      <tbody><tr class="loading-placeholder"><td colspan="6">Loading...</td></tr></tbody>
                                 </table>
                             </div>
                        </div>
                    </div>

                    <!-- Vehicles Section -->
                    <div id="manage-vehicles" class="manage-data-content">
                        <div class="card">
                             <div class="card-header">
                                <h3>Vehicles</h3>
                                <button class="btn btn-sm btn-primary add-new-btn" data-type="vehicle">Add New Vehicle</button>
                             </div>
                             <div class="table-responsive mt-2">
                                 <table class="report-table" id="vehicle-table">
                                     <thead>
                                         <tr>
                                             <th>ID</th>
                                             <th>Vehicle Number</th>
                                             <th>Type</th>
                                             <th>Transporter</th>
                                             <th>Capacity (Tons)</th>
                                             <th>Insurance Exp.</th>
                                             <th>Active</th>
                                             <th>Actions</th>
                                         </tr>
                                     </thead>
                                      <tbody><tr class="loading-placeholder"><td colspan="8">Loading...</td></tr></tbody>
                                 </table>
                             </div>
                        </div>
                    </div>

                    <!-- Drivers Section -->
                    <div id="manage-drivers" class="manage-data-content">
                         <div class="card">
                             <div class="card-header">
                                <h3>Drivers</h3>
                                <button class="btn btn-sm btn-primary add-new-btn" data-type="driver">Add New Driver</button>
                             </div>
                             <div class="table-responsive mt-2">
                                 <table class="report-table" id="driver-table">
                                     <thead>
                                         <tr>
                                             <th>ID</th>
                                             <th>Name</th>
                                             <th>Contact Number</th>
                                             <th>License Number</th>
                                             <th>License Exp.</th>
                                             <th>Transporter</th>
                                             <th>Active</th>
                                             <th>Actions</th>
                                         </tr>
                                     </thead>
                                      <tbody><tr class="loading-placeholder"><td colspan="8">Loading...</td></tr></tbody>
                                 </table>
                             </div>
                        </div>
                    </div>

                     <!-- Cargo Types Section -->
                    <div id="manage-cargo_types" class="manage-data-content">
                        <div class="card">
                             <div class="card-header">
                                <h3>Cargo Types</h3>
                                <button class="btn btn-sm btn-primary add-new-btn" data-type="cargo_type">Add New Cargo Type</button>
                             </div>
                             <div class="table-responsive mt-2">
                                 <table class="report-table" id="cargo_type-table">
                                     <thead>
                                         <tr>
                                             <th>ID</th>
                                             <th>Type Name</th>
                                             <th>Description</th>
                                             <th>Special Handling</th>
                                             <th>Actions</th>
                                         </tr>
                                     </thead>
                                      <tbody><tr class="loading-placeholder"><td colspan="5">Loading...</td></tr></tbody>
                                 </table>
                             </div>
                        </div>
                    </div>

                    <!-- Purchase Orders Section -->
                    <div id="manage-purchase_orders" class="manage-data-content">
                        <div class="card">
                             <div class="card-header">
                                <h3>Purchase Orders</h3>
                                <button class="btn btn-sm btn-primary add-new-btn" data-type="purchase_order">Add New PO</button>
                             </div>
                             <div class="table-responsive mt-2">
                                 <table class="report-table" id="purchase_order-table">
                                     <thead>
                                         <tr>
                                             <th>ID</th>
                                             <th>PO Number</th>
                                             <th>Vendor</th>
                                             <th>Order Date</th>
                                             <th>Expected Delivery</th>
                                             <th>Status</th>
                                             <th>Actions</th>
                                         </tr>
                                     </thead>
                                      <tbody><tr class="loading-placeholder"><td colspan="7">Loading...</td></tr></tbody>
                                 </table>
                             </div>
                        </div>
                    </div>

                </div> <!-- /manage-data-content-container -->
            </div><!-- /manage-data page -->


            <!-- Page: Reports -->
            <div id="reports" class="page">
                <h2 class="page-title">Report Analysis</h2>
                 <div class="card">
                     <h3>Filter Reports</h3>
                     <form id="report-filters">
                         <div class="form-group">
                            <label for="report-date-from">From Date</label>
                            <input type="date" id="report-date-from">
                         </div>
                         <div class="form-group">
                            <label for="report-date-to">To Date</label>
                            <input type="date" id="report-date-to">
                         </div>
                        <div class="form-group">
                            <label for="report-warehouse">Warehouse</label>
                            <select id="report-warehouse" class="warehouse-dropdown">
                                <option value="">All</option>
                            </select>
                        </div>
                         <div class="form-group">
                            <label for="report-transporter">Transporter</label>
                            <select id="report-transporter" class="transporter-dropdown">
                                <option value="">All</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="report-status">Status</label>
                             <select id="report-status">
                                <option value="">All</option>
                                <option value="Scheduled">Scheduled</option>
                                <option value="Arrived">Arrived</option>
                                <option value="Unloading">Unloading</option>
                                <option value="Delayed">Delayed</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                         <div class="filter-actions"> <!-- Buttons grouped -->
                             <button type="button" id="generate-report-btn" class="btn btn-primary btn-sm">Generate Report</button>
                             <button type="button" id="export-excel-btn" class="btn btn-success btn-sm hidden">Export Excel</button>
                             <button type="button" id="export-pdf-btn" class="btn btn-danger btn-sm hidden">Export PDF</button>
                         </div>
                     </form>
                     <div id="report-results" class="mt-2">
                         <p>Apply filters and click 'Generate Report' to view results.</p>
                         <div class="table-responsive">
                             <table class="report-table hidden">
                                 <thead>
                                     <tr>
                                         <th>UID</th>
                                         <th>Date/Time</th>
                                         <th>Warehouse</th>
                                         <th>Vehicle</th>
                                         <th>Driver</th>
                                         <th>Transporter</th>
                                         <th>Status</th>
                                         <th>PO#</th>
                                         <th>Cargo</th>
                                         <th>GatePass</th>
                                         <th>Bay</th>
                                     </tr>
                                 </thead>
                                 <tbody id="report-table-body">
                                     <!-- Data populated by JS -->
                                 </tbody>
                             </table>
                         </div>
                     </div>
                 </div>
                 <!-- Chart Placeholder -->
                 <!-- <div id="report-chart-container" class="card mt-2 hidden"> ... </div> -->
            </div>

             <!-- Page: Import Data -->
            <div id="import" class="page">
                <h2 class="page-title">Import Data</h2>
                <div class="card">
                    <h3>Import Appointments via CSV</h3>
                    <p>Upload a CSV file with appointment data. Ensure columns match required format (e.g., vehicle_number, driver_contact_number, warehouse_name, appointment_datetime YYYY-MM-DD HH:MM:SS, cargo_details).</p>
                    <form id="import-csv-form" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="csv-file">Select CSV File</label>
                            <input type="file" id="csv-file" name="csvFile" accept=".csv" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload and Import</button>
                        <p id="import-message" class="message mt-1 hidden"></p>
                        <div id="import-errors" class="mt-1 hidden" style="max-height: 200px; overflow-y: auto;"></div>
                    </form>
                     <div class="mt-2">
                         <p><strong>Note:</strong> Backend processing maps CSV columns to database fields. Lookups are performed for vehicle, driver, warehouse etc. based on unique identifiers in the CSV.</p>
                     </div>
                </div>
                 <!-- Public Data Placeholder -->
                 <!-- <div class="card mt-2"> ... </div> -->
            </div>

        </main> <!-- End Main Content -->
    </div> <!-- End Main App -->

    <!-- Modal for Appointment Details / Status Update -->
    <div id="appointment-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="appointment-modal-close">&times;</span>
            <h3 id="modal-title">Appointment Details</h3>
            <div id="modal-body">
                <!-- Details loaded by JS -->
            </div>
             <div id="modal-actions" class="mt-2">
                 <h4>Update Status</h4>
                  <input type="hidden" id="modal-appointment-id">
                 <div class="form-grid">
                     <div class="form-group">
                         <label for="modal-status">New Status</label>
                         <select id="modal-status">
                            <option value="Scheduled">Scheduled</option>
                            <option value="Arrived">Arrived</option>
                            <option value="Unloading">Unloading</option>
                            <option value="Delayed">Delayed</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                     </div>
                     <!-- Conditional fields based on status -->
                      <div class="form-group modal-status-field status-Arrived status-Unloading status-Completed hidden">
                         <label for="modal-gatepass">Gate Pass #</label>
                         <input type="text" id="modal-gatepass" name="gate_pass_number">
                     </div>
                      <div class="form-group modal-status-field status-Arrived status-Unloading hidden">
                         <label for="modal-bay">Unloading Bay</label>
                         <input type="text" id="modal-bay" name="unloading_bay_no">
                     </div>
                     <div class="form-group modal-status-field status-Arrived hidden">
                         <label for="modal-arrival-time">Arrival Time</label>
                         <input type="datetime-local" id="modal-arrival-time" name="arrival_datetime">
                     </div>
                     <div class="form-group modal-status-field status-Unloading hidden">
                         <label for="modal-unloading-start-time">Unloading Start Time</label>
                         <input type="datetime-local" id="modal-unloading-start-time" name="unloading_start_datetime">
                     </div>
                     <div class="form-group modal-status-field status-Completed hidden">
                         <label for="modal-unloading-end-time">Unloading End Time</label>
                         <input type="datetime-local" id="modal-unloading-end-time" name="unloading_end_datetime">
                     </div>
                     <div class="form-group modal-status-field status-Completed hidden">
                         <label for="modal-departure-time">Departure Time</label>
                         <input type="datetime-local" id="modal-departure-time" name="departure_datetime">
                     </div>
                      <div class="form-group modal-status-field status-Delayed status-Cancelled hidden">
                         <label for="modal-reason">Reason / Notes</label>
                         <textarea id="modal-reason" name="special_instructions" rows="2"></textarea>
                     </div>
                 </div>
                 <div class="text-center">
                    <button id="update-status-btn" class="btn btn-primary mt-1">Update Status</button>
                    <p id="modal-update-message" class="message mt-1 hidden"></p>
                 </div>
            </div>
        </div>
    </div>

     <!-- Modal for Managing Data (Add/Edit) -->
    <div id="manage-data-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="manage-data-modal-close">&times;</span>
            <h3 id="manage-data-modal-title">Manage Item</h3>
            <form id="manage-data-form" novalidate>
                <input type="hidden" id="manage-data-type" name="entityType"> <!-- To know what we are editing -->
                <input type="hidden" id="manage-data-id" name="id"> <!-- ID for updates -->

                <div id="manage-data-form-fields" class="form-grid">
                    <!-- Form fields will be dynamically generated here by JS -->
                </div>

                <div class="text-center mt-2">
                    <button type="submit" id="manage-data-save-btn" class="btn btn-primary">Save Changes</button>
                    <p id="manage-data-form-message" class="message mt-1 hidden"></p>
                </div>
            </form>
        </div>
    </div>


    <!-- Loading Indicator -->
    <div id="loading-indicator" class="hidden" style="position: fixed; top: 10px; right: 10px; background: rgba(255, 193, 7, 0.8); padding: 5px 15px; border-radius: 5px; z-index: 1001; box-shadow: 0 2px 5px rgba(0,0,0,0.2); color: #333; font-weight: bold;">Loading...</div>


    <script>
        // --- JavaScript ---
        document.addEventListener('DOMContentLoaded', () => {
            const API_URL = 'api.php'; // API endpoint

            // --- Global State ---
            let currentLoggedInUser = null;
            let manageDataCache = {}; // Simple cache for loaded manage data

            // --- DOM Elements ---
            const authSection = document.getElementById('auth-section');
            const mainApp = document.getElementById('main-app');
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            const loginContainer = document.getElementById('login-container');
            const registerContainer = document.getElementById('register-container');
            const showRegisterLink = document.getElementById('show-register');
            const showLoginLink = document.getElementById('show-login');
            const logoutBtn = document.getElementById('logout-btn');
            const usernameDisplay = document.getElementById('username-display');
            const pages = document.querySelectorAll('.page');
            const navLinks = document.querySelectorAll('.nav-link');
            const appointmentList = document.getElementById('appointment-list');
            const appointmentForm = document.getElementById('appointment-form');
            const appointmentFormMessage = document.getElementById('appointment-form-message');
            const loadingIndicator = document.getElementById('loading-indicator');

            // Dropdown selectors (cached)
            const dropdownSelectors = {
                warehouse: '.warehouse-dropdown',
                transporter: '.transporter-dropdown',
                vehicle: '.vehicle-dropdown',
                driver: '.driver-dropdown',
                cargo_type: '.cargo_type-dropdown',
                purchase_order: '.purchase_order-dropdown'
            };

            // Appointment Modal elements
            const appModal = document.getElementById('appointment-modal');
            const appModalCloseBtn = document.getElementById('appointment-modal-close');
            const appModalBody = document.getElementById('modal-body');
            const appModalTitle = document.getElementById('modal-title');
            const appModalAppointmentIdInput = document.getElementById('modal-appointment-id');
            const appModalStatusSelect = document.getElementById('modal-status');
            const appUpdateStatusBtn = document.getElementById('update-status-btn');
            const appModalUpdateMessage = document.getElementById('modal-update-message');
            const appModalStatusFields = appModal.querySelectorAll('.modal-status-field'); // Cache fields within modal

             // Dashboard Filter elements
            const dashFilterStatus = document.getElementById('dash-filter-status');
            const dashFilterWarehouse = document.getElementById('dash-filter-warehouse');
            const dashFilterDate = document.getElementById('dash-filter-date');
            const applyDashFiltersBtn = document.getElementById('apply-dash-filters');
            const resetDashFiltersBtn = document.getElementById('reset-dash-filters');

            // Report Filter elements
            const generateReportBtn = document.getElementById('generate-report-btn');
            const reportFiltersForm = document.getElementById('report-filters'); // The form itself
            const reportResultsContainer = document.getElementById('report-results');
            const reportTable = reportResultsContainer.querySelector('.report-table');
            const reportTableBody = document.getElementById('report-table-body');
            const exportExcelBtn = document.getElementById('export-excel-btn');
            const exportPdfBtn = document.getElementById('export-pdf-btn');

            // Import elements
            const importCsvForm = document.getElementById('import-csv-form');
            const importMessage = document.getElementById('import-message');
            const importErrorsDiv = document.getElementById('import-errors');
            // const fetchPublicDataBtn = document.getElementById('fetch-public-data-btn');
            // const publicDataResults = document.getElementById('public-data-results');

            // Manage Data Elements
            const manageDataTabsContainer = document.querySelector('.manage-data-tabs');
            const manageDataContentSections = document.querySelectorAll('.manage-data-content');
            const manageDataModal = document.getElementById('manage-data-modal');
            const manageDataModalCloseBtn = document.getElementById('manage-data-modal-close');
            const manageDataModalTitle = document.getElementById('manage-data-modal-title');
            const manageDataForm = document.getElementById('manage-data-form');
            const manageDataFormFields = document.getElementById('manage-data-form-fields');
            const manageDataFormMessage = document.getElementById('manage-data-form-message');
            const manageDataTypeInput = document.getElementById('manage-data-type');
            const manageDataIdInput = document.getElementById('manage-data-id');
            const manageDataTableBodies = { // Map type to table body
                warehouse: document.getElementById('warehouse-table')?.querySelector('tbody'),
                transporter: document.getElementById('transporter-table')?.querySelector('tbody'),
                vehicle: document.getElementById('vehicle-table')?.querySelector('tbody'),
                driver: document.getElementById('driver-table')?.querySelector('tbody'),
                cargo_type: document.getElementById('cargo_type-table')?.querySelector('tbody'),
                purchase_order: document.getElementById('purchase_order-table')?.querySelector('tbody'),
            };

            // --- Helper Functions ---
            const showLoading = () => loadingIndicator.classList.remove('hidden');
            const hideLoading = () => loadingIndicator.classList.add('hidden');

            const displayMessage = (element, message, isError = false, details = null) => {
                if (!element) {
                    console.warn("Attempted to display message on a null element for message:", message);
                    return;
                }
                element.innerHTML = message; // Use innerHTML to allow basic formatting if needed
                element.className = `message mt-1 ${isError ? 'error-message' : 'success-message'}`;
                element.classList.remove('hidden');

                 // Display details/errors if provided (e.g., from CSV import)
                const detailsContainer = element.nextElementSibling; // Assumes details div is sibling
                 if (details && detailsContainer && detailsContainer.id.includes('-errors')) {
                     if(Array.isArray(details)) {
                         detailsContainer.innerHTML = '<strong>Details:</strong><ul>' + details.map(e => `<li>${e}</li>`).join('') + '</ul>';
                     } else if (typeof details === 'string'){
                          detailsContainer.innerHTML = `<strong>Details:</strong><p>${details}</p>`;
                     }
                     detailsContainer.classList.remove('hidden');
                 } else if (detailsContainer && detailsContainer.id.includes('-errors')) {
                     detailsContainer.classList.add('hidden'); // Hide details if none provided
                 }

                // Auto-hide non-error messages after a few seconds
                if (!isError) {
                    setTimeout(() => {
                        element.classList.add('hidden');
                         if (detailsContainer && detailsContainer.id.includes('-errors')) {
                             detailsContainer.classList.add('hidden');
                         }
                    }, 5000);
                }
            };

            // --- API Call Function ---
            const apiRequest = async (action, method = 'GET', body = null, isFormData = false) => {
                showLoading();
                const options = {
                    method: method,
                    headers: {},
                    //credentials: 'include' // Send cookies with requests
                };
                // For GET, append params to URL. For POST/PUT, use body.
                let url = `${API_URL}?action=${action}`;

                if (method !== 'GET' && body) {
                    if (isFormData) {
                        // Browser sets Content-Type automatically for FormData
                        options.body = body;
                    } else {
                        options.headers['Content-Type'] = 'application/json';
                        options.body = JSON.stringify(body);
                    }
                } else if (method === 'GET' && body && Object.keys(body).length > 0) {
                    url += '&' + new URLSearchParams(body).toString();
                }

                try {
                    const response = await fetch(url, options);
                    const contentType = response.headers.get("content-type");

                    if (!response.ok) {
                        let errorData = { success: false, message: `HTTP error! Status: ${response.status}` };
                        if (contentType && contentType.includes("application/json")) {
                            // Try to parse JSON error response from API
                            const apiError = await response.json();
                             errorData.message = apiError.message || errorData.message;
                             errorData.auth_required = apiError.auth_required || false;
                        }
                        // Handle specific errors
                        if (response.status === 401 || errorData.auth_required) {
                            console.warn('API request unauthorized. Forcing logout.');
                             handleLogout(); // Force logout / show login
                             // Return a distinct error object for auth failure
                              return { success: false, message: 'Authentication required. Please login again.', auth_required: true };
                        }
                        throw new Error(errorData.message); // Throw generic error message
                    }

                    // Handle successful responses
                     if (contentType && contentType.includes("application/json")) {
                          hideLoading();
                          return await response.json();
                     } else {
                          // Handle non-JSON success responses (e.g., file downloads, plain text)
                          hideLoading();
                          console.log("Received non-JSON response:", await response.text());
                           return { success: true, message: 'Received non-JSON response.' }; // Indicate success but maybe no data
                     }

                } catch (error) {
                    hideLoading();
                    console.error('API Request Error:', error);
                    // Display error to user in a general area?
                    // For now, just return error object
                    return { success: false, message: error.message || 'Network or server error occurred.' };
                }
            };

             // --- Authentication ---
            const showLoginView = () => {
                authSection.classList.remove('hidden');
                mainApp.classList.add('hidden');
                loginContainer.classList.remove('hidden');
                registerContainer.classList.add('hidden');
                currentLoggedInUser = null;
                usernameDisplay.textContent = '';
                sessionStorage.removeItem('userData'); // Clear session storage too
                 // Clear any potentially sensitive cached data on logout
                 manageDataCache = {};
                 clearAllTables(); // Clear table content
            };

            const showRegisterView = () => {
                authSection.classList.remove('hidden');
                mainApp.classList.add('hidden');
                loginContainer.classList.add('hidden');
                registerContainer.classList.remove('hidden');
            };

            const showMainApp = (user) => {
                authSection.classList.add('hidden');
                mainApp.classList.remove('hidden');
                currentLoggedInUser = user;
                 if (user && user.username) {
                    usernameDisplay.textContent = `(${user.username})`;
                    sessionStorage.setItem('userData', JSON.stringify(user));
                }
                // Load initial necessary data
                loadAllDropdowns(); // Load all dropdowns needed across the app
                setActivePage('dashboard'); // Default to dashboard
            };

            const handleLogout = async () => {
                await apiRequest('logout', 'POST'); // Send logout request
                showLoginView();
            };

            loginForm?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(loginForm);
                const data = Object.fromEntries(formData.entries());
                const result = await apiRequest('login', 'POST', data);
                const errorElement = document.getElementById('login-error');

                if (result.success) {
                    showMainApp(result.user);
                    errorElement.classList.add('hidden');
                } else {
                    displayMessage(errorElement, result.message || 'Login failed.', true);
                }
            });

            registerForm?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const password = document.getElementById('register-password').value;
                const confirmPassword = document.getElementById('register-confirm-password').value;
                const errorElement = document.getElementById('register-error');
                const successElement = document.getElementById('register-success');

                if (password !== confirmPassword) {
                    displayMessage(errorElement, 'Passwords do not match.', true);
                    successElement.classList.add('hidden'); // Hide success msg
                    return;
                }
                 if (password.length < 6) {
                     displayMessage(errorElement, 'Password must be at least 6 characters.', true);
                     successElement.classList.add('hidden');
                     return;
                 }
                errorElement.classList.add('hidden'); // Clear previous error

                const formData = new FormData(registerForm);
                const data = Object.fromEntries(formData.entries());
                delete data.confirm_password;

                const result = await apiRequest('register', 'POST', data);

                if (result.success) {
                    displayMessage(successElement, result.message || 'Registration successful!', false);
                    registerForm.reset();
                    errorElement.classList.add('hidden');
                    setTimeout(showLoginView, 2000);
                } else {
                    displayMessage(errorElement, result.message || 'Registration failed.', true);
                    successElement.classList.add('hidden');
                }
            });

            showRegisterLink?.addEventListener('click', (e) => { e.preventDefault(); showRegisterView(); });
            showLoginLink?.addEventListener('click', (e) => { e.preventDefault(); showLoginView(); });
            logoutBtn?.addEventListener('click', handleLogout);

             // Check session on load
            const checkUserSession = async () => {
                 // Check sessionStorage first for quick check
                 const storedUser = sessionStorage.getItem('userData');
                 if (storedUser) {
                     try {
                         currentLoggedInUser = JSON.parse(storedUser);
                         // Verify with backend to ensure session is still valid
                         const result = await apiRequest('check_session', 'GET');
                         if (result.loggedIn && result.user.id === currentLoggedInUser.id) {
                             showMainApp(currentLoggedInUser);
                             return; // Session valid
                         }
                     } catch (e) { console.error("Error parsing stored user data"); }
                 }
                 // If sessionStorage fails or backend check fails, show login
                 showLoginView();
             };


            // --- Navigation ---
             const setActivePage = (pageId) => {
                 pages.forEach(page => page.classList.remove('active'));
                 navLinks.forEach(link => link.classList.remove('active'));

                 const activePage = document.getElementById(pageId);
                 const activeLink = document.querySelector(`.nav-link[data-page="${pageId}"]`);

                 if (activePage) {
                     activePage.classList.add('active');
                     // Trigger data loading if necessary for the activated page
                     loadDataForPage(pageId);
                 }
                 if (activeLink) activeLink.classList.add('active');

                 // Update URL hash (optional, for basic history/bookmarking)
                 // window.location.hash = pageId;
             };

             // Function to load data specific to a page when it becomes active
             const loadDataForPage = (pageId) => {
                  switch(pageId) {
                      case 'dashboard':
                          loadAppointments(); // Load appointments for dashboard
                          loadAllDropdowns(); // Refresh dashboard filters
                          break;
                      case 'create-appointment':
                           loadAllDropdowns(); // Ensure dropdowns are fresh
                          break;
                      case 'manage-data':
                          // Find the currently active *tab* within manage-data and load its data
                          const activeManageTab = manageDataTabsContainer?.querySelector('.tab-link.active');
                          const defaultTabType = 'warehouse'; // Default if none active yet
                          const targetType = activeManageTab ? activeManageTab.dataset.target.replace('manage-', '') : defaultTabType;
                          // Ensure plural 's' is added back if needed for API call consistency
                          const apiEntityType = targetType.endsWith('s') ? targetType : `${targetType}s`;
                          if(targetType === 'cargo_type') apiEntityType = 'cargo_types'; // Handle exceptions
                          if(targetType === 'purchase_order') apiEntityType = 'purchase_orders';

                          if (loadDataFunctions[targetType]) {
                              loadDataFunctions[targetType](apiEntityType); // Pass correct API type
                          }
                          loadAllDropdowns(); // Ensure dropdowns for manage forms are loaded
                          break;
                       case 'reports':
                           loadAllDropdowns(); // For report filters
                          break;
                       case 'import':
                           // Any specific loading needed for import page?
                           break;
                  }
             };

             navLinks.forEach(link => {
                 link.addEventListener('click', (e) => {
                     e.preventDefault();
                     const pageId = link.getAttribute('data-page');
                     setActivePage(pageId);
                 });
             });

            // --- Dropdown Loading ---
            const populateDropdown = (elements, data, valueField = 'id', textField = 'name', prompt = 'Select...') => {
                 if (!elements || elements.length === 0) return;

                 const selectedValues = new Map();
                 elements.forEach(dropdown => {
                      if(dropdown.dataset.keepSelection && dropdown.selectedIndex > 0) {
                         selectedValues.set(dropdown.id || dropdown.name, dropdown.value);
                      }
                     // Clear existing options except the placeholder (if it should be kept)
                     // Keep first option if it has value=""
                     const firstOption = dropdown.options[0];
                     dropdown.innerHTML = ''; // Clear all
                     if(firstOption && firstOption.value === "") {
                         dropdown.appendChild(firstOption); // Add placeholder back
                          firstOption.textContent = prompt; // Update prompt text
                     } else {
                          dropdown.innerHTML = `<option value="">${prompt}</option>`; // Add default placeholder
                     }

                 });


                 if (data && data.length > 0) {
                     data.forEach(item => {
                         const option = document.createElement('option');
                         option.value = item[valueField];
                         // Handle cases where textField might not exist directly (e.g., PO number vs name)
                         option.textContent = item[textField] !== undefined ? item[textField] : item[valueField];
                         elements.forEach(dropdown => {
                              // Avoid adding duplicates if multiple runs happen quickly
                              if (!dropdown.querySelector(`option[value="${option.value}"]`)) {
                                dropdown.appendChild(option.cloneNode(true));
                              }
                         });
                     });
                 }

                  // Restore selection if needed
                  elements.forEach(dropdown => {
                      const savedValue = selectedValues.get(dropdown.id || dropdown.name);
                      if (savedValue && dropdown.querySelector(`option[value="${savedValue}"]`)) {
                          dropdown.value = savedValue;
                      } else {
                          // If previous selection invalid, maybe reset to placeholder?
                          // dropdown.value = "";
                      }
                      // Remove 'loading...' state if present in placeholder
                      if(dropdown.options[0] && dropdown.options[0].value === "" && dropdown.options[0].textContent === "Loading...") {
                          dropdown.options[0].textContent = prompt;
                      }
                 });
             };

            // Load specific dropdown type across the entire document
            const loadDropdownData = async (type, valueField = 'id', textField = 'name', prompt = 'Select...') => {
                 const selector = dropdownSelectors[type.replace(/_/g, '-')]; // Get CSS selector
                 if (!selector) return;
                 const dropdownElements = document.querySelectorAll(selector);
                 if (dropdownElements.length === 0) return;

                 // Optional: Check cache or timestamp before fetching? For now, always fetch.
                 const apiEntityType = type.includes('_') ? type : `${type}s`; // Adjust for API naming (e.g., cargo_type -> cargo_types)

                 const result = await apiRequest('get_dropdown', 'GET', { type: apiEntityType });
                 if (result.success) {
                     populateDropdown(dropdownElements, result.data, valueField, textField, prompt);
                 } else {
                     console.error(`Failed to load ${type} data:`, result.message);
                      // Set placeholder to error state?
                      dropdownElements.forEach(el => {
                         if(el.options[0] && el.options[0].value === "") el.options[0].textContent = `Error loading ${type}`;
                      });
                 }
             };

             const loadAllDropdowns = () => {
                 loadDropdownData('warehouse', 'id', 'name', 'Select Warehouse...');
                 loadDropdownData('transporter', 'id', 'name', 'Select Transporter...');
                 loadDropdownData('vehicle', 'id', 'vehicle_number', 'Select Vehicle...');
                 loadDropdownData('driver', 'id', 'name', 'Select Driver...');
                 loadDropdownData('cargo_type', 'id', 'type_name', 'Select Cargo Type...');
                 loadDropdownData('purchase_order', 'id', 'po_number', 'Select PO...');
             };


             // --- Appointment Management ---
             const formatDateTime = (dateTimeString) => {
                 if (!dateTimeString) return 'N/A';
                 try {
                     const date = new Date(dateTimeString);
                     // Handle potential invalid date object
                     if (isNaN(date.getTime())) return 'Invalid Date';
                     return date.toLocaleString('en-IN', { // Indian English locale
                         day: '2-digit', month: 'short', year: 'numeric',
                         hour: 'numeric', minute: '2-digit', hour12: true
                        }).replace(',', ''); // Remove comma after year often added by toLocaleString
                 } catch (e) {
                     console.error("Error formatting date:", dateTimeString, e);
                     return dateTimeString; // Return original if parsing fails
                 }
             };
             // Formats Date object or string to 'YYYY-MM-DDTHH:mm' for datetime-local input
             const formatDateForInput = (dateTimeString) => {
                 if (!dateTimeString) return '';
                 try {
                     const date = new Date(dateTimeString);
                     if (isNaN(date.getTime())) return '';
                     // Adjust for local timezone offset BEFORE converting to ISO string
                     const tzoffset = date.getTimezoneOffset() * 60000; // offset in milliseconds
                     const localISOTime = (new Date(date.getTime() - tzoffset)).toISOString().slice(0, 16);
                     return localISOTime;
                 } catch (e) {
                      console.error("Error formatting date for input:", dateTimeString, e);
                     return '';
                 }
             };

            const renderAppointmentCard = (app) => {
                const card = document.createElement('div');
                card.className = 'card appointment-card';
                card.dataset.appointmentId = app.id;

                // Truncate long details for display
                const shortCargo = (app.cargo_details || '').length > 50 ? (app.cargo_details.substring(0, 50) + '...') : app.cargo_details;

                card.innerHTML = `
                    <div class="card-header">
                        <h3>Apt: ${app.appointment_uid || app.id} (${app.vehicle_number || 'N/A'})</h3>
                        <span class="status-badge status-${app.status}">${app.status}</span>
                    </div>
                    <div class="card-content">
                        <p><strong>Warehouse:</strong> ${app.warehouse_name || 'N/A'}</p>
                        <p><strong>Date & Time:</strong> ${formatDateTime(app.appointment_datetime)}</p>
                        <p><strong>Transporter:</strong> ${app.transporter_name || 'N/A'}</p>
                        <p><strong>Driver:</strong> ${app.driver_name || 'N/A'} (${app.driver_contact || 'N/A'})</p>
                        <p><strong>Cargo Type:</strong> ${app.cargo_type_name || 'N/A'}</p>
                        <p><strong>PO Number:</strong> ${app.po_number || 'N/A'}</p>
                        <p><strong>Gate Pass:</strong> ${app.gate_pass_number || 'N/A'}</p>
                         <p><strong>Bay:</strong> ${app.unloading_bay_no || 'N/A'}</p>
                         <p><strong>Cargo:</strong> ${shortCargo || 'N/A'}</p>
                         <!-- Collapsible Details -->
                         <div class="collapsible-details hidden">
                            <p><strong>Full Cargo Details:</strong> ${app.cargo_details || 'N/A'}</p>
                            <p><strong>Est. Duration:</strong> ${app.estimated_duration_mins || 'N/A'} mins</p>
                            <p><strong>Instructions:</strong> ${app.special_instructions || 'N/A'}</p>
                            <hr style="border:0; border-top: 1px dashed #eee; margin: 5px 0;">
                            <p><strong>Arrival:</strong> ${formatDateTime(app.arrival_datetime)}</p>
                            <p><strong>Unload Start:</strong> ${formatDateTime(app.unloading_start_datetime)}</p>
                            <p><strong>Unload End:</strong> ${formatDateTime(app.unloading_end_datetime)}</p>
                            <p><strong>Departure:</strong> ${formatDateTime(app.departure_datetime)}</p>
                            <hr style="border:0; border-top: 1px dashed #eee; margin: 5px 0;">
                            <p><strong>Created:</strong> ${formatDateTime(app.created_at)} (User: ${app.created_by || 'N/A'})</p>
                             <p><strong>Last Updated:</strong> ${formatDateTime(app.last_updated_at)}</p>
                         </div>
                    </div>
                    <div class="card-actions">
                         <button class="btn btn-sm btn-secondary view-details-btn">Details</button>
                         <button class="btn btn-sm btn-warning update-status-modal-btn">Update Status</button>
                         <!-- <button class="btn btn-sm btn-danger cancel-apt-btn">Cancel</button> -->
                    </div>
                `;

                 // Add event listeners using delegation later or directly here
                 card.querySelector('.view-details-btn').addEventListener('click', (e) => {
                     const details = e.target.closest('.card').querySelector('.collapsible-details');
                     details.classList.toggle('hidden');
                     e.target.textContent = details.classList.contains('hidden') ? 'Details' : 'Hide Details';
                 });

                 card.querySelector('.update-status-modal-btn').addEventListener('click', () => {
                     openAppointmentModal(app); // Pass the full appointment object
                 });

                return card;
            };

             const loadAppointments = async (filters = {}) => {
                 if (!appointmentList) return;
                 appointmentList.innerHTML = '<p>Loading appointments...</p>';
                 const result = await apiRequest('get_appointments', 'GET', filters);

                 appointmentList.innerHTML = ''; // Clear loading message
                 if (result.success && result.data?.length > 0) {
                     result.data.forEach(app => {
                         const card = renderAppointmentCard(app);
                         appointmentList.appendChild(card);
                     });
                 } else if (result.success) {
                     appointmentList.innerHTML = '<p>No appointments found matching the criteria.</p>';
                 } else {
                     appointmentList.innerHTML = `<p class="message error-message">Error loading appointments: ${result.message}</p>`;
                 }
             };

            appointmentForm?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(appointmentForm);
                const data = Object.fromEntries(formData.entries());

                // Convert datetime-local to required backend format 'YYYY-MM-DD HH:MM:SS'
                if(data.appointment_datetime) {
                     try {
                         // Create Date object directly from datetime-local value
                         const localDate = new Date(data.appointment_datetime);
                         if (isNaN(localDate.getTime())) throw new Error("Invalid Date Input");
                         // Format to ISO string, remove T, Z, and milliseconds
                         data.appointment_datetime = localDate.toISOString().slice(0, 19).replace('T', ' ');
                     } catch (err) {
                          displayMessage(appointmentFormMessage, 'Invalid appointment date/time format selected.', true);
                          console.error("Date parsing error:", err);
                          return;
                     }
                } else {
                     displayMessage(appointmentFormMessage, 'Appointment date/time is required.', true);
                     return;
                }


                const result = await apiRequest('add_appointment', 'POST', data);

                if (result.success) {
                    displayMessage(appointmentFormMessage, result.message || 'Appointment scheduled!', false);
                    appointmentForm.reset();
                    // Switch to dashboard and refresh
                     setActivePage('dashboard');
                     // loadAppointments(); // setActivePage triggers loadDataForPage which calls this
                } else {
                    displayMessage(appointmentFormMessage, result.message || 'Failed to schedule appointment.', true);
                }
            });

            // --- Appointment Modal Logic ---
             const openAppointmentModal = (appointmentData) => {
                 appModalAppointmentIdInput.value = appointmentData.id;
                 appModalTitle.textContent = `Update Apt #: ${appointmentData.appointment_uid || appointmentData.id}`;

                 // Populate basic details
                 appModalBody.innerHTML = `
                     <p><strong>Vehicle:</strong> ${appointmentData.vehicle_number || 'N/A'}</p>
                     <p><strong>Driver:</strong> ${appointmentData.driver_name || 'N/A'}</p>
                     <p><strong>Warehouse:</strong> ${appointmentData.warehouse_name || 'N/A'}</p>
                     <p><strong>Current Status:</strong> <span class="status-badge status-${appointmentData.status}">${appointmentData.status}</span></p>
                 `;

                 // Set current status in dropdown & populate fields
                 appModalStatusSelect.value = appointmentData.status;
                 appModal.querySelector('#modal-gatepass').value = appointmentData.gate_pass_number || '';
                 appModal.querySelector('#modal-bay').value = appointmentData.unloading_bay_no || '';
                 appModal.querySelector('#modal-arrival-time').value = formatDateForInput(appointmentData.arrival_datetime);
                 appModal.querySelector('#modal-unloading-start-time').value = formatDateForInput(appointmentData.unloading_start_datetime);
                 appModal.querySelector('#modal-unloading-end-time').value = formatDateForInput(appointmentData.unloading_end_datetime);
                 appModal.querySelector('#modal-departure-time').value = formatDateForInput(appointmentData.departure_datetime);
                 // Populate reason/notes field (used for Delayed/Cancelled)
                 appModal.querySelector('#modal-reason').value = (appointmentData.status === 'Delayed' || appointmentData.status === 'Cancelled') ? (appointmentData.special_instructions || '') : '';


                 toggleAppModalStatusFields(); // Show/hide fields based on current status
                 appModalUpdateMessage.classList.add('hidden');
                 appModal.style.display = 'block';
             };

             const closeAppModal = () => {
                 appModal.style.display = 'none';
             };

             appModalCloseBtn?.addEventListener('click', closeAppModal);

              // Show/hide relevant fields in modal based on selected status
             const toggleAppModalStatusFields = () => {
                 const selectedStatus = appModalStatusSelect.value;
                 appModalStatusFields.forEach(field => {
                     let show = false;
                     field.classList.forEach(cls => {
                         if (cls.startsWith('status-') && cls === `status-${selectedStatus}`) {
                             show = true;
                         }
                     });
                     // Keep Gate Pass visible for Arrived, Unloading, Completed
                     if (field.contains(appModal.querySelector('#modal-gatepass')) && ['Arrived', 'Unloading', 'Completed'].includes(selectedStatus)) {
                         show = true;
                     }
                     // Keep Bay visible for Arrived, Unloading
                     if (field.contains(appModal.querySelector('#modal-bay')) && ['Arrived', 'Unloading'].includes(selectedStatus)) {
                         show = true;
                     }

                     field.classList.toggle('hidden', !show);
                 });
             };

             appModalStatusSelect?.addEventListener('change', toggleAppModalStatusFields);

            appUpdateStatusBtn?.addEventListener('click', async () => {
                const appointmentId = appModalAppointmentIdInput.value;
                const newStatus = appModalStatusSelect.value;
                if (!appointmentId || !newStatus) {
                    displayMessage(appModalUpdateMessage, 'Missing appointment ID or status.', true);
                    return;
                }

                // Collect data from visible relevant fields
                const details = {};
                 appModalStatusFields.forEach(field => {
                     if (!field.classList.contains('hidden')) {
                         const input = field.querySelector('input, select, textarea');
                         if (input && input.name && input.value !== '') {
                             if(input.type === 'datetime-local') {
                                 // Convert back to backend format
                                  try {
                                     const localDate = new Date(input.value);
                                     if (!isNaN(localDate.getTime())) {
                                          details[input.name] = localDate.toISOString().slice(0, 19).replace('T', ' ');
                                     }
                                  } catch(e){ console.error("Error formatting date for update:", input.name, input.value); }
                             } else {
                                 details[input.name] = input.value;
                             }
                         }
                     }
                 });

                const body = {
                    appointment_id: parseInt(appointmentId),
                    status: newStatus,
                    details: details
                };

                const result = await apiRequest('update_appointment_status', 'POST', body);

                if (result.success) {
                    displayMessage(appModalUpdateMessage, result.message || 'Status updated!', false);
                    loadAppointments(); // Refresh the list on the dashboard
                    setTimeout(closeAppModal, 1500);
                } else {
                    displayMessage(appModalUpdateMessage, result.message || 'Failed to update status.', true);
                }
            });

             // --- Dashboard Filtering ---
             const loadDashboardData = () => {
                 const filters = {};
                 if (dashFilterStatus.value) filters.status = dashFilterStatus.value;
                 if (dashFilterWarehouse.value) filters.warehouse_id = dashFilterWarehouse.value;
                 if (dashFilterDate.value) {
                     // Filter for the entire selected day
                     filters.date_from = dashFilterDate.value;
                      filters.date_to = dashFilterDate.value;
                 }
                 loadAppointments(filters);
             };

             applyDashFiltersBtn?.addEventListener('click', loadDashboardData);
             resetDashFiltersBtn?.addEventListener('click', () => {
                 // Reset selects and inputs within the filter area
                 dashFilterStatus.value = "";
                 dashFilterWarehouse.value = "";
                 dashFilterDate.value = "";
                 loadDashboardData(); // Reload with default filters
             });

             // --- Reporting ---
             generateReportBtn?.addEventListener('click', async () => {
                 const filters = {};
                 const dateFrom = reportFiltersForm.querySelector('#report-date-from').value;
                 const dateTo = reportFiltersForm.querySelector('#report-date-to').value;
                 const warehouseId = reportFiltersForm.querySelector('#report-warehouse').value;
                 const transporterId = reportFiltersForm.querySelector('#report-transporter').value;
                 const status = reportFiltersForm.querySelector('#report-status').value;

                 if (dateFrom) filters.date_from = dateFrom;
                 if (dateTo) filters.date_to = dateTo;
                 if (warehouseId) filters.warehouse_id = warehouseId;
                 if (transporterId) filters.transporter_id = transporterId; // Ensure backend handles this filter
                 if (status) filters.status = status;

                 const result = await apiRequest('get_appointments', 'GET', filters);

                 reportTableBody.innerHTML = ''; // Clear previous results
                 reportTable.classList.add('hidden');
                 exportExcelBtn.classList.add('hidden');
                 exportPdfBtn.classList.add('hidden');
                 reportResultsContainer.querySelector('p').classList.remove('error-message'); // Clear error style

                 if (result.success && result.data?.length > 0) {
                     result.data.forEach(app => {
                         const row = reportTableBody.insertRow();
                         row.innerHTML = `
                             <td>${app.appointment_uid || app.id}</td>
                             <td>${formatDateTime(app.appointment_datetime)}</td>
                             <td>${app.warehouse_name || 'N/A'}</td>
                             <td>${app.vehicle_number || 'N/A'}</td>
                             <td>${app.driver_name || 'N/A'}</td>
                             <td>${app.transporter_name || 'N/A'}</td>
                             <td><span class="status-badge status-${app.status}">${app.status}</span></td>
                             <td>${app.po_number || 'N/A'}</td>
                             <td title="${app.cargo_details || ''}">${(app.cargo_details || '').substring(0, 30)}${(app.cargo_details || '').length > 30 ? '...' : ''}</td>
                             <td>${app.gate_pass_number || 'N/A'}</td>
                             <td>${app.unloading_bay_no || 'N/A'}</td>
                         `;
                     });
                     reportTable.classList.remove('hidden');
                     reportResultsContainer.querySelector('p').classList.add('hidden');
                    // Show export buttons (implement export logic separately)
                    // exportExcelBtn.classList.remove('hidden');
                    // exportPdfBtn.classList.remove('hidden');
                 } else if (result.success) {
                     reportResultsContainer.querySelector('p').textContent = 'No appointments found matching the criteria.';
                      reportResultsContainer.querySelector('p').classList.remove('hidden');
                 } else {
                      reportResultsContainer.querySelector('p').textContent = `Error generating report: ${result.message}`;
                      reportResultsContainer.querySelector('p').classList.remove('hidden').add('error-message');
                 }
             });
             // Add event listeners for Export buttons later


             // --- Manage Data Page Logic ---

             // Function map for loading data and specifying row renderers
             const loadDataFunctions = {
                'warehouse': (apiEntityType) => loadManageData(apiEntityType || 'warehouses', manageDataTableBodies.warehouse, renderWarehouseRow),
                'transporter': (apiEntityType) => loadManageData(apiEntityType || 'transporters', manageDataTableBodies.transporter, renderTransporterRow),
                'vehicle': (apiEntityType) => loadManageData(apiEntityType || 'vehicles', manageDataTableBodies.vehicle, renderVehicleRow),
                'driver': (apiEntityType) => loadManageData(apiEntityType || 'drivers', manageDataTableBodies.driver, renderDriverRow),
                'cargo_type': (apiEntityType) => loadManageData(apiEntityType || 'cargo_types', manageDataTableBodies.cargo_type, renderCargoTypeRow),
                'purchase_order': (apiEntityType) => loadManageData(apiEntityType || 'purchase_orders', manageDataTableBodies.purchase_order, renderPurchaseOrderRow),
             };

             // Clear all manage data tables
             const clearAllTables = () => {
                 Object.values(manageDataTableBodies).forEach(tbody => {
                     if(tbody) tbody.innerHTML = '';
                 });
             };

            // Generic function to load data into a table
            const loadManageData = async (apiEntityType, tableBody, rowRenderer) => {
                if (!tableBody) { console.error("Table body not found for", apiEntityType); return; }
                tableBody.innerHTML = '<tr class="loading-placeholder"><td colspan="100%">Loading...</td></tr>'; // Use colspan large enough
                const result = await apiRequest(`get_${apiEntityType}`, 'GET');

                tableBody.innerHTML = ''; // Clear loading/previous data
                if (result.success && result.data?.length > 0) {
                    manageDataCache[apiEntityType] = result.data; // Cache the loaded data
                    result.data.forEach(item => {
                        tableBody.appendChild(rowRenderer(item));
                    });
                } else if (result.success) {
                     manageDataCache[apiEntityType] = []; // Cache empty result
                    tableBody.innerHTML = `<tr><td colspan="100%">No ${apiEntityType.replace(/_/g, ' ')} found.</td></tr>`;
                } else {
                     manageDataCache[apiEntityType] = undefined; // Clear cache on error
                    tableBody.innerHTML = `<tr><td colspan="100%" class="error-message">Error loading ${apiEntityType.replace(/_/g, ' ')}: ${result.message}</td></tr>`;
                }
            };

            // --- Row Rendering Functions ---
            const createActionButtons = (entityType, id) => {
                // Use the singular form for data-type consistency
                const singularType = entityType.replace(/s$/, '').replace(/ie$/, 'y'); // basic singularization
                return `
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-warning edit-btn" data-type="${singularType}" data-id="${id}">Edit</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-type="${singularType}" data-id="${id}">Delete</button>
                    </div>
                `;
            };
             const escapeHtml = (unsafe) => { // Basic HTML escaping
                 if (unsafe === null || unsafe === undefined) return '';
                 return String(unsafe)
                      .replace(/&/g, "&amp;")
                      .replace(/</g, "&lt;")
                      .replace(/>/g, "&gt;")
                      .replace(/"/g, "&quot;")
                      .replace(/'/g, "&#039;");
             };

            const renderWarehouseRow = (item) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.id}</td>
                    <td>${escapeHtml(item.name)}</td>
                    <td>${escapeHtml(item.location_code)}</td>
                    <td>${escapeHtml(item.address)}</td>
                    <td>${escapeHtml(item.contact_person)}</td>
                    <td>${escapeHtml(item.contact_email)}</td>
                    <td>${escapeHtml(item.contact_phone)}</td>
                    <td>${createActionButtons('warehouses', item.id)}</td>
                `;
                return row;
            };
            const renderTransporterRow = (item) => {
                 const row = document.createElement('tr');
                 row.innerHTML = `
                    <td>${item.id}</td>
                    <td>${escapeHtml(item.name)}</td>
                    <td>${escapeHtml(item.contact_person)}</td>
                    <td>${escapeHtml(item.contact_email)}</td>
                    <td>${escapeHtml(item.contact_phone)}</td>
                    <td>${createActionButtons('transporters', item.id)}</td>
                `;
                return row;
            };
            const renderVehicleRow = (item) => {
                const row = document.createElement('tr');
                 // Format date for display if not null
                 const insuranceExp = item.insurance_expiry ? new Date(item.insurance_expiry).toLocaleDateString('en-IN') : '';
                row.innerHTML = `
                    <td>${item.id}</td>
                    <td>${escapeHtml(item.vehicle_number)}</td>
                    <td>${escapeHtml(item.type)}</td>
                    <td>${escapeHtml(item.transporter_name || item.transporter_id)}</td>
                    <td>${escapeHtml(item.capacity_tons)}</td>
                    <td>${insuranceExp}</td>
                    <td>${item.is_active ? 'Yes' : 'No'}</td>
                    <td>${createActionButtons('vehicles', item.id)}</td>
                `;
                return row;
            };
            const renderDriverRow = (item) => {
                 const row = document.createElement('tr');
                 const licenseExp = item.license_expiry ? new Date(item.license_expiry).toLocaleDateString('en-IN') : '';
                 row.innerHTML = `
                    <td>${item.id}</td>
                    <td>${escapeHtml(item.name)}</td>
                    <td>${escapeHtml(item.contact_number)}</td>
                    <td>${escapeHtml(item.license_number)}</td>
                    <td>${licenseExp}</td>
                     <td>${escapeHtml(item.transporter_name || item.transporter_id || 'N/A')}</td>
                    <td>${item.is_active ? 'Yes' : 'No'}</td>
                    <td>${createActionButtons('drivers', item.id)}</td>
                `;
                return row;
            };
            const renderCargoTypeRow = (item) => {
                 const row = document.createElement('tr');
                 row.innerHTML = `
                    <td>${item.id}</td>
                    <td>${escapeHtml(item.type_name)}</td>
                    <td>${escapeHtml(item.description)}</td>
                    <td>${item.requires_special_handling ? 'Yes' : 'No'}</td>
                    <td>${createActionButtons('cargo_types', item.id)}</td>
                `;
                return row;
            };
            const renderPurchaseOrderRow = (item) => {
                 const row = document.createElement('tr');
                  const orderDate = item.order_date ? new Date(item.order_date).toLocaleDateString('en-IN') : '';
                  const expectedDate = item.expected_delivery_date ? new Date(item.expected_delivery_date).toLocaleDateString('en-IN') : '';
                 row.innerHTML = `
                    <td>${item.id}</td>
                    <td>${escapeHtml(item.po_number)}</td>
                    <td>${escapeHtml(item.vendor_name)}</td>
                    <td>${orderDate}</td>
                    <td>${expectedDate}</td>
                    <td>${escapeHtml(item.status)}</td>
                    <td>${createActionButtons('purchase_orders', item.id)}</td>
                `;
                return row;
            };

             // --- Manage Data Tab Switching ---
             manageDataTabsContainer?.addEventListener('click', (e) => {
                 if (e.target.matches('.tab-link')) {
                     const targetId = e.target.dataset.target; // e.g., manage-warehouses
                     const targetType = targetId.replace('manage-', ''); // e.g., warehouses

                     // Update active tab link
                     manageDataTabsContainer.querySelectorAll('.tab-link').forEach(link => link.classList.remove('active'));
                     e.target.classList.add('active');

                     // Update active content section
                     manageDataContentSections.forEach(section => {
                         section.classList.toggle('active', section.id === targetId);
                     });
                     // Load data for the newly activated section
                      const singularType = targetType.replace(/s$/, '').replace(/ie$/, 'y');
                      if (loadDataFunctions[singularType]) {
                          loadDataFunctions[singularType](targetType); // Pass API type (plural)
                      }
                 }
             });

            // --- Manage Data Modal & Form Logic ---
             const getFormFieldsForType = (entityType) => { // entityType expected singular here
                 // Define form fields for each entity type
                 switch(entityType) {
                    case 'warehouse': return [
                        { name: 'name', label: 'Name', type: 'text', required: true },
                        { name: 'location_code', label: 'Location Code (Unique)', type: 'text' },
                        { name: 'address', label: 'Address', type: 'textarea' },
                        { name: 'contact_person', label: 'Contact Person', type: 'text' },
                        { name: 'contact_email', label: 'Contact Email', type: 'email' },
                        { name: 'contact_phone', label: 'Contact Phone', type: 'tel' },
                    ];
                    case 'transporter': return [
                         { name: 'name', label: 'Name', type: 'text', required: true },
                         { name: 'contact_person', label: 'Contact Person', type: 'text' },
                         { name: 'contact_email', label: 'Contact Email', type: 'email' },
                         { name: 'contact_phone', label: 'Contact Phone', type: 'tel' },
                    ];
                     case 'vehicle': return [
                         { name: 'vehicle_number', label: 'Vehicle Number (Unique)', type: 'text', required: true },
                         { name: 'type', label: 'Type', type: 'select', required: true, options: [
                             {value: 'Truck', text: 'Truck'}, {value: 'Mini Truck', text: 'Mini Truck'}, {value: 'Container', text: 'Container'}, {value: 'Van', text: 'Van'}, {value: 'Tanker', text: 'Tanker'}, {value: 'Other', text: 'Other'} ]},
                         { name: 'transporter_id', label: 'Transporter', type: 'select', required: true, dropdownClass: 'transporter-dropdown' }, // Use class to populate
                         { name: 'capacity_tons', label: 'Capacity (Tons)', type: 'number', step: '0.01', min: '0' },
                         { name: 'insurance_expiry', label: 'Insurance Expiry', type: 'date' },
                         { name: 'last_maintenance', label: 'Last Maintenance', type: 'date' },
                         { name: 'is_active', label: 'Is Active', type: 'checkbox', checkedValue: '1', uncheckedValue: '0'},
                     ];
                     case 'driver': return [
                         { name: 'name', label: 'Name', type: 'text', required: true },
                         { name: 'contact_number', label: 'Contact Number (Unique)', type: 'tel', required: true },
                         { name: 'license_number', label: 'License Number (Unique)', type: 'text' },
                         { name: 'license_expiry', label: 'License Expiry', type: 'date' },
                         { name: 'transporter_id', label: 'Transporter (Optional)', type: 'select', dropdownClass: 'transporter-dropdown' }, // Use class
                          { name: 'is_active', label: 'Is Active', type: 'checkbox', checkedValue: '1', uncheckedValue: '0'},
                     ];
                     case 'cargo_type': return [
                         { name: 'type_name', label: 'Type Name (Unique)', type: 'text', required: true },
                         { name: 'description', label: 'Description', type: 'textarea' },
                         { name: 'requires_special_handling', label: 'Requires Special Handling', type: 'checkbox', checkedValue: '1', uncheckedValue: '0' },
                     ];
                     case 'purchase_order': return [
                         { name: 'po_number', label: 'PO Number (Unique)', type: 'text', required: true },
                         { name: 'vendor_name', label: 'Vendor Name', type: 'text' },
                         { name: 'order_date', label: 'Order Date', type: 'date' },
                         { name: 'expected_delivery_date', label: 'Expected Delivery', type: 'date' },
                         { name: 'status', label: 'Status', type: 'select', required: true, options: [
                              {value: 'Open', text: 'Open'}, {value: 'Partial', text: 'Partial'}, {value: 'Closed', text: 'Closed'}, {value: 'Cancelled', text: 'Cancelled'} ]},
                     ];
                     default: console.error("Unknown entity type for form:", entityType); return [];
                 }
             };

             const populateManageDataForm = (entityType, data = {}) => {
                 manageDataFormFields.innerHTML = ''; // Clear previous fields
                 const fields = getFormFieldsForType(entityType); // Use singular type

                 fields.forEach(field => {
                     const formGroup = document.createElement('div');
                     formGroup.className = 'form-group';

                     const label = document.createElement('label');
                     label.htmlFor = `manage-data-${field.name}`;
                     label.innerHTML = escapeHtml(field.label) + (field.required ? ' <span class="required-star">*</span>' : ''); // Add required star

                      let input;
                      const inputId = `manage-data-${field.name}`;

                      if (field.type === 'textarea') {
                         input = document.createElement('textarea');
                         input.rows = 3;
                     } else if (field.type === 'select') {
                         input = document.createElement('select');
                         const prompt = `Select ${field.label}...`;
                         if (field.options) { // Static options
                             input.innerHTML = `<option value="">${prompt}</option>`;
                             field.options.forEach(opt => {
                                 input.innerHTML += `<option value="${escapeHtml(opt.value)}">${escapeHtml(opt.text)}</option>`;
                             });
                         } else if (field.dropdownClass) { // Dynamic options via class
                             input.className = `${field.dropdownClass}`; // Add class for population
                             input.innerHTML = `<option value="">Loading...</option>`; // Placeholder
                              // Trigger load if needed (though loadAllDropdowns should handle it)
                              // Example: if(field.dropdownClass === 'transporter-dropdown') loadDropdownData('transporter');
                         } else {
                              input.innerHTML = `<option value="">${prompt}</option>`;
                         }
                     } else if (field.type === 'checkbox') {
                         // Wrap checkbox and label for better layout
                         formGroup.classList.add('checkbox-group');
                         input = document.createElement('input');
                         input.type = 'checkbox';
                         label.classList.add('checkbox-label'); // Style label differently
                         label.htmlFor = inputId; // Associate label correctly
                         // Handle checked state based on data ('1' or true vs '0' or false/null)
                         input.checked = data[field.name] == (field.checkedValue || '1');
                     }
                     else {
                         input = document.createElement('input');
                         input.type = field.type || 'text';
                          if(field.step) input.step = field.step;
                          if(field.min !== undefined) input.min = field.min;
                     }

                     input.id = inputId;
                     input.name = field.name;
                     if (field.required) input.required = true;

                     // Set value for non-checkbox inputs
                     if (field.type !== 'checkbox') {
                         if (field.type === 'date' && data[field.name]) {
                            try {
                                // Ensure date from DB (YYYY-MM-DD) is correctly set in input type="date"
                                input.value = new Date(data[field.name]).toISOString().split('T')[0];
                            } catch(e) { console.error("Error parsing date:", field.name, data[field.name]); input.value = ''; }
                         } else {
                              input.value = data[field.name] || '';
                         }
                     }

                     // Append elements (order matters for checkbox)
                     if(field.type === 'checkbox') {
                          formGroup.appendChild(input); // Checkbox first
                          formGroup.appendChild(label); // Then label
                     } else {
                          formGroup.appendChild(label);
                          formGroup.appendChild(input);
                     }
                     manageDataFormFields.appendChild(formGroup);
                 });

                 // After adding ALL fields, re-populate dynamic dropdowns found within the modal form
                 // This ensures they get the latest data
                 setTimeout(() => { // Use setTimeout to allow DOM update
                    const transporterDropdownsInModal = manageDataFormFields.querySelectorAll('.transporter-dropdown');
                    if(transporterDropdownsInModal.length > 0) {
                        populateDropdown(transporterDropdownsInModal, manageDataCache['transporters'] || [], 'id', 'name', 'Select Transporter...');
                         // Set value after options are populated
                         if(data.transporter_id && manageDataFormFields.querySelector('#manage-data-transporter_id')) {
                            manageDataFormFields.querySelector('#manage-data-transporter_id').value = data.transporter_id;
                         }
                    }
                    // Add similar logic for other dynamic dropdowns if created
                 }, 50); // Short delay


             }; // End populateManageDataForm


             const openManageDataModal = (entityType, id = null) => { // Expects singular entityType
                 manageDataForm.reset();
                 manageDataFormMessage.classList.add('hidden');
                 manageDataTypeInput.value = entityType; // Store singular type
                 manageDataIdInput.value = id || '';

                 const entityName = entityType.replace(/_/g, ' ');
                 const apiEntityType = entityType.endsWith('s') ? entityType : `${entityType}s`; // Plural for cache/API
                  if(entityType === 'cargo_type') apiEntityType = 'cargo_types';
                  if(entityType === 'purchase_order') apiEntityType = 'purchase_orders';

                 let itemData = {};

                 if (id) { // Editing
                     manageDataModalTitle.textContent = `Edit ${entityName}`;
                     // Try to get data from cache first
                     const cachedData = manageDataCache[apiEntityType];
                     if (cachedData) {
                         itemData = cachedData.find(item => item.id == id) || {};
                     } else {
                         // If not cached, ideally fetch single item - requires API change
                         // As fallback, show message or try reloading the list?
                         console.warn(`Data for ${apiEntityType} not cached. Cannot edit ID ${id} without API call.`);
                          displayMessage(manageDataFormMessage, `Could not load data for ${entityName} ID ${id}. Please refresh the list and try again.`, true);
                          return; // Prevent opening modal without data
                     }

                 } else { // Adding New
                     manageDataModalTitle.textContent = `Add New ${entityName}`;
                 }

                 populateManageDataForm(entityType, itemData); // Populate form fields
                 manageDataModal.style.display = 'block';
             };

             const closeManageDataModal = () => {
                 manageDataModal.style.display = 'none';
             };

             manageDataModalCloseBtn?.addEventListener('click', closeManageDataModal);

             // Close modals on Escape key
             document.addEventListener('keydown', (event) => {
                if (event.key === "Escape") {
                    if (appModal.style.display === 'block') closeAppModal();
                    if (manageDataModal.style.display === 'block') closeManageDataModal();
                }
            });
             // Close modals on outside click
            window.addEventListener('click', (event) => {
                 if (event.target == appModal) closeAppModal();
                 if (event.target == manageDataModal) closeManageDataModal();
            });


            // --- Event Listeners for Add/Edit/Delete Buttons (using Event Delegation) ---
             document.getElementById('manage-data')?.addEventListener('click', (e) => {
                 const target = e.target;
                 const button = target.closest('.btn'); // Find the button itself if icon inside is clicked

                 if (button) {
                     const entityType = button.dataset.type; // Should be singular (warehouse, transporter, etc.)
                     const entityId = button.dataset.id;

                     if (button.classList.contains('add-new-btn')) {
                         e.preventDefault();
                         openManageDataModal(entityType);
                     } else if (button.classList.contains('edit-btn')) {
                         e.preventDefault();
                          if (entityType && entityId) {
                             openManageDataModal(entityType, entityId);
                          }
                     } else if (button.classList.contains('delete-btn')) {
                         e.preventDefault();
                          if (entityType && entityId) {
                             const entityName = entityType.replace(/_/g, ' ');
                             if (confirm(`Are you sure you want to delete this ${entityName} (ID: ${entityId})?\nThis cannot be undone and might fail if linked to other records.`)) {
                                 deleteManageDataItem(entityType, entityId);
                             }
                         }
                     }
                 }
             });

             // --- Form Submission Handler for Manage Data Modal ---
             manageDataForm?.addEventListener('submit', async (e) => {
                 e.preventDefault();
                 const entityType = manageDataTypeInput.value; // Singular
                 const entityId = manageDataIdInput.value;
                 const apiEntityType = entityType.endsWith('s') ? entityType : `${entityType}s`; // Plural for API
                 if(entityType === 'cargo_type') apiEntityType = 'cargo_types';
                 if(entityType === 'purchase_order') apiEntityType = 'purchase_orders';


                 const formData = new FormData(manageDataForm);
                 const data = {};

                 // Manually construct data object, handle checkboxes correctly
                 const fields = getFormFieldsForType(entityType);
                 fields.forEach(field => {
                     const input = manageDataForm.elements[field.name];
                     if (!input) return;

                     if (field.type === 'checkbox') {
                         data[field.name] = input.checked ? (field.checkedValue || '1') : (field.uncheckedValue || '0');
                     } else {
                          // Include empty values unless it's the ID field itself (which is hidden)
                         if(input.name !== 'id') {
                            data[field.name] = input.value;
                         }
                     }
                 });


                 let result;
                 if (entityId) { // Update existing
                     data.id = entityId; // Ensure ID is included for update
                     result = await apiRequest(`update_${apiEntityType}`, 'POST', data);
                 } else { // Add new
                     result = await apiRequest(`add_${apiEntityType}`, 'POST', data);
                 }

                 if (result.success) {
                     displayMessage(manageDataFormMessage, result.message || 'Operation successful!', false);
                      // Reload the corresponding table data and clear cache
                      manageDataCache[apiEntityType] = undefined; // Invalidate cache
                      if (loadDataFunctions[entityType]) {
                         loadDataFunctions[entityType](apiEntityType); // Reload list
                      }
                      loadAllDropdowns(); // Refresh dropdowns everywhere in case this entity is used in selects
                     setTimeout(closeManageDataModal, 1500);
                 } else {
                     displayMessage(manageDataFormMessage, result.message || 'Operation failed.', true);
                 }
             });

              // --- Delete Function ---
             const deleteManageDataItem = async (entityType, id) => { // Expects singular type
                 const apiEntityType = entityType.endsWith('s') ? entityType : `${entityType}s`; // Plural for API
                 if(entityType === 'cargo_type') apiEntityType = 'cargo_types';
                 if(entityType === 'purchase_order') apiEntityType = 'purchase_orders';

                 // Pass ID in the data payload for POST/DELETE request
                 const result = await apiRequest(`delete_${apiEntityType}`, 'POST', { id: id }); // Using POST for simplicity

                 if (result.success) {
                     alert(result.message || `${entityType} deleted successfully.`);
                     // Reload the corresponding table data and clear cache
                      manageDataCache[apiEntityType] = undefined; // Invalidate cache
                     if (loadDataFunctions[entityType]) {
                         loadDataFunctions[entityType](apiEntityType); // Reload list
                     }
                      loadAllDropdowns(); // Refresh dropdowns
                 } else {
                     alert(`Error deleting ${entityType}: ${result.message || 'Unknown error'}`);
                 }
             };


              // --- CSV Import ---
             importCsvForm?.addEventListener('submit', async (e) => {
                 e.preventDefault();
                 const fileInput = document.getElementById('csv-file');
                 importMessage.classList.add('hidden');
                 importErrorsDiv.classList.add('hidden');

                 if (!fileInput.files || fileInput.files.length === 0) {
                     displayMessage(importMessage, 'Please select a CSV file to upload.', true);
                     return;
                 }

                 const formData = new FormData();
                 formData.append('csvFile', fileInput.files[0]);

                 const result = await apiRequest('import_csv', 'POST', formData, true);

                 if (result.success) {
                     displayMessage(importMessage, result.message || 'CSV import completed.', false, result.errors);
                     // Optionally refresh appointments list if import was successful
                     if(result.errors === undefined || result.errors === null) loadAppointments();
                 } else {
                     displayMessage(importMessage, result.message || 'CSV import failed.', true, result.errors);
                 }
                 importCsvForm.reset(); // Clear the file input
             });


            // --- Initial Load ---
            checkUserSession(); // Check login status on page load

        }); // End DOMContentLoaded
    </script>

</body>
</html>