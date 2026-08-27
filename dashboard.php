<?php
session_start();

if (
    !isset($_SESSION["admin"]) ||
    $_SESSION["admin"] !== true
) {
    header("Location: admin.html");
    exit;
}

require "db.php";

$departments = [];
$result = $conn->query("
    SELECT department_name
    FROM departments
    WHERE status='Active'
    ORDER BY department_name
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row["department_name"];
    }
}

$baseFolder = "/home/femi/n8n-production/files";
$totalDocuments = 0;
$folders = is_dir($baseFolder) ? glob($baseFolder . "/*") : [];

if ($folders) {
    foreach ($folders as $folder) {
        if (!is_dir($folder)) {
            continue;
        }

        $files = glob($folder . "/*");
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    $totalDocuments++;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fob AI | Enterprise Administration</title>
    <style>
        :root {
            --primary-dark: #0A192F;
            --primary-accent: #0066FF;
            --accent-hover: #0052CC;
            --bg-main: #F8FAFC;
            --card-bg: #FFFFFF;
            --text-main: #1E293B;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
            --sidebar-width: 260px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--bg-main);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }

        /* ENTERPRISE SIDEBAR */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--primary-dark);
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            z-index: 100;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 12px 24px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 20px;
        }

        .sidebar-brand h2 {
            color: #FFFFFF;
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex-grow: 1;
        }

        .sidebar button {
            width: 100%;
            height: 42px;
            border: none;
            border-radius: 6px;
            background: transparent;
            color: #94A3B8;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-align: left;
            padding: 0 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
        }

        .sidebar button:hover {
            background: rgba(255, 255, 255, 0.04);
            color: #FFFFFF;
        }

        .sidebar button.active {
            background: var(--primary-accent);
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.25);
        }

        .sidebar-footer {
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .logout-btn {
            background: rgba(239, 68, 68, 0.1) !important;
            color: #EF4444 !important;
            width: 100%;
            height: 42px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: background 0.2s;
        }

        .logout-btn:hover {
            background: #EF4444 !important;
            color: #FFFFFF !important;
        }

        /* MAIN CONTENT AREA */
        .content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 32px 40px;
            max-width: calc(100vw - var(--sidebar-width));
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .page-header {
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-main);
        }

        .page-header p {
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* ENTERPRISE CARDS & CONTAINERS */
        .card, .table-card, .stat-card, .branding-card, .settings-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .stats, .branding-container, .settings-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            padding: 20px 24px;
            position: relative;
            overflow: hidden;
        }

        .stat-card h3 {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-card .stat-value {
            font-size: 30px;
            font-weight: 700;
            color: var(--text-main);
        }

        /* FORMS & INPUTS */
        label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
        }

        select, input[type=text], input[type=password], input[type=number], input[type=color], textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
            color: var(--text-main);
            background: #FFFFFF;
            margin-bottom: 16px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        select:focus, input:focus, textarea:focus {
            border-color: var(--primary-accent);
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1);
        }

        .btn-primary {
            background: var(--primary-accent);
            color: #FFFFFF;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
        }

        .btn-secondary {
            background: #F1F5F9;
            color: var(--text-main);
            border: 1px solid var(--border-color);
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-secondary:hover {
            background: #E2E8F0;
        }

        /* ENTERPRISE TABLES */
        .table-responsive {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    min-width: 1400px;
    border-collapse: collapse;
}

td {
    white-space: nowrap;
}

        th {
            background: #F8FAFC;
            color: var(--text-muted);
            font-weight: 600;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
        }

        tr:hover td {
            background: #FAFCFF;
        }

        .action-btn {
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            margin-right: 4px;
            display: inline-block;
        }

        .view-btn { background: #E8F5E9; color: #2E7D32; }
        .delete-btn { background: #FFEBEE; color: #C62828; }

        .badge-live {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #E8F5E9;
            color: #2E7D32;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-live::before {
            content: "";
            width: 6px;
            height: 6px;
            background: #2E7D32;
            border-radius: 50%;
        }

        .info-text {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 6px;
        }
    </style>
</head>
<body>

<!-- SIDEBAR NAVIGATION -->
<div class="sidebar">
    <div class="sidebar-brand">
        <h2>🛡️ Fob AI Admin</h2>
    </div>
    
    <div class="sidebar-menu">
        <button onclick="showSection('dashboard', this)" class="active">📊 Dashboard</button>
        <button onclick="showSection('users', this)">👥 User Management</button>
        <button onclick="showSection('departments', this)">🏢 Departments</button>
        <button onclick="showSection('documents', this)">📄 Document Center</button>
        <button onclick="showSection('sharepoint', this)">📁 SharePoint Integration</button>
        <button onclick="showSection('branding', this)">🎨 Branding & Persona</button>
        <button onclick="showSection('settings', this)">⚙️ System Settings</button>
    </div>

    <div class="sidebar-footer">
        <a href="admin_logout.php" class="logout-btn">🚪 Secure Logout</a>
    </div>
</div>

<!-- MAIN CONTENT WRAPPER -->
<div class="content">

    <!-- 1. DASHBOARD SECTION -->
    <div id="dashboard" class="section active">
        <div class="page-header">
            <h1>Dashboard Overview</h1>
            <p>Real-time analytics and telemetry of your Fob AI core.</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>Total Registered Users</h3>
                <p id="dashboardUsers" class="stat-value">5</p>
            </div>
            <div class="stat-card">
                <h3>Administrative Accounts</h3>
                <p id="dashboardAdmins" class="stat-value">1</p>
            </div>
            <div class="stat-card">
                <h3>Active Secure Groups</h3>
                <p class="stat-value">5</p>
            </div>
            <div class="stat-card">
                <h3>Operational Status</h3>
                <div style="margin-top: 6px;"><span class="badge-live">Systems Nominal</span></div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 16px; font-size: 16px;">Enterprise Activity Log</h3>
            <div style="display:flex; flex-direction:column; gap: 12px; font-size: 14px;">
                <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid var(--border-color);">
                    <span>? Administrative user profile created securely</span>
                    <span style="color:var(--text-muted); font-size:12px;">2 hours ago</span>
                </div>
                <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid var(--border-color);">
                    <span>? Dynamic tenant branding configuration updated</span>
                    <span style="color:var(--text-muted); font-size:12px;">Yesterday</span>
                </div>
                <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid var(--border-color);">
                    <span>? Master session token validation cycle completed</span>
                    <span style="color:var(--text-muted); font-size:12px;">3 days ago</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. USERS SECTION -->
    <div id="users" class="section">
        <div class="page-header">
            <h1>User Provisioning & Directory</h1>
            <p>Manage enterprise users, access roles, and permission levels.</p>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 16px; font-size: 16px;">Provision New User</h3>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                <div><label>First Name</label><input id="firstName" placeholder="Enter first name"></div>
                <div><label>Last Name</label><input id="lastName" placeholder="Enter last name"></div>
                <div><label>Email Address</label><input id="email" placeholder="user@company.com"></div>
                <div><label>Username</label><input id="newUsername" placeholder="username"></div>
                <div><label>Password</label><input id="newPassword" type="password" placeholder="Secure password"></div>
                <div>
                    <label>Role Privilege</label>
                    <select id="newRole"><option value="user">Standard User</option><option value="admin">Administrator</option></select>
                </div>
                <div>
                    <label>Assigned Department</label>
                    <select id="department"></select>
                </div>
            </div>
            <div style="margin-top: 16px;">
                <button class="btn-primary" onclick="createUser()">Provision Account</button>
            </div>
        </div>

        <div class="card">
            <div style="display:flex; gap: 12px; margin-bottom: 16px;">
                <input id="searchUser" placeholder="?? Search user directory..." style="margin-bottom:0; flex-grow:1;">
                <select id="departmentFilter" style="margin-bottom:0; width: 220px;"><option value="">All Departments</option></select>
            </div>
            <p id="userCount" style="margin-bottom: 16px; font-size: 13px; font-weight: 600; color: var(--text-muted);">Showing 0 active identities</p>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Group ID</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTable"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. DEPARTMENTS SECTION -->
    <div id="departments" class="section">
        <div class="page-header">
            <h1>Department Silos</h1>
            <p>Configure organizational structures and automated secure document boundaries.</p>
        </div>

        <div class="card" style="max-width: 600px;">
            <h3 style="margin-bottom: 16px; font-size: 16px;">Create Organizational Department</h3>
            <form method="POST" action="departments.php">
                <label>Department Name</label>
                <input type="text" name="department_name" placeholder="e.g. Legal Compliance" required>
                <button type="submit" class="btn-primary">Initialize Department</button>
            </form>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 16px; font-size: 16px;">Active Department Registry</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Department Name</th>
                            <th>Assigned Group ID</th>
                            <th>Files Repository Folder</th>
                            <th>Operational Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $deptResult = $conn->query("SELECT * FROM departments ORDER BY department_name");
                    if ($deptResult) {
                        while($row = $deptResult->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong>#" . $row["id"] . "</strong></td>";
                            echo "<td>" . htmlspecialchars($row["department_name"]) . "</td>";
                            echo "<td><code>" . htmlspecialchars($row["group_id"]) . "</code></td>";
                            echo "<td>/" . htmlspecialchars($row["folder_name"]) . "</td>";
                            echo "<td><span class='badge-live'>" . htmlspecialchars($row["status"] ?? 'Active') . "</span></td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 4. DOCUMENTS SECTION -->
    <div id="documents" class="section">
        <div class="page-header">
            <h1>Enterprise Document Center</h1>
            <p>Ingest, index, and monitor corporate files for RAG search retrieval pipelines.</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>Indexed Documents</h3>
                <p class="stat-value"><?= $totalDocuments ?></p>
            </div>
            <div class="stat-card">
                <h3>Active Pipelines</h3>
                <p class="stat-value"><?= count($departments) ?></p>
            </div>
            <div class="stat-card">
                <h3>RAG Engine State</h3>
                <div style="margin-top: 6px;"><span class="badge-live">Operational</span></div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 16px; font-size: 16px;">Ingest New Source File</h3>
            <form action="upload_documents.php" method="POST" enctype="multipart/form-data">
                <label>Target Department Vault</label>
                <select name="department" required>
                    <?php foreach ($departments as $dept): ?>
                    <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Document Payload (PDF, DOCX, TXT, XLSX, CSV)</label>
                <input type="file" name="document" accept=".pdf,.docx,.txt,.xlsx,.csv" required>

                <button type="submit" class="btn-primary">Upload & Vectorize</button>
            </form>
        </div>

        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="font-size: 16px; margin:0;">Vector Index Storage</h3>
                <input type="text" id="docSearch" placeholder="Filter indexed files..." style="width: 280px; margin-bottom:0;">
            </div>

            <div class="table-responsive">
                <table id="documentsTable">
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Type Format</th>
                        <th>Department Vault</th>
                        <th>Index Status</th>
                        <th>File Size</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($folders) {
                    foreach ($folders as $folder) {
                        if (!is_dir($folder)) continue;
                        $deptName = basename($folder);
                        $files = glob($folder . "/*");
                        if ($files) {
                            foreach ($files as $file) {
                                if (!is_file($file)) continue;
                                $fileName = basename($file);
                                $type = strtoupper(pathinfo($fileName, PATHINFO_EXTENSION));
                                $size = round(filesize($file) / 1024, 2);
                                echo "<tr>";
                                echo "<td><strong>" . htmlspecialchars($fileName) . "</strong></td>";
                                echo "<td><span style='background:#F1F5F9; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:600;'>{$type}</span></td>";
                                echo "<td>" . htmlspecialchars($deptName) . "</td>";
                                echo "<td><span class='badge-live'>Vectorized</span></td>";
                                echo "<td>{$size} KB</td>";
                                echo "<td>
                                    <a href='view_document.php?department=" . urlencode($deptName) . "&file=" . urlencode($fileName) . "' class='action-btn view-btn' target='_blank'>Inspect</a>
                                    <a href='delete_document.php?department=" . urlencode($deptName) . "&file=" . urlencode($fileName) . "' class='action-btn delete-btn' onclick=\"return confirm('Purge this document?')\">Purge</a>
                                </td>";
                                echo "</tr>";
                            }
                        }
                    }
                }
                ?>
                </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 5. SHAREPOINT SECTION -->
    <div id="sharepoint" class="section">
        <div class="page-header">
            <h1>SharePoint Cloud Integration</h1>
            <p>Sync enterprise cloud document stores automatically with your vector database.</p>
        </div>

        <div class="card" style="max-width: 650px;">
            <h3 style="margin-bottom: 16px; font-size: 16px;">Microsoft Graph API Connector</h3>
            <label>Azure Tenant ID</label>
            <input placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
            <label>Application (Client) ID</label>
            <input placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
            <label>Client Secret Value</label>
            <input type="password" placeholder="Client secret string">
            <button class="btn-primary">Establish OAuth Synchronization</button>
        </div>
    </div>

    <!-- 6. BRANDING SECTION -->
    <div id="branding" class="section">
        <div class="page-header">
            <h1>Branding & Visual Persona</h1>
            <p>Customize the corporate look and AI identity perceived by enterprise end-users.</p>
        </div>

        <div class="branding-container">
            <div class="branding-card">
                <h3>Corporate Logotype</h3>
                <img src="logo.png" width="160" id="logoPreview" style="margin-bottom:16px; border-radius:6px; border:1px solid var(--border-color); padding:8px; background:#FAFCFF;">
                <input type="file" id="logoFile">
                <button class="btn-primary">Upload Logotype</button>
            </div>

            <div class="branding-card">
                <h3>AI Agent Persona</h3>
                <label>Assistant Identity Name</label>
                <input id="brandingAIName" value="Fob AI">
                <label>Default Welcome Greeting</label>
                <textarea id="welcomeMessage" rows="3">Welcome to Fob Enterprise Intelligence. I am your secure assistant.</textarea>
                <button class="btn-primary">Save Persona Profile</button>
            </div>

            <div class="branding-card">
                <h3>Corporate Directory Info</h3>
                <label>Organization Legal Entity</label>
                <input id="companyName" value="Fob Global Support">
                <label>Enterprise Support Desk Email</label>
                <input id="supportEmail" value="support@enterprise.local">
                <label>Primary Portal Domain</label>
                <input id="website" value="www.enterprise.local">
                <button class="btn-primary">Save Entity Data</button>
            </div>

            <div class="branding-card">
                <h3>Global Interface Palette</h3>
                <label>Primary Brand Accent</label>
                <input type="color" value="#0066FF" style="height:42px; padding:4px; cursor:pointer;">
                <label>Dark Shell Tone</label>
                <input type="color" value="#0A192F" style="height:42px; padding:4px; cursor:pointer;">
                <button class="btn-primary">Apply Visual Palette</button>
            </div>
        </div>
    </div>

    <!-- 7. SETTINGS SECTION -->
    <div id="settings" class="section">
        <div class="page-header">
            <h1>System Infrastructure & Settings</h1>
            <p>Configure core administrative security, mail relay transports, and node diagnostics.</p>
        </div>

        <div class="settings-container">
            <div class="settings-card">
                <h3> Administrative Security</h3>
                <label>Current Master Password</label>
                <input type="password" placeholder="••••••••">
                <label>New Master Password</label>
                <input type="password" placeholder="••••••••">
                <label>Inactivity Timeout (Minutes)</label>
                <input type="number" value="30">
                <button class="btn-primary">Update Security Profile</button>
            </div>

            <div class="settings-card">
                <h3> Mail Transport Agent (SMTP)</h3>
                <label>SMTP Host Address</label>
                <input type="text" value="smtp.enterprise.com">
                <label>Port Protocol</label>
                <input type="number" value="587">
                <button class="btn-primary">Save SMTP Relay</button>
                <button class="btn-secondary" style="margin-top: 8px;">Run Diagnostic Test Ping</button>
            </div>

            <div class="settings-card">
                <h3> Database & Storage Backups</h3>
                <p class="info-text">Export immutable database SQL dumps and secure document file packages.</p>
                <br>
                <button class="btn-primary">Generate Master Backup</button>
                <button class="btn-secondary" style="margin-top: 8px;">Restore State Archive</button>
            </div>

            <div class="settings-card">
                <h3> Core Diagnostic Telemetry</h3>
                <p class="info-text"><strong>Fob Version Engine:</strong> 1.0 Enterprise</p>
<p class="info-text"><strong>PHP Environment:</strong> <?php echo phpversion(); ?></p>
                <p class="info-text"><strong>MySQL Database State:</strong> <span style="color:#2E7D32; font-weight:600;">? Connected Securely</span></p>
                <p class="info-text"><strong>Node Server Status:</strong> <span style="color:#2E7D32; font-weight:600;">? Fully Operational</span></p>
            </div>

            <div class="settings-card">
                <h3> Comprehensive Audit Trail</h3>
                <p class="info-text">Inspect detailed cryptographic audit logs and access attempts.</p>
                <br>
                <button class="btn-primary">View Real-time Logs</button>
                <button class="btn-secondary" style="margin-top: 8px;">Export Audit Stream (CSV)</button>
            </div>
        </div>
    </div>

</div><!-- End Content -->


<script>
document.addEventListener("DOMContentLoaded", function () {

    window.showSection = function(sectionId, buttonElement) {

        document.querySelectorAll('.section').forEach(section => {
            section.classList.remove('active');
        });

        document.querySelectorAll('.sidebar-menu button').forEach(button => {
            button.classList.remove('active');
        });

        const targetSection = document.getElementById(sectionId);

        if (targetSection) {
            targetSection.classList.add('active');
        }

        if (buttonElement) {
            buttonElement.classList.add('active');
        }
    };

    // Dashboard selected on load
    const activeButton =
        document.querySelector('.sidebar-menu button.active');

    if (activeButton) {

        const clickAttr =
            activeButton.getAttribute('onclick');

        const match =
            clickAttr.match(/showSection\('([^']+)'/);

        if (match) {
            showSection(match[1], activeButton);
        }
    }

    // Documents search
    const docSearch =
        document.getElementById("docSearch");

    if (docSearch) {

        docSearch.addEventListener("keyup", function () {

            const filter =
                this.value.toLowerCase();

            document.querySelectorAll(
                "#documentsTable tbody tr"
            ).forEach(row => {

                row.style.display =
                    row.innerText.toLowerCase()
                    .includes(filter)
                        ? ""
                        : "none";
            });
        });
    }

    // User search
    const userSearch =
        document.getElementById("searchUser");

    if (userSearch) {

        userSearch.addEventListener("keyup", function () {

            const filter =
                this.value.toLowerCase();

            document.querySelectorAll(
                "#userTable tr"
            ).forEach(row => {

                row.style.display =
                    row.innerText.toLowerCase()
                    .includes(filter)
                        ? ""
                        : "none";
            });
        });
    }

});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    window.showSection = function(sectionId, buttonElement) {

        document.querySelectorAll(".section").forEach(section => {
            section.classList.remove("active");
        });

        document.querySelectorAll(".sidebar-menu button").forEach(button => {
            button.classList.remove("active");
        });

        const targetSection = document.getElementById(sectionId);

        if (targetSection) {
            targetSection.classList.add("active");
        }

        if (buttonElement) {
            buttonElement.classList.add("active");
        }
    };

    const docSearch = document.getElementById("docSearch");

    if (docSearch) {
        docSearch.addEventListener("keyup", function () {

            const filter = this.value.toLowerCase();

            document.querySelectorAll("#documentsTable tbody tr")
                .forEach(row => {

                row.style.display =
                    row.innerText.toLowerCase().includes(filter)
                        ? ""
                        : "none";
            });
        });
    }
});
</script>

<script src="dashboard.js"></script>

</body>
</html>
