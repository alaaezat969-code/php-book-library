
<?php
/*
    Alaa Ezat Abu Alqombuz
    120182587
    Web 2
    Assignment02
*/
session_start();

$genres = ["Fiction", "Non-Fiction", "Science", "History", "Biography", "Technology"];

// فحص وجود البيانات في الجلسة
if (!isset($_SESSION['books'])) {
    $_SESSION['books'] = [
        [
            "id" => 1,
            "title" => "The Great Gatsby",
            "author" => "F. Scott Fitzgerald",
            "genre" => "Fiction",
            "year" => 1925,
            "pages" => 218
        ],
        [
            "id" => 2,
            "title" => "A Brief History of Time",
            "author" => "Stephen Hawking",
            "genre" => "Science",
            "year" => 1988,
            "pages" => 256
        ],
        [
            "id" => 3,
            "title" => "Steve Jobs",
            "author" => "Walter Isaacson",
            "genre" => "Biography",
            "year" => 2011,
            "pages" => 656
        ]
    ];
}
$books = &$_SESSION['books'];

$errors = [];
$submittedData = [
    "title" => "",
    "author" => "",
    "genre" => "",
    "year" => "",
    "pages" => ""
];

// معالجة البيانات المدخلة والتحقق منها
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $submittedData['title'] = htmlspecialchars(trim($_POST['title'] ?? ''));
    $submittedData['author'] = htmlspecialchars(trim($_POST['author'] ?? ''));
    $submittedData['genre'] = htmlspecialchars(trim($_POST['genre'] ?? ''));
    $submittedData['year'] = htmlspecialchars(trim($_POST['year'] ?? ''));
    $submittedData['pages'] = htmlspecialchars(trim($_POST['pages'] ?? ''));

    $titleLen = strlen($submittedData['title']);
    if (empty($submittedData['title'])) {
        $errors['title'] = "Title is required.";
    } elseif ($titleLen < 3 || $titleLen > 120) {
        $errors['title'] = "Title must be between 3 and 120 characters.";
    }

    if (empty($submittedData['author'])) {
        $errors['author'] = "Author is required.";
    } else {
        $authorWords = array_filter(explode(' ', $submittedData['author']));
        if (count($authorWords) < 2) {
            $errors['author'] = "Author must contain at least two words (first and last name).";
        }
    }

    if (empty($submittedData['genre'])) {
        $errors['genre'] = "Genre is required.";
    } elseif (!in_array($submittedData['genre'], $genres)) {
        $errors['genre'] = "Selected genre is invalid.";
    }

    $currentYear = (int)date("Y");
    if (empty($submittedData['year'])) {
        $errors['year'] = "Year is required.";
    } else {
        $yearInt = (int)$submittedData['year'];
        if (!preg_match('/^\d{4}$/', $submittedData['year']) || $yearInt < 1000 || $yearInt > $currentYear) {
            $errors['year'] = "Year must be a 4-digit integer between 1000 and $currentYear.";
        }
    }

    if (empty($submittedData['pages'])) {
        $errors['pages'] = "Pages field is required.";
    } else {
        $pagesInt = filter_var($submittedData['pages'], FILTER_VALIDATE_INT);
        if ($pagesInt === false || $pagesInt <= 0) {
            $errors['pages'] = "Pages must be a positive integer greater than 0.";
        }
    }

    // إضافة الكتاب في حال عدم وجود أخطاء
    if (empty($errors)) {
        $maxId = 0;
        foreach ($books as $book) {
            if ($book['id'] > $maxId) {
                $maxId = $book['id'];
            }
        }
        $newId = $maxId + 1;

        $books[] = [
            "id" => $newId,
            "title" => $submittedData['title'],
            "author" => $submittedData['author'],
            "genre" => $submittedData['genre'],
            "year" => (int)$submittedData['year'],
            "pages" => (int)$submittedData['pages']
        ];

        $_SESSION['success'] = "Book added successfully!";

        $submittedData = [
            "title" => "",
            "author" => "",
            "genre" => "",
            "year" => "",
            "pages" => ""
        ];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Book Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Personal Book Library</h1>

        <div class="row">
            <!-- قسم إضافة كتاب -->
            <div class="col-lg-4 col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Add New Book</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                Please correct the errors in the form below.
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" name="title" id="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" value="<?= $submittedData['title'] ?>">
                                <?php if (isset($errors['title'])): ?>
                                    <div class="invalid-feedback"><?= $errors['title'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="author" class="form-label">Author</label>
                                <input type="text" name="author" id="author" class="form-control <?= isset($errors['author']) ? 'is-invalid' : '' ?>" value="<?= $submittedData['author'] ?>">
                                <?php if (isset($errors['author'])): ?>
                                    <div class="invalid-feedback"><?= $errors['author'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="genre" class="form-label">Genre</label>
                                <select name="genre" id="genre" class="form-control <?= isset($errors['genre']) ? 'is-invalid' : '' ?>">
                                    <option value="">-- Select Genre --</option>
                                    <?php foreach ($genres as $g): ?>
                                        <option value="<?= htmlspecialchars($g) ?>" <?= ($submittedData['genre'] === $g) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($g) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['genre'])): ?>
                                    <div class="invalid-feedback"><?= $errors['genre'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="year" class="form-label">Year</label>
                                <input type="number" name="year" id="year" class="form-control <?= isset($errors['year']) ? 'is-invalid' : '' ?>" value="<?= $submittedData['year'] ?>">
                                <?php if (isset($errors['year'])): ?>
                                    <div class="invalid-feedback"><?= $errors['year'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="pages" class="form-label">Pages</label>
                                <input type="number" name="pages" id="pages" class="form-control <?= isset($errors['pages']) ? 'is-invalid' : '' ?>" value="<?= $submittedData['pages'] ?>">
                                <?php if (isset($errors['pages'])): ?>
                                    <div class="invalid-feedback"><?= $errors['pages'] ?></div>
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Add Book</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- قسم عرض الكتب -->
            <div class="col-lg-8 col-md-12">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Library Inventory</h5>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-striped table-hover table-bordered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Genre</th>
                                    <th>Year</th>
                                    <th>Pages</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($books)): ?>
                                    <?php foreach ($books as $book): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($book['id']) ?></td>
                                            <td><?= htmlspecialchars($book['title']) ?></td>
                                            <td><?= htmlspecialchars($book['author']) ?></td>
                                            <td><?= htmlspecialchars($book['genre']) ?></td>
                                            <td><?= htmlspecialchars((int)$book['year']) ?></td>
                                            <td><?= htmlspecialchars($book['pages']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No books available.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
