<?php
session_start();
require '../Config/db.php';


if (!isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$admin_id = $_SESSION['admin_id'];
$adminDir = "../uploads/admin";

if (!is_dir($adminDir)) mkdir($adminDir, 0755, true);

/*
|--------------------------------------------------------------------------
| PROFILE UPDATE LOGIC
|--------------------------------------------------------------------------
*/
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $username = trim($_POST['username']);

    // Handle Image Upload
    if (!empty($_FILES['profile_image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $img_name = "adm_" . time() . "_" . uniqid() . "." . $ext;
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $adminDir . "/" . $img_name)) {
            $conn->query("UPDATE admins SET profile_image = '$img_name' WHERE id = $admin_id");
            $_SESSION['profile_img'] = $img_name; // Update session image
        }
    }

    // Handle Password Change
    if (!empty($_POST['new_password'])) {
        $new_pass = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        $conn->query("UPDATE admins SET password = '$new_pass' WHERE id = $admin_id");
    }

    // Update Basic Info
    $stmt = $conn->prepare("UPDATE admins SET name = ?, phone = ?, username = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $phone, $username, $admin_id);
    if ($stmt->execute()) {
        $_SESSION['admin_name'] = $name;
        $message = "Profile updated successfully!";
    }
}

// Fetch Admin & Department Info
$query = "SELECT admins.*, departments.name as dept_name 
          FROM admins 
          LEFT JOIN departments ON admins.department_id = departments.id 
          WHERE admins.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_data = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Dashboard | Admin Profile</title>
</head>
<body class="bg-slate-50 font-sans flex">

    <?php include './sidebar.php'; ?>

    <div class="flex-1 p-8">
        <div class="max-w-5xl mx-auto">
            
            <div class="mb-8">
                <h2 class="text-3xl font-extrabold text-slate-800">Welcome, <?= htmlspecialchars($admin_data['name']) ?></h2>
                <p class="text-slate-500">Manage your profile and department settings for <span class="text-green-600 font-bold"><?= htmlspecialchars($admin_data['dept_name'] ?? 'Unassigned') ?></span></p>
            </div>

            <?php if($message): ?>
                <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r-xl mb-6 shadow-sm">
                    <p class="font-bold">Success!</p>
                    <p class="text-sm"><?= $message ?></p>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 overflow-hidden border border-slate-100">
                <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3">
                    
                    <div class="bg-slate-900 p-10 text-center text-white flex flex-col items-center justify-center">
                        <div class="relative group">
                            <img src="../uploads/admin/<?= $admin_data['profile_image'] ?: 'default.png' ?>" 
                                 class="w-44 h-44 rounded-full object-cover border-4 border-slate-700 shadow-2xl transition group-hover:scale-105 duration-300">
                            <div class="absolute inset-0 rounded-full flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition duration-300">
                                <i class="fas fa-camera text-2xl"></i>
                            </div>
                        </div>
                        <h3 class="mt-6 text-xl font-bold"><?= htmlspecialchars($admin_data['name']) ?></h3>
                        <span class="text-green-400 text-xs uppercase tracking-widest font-bold px-3 py-1 bg-green-400/10 rounded-full mt-2">
                             <?= htmlspecialchars($admin_data['dept_name'] ?? 'No Dept') ?>
                        </span>
                        
                        <div class="mt-8 w-full space-y-3">
                            <label class="block text-xs text-slate-400 uppercase font-bold text-left">Upload New Photo</label>
                            <input type="file" name="profile_image" class="text-xs text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700 cursor-pointer">
                        </div>
                    </div>

                    <div class="lg:col-span-2 p-10 space-y-6">
                        <h4 class="text-lg font-bold text-slate-800 border-b pb-4">Personal Information</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-600 mb-2">Full Name</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($admin_data['name']) ?>" 
                                       class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold text-slate-600 mb-2">Username</label>
                                <input type="text" name="username" value="<?= htmlspecialchars($admin_data['username']) ?>" 
                                       class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition" required>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-600 mb-2">Phone Number</label>
                                <input type="text" name="phone" value="<?= htmlspecialchars($admin_data['phone']) ?>" 
                                       class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-red-500 mb-2">New Password <small class="text-slate-400 font-normal ml-2">(Optional)</small></label>
                                <input type="password" name="new_password" placeholder="Change security key" 
                                       class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-red-400 focus:border-transparent outline-none transition">
                            </div>
                        </div>

                        <div class="pt-6">
                            <button type="submit" name="update_profile" 
                                    class="w-full md:w-auto bg-green-600 text-white font-bold px-10 py-4 rounded-xl hover:bg-green-700 shadow-lg shadow-green-600/30 transition transform active:scale-95">
                                Update My Profile
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>