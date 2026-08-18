<?php
/**
 * Core Helper Functions and Unified Data Layer
 * Handles authentication, item CRUD, stats, file uploads, and session management.
 * Transparently supports both MySQL database and JSON fallback.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/database.php";

$itemsJsonFile = dirname(__DIR__) . "/data/items.json";
$usersJsonFile = dirname(__DIR__) . "/data/users.json";

// Ensure data directory exists
if (!is_dir(dirname(__DIR__) . "/data")) {
    mkdir(dirname(__DIR__) . "/data", 0777, true);
}

// Ensure upload directories exist
if (!is_dir(dirname(__DIR__) . "/uploads")) {
    mkdir(dirname(__DIR__) . "/uploads", 0777, true);
}
if (!is_dir(dirname(__DIR__) . "/uploads/items")) {
    mkdir(dirname(__DIR__) . "/uploads/items", 0777, true);
}

/**
 * Read data from a JSON file safely
 */
function readJSON($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }
    $content = file_get_contents($filePath);
    if ($content === false || trim($content) === "") {
        return [];
    }
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

/**
 * Write data to a JSON file safely
 */
function writeJSON($filePath, $data) {
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    return file_put_contents($filePath, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

/**
 * Generate a unique identifier string
 */
function generateID() {
    return 'item_' . time() . '_' . bin2hex(random_bytes(4));
}

/**
 * Check if the user is currently logged in
 */
function isLoggedIn() {
    return isset($_SESSION["user_id"]) && !empty($_SESSION["user_id"]);
}

/**
 * Require user to be logged in; redirect to login.php if not
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $currentUri = $_SERVER["REQUEST_URI"] ?? "index.php";
        header("Location: login.php?redirect=" . urlencode($currentUri));
        exit;
    }
}

/**
 * Get current logged in user details
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        "id" => $_SESSION["user_id"],
        "name" => $_SESSION["user_name"] ?? "User",
        "email" => $_SESSION["user_email"] ?? ""
    ];
}

/**
 * Register a new user
 */
function registerUser($name, $email, $password) {
    global $conn, $dbAvailable, $usersJsonFile;

    $name = trim($name);
    $email = strtolower(trim($email));

    if (empty($name) || empty($email) || empty($password)) {
        return ["success" => false, "message" => "Please fill in all fields."];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ["success" => false, "message" => "Please enter a valid email address."];
    }
    if (strlen($password) < 6) {
        return ["success" => false, "message" => "Password must be at least 6 characters long."];
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $userId = 'user_' . time() . '_' . bin2hex(random_bytes(4));

    if ($dbAvailable && $conn) {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            return ["success" => false, "message" => "An account with this email already exists."];
        }

        $stmt = $conn->prepare("INSERT INTO users (id, name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $userId, $name, $email, $hashedPassword);
        if ($stmt->execute()) {
            // Also sync to JSON
            $users = readJSON($usersJsonFile);
            $users[] = [
                "id" => $userId,
                "name" => $name,
                "email" => $email,
                "password" => $hashedPassword,
                "created_at" => date("Y-m-d H:i:s")
            ];
            writeJSON($usersJsonFile, $users);

            return ["success" => true, "message" => "Registration successful! You can now login."];
        } else {
            return ["success" => false, "message" => "Database error during registration. Please try again."];
        }
    } else {
        // Use JSON storage
        $users = readJSON($usersJsonFile);
        foreach ($users as $u) {
            if (isset($u["email"]) && strtolower($u["email"]) === $email) {
                return ["success" => false, "message" => "An account with this email already exists."];
            }
        }

        $users[] = [
            "id" => $userId,
            "name" => $name,
            "email" => $email,
            "password" => $hashedPassword,
            "created_at" => date("Y-m-d H:i:s")
        ];
        writeJSON($usersJsonFile, $users);

        return ["success" => true, "message" => "Registration successful! You can now login."];
    }
}

/**
 * Authenticate a user by email & password
 */
function authenticateUser($email, $password) {
    global $conn, $dbAvailable, $usersJsonFile;

    $email = strtolower(trim($email));

    if (empty($email) || empty($password)) {
        return ["success" => false, "message" => "Please enter both email and password."];
    }

    if ($dbAvailable && $conn) {
        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows === 1) {
            $user = $res->fetch_assoc();
            if (password_verify($password, $user["password"])) {
                return ["success" => true, "user" => $user];
            }
        }
    }

    // Check JSON fallback (or if MySQL missed record)
    $users = readJSON($usersJsonFile);
    foreach ($users as $user) {
        if (isset($user["email"]) && strtolower($user["email"]) === $email) {
            if (password_verify($password, $user["password"])) {
                return ["success" => true, "user" => $user];
            }
        }
    }

    return ["success" => false, "message" => "Incorrect email or password."];
}

/**
 * Fetch all items with optional filters
 */
function getAllItems($filters = []) {
    global $conn, $dbAvailable, $itemsJsonFile;

    $items = [];

    if ($dbAvailable && $conn) {
        $sql = "SELECT * FROM items WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($filters["type"]) && $filters["type"] !== "All") {
            $sql .= " AND type = ?";
            $params[] = $filters["type"];
            $types .= "s";
        }
        if (!empty($filters["category"]) && $filters["category"] !== "All") {
            $sql .= " AND category = ?";
            $params[] = $filters["category"];
            $types .= "s";
        }
        if (!empty($filters["status"]) && $filters["status"] !== "All") {
            $sql .= " AND status = ?";
            $params[] = $filters["status"];
            $types .= "s";
        }
        if (!empty($filters["user_id"])) {
            $sql .= " AND user_id = ?";
            $params[] = (string)$filters["user_id"];
            $types .= "s";
        }

        $sql .= " ORDER BY created_at DESC, id DESC";

        if (!empty($filters["limit"])) {
            $sql .= " LIMIT " . (int)$filters["limit"];
        }

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
    }

    // If DB is offline or returned no items, use JSON
    if (empty($items)) {
        $jsonItems = readJSON($itemsJsonFile);
        
        // Sort newest first
        usort($jsonItems, function ($a, $b) {
            $tA = isset($a["created_at"]) ? strtotime($a["created_at"]) : (is_numeric($a["id"]) ? (int)$a["id"] : 0);
            $tB = isset($b["created_at"]) ? strtotime($b["created_at"]) : (is_numeric($b["id"]) ? (int)$b["id"] : 0);
            return $tB <=> $tA;
        });

        foreach ($jsonItems as $item) {
            if (!empty($filters["type"]) && $filters["type"] !== "All" && ($item["type"] ?? "") !== $filters["type"]) {
                continue;
            }
            if (!empty($filters["category"]) && $filters["category"] !== "All" && ($item["category"] ?? "") !== $filters["category"]) {
                continue;
            }
            if (!empty($filters["status"]) && $filters["status"] !== "All" && ($item["status"] ?? "Active") !== $filters["status"]) {
                continue;
            }
            if (!empty($filters["user_id"]) && (string)($item["user_id"] ?? "") !== (string)$filters["user_id"]) {
                continue;
            }

            $items[] = $item;

            if (!empty($filters["limit"]) && count($items) >= (int)$filters["limit"]) {
                break;
            }
        }
    }

    return $items;
}

/**
 * Get a single item by ID
 */
function getItemById($id) {
    global $conn, $dbAvailable, $itemsJsonFile;

    $idStr = (string)$id;

    if ($dbAvailable && $conn) {
        $stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("s", $idStr);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                return $res->fetch_assoc();
            }
        }
    }

    // JSON fallback
    $items = readJSON($itemsJsonFile);
    foreach ($items as $item) {
        if ((string)($item["id"] ?? "") === $idStr) {
            return $item;
        }
    }

    return null;
}

/**
 * Save a new item (to DB and/or JSON)
 */
function saveItem($itemData) {
    global $conn, $dbAvailable, $itemsJsonFile;

    $id = !empty($itemData["id"]) ? (string)$itemData["id"] : generateID();
    $userId = !empty($itemData["user_id"]) ? (string)$itemData["user_id"] : null;
    $title = trim($itemData["title"] ?? "");
    $category = trim($itemData["category"] ?? "Other");
    $description = trim($itemData["description"] ?? "");
    $location = trim($itemData["location"] ?? "");
    $date = trim($itemData["date"] ?? date("Y-m-d"));
    $type = in_array($itemData["type"] ?? "", ["Lost", "Found"]) ? $itemData["type"] : "Lost";
    $contact = trim($itemData["contact"] ?? "");
    $status = in_array($itemData["status"] ?? "", ["Active", "Resolved"]) ? $itemData["status"] : "Active";
    $image = trim($itemData["image"] ?? "");
    $createdAt = date("Y-m-d H:i:s");

    $itemRecord = [
        "id" => $id,
        "user_id" => $userId,
        "title" => $title,
        "category" => $category,
        "description" => $description,
        "location" => $location,
        "date" => $date,
        "type" => $type,
        "contact" => $contact,
        "status" => $status,
        "image" => $image,
        "created_at" => $createdAt
    ];

    if ($dbAvailable && $conn) {
        $stmt = $conn->prepare("INSERT INTO items (id, user_id, title, category, description, location, date, type, contact, status, image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssssssssss", $id, $userId, $title, $category, $description, $location, $date, $type, $contact, $status, $image, $createdAt);
            $stmt->execute();
        }
    }

    // Always keep JSON synchronized
    $items = readJSON($itemsJsonFile);
    $items[] = $itemRecord;
    writeJSON($itemsJsonFile, $items);

    return $id;
}

/**
 * Update an existing item
 */
function updateItem($id, $itemData) {
    global $conn, $dbAvailable, $itemsJsonFile;

    $idStr = (string)$id;
    $existing = getItemById($idStr);
    if (!$existing) {
        return false;
    }

    $title = isset($itemData["title"]) ? trim($itemData["title"]) : $existing["title"];
    $category = isset($itemData["category"]) ? trim($itemData["category"]) : $existing["category"];
    $description = isset($itemData["description"]) ? trim($itemData["description"]) : $existing["description"];
    $location = isset($itemData["location"]) ? trim($itemData["location"]) : $existing["location"];
    $date = isset($itemData["date"]) ? trim($itemData["date"]) : $existing["date"];
    $type = isset($itemData["type"]) && in_array($itemData["type"], ["Lost", "Found"]) ? $itemData["type"] : $existing["type"];
    $contact = isset($itemData["contact"]) ? trim($itemData["contact"]) : $existing["contact"];
    $status = isset($itemData["status"]) && in_array($itemData["status"], ["Active", "Resolved"]) ? $itemData["status"] : ($existing["status"] ?? "Active");
    $image = isset($itemData["image"]) ? trim($itemData["image"]) : ($existing["image"] ?? "");

    if ($dbAvailable && $conn) {
        $stmt = $conn->prepare("UPDATE items SET title = ?, category = ?, description = ?, location = ?, date = ?, type = ?, contact = ?, status = ?, image = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssssssssss", $title, $category, $description, $location, $date, $type, $contact, $status, $image, $idStr);
            $stmt->execute();
        }
    }

    // Sync to JSON
    $items = readJSON($itemsJsonFile);
    foreach ($items as $idx => $item) {
        if ((string)($item["id"] ?? "") === $idStr) {
            $items[$idx]["title"] = $title;
            $items[$idx]["category"] = $category;
            $items[$idx]["description"] = $description;
            $items[$idx]["location"] = $location;
            $items[$idx]["date"] = $date;
            $items[$idx]["type"] = $type;
            $items[$idx]["contact"] = $contact;
            $items[$idx]["status"] = $status;
            $items[$idx]["image"] = $image;
            break;
        }
    }
    writeJSON($itemsJsonFile, $items);

    return true;
}

/**
 * Delete an item and its associated uploaded image
 */
function deleteItem($id) {
    global $conn, $dbAvailable, $itemsJsonFile;

    $idStr = (string)$id;
    $existing = getItemById($idStr);

    if ($existing && !empty($existing["image"])) {
        $imgPath = dirname(__DIR__) . "/" . ltrim($existing["image"], "/\\");
        if (file_exists($imgPath) && is_file($imgPath)) {
            @unlink($imgPath);
        }
    }

    if ($dbAvailable && $conn) {
        $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("s", $idStr);
            $stmt->execute();
        }
    }

    // Sync to JSON
    $items = readJSON($itemsJsonFile);
    $newItems = [];
    foreach ($items as $item) {
        if ((string)($item["id"] ?? "") !== $idStr) {
            $newItems[] = $item;
        }
    }
    writeJSON($itemsJsonFile, $newItems);

    return true;
}

/**
 * Get system statistics
 */
function getSystemStats() {
    $items = getAllItems();
    $lost = 0;
    $found = 0;
    $resolved = 0;

    foreach ($items as $item) {
        if (($item["type"] ?? "") === "Lost") {
            $lost++;
        } elseif (($item["type"] ?? "") === "Found") {
            $found++;
        }
        if (($item["status"] ?? "") === "Resolved") {
            $resolved++;
        }
    }

    return [
        "lost" => $lost,
        "found" => $found,
        "total" => count($items),
        "resolved" => $resolved
    ];
}

/**
 * Handle secure image uploads
 */
function handleImageUpload($fileInputName, $subDir = "uploads/") {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]["error"] !== UPLOAD_ERR_OK) {
        return ["success" => false, "path" => null, "error" => null];
    }

    $file = $_FILES[$fileInputName];
    $maxSize = 5 * 1024 * 1024; // 5MB limit

    if ($file["size"] > $maxSize) {
        return ["success" => false, "path" => null, "error" => "Image file size must be under 5MB."];
    }

    $allowedExts = ["jpg", "jpeg", "png", "webp", "gif"];
    $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExts)) {
        return ["success" => false, "path" => null, "error" => "Invalid image format. Allowed formats: JPG, PNG, WEBP, GIF."];
    }

    // Validate MIME type with finfo if available
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file["tmp_name"]);
        finfo_close($finfo);
        $allowedMimes = ["image/jpeg", "image/png", "image/webp", "image/gif"];
        if (!in_array($mime, $allowedMimes)) {
            return ["success" => false, "path" => null, "error" => "Uploaded file is not a valid image."];
        }
    }

    $targetDir = dirname(__DIR__) . "/" . trim($subDir, "/\\") . "/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = 'item_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetPath = $targetDir . $fileName;

    if (move_uploaded_file($file["tmp_name"], $targetPath)) {
        $relativePath = trim($subDir, "/\\") . "/" . $fileName;
        return ["success" => true, "path" => $relativePath, "error" => null];
    }

    return ["success" => false, "path" => null, "error" => "Failed to save uploaded image."];
}

/**
 * Clean and escape string output
 */
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}
