<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['username'] !== 'admin') {
    header("Location: index.php");
    exit();
}

require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
$success = '';

// Handle adding a user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $new_username = trim($_POST['username']);
    $new_password = trim($_POST['password']);
    
    if (!empty($new_username) && !empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $new_username, $hashed_password);
        
        if ($stmt->execute()) {
            $success = "User $new_username added successfully.";
        } else {
            $error = "Error: " . $stmt->error;
        }
    } else {
        $error = "Username and password are required.";
    }
}

// Handle deleting a user
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];

    if ($user_id != 1) { // Prevent admin deletion
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);

        if ($stmt->execute()) {
            $success = "User deleted successfully.";
        } else {
            $error = "Error: " . $stmt->error;
        }
    } else {
        $error = "Admin cannot be deleted.";
    }
}

// Handle updating a user's password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['new_password'];

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $success = "Password updated successfully for user ID $user_id.";
        } else {
            $error = "Error: " . $stmt->error;
        }
    } else {
        $error = "New password is required.";
    }
}

// Fetch all users
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin-top: 70px;
        }
        td, th {
            vertical-align: middle;
            text-align: center;
        }
        .nowrap {
            white-space: nowrap;
        }
        .narrow {
            width: 60px;
        }
    </style>
</head>
<body>

<?php include('navigation.php'); ?>

<div class="container mt-5">
    <h1>Manage Users</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <h2>Add New User</h2>
    <form method="POST" action="manage_users.php">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary" name="add_user">Add User</button>
    </form>

    <h2 class="mt-5">Existing Users</h2>
    <table class="table table-striped table-hover">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td>
                        <?php if ($row['id'] != 1): // Prevent deleting admin ?>
                            <a href="manage_users.php?delete_user=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        <?php else: ?>
                            Admin
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2 class="mt-5">Update User Password</h2>
    <form method="POST" action="manage_users.php">
        <div class="mb-3">
            <label for="user_id" class="form-label">User ID</label>
            <input type="number" class="form-control" id="user_id" name="user_id" required>
        </div>
        <div class="mb-3">
            <label for="new_password" class="form-label">New Password</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required>
        </div>
        <button type="submit" class="btn btn-primary" name="update_password">Update Password</button>
    </form>
</div>

<?php include('footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

