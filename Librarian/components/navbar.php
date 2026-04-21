<style>
    /* Sidebar Container */
    .sidebar {
        width: 260px;
        height: 100vh;
        background-color: #386641; /* Hunter Green */
        padding: 30px 20px;
        display: flex;
        flex-direction: column;
        position: fixed;
        left: 0;
        top: 0;
        box-shadow: 4px 0px 10px rgba(0, 0, 0, 0.2);
    }

    /* Sidebar Title */
    .sidebar h2 {
        color: #F2E8CF; /* Vanilla Cream */
        font-size: 22px;
        margin-bottom: 40px;
        text-align: center;
        border-bottom: 2px solid #6A994E; /* Sage Green */
        padding-bottom: 15px;
        letter-spacing: 1px;
    }

    /* Navigation Links */
    .sidebar a {
        text-decoration: none;
        margin-bottom: 12px;
    }

    /* Menu Buttons - Preserving class name */
    .menu-btn {
        width: 100%;
        padding: 12px 15px;
        background-color: transparent;
        color: #F2E8CF; /* Vanilla Cream */
        border: 1px solid #6A994E; /* Sage Green */
        border-radius: 8px;
        font-size: 16px;
        font-weight: 500;
        text-align: left;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
    }

    /* Hover State */
    .menu-btn:hover {
        background-color: #A7C957; /* Yellow Green */
        color: #386641; /* Hunter Green text for contrast */
        border-color: #A7C957;
        transform: translateX(5px);
    }

    /* Logout Button Specific Style (Targeted by the link destination) */
    a[href*="logout.php"] {
        margin-top: auto; /* Pushes logout to the bottom */
    }

    a[href*="logout.php"] .menu-btn {
        border-color: #BC4749; /* Blushed Brick */
        color: #F2E8CF;
    }

    a[href*="logout.php"] .menu-btn:hover {
        background-color: #BC4749; /* Blushed Brick */
        color: white;
    }

    /* Body adjustment to prevent content overlap */
    body {
        margin-left: 260px;
        background-color: #F2E8CF; /* Vanilla Cream */
    }
</style>

<div class="sidebar">
    <h2>📚 Library</h2>

    <a href="../pages/dashboard.php"><button class="menu-btn">Dashboard</button></a>
    <a href="../pages/books.php"><button class="menu-btn">Books</button></a>
    <a href="../pages/students.php"><button class="menu-btn">Students</button></a>
    <a href="../pages/borrows.php"><button class="menu-btn">Borrowed</button></a>

    <a href="../auth/logout.php"><button class="menu-btn">Logout</button></a>
</div>