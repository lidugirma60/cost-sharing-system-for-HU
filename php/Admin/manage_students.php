<?php
session_start();
require '../Config/db.php';

if (!isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$dept_id = $_SESSION['department_id']; 

/*
|--------------------------------------------------------------------------
| LOGIC: ADD / UPDATE STUDENT
|--------------------------------------------------------------------------
*/
if (isset($_POST['save_student'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $id_num = $_POST['id_number'];
    $batch = $_POST['batch'];
    $region = $_POST['region'];
    $woreda = $_POST['woreda'];
    $tin = $_POST['tin_number'];
    
    // New Fields
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($_POST['student_id'])) {
        // UPDATE EXISTING
        $id = $_POST['student_id'];
        // We only update password if a new one is provided
        if (!empty($password)) {
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE students SET name=?, phone=?, id_number=?, batch=?, region=?, woreda=?, tin_number=?, username=?, password=? WHERE id=? AND department_id=?");
            $stmt->bind_param("sssisssssii", $name, $phone, $id_num, $batch, $region, $woreda, $tin, $username, $hashed_pass, $id, $dept_id);
        } else {
            $stmt = $conn->prepare("UPDATE students SET name=?, phone=?, id_number=?, batch=?, region=?, woreda=?, tin_number=?, username=? WHERE id=? AND department_id=?");
            $stmt->bind_param("sssissssii", $name, $phone, $id_num, $batch, $region, $woreda, $tin, $username, $id, $dept_id);
        }
        $action = "updated";
    } else {
        // INSERT NEW
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO students (name, phone, id_number, department_id, batch, region, woreda, tin_number, username, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiisssss", $name, $phone, $id_num, $dept_id, $batch, $region, $woreda, $tin, $username, $hashed_pass);
        $action = "registered";
    }

    if ($stmt->execute()) {
        $message = "Student $action successfully!";
    } else {
        $message = "Error: Process failed. Check if ID Number or Username is unique.";
    }
}

// --- Logic: Delete Student ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM students WHERE id = $id AND department_id = $dept_id");
    header("Location: manage_students.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Manage Students</title>
</head>
<body class="bg-slate-50 flex">

    <?php include './sidebar.php'; ?>

    <main class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-slate-800">Student Records</h2>
            <button onclick="openModal()" class="bg-green-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-green-700 shadow-lg transition">
                <i class="fas fa-plus mr-2"></i> Register Student
            </button>
        </div>

        <?php if($message): ?>
            <div class="bg-emerald-100 text-emerald-700 p-4 mb-6 rounded-lg border border-emerald-200"><?= $message ?></div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="p-4">ID Number</th>
                        <th class="p-4">Username</th>
                        <th class="p-4">Full Name</th>
                        <th class="p-4">Phone</th>
                        <th class="p-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php 
                    $res = $conn->query("SELECT * FROM students WHERE department_id = $dept_id ORDER BY id DESC");
                    while($row = $res->fetch_assoc()): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="p-4 font-mono font-bold text-blue-600"><?= $row['id_number'] ?></td>
                        <td class="p-4 font-semibold text-slate-500"><?= $row['username'] ?></td>
                        <td class="p-4 font-semibold"><?= $row['name'] ?></td>
                        <td class="p-4"><?= $row['phone'] ?></td>
                        <td class="p-4 text-center">
                            <button onclick='editStudent(<?= json_encode($row) ?>)' class="text-blue-500 hover:text-blue-700 mx-2">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this student?')" class="text-red-400 hover:text-red-600 mx-2">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="studentModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-3xl p-8 max-w-2xl w-full shadow-2xl overflow-y-auto max-h-[90vh]">
            <h3 id="modalTitle" class="text-2xl font-bold text-slate-800 mb-6">Register Student</h3>
            
            <form method="POST" id="studentForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="student_id" id="form_student_id">
                
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-slate-600 mb-1">Full Name</label>
                    <input type="text" name="name" id="form_name" class="w-full border p-3 rounded-xl bg-slate-50" required>
                </div>
                
                <div class="bg-blue-50 p-4 rounded-2xl col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 border border-blue-100 mb-2">
                    <div class="col-span-2 text-blue-800 font-bold text-sm uppercase tracking-wider">Login Credentials</div>
                    <div>
                        <label class="block text-sm font-bold text-slate-600 mb-1">Username</label>
                        <input type="text" name="username" id="form_username" class="w-full border p-3 rounded-xl bg-white" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-600 mb-1">Password</label>
                        <input type="password" name="password" id="form_password" placeholder="Leave blank to keep current" class="w-full border p-3 rounded-xl bg-white">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-600 mb-1">ID Number</label>
                    <input type="text" name="id_number" id="form_id_number" onkeyup="syncUsername(this.value)" class="w-full border p-3 rounded-xl bg-slate-50" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-600 mb-1">Phone (Default Password)</label>
                    <input type="text" name="phone" id="form_phone" class="w-full border p-3 rounded-xl bg-slate-50" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-600 mb-1">Batch (Year)</label>
                    <input type="number" name="batch" id="form_batch" class="w-full border p-3 rounded-xl bg-slate-50" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-600 mb-1">TIN Number</label>
                    <input type="text" name="tin_number" id="form_tin" class="w-full border p-3 rounded-xl bg-slate-50">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-600 mb-1">Region</label>
                    <input type="text" name="region" id="form_region" class="w-full border p-3 rounded-xl bg-slate-50">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-600 mb-1">Woreda</label>
                    <input type="text" name="woreda" id="form_woreda" class="w-full border p-3 rounded-xl bg-slate-50">
                </div>

                <div class="col-span-2 mt-4 flex space-x-3">
                    <button type="button" onclick="closeModal()" class="flex-1 bg-slate-100 text-slate-600 font-bold py-4 rounded-xl">Cancel</button>
                    <button type="submit" name="save_student" class="flex-1 bg-green-600 text-white font-bold py-4 rounded-xl hover:bg-green-700 shadow-lg">Save Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const modal = document.getElementById('studentModal');
    const form = document.getElementById('studentForm');

    function openModal() {
        form.reset();
        document.getElementById('form_student_id').value = "";
        document.getElementById('modalTitle').innerText = "Register New Student";
        document.getElementById('form_password').required = true; // Required for new students
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    // Automatically fill username as the ID Number for convenience
    function syncUsername(val) {
        const idField = document.getElementById('form_student_id').value;
        if(!idField) { // Only auto-sync if it's a new registration
            document.getElementById('form_username').value = val;
        }
    }

    function editStudent(data) {
        openModal();
        document.getElementById('modalTitle').innerText = "Edit Student Profile";
        document.getElementById('form_password').required = false; // Not required for edits
        
        document.getElementById('form_student_id').value = data.id;
        document.getElementById('form_name').value = data.name;
        document.getElementById('form_username').value = data.username;
        document.getElementById('form_id_number').value = data.id_number;
        document.getElementById('form_phone').value = data.phone;
        document.getElementById('form_batch').value = data.batch;
        document.getElementById('form_tin').value = data.tin_number;
        document.getElementById('form_region').value = data.region;
        document.getElementById('form_woreda').value = data.woreda;
    }
    </script>
</body>
</html>