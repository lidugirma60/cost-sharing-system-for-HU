<?php
session_start();
require_once '../Config/db.php';
if (!isset($_SESSION['student_id'])) header("Location: login.php");

$sid = $_SESSION['student_id'];
$costs_query = "SELECT ac.*, cc.name as category_name 
                FROM assigned_costs ac 
                JOIN cost_categories cc ON ac.cost_type_id = cc.id 
                WHERE ac.student_id = $sid 
                ORDER BY ac.academic_year DESC";
$results = mysqli_query($conn, $costs_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Login | Portal Access</title>
</head>
<body class="bg-gray-50 flex">
    <main class="flex-1 p-8">
        <h2 class="text-2xl font-bold mb-6">Financial Overview</h2>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-gray-600 text-sm uppercase">
                    <tr>
                        <th class="p-4 border-b">Category</th>
                        <th class="p-4 border-b text-center">Year</th>
                        <th class="p-4 border-b text-right">Amount</th>
                        <th class="p-4 border-b text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($results)): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4 border-b font-medium"><?php echo $row['category_name']; ?></td>
                        <td class="p-4 border-b text-center"><?php echo $row['academic_year']; ?></td>
                        <td class="p-4 border-b text-right text-blue-700 font-bold">$<?php echo number_format($row['amount'], 2); ?></td>
                        <td class="p-4 border-b text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold 
                                <?php echo $row['status'] == 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'; ?>">
                                <?php echo strtoupper($row['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>