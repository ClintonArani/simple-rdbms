<?php
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Core/Table.php';
require_once __DIR__ . '/../src/Parser/SQLParser.php';
require_once __DIR__ . '/../src/Engine/QueryEngine.php';

$engine = new QueryEngine();
$parser = new SQLParser();

$message = '';
$error = '';

// Function to escape SQL values PROPERLY
function escapeSqlValue($value) {
    // Only escape backslashes, NOT quotes
    $value = str_replace('\\', '\\\\', $value);
    return $value;
}

function execSQL($engine, $parser, $sql) {
    return $engine->execute($parser->parse($sql));
}

/* Handle actions */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($_POST['action'] === 'create_user') {
            // DON'T escape quotes, just backslashes
            $name = escapeSqlValue($_POST['name']);
            $email = escapeSqlValue($_POST['email']);
            $message = execSQL($engine, $parser,
                sprintf(
                    'INSERT INTO users VALUES (%d,"%s","%s")',
                    (int)$_POST['id'], $name, $email
                )
            );
        }

        if ($_POST['action'] === 'update_user') {
            // DON'T escape quotes, just backslashes
            $name = escapeSqlValue($_POST['name']);
            $email = escapeSqlValue($_POST['email']);
            $message = execSQL($engine, $parser,
                sprintf(
                    'UPDATE users SET name="%s", email="%s" WHERE id=%d',
                    $name, $email, (int)$_POST['id']
                )
            );
        }

        if ($_POST['action'] === 'delete_user') {
            $message = execSQL($engine, $parser,
                'DELETE FROM users WHERE id=' . (int)$_POST['id']
            );
        }

        if ($_POST['action'] === 'create_task') {
            // DON'T escape quotes, just backslashes
            $title = escapeSqlValue($_POST['title']);
            $status = escapeSqlValue($_POST['status']);
            $message = execSQL($engine, $parser,
                sprintf(
                    'INSERT INTO tasks VALUES (%d,"%s","%s",%d)',
                    (int)$_POST['id'], $title, $status, (int)$_POST['user_id']
                )
            );
        }

        if ($_POST['action'] === 'update_task') {
            $title = escapeSqlValue($_POST['title']);
            $status = escapeSqlValue($_POST['status']);
            $message = execSQL($engine, $parser,
                sprintf(
                    'UPDATE tasks SET title="%s", status="%s", user_id=%d WHERE id=%d',
                    $title, $status, (int)$_POST['user_id'], (int)$_POST['id']
                )
            );
        }

        if ($_POST['action'] === 'delete_task') {
            $message = execSQL($engine, $parser,
                'DELETE FROM tasks WHERE id=' . (int)$_POST['id']
            );
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

/* Fetch data */
$users = $engine->execute($parser->parse('SELECT * FROM users'));
$tasks = $engine->execute($parser->parse('SELECT * FROM tasks'));
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Custom PHP RDBMS Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
:root {
    --primary: #22c55e;
    --primary-dark: #16a34a;
    --danger: #ef4444;
    --danger-dark: #dc2626;
    --warning: #f59e0b;
    --info: #3b82f6;
    --dark: #020617;
    --darker: #0f172a;
    --light: #e5e7eb;
    --gray: #334155;
    --gray-light: #475569;
    --success-bg: #14532d;
    --error-bg: #7f1d1d;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    background: linear-gradient(135deg, var(--darker) 0%, #1e293b 100%);
    color: var(--light);
    min-height: 100vh;
}

/* Header */
header {
    background: linear-gradient(90deg, var(--dark) 0%, #111827 100%);
    padding: 1.5rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    border-bottom: 1px solid var(--primary);
}

.logo {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.logo i {
    font-size: 2rem;
    color: var(--primary);
}

.logo-text h1 {
    font-size: 1.8rem;
    font-weight: 700;
    background: linear-gradient(90deg, var(--primary), #4ade80);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.logo-text p {
    font-size: 0.9rem;
    color: #94a3b8;
}

.header-stats {
    display: flex;
    gap: 2rem;
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary);
}

.stat-label {
    font-size: 0.9rem;
    color: #94a3b8;
}

/* Container */
.container {
    padding: 2rem;
    max-width: 1600px;
    margin: 0 auto;
}

/* Dashboard Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(600px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

@media (max-width: 1300px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

/* Cards */
.card {
    background: linear-gradient(145deg, rgba(2, 6, 23, 0.9), rgba(15, 23, 42, 0.9));
    border-radius: 16px;
    padding: 1.75rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(34, 197, 94, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
    border-color: rgba(34, 197, 94, 0.2);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray);
}

.card-title {
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.card-title i {
    color: var(--primary);
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(90deg, var(--primary), var(--primary-dark));
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(90deg, var(--primary-dark), #15803d);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(34, 197, 94, 0.3);
}

.btn-danger {
    background: linear-gradient(90deg, var(--danger), var(--danger-dark));
    color: white;
}

.btn-danger:hover {
    background: linear-gradient(90deg, var(--danger-dark), #b91c1c);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
}

.btn-warning {
    background: linear-gradient(90deg, var(--warning), #d97706);
    color: white;
}

.btn-warning:hover {
    background: linear-gradient(90deg, #d97706, #b45309);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.3);
}

.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.85rem;
}

.btn-icon {
    padding: 0.5rem;
    border-radius: 8px;
    min-width: 36px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
}

/* Tables */
.table-container {
    overflow-x: auto;
    border-radius: 10px;
    border: 1px solid var(--gray);
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
}

thead {
    background: linear-gradient(90deg, rgba(34, 197, 94, 0.1), rgba(34, 197, 94, 0.05));
}

th {
    padding: 1rem 1.25rem;
    text-align: left;
    font-weight: 600;
    color: var(--primary);
    border-bottom: 2px solid var(--primary);
}

td {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--gray);
}

tbody tr {
    transition: background-color 0.2s ease;
}

tbody tr:hover {
    background-color: rgba(34, 197, 94, 0.05);
}

.status-badge {
    display: inline-block;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: capitalize;
}

.status-pending {
    background-color: rgba(245, 158, 11, 0.2);
    color: #fbbf24;
}

.status-in-progress {
    background-color: rgba(59, 130, 246, 0.2);
    color: #60a5fa;
}

.status-done {
    background-color: rgba(34, 197, 94, 0.2);
    color: #4ade80;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

/* Messages */
.message {
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    animation: slideIn 0.5s ease;
}

.message.success {
    background: linear-gradient(90deg, var(--success-bg), #166534);
    border-left: 4px solid var(--primary);
}

.message.error {
    background: linear-gradient(90deg, var(--error-bg), #991b1b);
    border-left: 4px solid var(--danger);
}

.message-close {
    background: transparent;
    border: none;
    color: inherit;
    cursor: pointer;
    font-size: 1.2rem;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.message-close:hover {
    opacity: 1;
}

@keyframes slideIn {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Modals */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    align-items: center;
    justify-content: center;
    z-index: 1000;
    backdrop-filter: blur(5px);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: linear-gradient(145deg, var(--dark), #111827);
    padding: 2rem;
    width: 90%;
    max-width: 500px;
    border-radius: 16px;
    border: 1px solid var(--gray);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    animation: modalSlideIn 0.4s ease;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(-30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray);
}

.modal-title {
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.modal-close {
    background: transparent;
    border: none;
    color: var(--light);
    font-size: 1.5rem;
    cursor: pointer;
    transition: color 0.2s;
}

.modal-close:hover {
    color: var(--primary);
}

/* Form */
.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #cbd5e1;
}

.form-control {
    width: 100%;
    padding: 0.875rem 1rem;
    background: rgba(15, 23, 42, 0.7);
    border: 1px solid var(--gray);
    border-radius: 10px;
    color: var(--light);
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
}

.form-control::placeholder {
    color: #64748b;
}

select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 16px;
    padding-right: 2.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.form-actions .btn {
    flex: 1;
    justify-content: center;
}

/* Confirmation Modal */
.confirmation-modal .modal-content {
    max-width: 400px;
    text-align: center;
}

.confirmation-icon {
    font-size: 4rem;
    color: var(--danger);
    margin-bottom: 1.5rem;
}

.confirmation-text h3 {
    font-size: 1.5rem;
    margin-bottom: 0.75rem;
}

.confirmation-text p {
    color: #94a3b8;
    line-height: 1.6;
}

.confirmation-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #64748b;
}

.empty-state i {
    font-size: 3.5rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: #cbd5e1;
}

/* Footer */
footer {
    text-align: center;
    padding: 1.5rem;
    margin-top: 2rem;
    color: #94a3b8;
    font-size: 0.9rem;
    border-top: 1px solid var(--gray);
}
</style>
</head>

<body>

<header>
    <div class="logo">
        <i class="fas fa-database"></i>
        <div class="logo-text">
            <h1>Custom PHP RDBMS</h1>
            <p>Database Management Dashboard</p>
        </div>
    </div>
    <div class="header-stats">
        <div class="stat-item">
            <div class="stat-value"><?php echo count($users); ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?php echo count($tasks); ?></div>
            <div class="stat-label">Total Tasks</div>
        </div>
    </div>
</header>

<div class="container">
    <?php if($message): ?>
    <div class="message success">
        <div><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?></div>
        <button class="message-close">&times;</button>
    </div>
    <?php endif; ?>
    
    <?php if($error): ?>
    <div class="message error">
        <div><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <button class="message-close">&times;</button>
    </div>
    <?php endif; ?>
    
    <div class="dashboard-grid">
        <!-- USERS SECTION -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-users"></i> User Management</h2>
                <button class="btn btn-primary" onclick="openModal('createUser')">
                    <i class="fas fa-plus"></i> Create User
                </button>
            </div>
            
            <?php if(empty($users)): ?>
            <div class="empty-state">
                <i class="fas fa-users-slash"></i>
                <h3>No Users Found</h3>
                <p>Create your first user to get started</p>
                <button class="btn btn-primary mt-2" onclick="openModal('createUser')">
                    <i class="fas fa-plus"></i> Create User
                </button>
            </div>
            <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $u): ?>
                        <tr>
                            <td><strong>#<?php echo htmlspecialchars($u['id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-warning btn-icon" 
                                            onclick="openEditUserModal(<?php echo $u['id']; ?>, '<?php echo addslashes($u['name']); ?>', '<?php echo addslashes($u['email']); ?>')"
                                            title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-icon" 
                                            onclick="confirmDelete('user', <?php echo $u['id']; ?>, '<?php echo addslashes($u['name']); ?>')"
                                            title="Delete User">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- TASKS SECTION -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-tasks"></i> Task Management</h2>
                <button class="btn btn-primary" onclick="openModal('createTask')">
                    <i class="fas fa-plus"></i> Create Task
                </button>
            </div>
            
            <?php if(empty($tasks)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h3>No Tasks Found</h3>
                <p>Create your first task to get started</p>
                <button class="btn btn-primary mt-2" onclick="openModal('createTask')">
                    <i class="fas fa-plus"></i> Create Task
                </button>
            </div>
            <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tasks as $t): 
                            // Find user name for this task
                            $user_name = "Unknown";
                            foreach($users as $u) {
                                if($u['id'] == $t['user_id']) {
                                    $user_name = $u['name'];
                                    break;
                                }
                            }
                        ?>
                        <tr>
                            <td><strong>#<?php echo htmlspecialchars($t['id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($t['title']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo str_replace(' ', '-', strtolower($t['status'])); ?>">
                                    <?php echo htmlspecialchars($t['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($user_name); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-warning btn-icon" 
                                            onclick="openEditTaskModal(<?php echo $t['id']; ?>, '<?php echo addslashes($t['title']); ?>', '<?php echo addslashes($t['status']); ?>', <?php echo $t['user_id']; ?>)"
                                            title="Edit Task">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-icon" 
                                            onclick="confirmDelete('task', <?php echo $t['id']; ?>, '<?php echo addslashes($t['title']); ?>')"
                                            title="Delete Task">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- CREATE USER MODAL -->
<div class="modal" id="createUser">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-user-plus"></i> Create New User</h3>
            <button class="modal-close" onclick="closeModal('createUser')">&times;</button>
        </div>
        <form method="post">
            <div class="form-group">
                <label for="user_id">User ID</label>
                <input type="number" class="form-control" id="user_id" name="id" placeholder="Enter unique ID" required min="1">
            </div>
            <div class="form-group">
                <label for="user_name">Full Name</label>
                <input type="text" class="form-control" id="user_name" name="name" placeholder="Enter user's full name" required>
            </div>
            <div class="form-group">
                <label for="user_email">Email Address</label>
                <input type="email" class="form-control" id="user_email" name="email" placeholder="Enter user's email" required>
            </div>
            <input type="hidden" name="action" value="create_user">
            <div class="form-actions">
                <button type="button" class="btn" onclick="closeModal('createUser')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT USER MODAL -->
<div class="modal" id="editUser">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-user-edit"></i> Edit User</h3>
            <button class="modal-close" onclick="closeModal('editUser')">&times;</button>
        </div>
        <form method="post">
            <div class="form-group">
                <label for="edit_user_id">User ID</label>
                <input type="number" class="form-control" id="edit_user_id" name="id" readonly>
            </div>
            <div class="form-group">
                <label for="edit_user_name">Full Name</label>
                <input type="text" class="form-control" id="edit_user_name" name="name" placeholder="Enter user's full name" required>
            </div>
            <div class="form-group">
                <label for="edit_user_email">Email Address</label>
                <input type="email" class="form-control" id="edit_user_email" name="email" placeholder="Enter user's email" required>
            </div>
            <input type="hidden" name="action" value="update_user">
            <div class="form-actions">
                <button type="button" class="btn" onclick="closeModal('editUser')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>

<!-- CREATE TASK MODAL -->
<div class="modal" id="createTask">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-tasks"></i> Create New Task</h3>
            <button class="modal-close" onclick="closeModal('createTask')">&times;</button>
        </div>
        <form method="post">
            <div class="form-group">
                <label for="task_id">Task ID</label>
                <input type="number" class="form-control" id="task_id" name="id" placeholder="Enter unique ID" required min="1">
            </div>
            <div class="form-group">
                <label for="task_title">Task Title</label>
                <input type="text" class="form-control" id="task_title" name="title" placeholder="Enter task title" required>
            </div>
            <div class="form-group">
                <label for="task_status">Status</label>
                <select class="form-control" id="task_status" name="status" required>
                    <option value="">Select status</option>
                    <option value="pending">Pending</option>
                    <option value="in-progress">In Progress</option>
                    <option value="done">Done</option>
                </select>
            </div>
            <div class="form-group">
                <label for="task_user_id">Assign To User</label>
                <select class="form-control" id="task_user_id" name="user_id" required>
                    <option value="">Select user</option>
                    <?php foreach($users as $u): ?>
                    <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if(empty($users)): ?>
                <small style="color: #ef4444; display: block; margin-top: 0.5rem;">
                    <i class="fas fa-exclamation-triangle"></i> No users available. Please create a user first.
                </small>
                <?php endif; ?>
            </div>
            <input type="hidden" name="action" value="create_task">
            <div class="form-actions">
                <button type="button" class="btn" onclick="closeModal('createTask')">Cancel</button>
                <button type="submit" class="btn btn-primary" <?php echo empty($users) ? 'disabled' : ''; ?>>Create Task</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT TASK MODAL -->
<div class="modal" id="editTask">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-edit"></i> Edit Task</h3>
            <button class="modal-close" onclick="closeModal('editTask')">&times;</button>
        </div>
        <form method="post">
            <div class="form-group">
                <label for="edit_task_id">Task ID</label>
                <input type="number" class="form-control" id="edit_task_id" name="id" readonly>
            </div>
            <div class="form-group">
                <label for="edit_task_title">Task Title</label>
                <input type="text" class="form-control" id="edit_task_title" name="title" placeholder="Enter task title" required>
            </div>
            <div class="form-group">
                <label for="edit_task_status">Status</label>
                <select class="form-control" id="edit_task_status" name="status" required>
                    <option value="pending">Pending</option>
                    <option value="in-progress">In Progress</option>
                    <option value="done">Done</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_task_user_id">Assign To User</label>
                <select class="form-control" id="edit_task_user_id" name="user_id" required>
                    <?php foreach($users as $u): ?>
                    <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?> (ID: <?php echo $u['id']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="hidden" name="action" value="update_task">
            <div class="form-actions">
                <button type="button" class="btn" onclick="closeModal('editTask')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Task</button>
            </div>
        </form>
    </div>
</div>

<!-- DELETE CONFIRMATION MODAL -->
<div class="modal confirmation-modal" id="deleteConfirm">
    <div class="modal-content">
        <div class="confirmation-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="confirmation-text">
            <h3>Confirm Deletion</h3>
            <p id="delete-message">Are you sure you want to delete this item? This action cannot be undone.</p>
        </div>
        <form method="post" id="delete-form">
            <input type="hidden" name="id" id="delete-id">
            <input type="hidden" name="action" id="delete-action">
            <div class="confirmation-actions">
                <button type="button" class="btn" onclick="closeModal('deleteConfirm')">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>

<footer>
    <p>Custom PHP RDBMS Dashboard &copy; <?php echo date('Y'); ?> | Built with PHP & Custom Query Engine</p>
</footer>

<script>
// Modal functions
function openModal(id) {
    document.getElementById(id).style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modals when clicking outside
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal(this.id);
        }
    });
});

// Close message alerts
document.querySelectorAll('.message-close').forEach(btn => {
    btn.addEventListener('click', function() {
        this.closest('.message').style.display = 'none';
    });
});

// Auto-hide messages after 5 seconds
setTimeout(() => {
    document.querySelectorAll('.message').forEach(msg => {
        msg.style.display = 'none';
    });
}, 5000);

// Edit user modal
function openEditUserModal(id, name, email) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_user_name').value = name;
    document.getElementById('edit_user_email').value = email;
    openModal('editUser');
}

// Edit task modal
function openEditTaskModal(id, title, status, userId) {
    document.getElementById('edit_task_id').value = id;
    document.getElementById('edit_task_title').value = title;
    document.getElementById('edit_task_status').value = status;
    document.getElementById('edit_task_user_id').value = userId;
    openModal('editTask');
}

// Delete confirmation
function confirmDelete(type, id, name) {
    const modal = document.getElementById('deleteConfirm');
    const message = document.getElementById('delete-message');
    const deleteId = document.getElementById('delete-id');
    const deleteAction = document.getElementById('delete-action');
    
    if (type === 'user') {
        message.textContent = `Are you sure you want to delete user "${name}" (ID: ${id})? This action cannot be undone.`;
        deleteAction.value = 'delete_user';
    } else if (type === 'task') {
        message.textContent = `Are you sure you want to delete task "${name}" (ID: ${id})? This action cannot be undone.`;
        deleteAction.value = 'delete_task';
    }
    
    deleteId.value = id;
    openModal('deleteConfirm');
}

// Prevent form submission if no users are available for task creation
document.addEventListener('DOMContentLoaded', function() {
    const createTaskBtn = document.querySelector('#createTask button[type="submit"]');
    const userSelect = document.getElementById('task_user_id');
    
    if (createTaskBtn && userSelect && userSelect.options.length <= 1) {
        createTaskBtn.disabled = true;
        createTaskBtn.title = "No users available. Please create a user first.";
    }
});
</script>
</body>
</html>