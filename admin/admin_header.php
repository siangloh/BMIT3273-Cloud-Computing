<?php
require_once '../_base.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize admin variable
$admin = null;

// Check admin login
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    
    // Use prepared statement for security
    $stmt = $_db->prepare("SELECT * FROM user WHERE uid = ? AND status = '1' AND level = '1'");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();
    
    // Override with session data if profile was recently updated
    if ($admin && isset($_SESSION['admin_name'])) {
        $admin->uname = $_SESSION['admin_name'];
    }
    if ($admin && isset($_SESSION['admin_pic'])) {
        $admin->proPic = $_SESSION['admin_pic'];
    }
}

// Redirect if admin not found or not logged in
if (empty($admin)) {
    sweet_alert_msg('Login Required!', 'error', 'admin_login.php', true);
    session_destroy();
    exit; // Important: stop execution after redirect
}

// AWS SDK Setup
require '../vendor/autoload.php';
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\Credentials\CredentialProvider;

// Initialize S3 client
$s3Client = new S3Client([
    'version'     => 'latest',
    'region'      => 'us-east-1',
]);

// Bucket name in S3
$bucketName = 'assm-student-web-bucketss';

// Function to get S3 profile picture URL
function getProfilePicUrl($picName, $bucketName) {
    if (!empty($picName) && $picName !== 'profile.png') {
        return "https://{$bucketName}.s3.amazonaws.com/user-images/{$picName}";
    }
    return "https://{$bucketName}.s3.amazonaws.com/user-images/profile.png";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($_title ?? "Admin Panel") ?></title>
    <link href="../css/utility.css" rel="stylesheet" type="text/css" />
    <link href="../css/admin_header.css" rel="stylesheet" type="text/css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="../js/app.js"></script>
</head>

<script>
    $(document).ready(function(e) {
        $(".accordion-parent").click(function() {
            if ($(this).hasClass("active")) {
                $(this).toggleClass("active");
            } else {
                $(".accordion-parent").removeClass("active");
                $(this).toggleClass("active");
            }
        });

        // Add click handlers for navigation
        $("[data-get]").click(function() {
            var url = $(this).data('get');
            if (url) {
                window.location.href = url;
            }
        });

        // Add logout confirmation
        $(".logout").click(function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../logout.php';
            }
        });
    });
</script>

<body class="admin">
    <div class="admin-content">
        <div class="sidebar">
            <div class="sidebar-content">
                <div class="logo" data-get="admin_homepage.php">
                    <p style="font-family: serif; font-size: 25px; font-weight: bold; font-style: italic; text-transform: uppercase; letter-spacing: 2px; line-height: 1.5;">
                        Eastbridge University
                    </p>
                </div>
                
                <div class="menu-list">
                    <div class="admin-profile">
                        <div class="profile d-flex-center" data-get="admin_profile.php">
                            <div class="admin-pic pointer-event-none">
                                <img src="<?= getProfilePicUrl($admin->proPic ?? '', $bucketName) ?>" 
                                     alt="Admin Profile Picture" 
                                     class="admin-pic"
                                     onerror="this.src='https://<?= $bucketName ?>.s3.amazonaws.com/user-images/profile.png'">
                            </div>
                            <div class="admin-name pointer-event-none">
                                <?= htmlspecialchars($admin->uname ?? 'Admin') ?>
                            </div>
                        </div>
                        
                        <div class="logout" role="button" tabindex="0" 
                             title="Logout" 
                             style="cursor: pointer;">
                            <svg xmlns="http://www.w3.org/2000/svg" 
                                 width="30" height="30" 
                                 viewBox="0 0 24 24" 
                                 fill="none" 
                                 stroke="#fdfdfd" 
                                 stroke-width="1.5" 
                                 stroke-linecap="round" 
                                 stroke-linejoin="round">
                                <path d="M10 3H6a2 2 0 0 0-2 2v14c0 1.1.9 2 2 2h4M16 17l5-5-5-5M19.8 12H9" />
                            </svg>
                        </div>
                    </div>
                    
                    <div class="menu-item">
                        <div class="menu-title" data-get="admin_homepage.php" role="button" tabindex="0">
                            Home
                        </div>
                        
                        <!-- Show admin menu only for superadmin -->
                        <?php if (isset($admin->superadmin) && $admin->superadmin == 1): ?> 
                            <div class="menu-title" data-get="admin_list.php" role="button" tabindex="0">
                                Admin Management
                            </div>
                        <?php endif; ?>
                        
                        <div class="menu-title" data-get="student_list.php" role="button" tabindex="0">
                            Student List
                        </div>
                        
                        <!-- Additional menu items (currently commented out) -->
                        <!--
                        <div class="menu-title" data-get="categoryList.php" role="button" tabindex="0">Category</div>
                        <div class="menu-title" data-get="productList.php" role="button" tabindex="0">Product</div>
                        <div class="menu-title" data-get="optionList.php" role="button" tabindex="0">Options</div>
                        <div class="menu-title" data-get="order_record.php" role="button" tabindex="0">Order</div>
                        -->
                    </div>
                </div>
            </div>
        </div>
        
        <div class="top-menu-body-content">
            <div class="top-menu">
                <div class="top-menu-bar">
                    <div class="page-title">
                        <?= htmlspecialchars($_title ?? "Admin Panel") ?>
                    </div>
                    
                    <!-- Optional: Add breadcrumb or additional top menu items -->
                    <div class="top-menu-actions">
                        <!-- Add any top menu actions here -->
                    </div>
                </div>
            </div>
            
            <div class="body-content">
                <!-- Main content will be inserted here -->