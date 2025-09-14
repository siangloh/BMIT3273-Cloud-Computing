<?php
$_title = "My Profile";
require_once '../_base.php';

// Include header to get S3 client and admin data
include './admin_header.php';

// Ensure we have admin data
if (!$admin) {
    sweet_alert_msg('Session expired. Please login again.', 'error', 'admin_login.php', true);
    exit;
}

$_err = []; // Use consistent error array naming

if (is_post()) {
    // Get form data
    $uname = post("uname") ?? "";
    $uemail = post('uemail') ?? "";
    $umobile = post("umobile") ?? "";

    // Get current admin data for comparison
    $stmt = $_db->prepare("SELECT * FROM user WHERE uid = ?");
    $stmt->execute([$admin->uid]);
    $currentAdmin = $stmt->fetch();

    if (!$currentAdmin) {
        sweet_alert_msg('Admin not found.', 'error', 'admin_login.php', true);
        exit;
    }

    // Validate only changed fields
    if ($uname != $currentAdmin->uname) {
        $nameCheck = checkUsername($uname);
        if ($nameCheck) $_err["uname"] = $nameCheck;
    }
    
    if ($uemail != $currentAdmin->email) {
        $emailCheck = checkRegisterEmail($uemail);
        if ($emailCheck) $_err["uemail"] = $emailCheck;
    }
    
    if ($umobile != $currentAdmin->contact) {
        $contactCheck = checkContact($umobile);
        if ($contactCheck) $_err["umobile"] = $contactCheck;
    }

    // Handle profile picture upload
    $newFileName = $currentAdmin->proPic; // Default to current image
    $imageUpdated = false;
    
    if (isset($_FILES['upic']) && $_FILES['upic']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['upic'];
        $uploadCheck = checkUploadPic($file);
        
        if ($uploadCheck) {
            $_err["upic"] = $uploadCheck;
        } else {
            // Generate unique filename
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $newFileName = uniqid() . '.' . $ext;

            try {
                // Upload new file to S3
                $result = $s3Client->upload(
                    $bucketName,
                    'user-images/' . $newFileName,
                    fopen($file['tmp_name'], 'rb')
                );

                $imageUpdated = true;
                
                // Delete old image from S3 if it exists and is not the default profile image
                if ($currentAdmin->proPic && 
                    $currentAdmin->proPic !== 'profile.png' && 
                    $currentAdmin->proPic !== $newFileName) {
                    
                    try {
                        $s3Client->deleteObject([
                            'Bucket' => $bucketName,
                            'Key'    => 'user-images/' . $currentAdmin->proPic
                        ]);
                    } catch (AwsException $e) {
                        // Log the error but don't fail the update
                        error_log('Failed to delete old admin image from S3: ' . $e->getMessage());
                    }
                }
                
            } catch (AwsException $e) {
                $_err['upic'] = 'Error uploading file to S3: ' . $e->getMessage();
                $newFileName = $currentAdmin->proPic; // Revert to existing file
                $imageUpdated = false;
            }
        }
    }

    // Filter out empty errors
    $_err = array_filter($_err);

    // Update admin record if no errors
    if (empty($_err)) {
        try {
            $stmt = $_db->prepare("
                UPDATE user 
                SET uname = ?, email = ?, contact = ?, proPic = ? 
                WHERE uid = ?
            ");
            
            $stmt->execute([$uname, $uemail, $umobile, $newFileName, $admin->uid]);
            
            if ($stmt->rowCount() < 1 && !$imageUpdated) {
                sweet_alert_msg("No changes detected. Record remains the same.", 'info', null, false, true);
            } else {
                // Update session data to reflect changes
                $_SESSION['admin_updated'] = true;
                sweet_alert_msg('Profile updated successfully', 'success', null, false);
                
                // Refresh admin data for display
                $stmt = $_db->prepare("SELECT * FROM user WHERE uid = ?");
                $stmt->execute([$admin->uid]);
                $admin = $stmt->fetch();
            }
            
        } catch (PDOException $e) {
            // If database update fails and we uploaded a new image, clean up S3
            if ($imageUpdated && $newFileName !== $currentAdmin->proPic) {
                try {
                    $s3Client->deleteObject([
                        'Bucket' => $bucketName,
                        'Key'    => 'user-images/' . $newFileName
                    ]);
                } catch (AwsException $s3e) {
                    error_log('Failed to cleanup S3 after database error: ' . $s3e->getMessage());
                }
            }
            
            $_err['general'] = 'Database error occurred. Please try again.';
            error_log('Admin profile update error: ' . $e->getMessage());
        }
    }
}

// Set form values (will be overridden by POST data if form was submitted)
$uname = $admin->uname;
$uemail = $admin->email;
$umobile = $admin->contact;
$upic = $admin->proPic;
$superadmin = $admin->superadmin;

// Generate the S3 URL for the profile picture
$profilePicUrl = getProfilePicUrl($upic, $bucketName);
?>

<head>
    <link href="../css/profile.css" rel="stylesheet" type="text/css" />
    <style>
        input[type="submit"] {
            background: #373737;
        }

        .input-field {
            flex-direction: row;
        }

        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .photo img {
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 50%;
        }

        /* Edit mode styles */
        .edit-area.disabled-edit input:not([type="button"]):not([type="submit"]) {
            background-color: #f8f9fa;
            border-color: #e9ecef;
            color: #6c757d;
        }

        .edit-area.disabled-edit #upload-preview {
            pointer-events: none;
            opacity: 0.7;
        }

        .edit-area.active input {
            background-color: #fff;
            border-color: #ced4da;
            color: #495057;
        }

        .edit-area.active #upload-preview {
            pointer-events: all;
            opacity: 1;
        }

        .show-edit {
            display: none;
        }

        .edit-area.active .show-edit {
            display: inline-block;
        }

        .edit-area.disabled-edit .hide-edit {
            display: inline-block;
        }

        .edit-area.active .hide-edit {
            display: none;
        }
    </style>
</head>

<div class="profile-box">
    <div class="profile-nav">
        <div class="open" id="account-details">
            <div class="nav-title">
                <svg width="20px" height="20px" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <g id="Icons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <g id="Rounded" transform="translate(-238.000000, -156.000000)">
                            <g id="Action" transform="translate(100.000000, 100.000000)">
                                <g id="-Round-/-Action-/-account_circle" transform="translate(136.000000, 54.000000)">
                                    <g>
                                        <polygon id="Path" points="0 0 24 0 24 24 0 24"></polygon>
                                        <path d="M12,2 C6.48,2 2,6.48 2,12 C2,17.52 6.48,22 12,22 C17.52,22 22,17.52 22,12 C22,6.48 17.52,2 12,2 Z M12,5 C13.66,5 15,6.34 15,8 C15,9.66 13.66,11 12,11 C10.34,11 9,9.66 9,8 C9,6.34 10.34,5 12,5 Z M12,19.2 C9.5,19.2 7.29,17.92 6,15.98 C6.03,13.99 10,12.9 12,12.9 C13.99,12.9 17.97,13.99 18,15.98 C16.71,17.92 14.5,19.2 12,19.2 Z" id="🔹Icon-Color" fill="#1D1D1D"></path>
                                    </g>
                                </g>
                            </g>
                        </g>
                    </g>
                </svg>
                <span>Account Details</span>
            </div>
        </div>
        <div id="change-password" onclick="window.location.href='change_pass.php'" role="button" tabindex="0">
            <div class="nav-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <span>Change Password</span>
            </div>
        </div>
    </div>
    
    <div id="page-content">
        <div id="account-details">
            <?php if (isset($_err['general'])): ?>
                <div class="error-message"><?= htmlspecialchars($_err['general']) ?></div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data">
                <div class="edit-area disabled-edit" id="edit-area">
                    <div class="photo-area">
                        <div class="d-flex-center">
                            <!-- photo preview -->
                            <label class="photo" for="upic" id="upload-preview" tabindex="0">
                                <?= html_file('upic', 'image/*', 'hidden') ?>
                                <img src="<?= $profilePicUrl ?>" 
                                     alt="Admin Profile Picture"
                                     onerror="this.src='https://<?= $bucketName ?>.s3.amazonaws.com/user-images/profile.png'">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 15v4c0 1.1.9 2 2 2h14a2 2 0 0 0 2-2v-4M17 8l-5-5-5 5M12 4.2v10.3" />
                                    </svg>
                                </span>
                            </label>
                        </div>
                        <?= err('upic') ?>

                        <?php if ($superadmin == 1): ?>
                            <div class="label">
                                <label for="superadmin">Superadmin</label>
                            </div>
                        <?php endif ?>
                    </div>
                    
                    <div class="input-data">
                        <div class="input-field">
                            <label for="uname" class="required">Username</label>
                            <?= html_text('uname', "placeholder='Enter Username'") ?>
                            <?= err("uname") ?>
                        </div>
                        
                        <div class="input-field">
                            <label for="uemail" class="required">Email address</label>
                            <?= html_text('uemail', "placeholder='Enter Email (e.g. xxxx@xxx.xxx)'") ?>
                            <?= err("uemail") ?>
                        </div>
                        
                        <div class="input-field">
                            <label for="umobile" class="required">Mobile</label>
                            <?= html_text('umobile', "placeholder='Enter Mobile Number (e.g. 0123456789)'") ?>
                            <?= err("umobile") ?>
                        </div>
                    </div>
                    
                    <input type="submit" class="show-edit form-button" value="Save Changes">
                </div>
                
                <input type="button" class="form-button hide-edit" value="Edit Profile" id="enable-edit">
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initially disable all form inputs except buttons
    $('.disabled-edit input:not([type="button"]):not([type="submit"])').prop('disabled', true);
    
    $('#enable-edit').click(function() {
        var editArea = $('#edit-area');
        
        // Enable all inputs
        $('.disabled-edit input').prop('disabled', false);
        
        // Switch classes
        editArea.removeClass('disabled-edit').addClass('active');
        
        // Focus on first input
        $('#uname').focus();
    });
    
    // Handle image preview
    $('#upic').change(function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#upload-preview img').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Add keyboard navigation for change password
    $('#change-password').keypress(function(e) {
        if (e.which === 13) { // Enter key
            window.location.href = 'change_pass.php';
        }
    });
});
</script>

<?php include "admin_footer.php" ?>