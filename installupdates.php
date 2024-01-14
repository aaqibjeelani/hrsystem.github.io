<?php
session_start();
error_reporting(0);

// Include the configuration
include('includes/config.php');

// Fetch update logs from the updatelog table
$sql = "SELECT * FROM updatelog";
$result = $dbh->query($sql);

// Check if any update logs found
if ($result->rowCount() > 0) {
    $updateLogs = $result->fetchAll(PDO::FETCH_ASSOC);
} else {
    $updateLogs = array();
}

// Handle form submission for adding updates required
if (isset($_POST['add_update'])) {
    $update_required = $_POST['update_required'];
    $updates_pending = $_POST['updates_pending'];
    $updates_made = $_POST['updates_made'];
    $update_remarks = $_POST['update_remarks'];

    try {
        // Insert a new update log
        $sqlAddUpdate = "INSERT INTO updatelog (update_required, updates_pending, updates_made, update_remarks)
                         VALUES (:update_required, :updates_pending, :updates_made, :update_remarks)";
        $stmtAddUpdate = $dbh->prepare($sqlAddUpdate);
        $stmtAddUpdate->bindParam(':update_required', $update_required, PDO::PARAM_STR);
        $stmtAddUpdate->bindParam(':updates_pending', (string)$updates_pending, PDO::PARAM_STR);
        $stmtAddUpdate->bindParam(':updates_made', (string)$updates_made, PDO::PARAM_STR);
                $stmtAddUpdate->bindParam(':update_remarks', $update_remarks, PDO::PARAM_STR);
        $stmtAddUpdate->execute();

        $successMessage = "Update added successfully.";
        header("Location: installupdates.php?successMessage=$successMessage"); // Redirect to refresh the page
        exit();
    } catch (PDOException $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// Handle form submission for editing update logs
if (isset($_POST['edit_update'])) {
    $update_id = $_POST['edit_update_id'];

    // Fetch the details of the selected update log
    $sqlFetchUpdate = "SELECT * FROM updatelog WHERE update_id = :update_id";
    $stmtFetchUpdate = $dbh->prepare($sqlFetchUpdate);
    $stmtFetchUpdate->bindParam(':update_id', $update_id, PDO::PARAM_INT);
    $stmtFetchUpdate->execute();

    if ($stmtFetchUpdate->rowCount() > 0) {
        $editUpdate = $stmtFetchUpdate->fetch(PDO::FETCH_ASSOC);
    } else {
        $editUpdate = array();
    }
}

// Handle form submission for updating update logs
if (isset($_POST['update_update'])) {
    $update_id = $_POST['edit_update_id'];
    $update_required = $_POST['edit_update_required'];
    $updates_pending = $_POST['edit_updates_pending'];
    $updates_made = $_POST['edit_updates_made'];
    $update_remarks = $_POST['edit_update_remarks'];

    try {
        // Update the selected update log entry
        $sqlUpdateUpdate = "UPDATE updatelog 
                            SET update_required = :update_required, 
                                updates_pending = :updates_pending, 
                                updates_made = :updates_made, 
                                update_remarks = :update_remarks 
                            WHERE update_id = :update_id";
        $stmtUpdateUpdate = $dbh->prepare($sqlUpdateUpdate);
        $stmtUpdateUpdate->bindParam(':update_required', $update_required, PDO::PARAM_STR);
        $stmtUpdateUpdate->bindParam(':updates_pending', $updates_pending, PDO::PARAM_STR);
        $stmtUpdateUpdate->bindParam(':updates_made', $updates_made, PDO::PARAM_STR);
        $stmtUpdateUpdate->bindParam(':update_remarks', $update_remarks, PDO::PARAM_STR);
        $stmtUpdateUpdate->bindParam(':update_id', $update_id, PDO::PARAM_INT);
        $stmtUpdateUpdate->execute();

        $successMessage = "Update updated successfully.";
        header("Location: installupdates.php?successMessage=$successMessage"); // Redirect to refresh the page
        exit();
    } catch (PDOException $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin | Update Log</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <meta charset="UTF-8">
    <meta name="description" content="Responsive Admin Dashboard Template" />
    <meta name="keywords" content="admin,dashboard" />
    <meta name="author" content="Your Name" />

    <link type="text/css" rel="stylesheet" href="../assets/plugins/materialize/css/materialize.min.css"/>
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="../assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet">
    <link href="../assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
    <link href="../assets/css/custom.css" rel="stylesheet" type="text/css"/>
    <style>
        .success-message {
            color: #4CAF50;
            font-weight: bold;
        }

        .red-row {
            background-color: #D32F2F; /* Dark Red background color */
        }

        .green-row {
            background-color: #C8E6C9; /* Green background color */
        }
    </style>
</head>
<body>

<?php include('includes/header.php'); ?>

<?php include('includes/sidebar.php'); ?>

<main class="mn-inner">
    <div class="row">
        <div class="col s12">
            <div class="page-title">Update Log</div>
        </div>
        <div class="col s12 m12 l12">
            <div class="card">
                <div class="card-content">
                    <?php if (isset($successMessage)): ?>
                        <p class="success-message"><?php echo $successMessage; ?></p>
                    <?php endif; ?>

                   

                    <?php if (!empty($updateLogs)): ?>
                        <!-- Display existing update logs -->
                        <table class="responsive-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Update Required</th>
                                    <th>Updates Pending</th>
                                    <th>Updates Made</th>
                                    <th>Remarks</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($updateLogs as $log): ?>
                                    <?php
// Determine the background color based on the 'Updates Made' value
$rowClass = empty($log['updates_made']) ? 'red-row' : 'green-row';
                                    ?>
                                    <tr class="<?php echo $rowClass; ?>">
                                        <td><?php echo $log['update_id']; ?></td>
                                        <td><?php echo $log['update_date']; ?></td>
                                        <td><?php echo $log['update_required']; ?></td>
                                        <td><?php echo $log['updates_pending']; ?></td>
                                        <td><?php echo $log['updates_made']; ?></td>
                                        <td><?php echo $log['update_remarks']; ?></td>
                                        <td>
                                            <!-- Add an "Edit" button for each row -->
                                            <form method="post" action="installupdates.php">
                                                <input type="hidden" name="edit_update_id" value="<?php echo $log['update_id']; ?>">
                                                <button type="submit" name="edit_update" class="waves-effect waves-light btn">Edit</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No update logs available.</p>
                    <?php endif; ?>

                    <?php if (!empty($editUpdate)): ?>
                        <!-- Display the form for editing the selected update log -->
                        <form method="post" action="installupdates.php">
                            <div class="row">
                                <div class="col s6">
                                    <label for="edit_update_required">Update Required:</label>
                                    <input id="edit_update_required" name="edit_update_required" type="text" class="validate" value="<?php echo $editUpdate['update_required']; ?>" required>
                                </div>
                                <div class="col s6">
                                    <label for="edit_updates_pending">Updates Pending:</label>
                                    <input id="edit_updates_pending" name="edit_updates_pending" type="text" class="validate" value="<?php echo $editUpdate['updates_pending']; ?>" required>
                                </div>
                                <div class="col s6">
                                    <label for="edit_updates_made">Updates Made:</label>
                                    <input id="edit_updates_made" name="edit_updates_made" type="text" class="validate" value="<?php echo $editUpdate['updates_made']; ?>" required>
                                </div>
                                <div class="col s12">
                                    <label for="edit_update_remarks">Remarks:</label>
                                    <textarea id="edit_update_remarks" name="edit_update_remarks" class="materialize-textarea"><?php echo $editUpdate['update_remarks']; ?></textarea>
                                </div>
                                <div class="col s12">
                                    <input type="hidden" name="edit_update_id" value="<?php echo $editUpdate['update_id']; ?>">
                                    <button type="submit" name="update_update" class="waves-effect waves-light btn">Update Update</button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="../assets/plugins/jquery/jquery-2.2.0.min.js"></script>
<script src="../assets/plugins/materialize/js/materialize.min.js"></script>
<script src="../assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
<script src="../assets/plugins/jquery-blockui/jquery.blockui.js"></script>
<script src="../assets/js/alpha.min.js"></script>
</body>
</html>
