<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
          --primary-color: #0a1945;
          --secondary-color: #c6a052;
          --accent-color: #e74c3c;
          --light-color: #ecf0f1;
          --light-gray: #f8f9fa;
          --dark-color: #2d3748;
          --dark-color-2: #212529;
          --heading-color: #0a2463;
          --success: #2ecc71;
          --pending: #f39c12;
          --missing: #e74c3c;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-color);
            background-color: var(--light-gray);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Header Styles */
        header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-icon {
            background: var(--primary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
        }
        
        .logo-text {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
            position: relative;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }
        
        .user-name {
            font-weight: 600;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: var(--primary-color);
            color: white;
            padding: 25px 0;
            transition: all 0.3s ease;
            height: calc(100vh - 70px);
            position: sticky;
            top: 70px;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .menu-item {
            padding: 15px 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
        }
        
        .menu-item:hover, .menu-item.active {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid var(--secondary-color);
        }
        
        .menu-item i {
            width: 24px;
            text-align: center;
            font-size: 18px;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 30px;
            transition: all 0.3s;
        }
        
        .content-section {
            display: none;
        }
        
        .content-section.active {
            display: block;
        }
        
        .welcome-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .welcome-title {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 28px;
        }
        
        .welcome-subtitle {
            color: var(--dark-color);
            margin-bottom: 25px;
            max-width: 700px;
            line-height: 1.6;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }
        
        .total-card::before {
            background: var(--primary-color);
        }
        
        .pending-card::before {
            background: var(--pending);
        }
        
        .missing-card::before {
            background: var(--missing);
        }
        
        .completed-card::before {
            background: var(--success);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .stat-title {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 16px;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        
        .icon-1 {
            background: rgba(10, 25, 69, 0.1);
            color: var(--primary-color);
        }
        
        .icon-2 {
            background: rgba(198, 160, 82, 0.1);
            color: var(--secondary-color);
        }
        
        .icon-3 {
            background: rgba(231, 76, 60, 0.1);
            color: var(--accent-color);
        }
        
        .icon-4 {
            background: rgba(46, 204, 113, 0.1);
            color: var(--success);
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--heading-color);
            margin-bottom: 5px;
        }
        
        .stat-diff {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .diff-positive {
            color: var(--success);
        }
        
        .diff-negative {
            color: var(--accent-color);
        }
        
        /* Application Table */
        .application-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .application-table th,
        .application-table td {
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .application-table th {
            background-color: var(--light-gray);
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .application-table tr:last-child td {
            border-bottom: none;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-pending {
            background: rgba(243, 156, 18, 0.1);
            color: var(--pending);
        }
        
        .status-missing {
            background: rgba(231, 76, 60, 0.1);
            color: var(--missing);
        }
        
        .status-completed {
            background: rgba(46, 204, 113, 0.1);
            color: var(--success);
        }
        
        /* Profile Section */
        .profile-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            font-weight: bold;
        }
        
        .profile-info h2 {
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .profile-info p {
            color: var(--dark-color);
        }
        
        .profile-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: span 2;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(10, 25, 69, 0.1);
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: #08112e;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background: rgba(10, 25, 69, 0.05);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }
            
            .menu-item span {
                display: none;
            }
            
            .menu-item {
                justify-content: center;
                padding: 20px 0;
            }
        }
        
        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .welcome-section {
                padding: 20px;
            }
            
            .profile-form {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
        }
        
        @media (max-width: 576px) {
            header {
                padding: 15px;
            }
            
            .main-content {
                padding: 20px 15px;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <div class="logo-icon">DP</div>
            <div class="logo-text">DashboardPro</div>
        </div>
        <div class="user-info">
            <div class="user-avatar" id="user-avatar">JS</div>
            <div class="user-name" id="user-name">John Smith</div>
            <i class="fas fa-chevron-down"></i>
        </div>
    </header>
    
    <div class="container">
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li class="menu-item active" data-target="dashboard">
                    <i class="fas fa-home"></i> 
                    <span>Dashboard</span>
                </li>
                <li class="menu-item" data-target="profile">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </li>
                <li class="menu-item" data-target="applications">
                    <i class="fas fa-file-alt"></i>
                    <span>My Applications</span>
                </li>
                <li class="menu-item" id="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </li>
            </ul>
        </aside>
        
        <main class="main-content">
            <!-- Dashboard Section -->
            <section id="dashboard" class="content-section active">
                <div class="welcome-section">
                    <h1 class="welcome-title">Welcome back, <span id="welcome-name">John</span>!</h1>
                    <p class="welcome-subtitle">Here's an overview of your application status. You have <strong>3 pending applications</strong> that require your attention.</p>
                </div>
                
                <div class="stats-container">
                    <div class="stat-card total-card">
                        <div class="stat-header">
                            <div class="stat-title">Total Applications</div>
                            <div class="stat-icon icon-1">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        </div>
                        <div class="stat-value">12</div>
                        <div class="stat-diff diff-positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>2 new applications this month</span>
                        </div>
                    </div>
                    
                    <div class="stat-card pending-card">
                        <div class="stat-header">
                            <div class="stat-title">Pending Applications</div>
                            <div class="stat-icon icon-2">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="stat-value">5</div>
                        <div class="stat-diff diff-negative">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Action required</span>
                        </div>
                    </div>
                    
                    <div class="stat-card missing-card">
                        <div class="stat-header">
                            <div class="stat-title">Missing Documents</div>
                            <div class="stat-icon icon-3">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="stat-value">3</div>
                        <div class="stat-diff diff-negative">
                            <i class="fas fa-arrow-down"></i>
                            <span>2 urgent documents needed</span>
                        </div>
                    </div>
                    
                    <div class="stat-card completed-card">
                        <div class="stat-header">
                            <div class="stat-title">Completed Applications</div>
                            <div class="stat-icon icon-4">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-value">4</div>
                        <div class="stat-diff diff-positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>20% increase from last month</span>
                        </div>
                    </div>
                </div>
                
                <div class="welcome-section">
                    <h2 class="welcome-title">Recent Activity</h2>
                    <div class="activity-item">
                        <div class="activity-content">
                            <p class="activity-desc"><i class="fas fa-circle text-danger" style="font-size: 8px; margin-right: 8px;"></i> Your visa application requires updated passport copy</p>
                            <div class="activity-time">
                                <i class="far fa-clock"></i>
                                <span>2 hours ago</span>
                            </div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-content">
                            <p class="activity-desc"><i class="fas fa-circle text-success" style="font-size: 8px; margin-right: 8px;"></i> Your scholarship application has been approved</p>
                            <div class="activity-time">
                                <i class="far fa-clock"></i>
                                <span>1 day ago</span>
                            </div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-content">
                            <p class="activity-desc"><i class="fas fa-circle text-warning" style="font-size: 8px; margin-right: 8px;"></i> Your license renewal is under review</p>
                            <div class="activity-time">
                                <i class="far fa-clock"></i>
                                <span>3 days ago</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Profile Section -->
            <section id="profile" class="content-section">
                <div class="profile-section">
                    <div class="profile-header">
                        <div class="profile-avatar" id="profile-avatar">JS</div>
                        <div class="profile-info">
                            <h2 id="profile-name">John Smith</h2>
                            <p id="profile-email">john.smith@example.com</p>
                        </div>
                    </div>
                    
                    <form id="profile-form">
                        <div class="profile-form">
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" class="form-control" id="firstName" value="John">
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" class="form-control" id="lastName" value="Smith">
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" value="john.smith@example.com">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" value="+1 (555) 123-4567">
                            </div>
                            <div class="form-group full-width">
                                <label for="address">Address</label>
                                <input type="text" class="form-control" id="address" value="123 Main Street, New York, NY 10001">
                            </div>
                            <div class="form-group">
                                <label for="dob">Date of Birth</label>
                                <input type="date" class="form-control" id="dob" value="1985-05-15">
                            </div>
                            <div class="form-group">
                                <label for="nationality">Nationality</label>
                                <input type="text" class="form-control" id="nationality" value="American">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </section>
            
            <!-- Applications Section -->
            <section id="applications" class="content-section">
                <div class="welcome-section">
                    <h1 class="welcome-title">My Applications</h1>
                    <p class="welcome-subtitle">Here is a list of all your submitted applications. You can view details or upload missing documents.</p>
                </div>
                
                <table class="application-table">
                    <thead>
                        <tr>
                            <th>Application ID</th>
                            <th>Type</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                            <th>Documents</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#APP-2023-001</td>
                            <td>Student Visa</td>
                            <td>15 July 2023</td>
                            <td><span class="status-badge status-pending">Pending</span></td>
                            <td><span class="status-badge status-completed">Complete</span></td>
                            <td>
                                <button class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>#APP-2023-005</td>
                            <td>Work Permit</td>
                            <td>18 July 2023</td>
                            <td><span class="status-badge status-missing">Missing Docs</span></td>
                            <td><span class="status-badge status-missing">2 Missing</span></td>
                            <td>
                                <button class="btn btn-outline btn-sm">
                                    <i class="fas fa-upload"></i> Upload
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>#APP-2023-003</td>
                            <td>Scholarship</td>
                            <td>10 July 2023</td>
                            <td><span class="status-badge status-completed">Completed</span></td>
                            <td><span class="status-badge status-completed">Complete</span></td>
                            <td>
                                <button class="btn btn-outline btn-sm">
                                    <i class="fas fa-download"></i> Download
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>#APP-2023-007</td>
                            <td>License Renewal</td>
                            <td>22 July 2023</td>
                            <td><span class="status-badge status-pending">Pending</span></td>
                            <td><span class="status-badge status-completed">Complete</span></td>
                            <td>
                                <button class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>#APP-2023-009</td>
                            <td>Residency Permit</td>
                            <td>25 July 2023</td>
                            <td><span class="status-badge status-missing">Missing Docs</span></td>
                            <td><span class="status-badge status-missing">3 Missing</span></td>
                            <td>
                                <button class="btn btn-outline btn-sm">
                                    <i class="fas fa-upload"></i> Upload
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        // User data (would typically come from a database)
        const userData = {
            firstName: "John",
            lastName: "Smith",
            email: "john.smith@example.com",
            phone: "+1 (555) 123-4567",
            address: "123 Main Street, New York, NY 10001",
            dob: "1985-05-15",
            nationality: "American"
        };
        
        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Set user information
            document.getElementById('user-name').textContent = `${userData.firstName} ${userData.lastName}`;
            document.getElementById('welcome-name').textContent = userData.firstName;
            document.getElementById('profile-name').textContent = `${userData.firstName} ${userData.lastName}`;
            document.getElementById('profile-email').textContent = userData.email;
            
            // Set avatar initials
            const avatar = document.getElementById('user-avatar');
            const profileAvatar = document.getElementById('profile-avatar');
            const initials = `${userData.firstName.charAt(0)}${userData.lastName.charAt(0)}`;
            avatar.textContent = initials;
            profileAvatar.textContent = initials;
            
            // Menu navigation
            const menuItems = document.querySelectorAll('.menu-item');
            const contentSections = document.querySelectorAll('.content-section');
            
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    const target = this.getAttribute('data-target');
                    
                    // Remove active class from all menu items
                    menuItems.forEach(i => i.classList.remove('active'));
                    
                    // Add active class to clicked menu item
                    this.classList.add('active');
                    
                    // Hide all content sections
                    contentSections.forEach(section => {
                        section.classList.remove('active');
                    });
                    
                    // Show target section
                    if (target) {
                        document.getElementById(target).classList.add('active');
                    }
                });
            });
            
            // Logout functionality
            const logoutBtn = document.getElementById('logout-btn');
            logoutBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to log out?')) {
                    // Simulate logout process
                    alert('You have been logged out successfully.');
                    // In a real app, you would redirect to login page
                    // window.location.href = 'login.html';
                }
            });
            
            // Profile form submission
            const profileForm = document.getElementById('profile-form');
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Update user data
                userData.firstName = document.getElementById('firstName').value;
                userData.lastName = document.getElementById('lastName').value;
                userData.email = document.getElementById('email').value;
                userData.phone = document.getElementById('phone').value;
                userData.address = document.getElementById('address').value;
                userData.dob = document.getElementById('dob').value;
                userData.nationality = document.getElementById('nationality').value;
                
                // Update displayed user info
                document.getElementById('user-name').textContent = `${userData.firstName} ${userData.lastName}`;
                document.getElementById('welcome-name').textContent = userData.firstName;
                document.getElementById('profile-name').textContent = `${userData.firstName} ${userData.lastName}`;
                document.getElementById('profile-email').textContent = userData.email;
                
                // Update avatar initials
                const newInitials = `${userData.firstName.charAt(0)}${userData.lastName.charAt(0)}`;
                avatar.textContent = newInitials;
                profileAvatar.textContent = newInitials;
                
                alert('Profile updated successfully!');
            });
        });
    </script>
</body>
</html>