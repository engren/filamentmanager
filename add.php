<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
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

$isEditing = false;
$filament = null;
$error = '';
$success = '';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $isEditing = true;

    $sql = "SELECT * FROM filaments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $filament = $result->fetch_assoc();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $brand = trim($_POST['brand']);
    $material = trim($_POST['material']);
    $color = trim($_POST['color']);
    $ideal_nozzle_temp = (int) $_POST['ideal_nozzle_temp'];
    $ideal_bed_temp = (int) $_POST['ideal_bed_temp'];
    $rolls_250g = (int) $_POST['rolls_250g'];
    $rolls_500g = (int) $_POST['rolls_500g'];
    $rolls_750g = (int) $_POST['rolls_750g'];
    $rolls_1000g = (int) $_POST['rolls_1000g'];
    $rolls_2000g = (int) $_POST['rolls_2000g'];
    $purchase_url = trim($_POST['purchase_url']);
    $notes = trim($_POST['notes']);
    $image_url = '';

    // Debugging: Print purchase URL to verify value before binding it
    var_dump($purchase_url); // Ensure this shows the correct URL

    // Validation
    if (empty($brand) || empty($material) || empty($color)) {
        $error = "Brand, Material, and Color are required fields.";
    } elseif (!filter_var($purchase_url, FILTER_VALIDATE_URL) && !empty($purchase_url)) {
        $error = "Purchase URL is invalid.";
    } else {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_url = 'uploads/' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $image_url);
        }

        // Generate unique filament ID based on material (PLA-12345)
        $random_number = random_int(10000, 99999);
        $unique_id = strtoupper($material) . '-' . $random_number;

        if ($isEditing) {
            $sql = "UPDATE filaments SET brand=?, material=?, color=?, ideal_nozzle_temp=?, ideal_bed_temp=?, rolls_250g=?, rolls_500g=?, rolls_750g=?, rolls_1000g=?, rolls_2000g=?, purchase_url=?, notes=?, image_url=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssiisssssssi', $brand, $material, $color, $ideal_nozzle_temp, $ideal_bed_temp, $rolls_250g, $rolls_500g, $rolls_750g, $rolls_1000g, $rolls_2000g, $purchase_url, $notes, $image_url, $id);
        } else {
            $sql = "INSERT INTO filaments (brand, material, color, unique_id, ideal_nozzle_temp, ideal_bed_temp, rolls_250g, rolls_500g, rolls_750g, rolls_1000g, rolls_2000g, purchase_url, notes, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssssiisssssss', $brand, $material, $color, $unique_id, $ideal_nozzle_temp, $ideal_bed_temp, $rolls_250g, $rolls_500g, $rolls_750g, $rolls_1000g, $rolls_2000g, $purchase_url, $notes, $image_url);
        }

        if ($stmt->execute()) {
            $success = $isEditing ? "Filament updated successfully!" : "Filament added successfully!";
            header("Location: index.php?success=" . urlencode($success));
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEditing ? 'Edit Filament' : 'Add Filament'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include('navigation.php'); ?>

<div class="container mt-5">
    <h1 class="mb-4"><?php echo $isEditing ? 'Edit Filament' : 'Add Filament'; ?></h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
            <label for="brand" class="form-label">Brand</label>
            <input type="text" class="form-control" id="brand" name="brand" value="<?php echo htmlspecialchars($filament['brand'] ?? ''); ?>" required>
        </div>

        <div class="col-md-6">
            <label for="material" class="form-label">Material</label>
            <input type="text" class="form-control" id="material" name="material" value="<?php echo htmlspecialchars($filament['material'] ?? ''); ?>" required>
        </div>

        <div class="col-md-6">
            <label for="color" class="form-label">Color</label>
            <input type="text" class="form-control" id="color" name="color" value="<?php echo htmlspecialchars($filament['color'] ?? ''); ?>" required>
        </div>

        <div class="col-md-6">
            <label for="nozzle_temp" class="form-label">Ideal Nozzle Temperature (°C)</label>
            <input type="number" class="form-control" id="nozzle_temp" name="ideal_nozzle_temp" value="<?php echo htmlspecialchars($filament['ideal_nozzle_temp'] ?? ''); ?>" required>
        </div>

        <div class="col-md-6">
            <label for="bed_temp" class="form-label">Ideal Bed Temperature (°C)</label>
            <input type="number" class="form-control" id="bed_temp" name="ideal_bed_temp" value="<?php echo htmlspecialchars($filament['ideal_bed_temp'] ?? ''); ?>" required>
        </div>

        <div class="col-md-6">
            <label for="rolls_250g" class="form-label">250g Rolls</label>
            <input type="number" class="form-control" id="rolls_250g" name="rolls_250g" value="<?php echo htmlspecialchars($filament['rolls_250g'] ?? 0); ?>">
        </div>

        <div class="col-md-6">
            <label for="rolls_500g" class="form-label">500g Rolls</label>
            <input type="number" class="form-control" id="rolls_500g" name="rolls_500g" value="<?php echo htmlspecialchars($filament['rolls_500g'] ?? 0); ?>">
        </div>

        <div class="col-md-6">
            <label for="rolls_750g" class="form-label">750g Rolls</label>
            <input type="number" class="form-control" id="rolls_750g" name="rolls_750g" value="<?php echo htmlspecialchars($filament['rolls_750g'] ?? 0); ?>">
        </div>

        <div class="col-md-6">
            <label for="rolls_1000g" class="form-label">1000g Rolls</label>
            <input type="number" class="form-control" id="rolls_1000g" name="rolls_1000g" value="<?php echo htmlspecialchars($filament['rolls_1000g'] ?? 0); ?>">
        </div>

        <div class="col-md-6">
            <label for="rolls_2000g" class="form-label">2000g Rolls</label>
            <input type="number" class="form-control" id="rolls_2000g" name="rolls_2000g" value="<?php echo htmlspecialchars($filament['rolls_2000g'] ?? 0); ?>">
        </div>

        <div class="col-md-12">
            <label for="purchase_url" class="form-label">Purchase Location (URL)</label>
            <input type="url" class="form-control" id="purchase_url" name="purchase_url" value="<?php echo htmlspecialchars($filament['purchase_url'] ?? ''); ?>">
        </div>

        <div class="col-md-12">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($filament['notes'] ?? ''); ?></textarea>
        </div>

        <div class="col-md-12">
            <label for="image" class="form-label">Upload Image</label>
            <input type="file" class="form-control" id="image" name="image">
        </div>

        <div class="col-md-12">
            <button type="submit" class="btn btn-primary w-100"><?php echo $isEditing ? 'Update Filament' : 'Add Filament'; ?></button>
        </div>
    </form>
</div>
<br><br><br><br>

<?php include('footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

