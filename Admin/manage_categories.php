<?php
session_start();
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/models.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$categoryModel = new JobCategory($DB);
$message = '';

// Handle Add / Delete / Edit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['add_category'])) {
        $category_name = trim($_POST['category_name']);
        if (!empty($category_name)) {
            $success = $categoryModel->addCategory($category_name);
            $message = $success ? "Category added successfully!" : "Failed to add category.";
        } else {
            $message = "Category name cannot be empty.";
        }
    } elseif (isset($_POST['delete_category'])) {
        $category_id = intval($_POST['category_id']);
        $success = $categoryModel->deleteCategory($category_id);
        $message = $success ? "Category deleted successfully!" : "Failed to delete category.";
    } elseif (isset($_POST['edit_category'])) {
        $category_id = intval($_POST['edit_category_id']);
        $new_category_name = trim($_POST['new_category_name']);
        if (!empty($new_category_name)) {
            $success = $categoryModel->editCategory($category_id, $new_category_name);
            $message = $success ? "Category updated successfully!" : "Failed to update category.";
        } else {
            $message = "New category name cannot be empty.";
        }
    }
}

$categories = $categoryModel->getAllCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this category?");
        }
    </script>
</head>
<body>
<div class="dashboard">

    <form action="../dashboards/admin_dashboard.php" method="get">
        <button type="submit" class="go-back-btn">Go Back to Dashboard</button>
    </form>

    <h1>Manage Job Categories</h1>
    <?php if (!empty($message)) echo "<p>$message</p>"; ?>

    <div class="manage-categories-page">
        <h2>Available Categories</h2>
        <ul>
            <?php while ($row = $categories->fetch_assoc()): ?>
                <li>
                    <?= htmlspecialchars($row['category_id']) . " - " . htmlspecialchars($row['category_name']) ?>
                </li>
            <?php endwhile; ?>
        </ul>

        <h2>Add Category</h2>
        <form method="POST" action="manage_categories.php">
            <label for="category_name">Category Name:</label>
            <input type="text" name="category_name" id="category_name" required>
            <button type="submit" name="add_category">Add Category</button>
        </form>

        <h2>Edit Category</h2>
        <form method="POST" action="manage_categories.php">
            <label for="edit_category_id">Select Category to Edit:</label>
            <select name="edit_category_id" id="edit_category_id" required>
                <option value="">-- Select --</option>
                <?php
                $categories->data_seek(0);
                while ($row = $categories->fetch_assoc()) : ?>
                    <option value="<?= $row['category_id'] ?>">
                        <?= htmlspecialchars($row['category_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <label for="new_category_name">New Category Name:</label>
            <input type="text" name="new_category_name" id="new_category_name" required>
            <button type="submit" name="edit_category">Update Category</button>
        </form>

        <h2>Delete Category</h2>
        <form method="POST" action="manage_categories.php" onsubmit="return confirmDelete();">
            <label for="category_id">Select Category to Delete:</label>
            <select name="category_id" id="category_id" required>
                <option value="">-- Select --</option>
                <?php
                $categories->data_seek(0);
                while ($row = $categories->fetch_assoc()) : ?>
                    <option value="<?= $row['category_id'] ?>">
                        <?= htmlspecialchars($row['category_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="delete_category">Delete Category</button>
        </form>
    </div>

</div>
</body>
</html>