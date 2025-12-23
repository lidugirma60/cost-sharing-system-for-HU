<?php
session_start();
require '../Config/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 1. Check the 'admins' table first (Department Admins)
    $stmt = $conn->prepare("SELECT id, name, password, department_id, profile_image FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['dept_id'] = $user['department_id'];
            $_SESSION['profile_img'] = $user['profile_image'];
            $_SESSION['department_id'] = $user['department_id'];

            $_SESSION['is_admin'] = true;

            $_SESSION['role'] = 'admin';
            
            header("Location: dashboard.php");
            exit();
        }
    }

    // 2. If not found in admins, check 'SuperAdmin' table
    $stmt = $conn->prepare("SELECT id, name, password, profile_image FROM SuperAdmin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['is_admin'] = true; // SuperAdmin flag
            $_SESSION['role'] = 'superadmin';
            $_SESSION['profile_img'] = $user['profile_image'];

            header("Location: dashboard.php");
            exit();
        }
    }

    $error = "Invalid username or password!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Login | Portal Access</title>
</head>
<body class="bg-slate-100 h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-gray-100">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-slate-800">Welcome Back</h1>
            <p class="text-gray-500 mt-2">Sign in to manage your department</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="text-sm font-medium"><?= $error ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Username</label>
                <input type="text" name="username" required 
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required 
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition">
            </div>

            <button type="submit" 
                    class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl hover:bg-slate-800 transition duration-300 transform active:scale-[0.98]">
                Login to Portal
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-widest font-semibold">Authorized Personnel Only</p>
        </div>
    </div>

</body>
</html>