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

$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%%';
$filter_brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$filter_material = isset($_GET['material']) ? $_GET['material'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'brand';
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

$sql = "SELECT * FROM filaments WHERE (brand LIKE ? OR material LIKE ? OR color LIKE ?) ";
$params = [$search, $search, $search];

if (!empty($filter_brand)) {
    $sql .= "AND brand = ? ";
    $params[] = $filter_brand;
}

if (!empty($filter_material)) {
    $sql .= "AND material = ? ";
    $params[] = $filter_material;
}

$sql .= "ORDER BY $sort LIMIT $items_per_page OFFSET $offset";

$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();

$total_sql = "SELECT COUNT(*) as total FROM filaments WHERE (brand LIKE ? OR material LIKE ? OR color LIKE ?)";
$total_stmt = $conn->prepare($total_sql);
$total_stmt->bind_param('sss', $search, $search, $search);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);

$brand_result = $conn->query("SELECT DISTINCT brand FROM filaments");
$material_result = $conn->query("SELECT DISTINCT material FROM filaments");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filament List</title>
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

<div class="container">
    <h1>Filament List</h1>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>

    <form method="GET" action="index.php" class="mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <select name="brand" class="form-select">
                    <option value="">All Brands</option>
                    <?php while ($row = $brand_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['brand']; ?>" <?php if ($filter_brand == $row['brand']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['brand']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="material" class="form-select">
                    <option value="">All Materials</option>
                    <?php while ($row = $material_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['material']; ?>" <?php if ($filter_material == $row['material']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['material']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </div>
    </form>

    <a href="add.php" class="btn btn-primary mb-3">Add New Filament</a>

    <table class="table table-striped table-hover">
        <thead class="thead-dark">
            <tr>
                <th class="nowrap"><a href="index.php?sort=unique_id">ID</a></th>
                <th><a href="index.php?sort=brand">Brand</a></th>
                <th><a href="index.php?sort=material">Material</a></th>
                <th><a href="index.php?sort=color">Color</a></th>
                <th><a href="index.php?sort=ideal_nozzle_temp">Nozzle Temp</a></th>
                <th><a href="index.php?sort=ideal_bed_temp">Bed Temp</a></th>
                <th class="narrow">250g Rolls</th>
                <th class="narrow">500g Rolls</th>
                <th class="narrow">750g Rolls</th>
                <th class="narrow">1000g Rolls</th>
                <th class="narrow">2000g Rolls</th>
                <th>Purchase</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="nowrap"><?php echo htmlspecialchars($row['unique_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['brand']); ?></td>
                        <td><?php echo htmlspecialchars($row['material']); ?></td>
                        <td><?php echo htmlspecialchars($row['color']); ?></td>
                        <td><?php echo htmlspecialchars($row['ideal_nozzle_temp']); ?> °C</td>
                        <td><?php echo htmlspecialchars($row['ideal_bed_temp']); ?> °C</td>
                        <td class="narrow"><?php echo htmlspecialchars($row['rolls_250g']); ?></td>
                        <td class="narrow"><?php echo htmlspecialchars($row['rolls_500g']); ?></td>
                        <td class="narrow"><?php echo htmlspecialchars($row['rolls_750g']); ?></td>
                        <td class="narrow"><?php echo htmlspecialchars($row['rolls_1000g']); ?></td>
                        <td class="narrow"><?php echo htmlspecialchars($row['rolls_2000g']); ?></td>
                        <td>
                            <?php if (!empty($row['purchase_url'])): ?>
                                <a href="<?php echo htmlspecialchars($row['purchase_url']); ?>" target="_blank" class="btn btn-sm btn-info">Buy</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><a href="add.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a></td>
                        <td><a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this filament?');">Delete</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="14" class="text-center">No filaments found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                    <a class="page-link" href="index.php?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&search=<?php echo urlencode($_GET['search'] ?? ''); ?>&brand=<?php echo urlencode($filter_brand); ?>&material=<?php echo urlencode($filter_material); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<?php include('footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

