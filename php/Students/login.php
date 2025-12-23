<?php
session_start();
require_once '../Config/db.php';

if (isset($_POST['login'])) {
    // Sanitize username input
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Fetch the student record by username
    $query = "SELECT * FROM students WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        // Use password_verify to check the plain-text input against the hashed password
        if (password_verify($password, $row['password'])) {
            // Regeneration of session ID is recommended for security
            session_regenerate_id(true);
            
            $_SESSION['student_id'] = $row['id'];
            $_SESSION['student_name'] = $row['name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Username not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Student Login | OCSM</title>
</head>
<body class="bg-slate-100 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-slate-200">
        <div class="text-center mb-8">
            <div class="bg-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <i class="fas fa-user-graduate text-white text-2xl"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-slate-800">Student Portal</h2>
            <p class="text-slate-500 mt-2">Sign in to manage your costs</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-red-50 text-red-600 p-4 mb-6 rounded-xl border border-red-100 flex items-center space-x-3">
                <i class="fas fa-exclamation-circle"></i>
                <span class="text-sm font-medium"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" name="username" 
                           class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                           placeholder="Enter your username" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password" 
                           class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                           placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" name="login" 
                    class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-blue-700 transform hover:-translate-y-0.5 transition-all shadow-md">
                Sign In
            </button>
        </form>

        <div class="mt-8 text-center text-slate-400 text-xs">
            &copy; 2025 OCSM System. All rights reserved.
        </div>
    </div>

</body>
</html>