<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Login</title>
    <style>
        /* Modern UI Reset & Typography */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #F2E8CF; /* Vanilla Cream */
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Preserved .content class */
        .content {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(56, 102, 65, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border-top: 8px solid #386641; /* Hunter Green accent */
        }

        h2 {
            color: #386641; /* Hunter Green */
            margin-bottom: 25px;
            font-size: 24px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* Input styling using Sage Green accents */
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #F2E8CF;
            border-radius: 8px;
            outline: none;
            transition: border-color 0.3s ease;
            font-size: 16px;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #6A994E; /* Sage Green focus */
        }

        /* Submit Button using Hunter and Yellow Green */
        button[type="submit"] {
            background-color: #386641; /* Hunter Green */
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.1s ease;
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            background-color: #6A994E; /* Sage Green hover */
        }

        button[type="submit"]:active {
            transform: scale(0.98);
        }

        /* Placeholder color */
        ::placeholder {
            color: #A7C957; /* Yellow Green */
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="content">
        <h2>Librarian Login</h2>

        <form action="login_process.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>