<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assests/css/user.css">
</head>

<body>
    <header>
        <div class="logo">
            <div class="logo-icon">DP</div>
            <div class="logo-text">DashboardPro</div>
        </div>
        <div class="user-info">
            <div class="user-avatar" id="user-avatar">
                <?php
                $nameParts = explode(' ', $_SESSION['name']);
                $firstInitial = strtoupper(substr($nameParts[0], 0, 1));
                $secondInitial = isset($nameParts[1]) ? strtoupper(substr($nameParts[1], 0, 1)) : '';
                echo $firstInitial . $secondInitial;
                ?>
            </div>
            <div class="user-name" id="user-name"><?php echo $_SESSION['name']; ?></div>
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
                    <a href="auth/logout.php" style="display: flex; align-items: center; gap: 10px; color: white; text-decoration: none;">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>

            </ul>
        </aside>

        <main class="main-content">
            <!-- Dashboard Section -->
            <section id="dashboard" class="content-section active">
                <div class="welcome-section">
                    <h1 class="welcome-title">Welcome back, <span id="welcome-name"><?php echo explode(' ', $_SESSION['name'])[0]; ?></span>!</h1>
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
                        <div class="profile-avatar" id="profile-avatar">
                            <?php echo $firstInitial . $secondInitial; ?>
                        </div>
                        <div class="profile-info">
                            <h2 id="profile-name"><?php echo $_SESSION['name']; ?></h2>
                            <p id="profile-email"><?php echo $_SESSION['email']; ?></p>
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
        const userData = {
            firstName: "<?php echo explode(' ', $_SESSION['name'])[0]; ?>",
            lastName: "<?php echo isset(explode(' ', $_SESSION['name'])[1]) ? explode(' ', $_SESSION['name'])[1] : ''; ?>",
            email: "<?php echo $_SESSION['email']; ?>"
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