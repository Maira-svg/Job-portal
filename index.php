<?php
// ============================================
// COMPLETE JOB PORTAL - ONE FILE SOLUTION
// ============================================
// Save as: index.php in C:\wamp64\www\jobportal\
// Run: http://localhost/jobportal/
// ============================================

// Database Configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jobportal_db';

// Create connection
$conn = new mysqli($host, $user, $pass);

// Create database and tables if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

// Create tables
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    full_name VARCHAR(150),
    role ENUM('admin', 'employer') DEFAULT 'employer',
    company_name VARCHAR(200),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT,
    category_id INT,
    title VARCHAR(200),
    description TEXT,
    location VARCHAR(150),
    salary_min INT,
    salary_max INT,
    job_type VARCHAR(50),
    skills_required TEXT,
    experience_required INT DEFAULT 0,
    status ENUM('active', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT,
    applicant_name VARCHAR(150),
    applicant_email VARCHAR(100),
    applicant_phone VARCHAR(20),
    cover_letter TEXT,
    expected_salary INT,
    experience_years INT,
    status ENUM('pending', 'shortlisted', 'rejected', 'hired') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Insert admin if not exists
$check_admin = $conn->query("SELECT * FROM users WHERE email='admin@admin.com'");
if($check_admin->num_rows == 0) {
    $conn->query("INSERT INTO users (username, email, password, full_name, role) VALUES 
    ('admin', 'admin@admin.com', MD5('admin123'), 'Administrator', 'admin')");
}

// Insert sample categories if empty
$check_cat = $conn->query("SELECT * FROM categories");
if($check_cat->num_rows == 0) {
    $conn->query("INSERT INTO categories (name) VALUES 
    ('Technology'), ('AI/ML'), ('Data Science'), ('Cybersecurity'), ('Cloud Computing'), ('DevOps')");
}

session_start();

// Handle POST requests
if($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // LOGIN
    if(isset($_POST["login"])) {
        $email = $conn->real_escape_string($_POST["email"]);
        $password = md5($_POST["password"]);
        $result = $conn->query("SELECT * FROM users WHERE email='$email' AND password='$password'");
        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $_SESSION["user_id"] = $row["id"];
            $_SESSION["full_name"] = $row["full_name"];
            $_SESSION["role"] = $row["role"];
            $_SESSION["email"] = $row["email"];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    }
    
    // REGISTER
    if(isset($_POST["register"])) {
        $username = $conn->real_escape_string($_POST["username"]);
        $email = $conn->real_escape_string($_POST["email"]);
        $password = md5($_POST["password"]);
        $full_name = $conn->real_escape_string($_POST["full_name"]);
        $company_name = $conn->real_escape_string($_POST["company_name"]);
        $phone = $conn->real_escape_string($_POST["phone"]);
        
        $check = $conn->query("SELECT * FROM users WHERE email='$email' OR username='$username'");
        if($check->num_rows > 0) {
            $error = "Email or Username already exists!";
        } else {
            $conn->query("INSERT INTO users (username, email, password, full_name, role, company_name, phone) 
                         VALUES ('$username', '$email', '$password', '$full_name', 'employer', '$company_name', '$phone')");
            $success = "Registration successful! Please login.";
        }
    }
    
    // POST JOB
    if(isset($_POST["post_job"])) {
        $title = $conn->real_escape_string($_POST["title"]);
        $description = $conn->real_escape_string($_POST["description"]);
        $location = $conn->real_escape_string($_POST["location"]);
        $salary_min = intval($_POST["salary_min"]);
        $salary_max = intval($_POST["salary_max"]);
        $job_type = $conn->real_escape_string($_POST["job_type"]);
        $skills = $conn->real_escape_string($_POST["skills_required"]);
        $experience = intval($_POST["experience_required"]);
        $category_id = intval($_POST["category_id"]);
        
        $conn->query("INSERT INTO jobs (employer_id, category_id, title, description, location, salary_min, salary_max, job_type, skills_required, experience_required) 
                     VALUES ('{$_SESSION["user_id"]}', '$category_id', '$title', '$description', '$location', '$salary_min', '$salary_max', '$job_type', '$skills', '$experience')");
        header("Location: index.php");
        exit();
    }
    
    // ADD CATEGORY (ADMIN)
    if(isset($_POST["add_category"]) && $_SESSION["role"] == "admin") {
        $name = $conn->real_escape_string($_POST["name"]);
        $conn->query("INSERT INTO categories (name) VALUES ('$name')");
        header("Location: index.php");
        exit();
    }
    
    // APPLY FOR JOB
    if(isset($_POST["apply"])) {
        $job_id = intval($_POST["job_id"]);
        $name = $conn->real_escape_string($_POST["applicant_name"]);
        $email = $conn->real_escape_string($_POST["applicant_email"]);
        $phone = $conn->real_escape_string($_POST["applicant_phone"]);
        $cover = $conn->real_escape_string($_POST["cover_letter"]);
        $salary = intval($_POST["expected_salary"]);
        $exp = intval($_POST["experience_years"]);
        
        $conn->query("INSERT INTO applications (job_id, applicant_name, applicant_email, applicant_phone, cover_letter, expected_salary, experience_years) 
                     VALUES ('$job_id', '$name', '$email', '$phone', '$cover', '$salary', '$exp')");
        $apply_success = "Application submitted successfully!";
    }
}

// Handle GET requests (Delete/Update)
if(isset($_GET["action"])) {
    
    // DELETE JOB
    if($_GET["action"] == "delete_job" && isset($_GET["id"])) {
        $id = intval($_GET["id"]);
        if($_SESSION["role"] == "admin") {
            $conn->query("DELETE FROM jobs WHERE id=$id");
        } else {
            $conn->query("DELETE FROM jobs WHERE id=$id AND employer_id={$_SESSION["user_id"]}");
        }
        header("Location: index.php");
        exit();
    }
    
    // DELETE CATEGORY (ADMIN)
    if($_GET["action"] == "delete_cat" && isset($_GET["id"]) && $_SESSION["role"] == "admin") {
        $id = intval($_GET["id"]);
        $conn->query("DELETE FROM categories WHERE id=$id");
        header("Location: index.php");
        exit();
    }
    
    // TOGGLE JOB STATUS
    if($_GET["action"] == "toggle_status" && isset($_GET["id"])) {
        $id = intval($_GET["id"]);
        $job = $conn->query("SELECT status FROM jobs WHERE id=$id")->fetch_assoc();
        $new = ($job["status"] == "active") ? "closed" : "active";
        $conn->query("UPDATE jobs SET status='$new' WHERE id=$id");
        header("Location: index.php");
        exit();
    }
    
    // UPDATE APPLICATION STATUS
    if($_GET["action"] == "update_app_status" && isset($_GET["id"]) && isset($_GET["status"])) {
        $id = intval($_GET["id"]);
        $status = $conn->real_escape_string($_GET["status"]);
        $conn->query("UPDATE applications SET status='$status' WHERE id=$id");
        header("Location: index.php");
        exit();
    }
    
    // DELETE APPLICATION
    if($_GET["action"] == "delete_app" && isset($_GET["id"])) {
        $id = intval($_GET["id"]);
        $conn->query("DELETE FROM applications WHERE id=$id");
        header("Location: index.php");
        exit();
    }
    
    // DELETE EMPLOYER (ADMIN)
    if($_GET["action"] == "delete_employer" && isset($_GET["id"]) && $_SESSION["role"] == "admin") {
        $id = intval($_GET["id"]);
        $conn->query("DELETE FROM users WHERE id=$id AND role='employer'");
        header("Location: index.php");
        exit();
    }
    
    // LOGOUT
    if($_GET["action"] == "logout") {
        session_destroy();
        header("Location: index.php");
        exit();
    }
}

// Pagination variables
$page = isset($_GET["page"]) ? $_GET["page"] : "home";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobPortal - Complete Recruitment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: #1a1a2e; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .navbar-brand { font-size: 1.5rem; font-weight: bold; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); transition: all 0.3s; margin-bottom: 20px; }
        .card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.12); }
        .btn-primary { background: #4a6cf7; border: none; border-radius: 8px; padding: 10px 20px; }
        .btn-primary:hover { background: #3a5ce5; }
        .btn-success { background: #00b894; border: none; }
        .btn-danger { background: #e74c3c; border: none; }
        .btn-warning { background: #f39c12; border: none; color: white; }
        .stat-card { background: white; border-radius: 15px; padding: 20px; text-align: center; margin-bottom: 20px; }
        .stat-card i { font-size: 2rem; margin-bottom: 10px; }
        .stat-number { font-size: 2rem; font-weight: bold; }
        .sidebar { background: #1a1a2e; min-height: 100vh; position: fixed; width: 260px; }
        .sidebar-link { color: rgba(255,255,255,0.8); padding: 12px 20px; display: block; text-decoration: none; transition: all 0.3s; border-radius: 10px; margin: 5px 10px; }
        .sidebar-link:hover { background: #4a6cf7; color: white; }
        .sidebar-link.active { background: #4a6cf7; color: white; }
        .main-content { margin-left: 260px; padding: 20px; }
        .badge-pending { background: #f39c12; color: white; padding: 5px 12px; border-radius: 20px; }
        .badge-shortlisted { background: #3498db; color: white; padding: 5px 12px; border-radius: 20px; }
        .badge-hired { background: #00b894; color: white; padding: 5px 12px; border-radius: 20px; }
        .badge-rejected { background: #e74c3c; color: white; padding: 5px 12px; border-radius: 20px; }
        .badge-active { background: #00b894; color: white; padding: 5px 12px; border-radius: 20px; }
        .badge-closed { background: #95a5a6; color: white; padding: 5px 12px; border-radius: 20px; }
        footer { background: #1a1a2e; color: white; text-align: center; padding: 20px; margin-top: 50px; }
    </style>
</head>
<body>

<?php if(!isset($_SESSION["user_id"])): ?>
<!-- PUBLIC NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-briefcase me-2"></i>JobPortal</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="?page=home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="?page=jobs">Browse Jobs</a></li>
                <li class="nav-item"><a class="nav-link btn btn-primary text-white ms-2 px-4" href="?page=login">Login</a></li>
                <li class="nav-item"><a class="nav-link btn btn-success text-white ms-2 px-4" href="?page=register">Register</a></li>
            </ul>
        </div>
    </div>
</nav>
<div style="margin-top: 70px;"></div>

<?php else: ?>
<!-- LOGGED IN NAVBAR -->
<nav class="navbar navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-briefcase me-2"></i>JobPortal</a>
        <div>
            <span class="text-white me-3">Welcome, <?php echo $_SESSION["full_name"]; ?> (<?php echo $_SESSION["role"]; ?>)</span>
            <a href="?action=logout" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>
<div style="margin-top: 70px;"></div>

<!-- SIDEBAR FOR LOGGED IN USERS -->
<div class="sidebar">
    <div class="text-center py-4">
        <i class="fas fa-briefcase fa-3x text-white"></i>
        <h5 class="text-white mt-2">JobPortal</h5>
        <p class="text-white-50 small"><?php echo $_SESSION["full_name"]; ?></p>
    </div>
    <hr class="bg-white-50">
    <a href="?page=dashboard" class="sidebar-link"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
    
    <?php if($_SESSION["role"] == "admin"): ?>
    <a href="?page=categories" class="sidebar-link"><i class="fas fa-tags me-2"></i>Categories</a>
    <a href="?page=employers" class="sidebar-link"><i class="fas fa-building me-2"></i>Employers</a>
    <a href="?page=all_jobs" class="sidebar-link"><i class="fas fa-briefcase me-2"></i>All Jobs</a>
    <a href="?page=all_applications" class="sidebar-link"><i class="fas fa-file-alt me-2"></i>Applications</a>
    <?php else: ?>
    <a href="?page=post_job" class="sidebar-link"><i class="fas fa-plus-circle me-2"></i>Post Job</a>
    <a href="?page=my_jobs" class="sidebar-link"><i class="fas fa-list me-2"></i>My Jobs</a>
    <a href="?page=my_applications" class="sidebar-link"><i class="fas fa-users me-2"></i>Applications</a>
    <?php endif; ?>
    
    <a href="?page=browse_jobs" class="sidebar-link"><i class="fas fa-search me-2"></i>Browse Jobs</a>
    <hr class="bg-white-50">
    <a href="?action=logout" class="sidebar-link"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
</div>
<div class="main-content">
<?php endif; ?>

<!-- ============================================ -->
<!-- PAGE ROUTING -->
<!-- ============================================ -->

<?php
// HOME PAGE (Public)
if($page == "home" && !isset($_SESSION["user_id"])):
?>
<div class="container">
    <div class="text-center" style="padding: 80px 0;">
        <h1 style="font-size: 3.5rem; color: #1a1a2e;">Find Your <span style="color: #4a6cf7;">Dream Job</span></h1>
        <p style="font-size: 1.2rem; margin: 20px 0;">Connect with top employers and advance your career</p>
        <a href="?page=jobs" class="btn btn-primary btn-lg px-5 me-3">Browse Jobs</a>
        <a href="?page=register" class="btn btn-success btn-lg px-5">Register Now</a>
    </div>
    
    <div class="row mt-5">
        <?php
        $totalJobs = $conn->query("SELECT COUNT(*) as c FROM jobs")->fetch_assoc()["c"];
        $totalEmployers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='employer'")->fetch_assoc()["c"];
        $totalApps = $conn->query("SELECT COUNT(*) as c FROM applications")->fetch_assoc()["c"];
        ?>
        <div class="col-md-4">
            <div class="stat-card"><i class="fas fa-briefcase" style="color:#4a6cf7"></i><div class="stat-number"><?php echo $totalJobs; ?></div><p>Open Positions</p></div>
        </div>
        <div class="col-md-4">
            <div class="stat-card"><i class="fas fa-building" style="color:#00b894"></i><div class="stat-number"><?php echo $totalEmployers; ?></div><p>Companies</p></div>
        </div>
        <div class="col-md-4">
            <div class="stat-card"><i class="fas fa-file-alt" style="color:#f39c12"></i><div class="stat-number"><?php echo $totalApps; ?></div><p>Applications</p></div>
        </div>
    </div>
    
    <h3 class="text-center mt-5 mb-4">Latest Jobs</h3>
    <div class="row">
        <?php $jobs = $conn->query("SELECT j.*, u.company_name FROM jobs j LEFT JOIN users u ON j.employer_id=u.id WHERE j.status='active' ORDER BY j.id DESC LIMIT 6");
        while($job = $jobs->fetch_assoc()): ?>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5><?php echo htmlspecialchars($job["title"]); ?></h5>
                    <p class="text-muted"><i class="fas fa-building me-2"></i><?php echo $job["company_name"]; ?></p>
                    <p class="text-muted"><i class="fas fa-map-marker-alt me-2"></i><?php echo $job["location"]; ?></p>
                    <p class="text-success">$<?php echo number_format($job["salary_min"]); ?> - $<?php echo number_format($job["salary_max"]); ?></p>
                    <a href="?page=apply&id=<?php echo $job["id"]; ?>" class="btn btn-primary btn-sm">Apply Now</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>

<!-- BROWSE JOBS PAGE -->
<?php if($page == "jobs" || $page == "browse_jobs"): ?>
<div class="container">
    <h2 class="mb-4">Browse All Jobs</h2>
    <div class="row">
        <div class="col-md-3">
            <div class="card p-3">
                <h5>Filter by Category</h5>
                <hr>
                <a href="?page=jobs" class="d-block mb-2">All Jobs</a>
                <?php $cats = $conn->query("SELECT * FROM categories"); while($cat = $cats->fetch_assoc()): ?>
                <a href="?page=jobs&cat=<?php echo $cat["id"]; ?>" class="d-block mb-2"><?php echo $cat["name"]; ?></a>
                <?php endwhile; ?>
            </div>
        </div>
        <div class="col-md-9">
            <?php 
            $cat_filter = isset($_GET["cat"]) ? intval($_GET["cat"]) : 0;
            $sql = "SELECT j.*, u.company_name FROM jobs j LEFT JOIN users u ON j.employer_id=u.id WHERE j.status='active'";
            if($cat_filter > 0) $sql .= " AND j.category_id=$cat_filter";
            $sql .= " ORDER BY j.id DESC";
            $jobs = $conn->query($sql);
            while($job = $jobs->fetch_assoc()): 
            ?>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4><?php echo htmlspecialchars($job["title"]); ?></h4>
                            <p><i class="fas fa-building me-2"></i><?php echo $job["company_name"]; ?> | <i class="fas fa-map-marker-alt ms-2 me-2"></i><?php echo $job["location"]; ?></p>
                            <p><i class="fas fa-dollar-sign me-2"></i>$<?php echo number_format($job["salary_min"]); ?> - $<?php echo number_format($job["salary_max"]); ?> | <i class="fas fa-clock ms-2 me-2"></i><?php echo $job["job_type"]; ?></p>
                            <p><?php echo substr($job["description"], 0, 150); ?>...</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="?page=apply&id=<?php echo $job["id"]; ?>" class="btn btn-primary">Apply Now</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- LOGIN PAGE -->
<?php if($page == "login"): ?>
<div class="container">
    <div class="row">
        <div class="col-md-5 mx-auto">
            <div class="card p-4">
                <div class="text-center mb-4">
                    <i class="fas fa-briefcase fa-3x" style="color:#4a6cf7"></i>
                    <h3>Login to JobPortal</h3>
                </div>
                <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="POST">
                    <input type="email" name="email" class="form-control mb-3" placeholder="Email Address" required>
                    <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
                    <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                </form>
                <hr>
                <p class="text-center">Admin: admin@admin.com / admin123</p>
                <p class="text-center">Don't have an account? <a href="?page=register">Register as Employer</a></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- REGISTER PAGE -->
<?php if($page == "register"): ?>
<div class="container">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card p-4">
                <div class="text-center mb-4">
                    <i class="fas fa-user-plus fa-3x" style="color:#4a6cf7"></i>
                    <h3>Employer Registration</h3>
                </div>
                <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3"><input type="text" name="username" class="form-control" placeholder="Username" required></div>
                        <div class="col-md-6 mb-3"><input type="text" name="full_name" class="form-control" placeholder="Full Name" required></div>
                    </div>
                    <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
                    <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
                    <input type="text" name="company_name" class="form-control mb-3" placeholder="Company Name" required>
                    <input type="text" name="phone" class="form-control mb-3" placeholder="Phone Number">
                    <button type="submit" name="register" class="btn btn-success w-100">Register</button>
                </form>
                <p class="text-center mt-3">Already have an account? <a href="?page=login">Login</a></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- APPLY PAGE -->
<?php if($page == "apply" && isset($_GET["id"])): 
$job_id = intval($_GET["id"]);
$job = $conn->query("SELECT * FROM jobs WHERE id=$job_id")->fetch_assoc();
?>
<div class="container">
    <div class="row">
        <div class="col-md-7 mx-auto">
            <div class="card p-4">
                <h3>Apply for: <?php echo htmlspecialchars($job["title"]); ?></h3>
                <hr>
                <?php if(isset($apply_success)) echo "<div class='alert alert-success'>$apply_success</div>"; ?>
                <form method="POST">
                    <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                    <input type="text" name="applicant_name" class="form-control mb-3" placeholder="Full Name" required>
                    <input type="email" name="applicant_email" class="form-control mb-3" placeholder="Email" required>
                    <input type="tel" name="applicant_phone" class="form-control mb-3" placeholder="Phone Number">
                    <input type="number" name="experience_years" class="form-control mb-3" placeholder="Years of Experience">
                    <input type="number" name="expected_salary" class="form-control mb-3" placeholder="Expected Salary (USD)">
                    <textarea name="cover_letter" class="form-control mb-3" rows="5" placeholder="Cover Letter / Why should we hire you?" required></textarea>
                    <button type="submit" name="apply" class="btn btn-success w-100">Submit Application</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- DASHBOARD (For logged in users) -->
<?php if($page == "dashboard" && isset($_SESSION["user_id"])): 
$totalJobs = $conn->query("SELECT COUNT(*) as c FROM jobs")->fetch_assoc()["c"];
$totalApps = $conn->query("SELECT COUNT(*) as c FROM applications")->fetch_assoc()["c"];
$totalEmployers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='employer'")->fetch_assoc()["c"];
?>
<div class="container-fluid">
    <h2 class="mb-4">Dashboard</h2>
    <div class="row">
        <div class="col-md-3"><div class="stat-card"><i class="fas fa-briefcase" style="color:#4a6cf7"></i><div class="stat-number"><?php echo $totalJobs; ?></div><p>Total Jobs</p></div></div>
        <div class="col-md-3"><div class="stat-card"><i class="fas fa-file-alt" style="color:#00b894"></i><div class="stat-number"><?php echo $totalApps; ?></div><p>Applications</p></div></div>
        <div class="col-md-3"><div class="stat-card"><i class="fas fa-building" style="color:#f39c12"></i><div class="stat-number"><?php echo $totalEmployers; ?></div><p>Employers</p></div></div>
        <div class="col-md-3"><div class="stat-card"><i class="fas fa-tags" style="color:#e74c3c"></i><div class="stat-number"><?php echo $conn->query("SELECT COUNT(*) as c FROM categories")->fetch_assoc()["c"]; ?></div><p>Categories</p></div></div>
    </div>
</div>
<?php endif; ?>

<!-- ADMIN: CATEGORIES MANAGEMENT -->
<?php if($page == "categories" && isset($_SESSION["user_id"]) && $_SESSION["role"] == "admin"): ?>
<div class="container-fluid">
    <div class="card p-4">
        <h4><i class="fas fa-tags me-2"></i>Manage Categories</h4>
        <hr>
        <form method="POST" class="row mb-4">
            <div class="col-md-6"><input type="text" name="name" class="form-control" placeholder="Category Name" required></div>
            <div class="col-md-3"><button type="submit" name="add_category" class="btn btn-primary w-100">Add Category</button></div>
        </form>
        <table class="table table-bordered">
            <thead class="table-dark"><tr><th>ID</th><th>Name</th><th>Created</th><th>Action</th></tr></thead>
            <tbody>
            <?php $cats = $conn->query("SELECT * FROM categories ORDER BY id DESC"); while($cat = $cats->fetch_assoc()): ?>
            <tr><td><?php echo $cat["id"]; ?></td><td><?php echo $cat["name"]; ?></td><td><?php echo date("M d, Y", strtotime($cat["created_at"])); ?></td>
            <td><a href="?action=delete_cat&id=<?php echo $cat["id"]; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete category?')">Delete</a></td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ADMIN: EMPLOYERS MANAGEMENT -->
<?php if($page == "employers" && isset($_SESSION["user_id"]) && $_SESSION["role"] == "admin"): ?>
<div class="container-fluid">
    <div class="card p-4">
        <h4><i class="fas fa-building me-2"></i>Manage Employers</h4>
        <hr>
        <table class="table table-bordered">
            <thead class="table-dark"><tr><th>ID</th><th>Username</th><th>Email</th><th>Company</th><th>Phone</th><th>Registered</th><th>Action</th></tr></thead>
            <tbody>
            <?php $emps = $conn->query("SELECT * FROM users WHERE role='employer' ORDER BY id DESC"); while($emp = $emps->fetch_assoc()): ?>
            <tr><td><?php echo $emp["id"]; ?></td><td><?php echo $emp["username"]; ?></td><td><?php echo $emp["email"]; ?></td>
            <td><?php echo $emp["company_name"]; ?></td><td><?php echo $emp["phone"]; ?></td><td><?php echo date("M d, Y", strtotime($emp["created_at"])); ?></td>
            <td><a href="?action=delete_employer&id=<?php echo $emp["id"]; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete employer?')">Delete</a></td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ADMIN: ALL JOBS -->
<?php if($page == "all_jobs" && isset($_SESSION["user_id"]) && $_SESSION["role"] == "admin"): ?>
<div class="container-fluid">
    <div class="card p-4">
        <h4><i class="fas fa-briefcase me-2"></i>All Jobs</h4>
        <hr>
        <table class="table table-bordered">
            <thead class="table-dark"><tr><th>ID</th><th>Title</th><th>Company</th><th>Location</th><th>Status</th><th>Posted</th><th>Action</th></tr></thead>
            <tbody>
            <?php $jobs = $conn->query("SELECT j.*, u.company_name FROM jobs j LEFT JOIN users u ON j.employer_id=u.id ORDER BY j.id DESC"); while($job = $jobs->fetch_assoc()): ?>
            <tr><td><?php echo $job["id"]; ?></td><td><?php echo $job["title"]; ?></td><td><?php echo $job["company_name"]; ?></td>
            <td><?php echo $job["location"]; ?></td><td><span class="badge-<?php echo $job["status"]; ?>"><?php echo $job["status"]; ?></span></td>
            <td><?php echo date("M d", strtotime($job["created_at"])); ?></td>
            <td><a href="?action=delete_job&id=<?php echo $job["id"]; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete job?')">Delete</a></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ADMIN: ALL APPLICATIONS -->
<?php if($page == "all_applications" && isset($_SESSION["user_id"]) && $_SESSION["role"] == "admin"): ?>
<div class="container-fluid">
    <div class="card p-4">
        <h4><i class="fas fa-file-alt me-2"></i>All Applications</h4>
        <hr>
        <table class="table table-bordered">
            <thead class="table-dark"><tr><th>ID</th><th>Job</th><th>Applicant</th><th>Email</th><th>Status</th><th>Applied</th><th>Action</th></tr></thead>
            <tbody>
            <?php $apps = $conn->query("SELECT a.*, j.title as job_title FROM applications a LEFT JOIN jobs j ON a.job_id=j.id ORDER BY a.id DESC"); while($app = $apps->fetch_assoc()): ?>
            <tr>
                <td><?php echo $app["id"]; ?></td>
                <td><?php echo $app["job_title"]; ?></td>
                <td><?php echo $app["applicant_name"]; ?></td>
                <td><?php echo $app["applicant_email"]; ?></td>
                <td><span class="badge-<?php echo $app["status"]; ?>"><?php echo $app["status"]; ?></span></td>
                <td><?php echo date("M d", strtotime($app["applied_at"])); ?></td>
                <td>
                    <button class="btn btn-info btn-sm" onclick="alert('Cover Letter:\n\n<?php echo addslashes($app["cover_letter"]); ?>')">View</button>
                    <a href="?action=delete_app&id=<?php echo $app["id"]; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete application?')">Delete</a>
                 </td>
             </tr>
            <?php endwhile; ?>
            </tbody>
         </table>
    </div>
</div>
<?php endif; ?>

<!-- EMPLOYER: POST JOB -->
<?php if($page == "post_job" && isset($_SESSION["user_id"]) && $_SESSION["role"] == "employer"): ?>
<div class="container-fluid">
    <div class="card p-4">
        <h4><i class="fas fa-plus-circle me-2"></i>Post New Job</h4>
        <hr>
        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3"><input type="text" name="title" class="form-control" placeholder="Job Title" required></div>
                <div class="col-md-6 mb-3">
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php $cats = $conn->query("SELECT * FROM categories"); while($cat = $cats->fetch_assoc()): ?>
                        <option value="<?php echo $cat["id"]; ?>"><?php echo $cat["name"]; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="mb-3"><textarea name="description" class="form-control" rows="5" placeholder="Job Description" required></textarea></div>
            <div class="row">
                <div class="col-md-4 mb-3"><input type="text" name="location" class="form-control" placeholder="Location" required></div>
                <div class="col-md-2 mb-3"><input type="number" name="salary_min" class="form-control" placeholder="Min Salary"></div>
                <div class="col-md-2 mb-3"><input type="number" name="salary_max" class="form-control" placeholder="Max Salary"></div>
                <div class="col-md-2 mb-3">
                    <select name="job_type" class="form-control">
                        <option value="Full Time">Full Time</option>
                        <option value="Part Time">Part Time</option>
                        <option value="Remote">Remote</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3"><input type="number" name="experience_required" class="form-control" placeholder="Experience (years)"></div>
            </div>
            <div class="mb-3"><input type="text" name="skills_required" class="form-control" placeholder="Skills Required (comma separated)"></div>
            <button type="submit" name="post_job" class="btn btn-primary">Post Job</button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- EMPLOYER: MY JOBS -->
<?php if($page == "my_jobs" && isset($_SESSION["user_id"]) && $_SESSION["role"] == "employer"): ?>
<div class="container-fluid">
    <div class="card p-4">
        <h4><i class="fas fa-list me-2"></i>My Jobs</h4>
        <hr>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr><th>ID</th><th>Title</th><th>Location</th><th>Salary</th><th>Applications</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php $myjobs = $conn->query("SELECT * FROM jobs WHERE employer_id={$_SESSION["user_id"]} ORDER BY id DESC"); 
            while($job = $myjobs->fetch_assoc()): 
                $appCount = $conn->query("SELECT COUNT(*) as c FROM applications WHERE job_id=".$job["id"])->fetch_assoc()["c"];
            ?>
            <tr>
                <td><?php echo $job["id"]; ?></td>
                <td><?php echo $job["title"]; ?></td>
                <td><?php echo $job["location"]; ?></td>
                <td>$<?php echo number_format($job["salary_min"]); ?>-<?php echo number_format($job["salary_max"]); ?></td>
                <td><span class="badge bg-info"><?php echo $appCount; ?></span></td>
                <td><span class="badge-<?php echo $job["status"]; ?>"><?php echo $job["status"]; ?></span></td>
                <td>
                    <a href="?action=toggle_status&id=<?php echo $job["id"]; ?>" class="btn btn-warning btn-sm">Toggle Status</a>
                    <a href="?action=delete_job&id=<?php echo $job["id"]; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete job?')">Delete</a>
                 </td>
             </tr>
            <?php endwhile; ?>
            </tbody>
         </table>
    </div>
</div>
<?php endif; ?>

<!-- EMPLOYER: MY APPLICATIONS RECEIVED -->
<?php if($page == "my_applications" && isset($_SESSION["user_id"]) && $_SESSION["role"] == "employer"): ?>
<div class="container-fluid">
    <div class="card p-4">
        <h4><i class="fas fa-users me-2"></i>Applications Received</h4>
        <hr>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr><th>ID</th><th>Job</th><th>Applicant</th><th>Email</th><th>Experience</th><th>Expected Salary</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php 
            $myapps = $conn->query("SELECT a.*, j.title as job_title FROM applications a JOIN jobs j ON a.job_id=j.id WHERE j.employer_id={$_SESSION["user_id"]} ORDER BY a.id DESC"); 
            while($app = $myapps->fetch_assoc()): 
            ?>
            <tr>
                <td><?php echo $app["id"]; ?></td>
                <td><?php echo $app["job_title"]; ?></td>
                <td><?php echo $app["applicant_name"]; ?></td>
                <td><?php echo $app["applicant_email"]; ?></td>
                <td><?php echo $app["experience_years"]; ?> yrs</td>
                <td>$<?php echo number_format($app["expected_salary"]); ?></td>
                <td><span class="badge-<?php echo $app["status"]; ?>"><?php echo $app["status"]; ?></span></td>
                <td>
                    <button class="btn btn-info btn-sm" onclick="alert('Cover Letter:\n\n<?php echo addslashes($app["cover_letter"]); ?>')">View</button>
                    <a href="?action=update_app_status&id=<?php echo $app["id"]; ?>&status=shortlisted" class="btn btn-success btn-sm">Shortlist</a>
                    <a href="?action=update_app_status&id=<?php echo $app["id"]; ?>&status=rejected" class="btn btn-danger btn-sm">Reject</a>
                    <a href="?action=update_app_status&id=<?php echo $app["id"]; ?>&status=hired" class="btn btn-primary btn-sm">Hire</a>
                    <a href="?action=delete_app&id=<?php echo $app["id"]; ?>" class="btn btn-secondary btn-sm">Delete</a>
                 </td>
             </tr>
            <?php endwhile; ?>
            </tbody>
         </table>
    </div>
</div>
<?php endif; ?>

<?php if(isset($_SESSION["user_id"])): ?>
</div> <!-- Close main-content -->
<?php endif; ?>

<footer>
    <div class="container">
        <p>&copy; 2024 JobPortal. All rights reserved. | Complete Recruitment Management System</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>