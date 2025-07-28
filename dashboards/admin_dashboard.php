<?php
session_start();
require_once __DIR__ . '/../config/bootstrap.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../models/Admin.php';
$adminModel = new Admin($DB);
$adminResult = $adminModel->getAdminById($_SESSION['user']['id']);
$admin = $adminResult->fetch_assoc();

$allAdmins = $adminModel->getAllAdmins();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - ORS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="dashboard">
        <header>
                    <!-- Updates with info about Admin who it is logged in as -->
            <h1>Administrator Dashboard</h1>
            <div class="admin-info">
                <p>Welcome, <strong><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></strong></p>
                <p>Email: <?php echo htmlspecialchars($admin['email']); ?></p>
                <p>Phone: <?php echo htmlspecialchars($admin['phone_number']); ?></p>
                <p><a href="../logout.php"><button class="logout">Logout</button></a></p>
                </div>
        </header>
                
                <!-- testing-->
        <section class="card">
            <h2>HR Staff Management</h2>
            <a href="../Admin/create_hr.php"><button>Create HR Staff Account</button></a>   <!-- takes you to create HR page -->
            <a href="../Admin/update_hr.php"><button>Update HR Staff Account</button></a>   <!-- takes you to update HR page -->
            <a href="../Admin/delete_hr.php"><button>Delete HR Staff Account</button></a>   <!-- takes you to delete HR page -->
        </section>

        <section class="card">
            <h2>Job Management</h2>
            <a href="../Admin/delete_job.php"><button>Delete Job Posting</button></a>  <!-- Opens delete job page -->
            <a href="../Admin/manage_categories.php"><button>Manage Job Categories</button></a>  <!-- Opens manage categories page -->
        </section>

                 <!-- Buttons take you to other pages to generate monthly or yearly reports -->
        <section class="card">
            <h2>Reports</h2>
            <a href="../Admin/monthly_report.php"><button>Generate Monthly Report</button></a> 
            <a href="../Admin/yearly_report.php"><button>Generate Yearly Report</button></a>
        </section>

            <!-- contains a table of all admins and their contact information for reference -->
        <section class="card">
            <h2>All Admins</h2>
            <table border="1" cellpadding="6" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Admin Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                    </tr>
                </thead>
                <tbody>
                         <!-- receives admin personal data from database -->
                    <?php while ($row = $allAdmins->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $row['admin_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
