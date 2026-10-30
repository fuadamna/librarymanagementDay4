<?php
include 'config.php';

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchTerm = "%$search%";

// Pagination settings
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

// Count total matching entries
$count_sql = "SELECT COUNT(*) AS total FROM form
              WHERE name LIKE ? OR email LIKE ?";

$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("ss", $searchTerm, $searchTerm);
$count_stmt->execute();

$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];

$total_pages = (int) ceil($total_rows / $limit);

// Prevent page number from exceeding the last page
if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
}

// Calculate offset
$offset = ($page - 1) * $limit;

// Retrieve entries
$sql = "SELECT * FROM form
        WHERE name LIKE ? OR email LIKE ?
        ORDER BY id DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>View Entries | Library Management System</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-body-tertiary d-flex flex-column min-vh-100">

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="about.html">
                Library Management System
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow-1">
        <div class="container mt-5">

            <h2 class="text-center mb-4">Manage Users</h2>

            <!-- Search Form -->
            <form method="GET" action="view.php" class="mb-3">
                <div class="d-flex gap-2">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search by Name or Email"
                        value="<?php echo htmlspecialchars($search); ?>"
                    >

                    <button type="submit" class="btn btn-primary">
                        Search
                    </button>

                    <a href="view.php" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>

            <!-- Total Entries -->
            <p class="text-muted">
                Total Entries:
                <strong><?php echo $total_rows; ?></strong>
            </p>

            <!-- Entries Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover bg-white">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($result->num_rows > 0): ?>

                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars((string)$row['id']); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($row['name']); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($row['email']); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($row['phone']); ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo isset($row['created_at'])
                                            ? htmlspecialchars($row['created_at'])
                                            : 'N/A';
                                        ?>
                                    </td>
                                    <td>
                                        <a href="update.php?id=<?php echo $row['id']; ?>"
                                         class="btn btn-primary btn-sm">
                                         Update
                                        </a>

                                        <a href="delete.php?id=<?php echo $row['id']; ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to delete this entry?');">
                                        Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No entries found.
                                </td>
                            </tr>

                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 0): ?>

                <nav aria-label="Entries pagination">
                    <ul class="pagination justify-content-center">

                        <!-- Previous Button -->
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <?php if ($page > 1): ?>
                                <a class="page-link"
                                   href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>">
                                    Previous
                                </a>
                            <?php else: ?>
                                <span class="page-link">Previous</span>
                            <?php endif; ?>
                        </li>

                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link"
                                   href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next Button -->
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <?php if ($page < $total_pages): ?>
                                <a class="page-link"
                                   href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>">
                                    Next
                                </a>
                            <?php else: ?>
                                <span class="page-link">Next</span>
                            <?php endif; ?>
                        </li>

                    </ul>
                </nav>

                <p class="text-center text-muted">
                    Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                </p>

            <?php endif; ?>

        </div>
    </main>

    <!-- Footer -->
    <footer class="text-white text-center mt-auto py-3 bg-black">
        <p class="mb-0">Developed by Amna Fuad | 2026</p>
    </footer>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
$stmt->close();
$count_stmt->close();
$conn->close();
?>
