<?php include('../components/auth_check.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        /* Modern Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: #F2E8CF; /* Vanilla Cream */
            color: #386641; /* Hunter Green */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar Styling */
        nav {
            background-color: #386641 !important; /* Hunter Green */
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
        }

        /* Centering the Welcome Box */
        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            /* If sidebar is present, this ensures it centers in the remaining space */
            margin-left: auto;
            margin-right: auto; 
        }

        /* The Welcome "Box" Container */
        .welcome-card {
            background-color: #ffffff;
            padding: 60px 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(56, 102, 65, 0.1);
            border-top: 10px solid #386641; /* Hunter Green accent */
            text-align: center;
            max-width: 700px;
            width: 100%;
        }

        /* Welcome Heading */
        h2 {
            font-size: 2.5rem;
            color: #386641; /* Hunter Green */
            margin-bottom: 5px;
            justify-content: center;
        }

        /* Subtitle / System Title */
        .system-title {
            font-size: 1.2rem;
            color: #A7C957; /* Yellow Green */
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 30px;
        }

        /* Instructional text */
        p {
            font-size: 1.1rem;
            color: #6A994E; /* Sage Green */
            font-weight: 500;
            background-color: #F2E8CF; /* Vanilla Cream background for contrast */
            display: inline-block;
            padding: 12px 25px;
            border-radius: 50px;
            border: 1px dashed #6A994E;
        }

        /* Animation for a "perfect" feel */
        .welcome-card {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            h2 { font-size: 1.8rem; }
            .welcome-card { padding: 40px 20px; }
        }
    </style>
</head>
<body>
    <?php include('../components/navbar.php'); ?>
    
    <div class="content">
        <div class="welcome-card">
            <h2>Welcome, <?= $_SESSION['name'] ?> 👋</h2>
            <div class="system-title">Library Management System</div>

            <p>Select an option from the sidebar to manage the system.</p>
        </div>
    </div>
</body>
</html>