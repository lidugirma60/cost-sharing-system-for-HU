<?php
session_start();
require_once '../Config/db.php';
if (!isset($_SESSION['student_id'])) header("Location: login.php");

$sid = $_SESSION['student_id'];
// Fetch student info and department name via JOIN
$sql = "SELECT s.*, d.name as dept_name FROM students s 
        JOIN departments d ON s.department_id = d.id 
        WHERE s.id = $sid";
$student = mysqli_fetch_assoc(mysqli_query($conn, $sql));

// Profile Image Upload Logic
if (isset($_POST['upload'])) {
    $dir = "../uploads/student/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    
    $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
    $new_name = "student_" . $sid . "_" . time() . "." . $ext;
    
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $dir . $new_name)) {
        // We will store the image name in the tin_number column for now as a placeholder
        mysqli_query($conn, "UPDATE students SET tin_number = '$new_name' WHERE id = $sid");
        header("Location: dashboard.php");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Student Dashboard</title>
</head>
<body class="bg-gray-50 flex">
    <div class="w-64 bg-slate-900 min-h-screen text-white p-6">
        <div class="text-xl font-bold mb-8 border-b border-slate-700 pb-4">OCSM Portal</div>
        <nav class="space-y-4">
            <a href="dashboard.php" class="flex items-center space-x-3 p-3 bg-blue-600 rounded-lg">
                <i class="fas fa-user-circle"></i> <span>Profile</span>
            </a>
            <a href="my_costs.php" class="flex items-center space-x-3 p-3 hover:bg-slate-800 rounded-lg">
                <i class="fas fa-file-invoice-dollar"></i> <span>My Costs</span>
            </a>
            <a href="logout.php" class="flex items-center space-x-3 p-3 text-red-400 hover:bg-red-900/20 rounded-lg mt-10">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </nav>
    </div>

    <main class="flex-1 p-8">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold">Welcome, <?php echo $student['name']; ?>!</h1>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <div class="text-center">
                    <img src="<?php echo $student['tin_number'] ? '../uploads/student/'.$student['tin_number'] : '../upload/student/default.png'; ?>" 
                         class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-blue-50">
                    <form method="POST" enctype="multipart/form-data" class="mt-4">
                        <input type="file" name="image" class="text-xs mb-2 block w-full">
                        <button name="upload" class="bg-gray-800 text-white text-xs px-3 py-1 rounded">Update Photo</button>
                    </form>
                </div>
            </div>

            <div class="md:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <h3 class="text-lg font-bold mb-4 border-b pb-2">Academic Profile</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-500 text-sm">ID Number</p>
                        <p class="font-medium"><?php echo $student['id_number']; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Department</p>
                        <p class="font-medium"><?php echo $student['dept_name']; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Batch Year</p>
                        <p class="font-medium"><?php echo $student['batch']; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Contact Phone</p>
                        <p class="font-medium"><?php echo $student['phone']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>