<?php

use Aws\Exception\AwsException;

$_title = "My Profile";
require_once '../_base.php';

// Check admin login first (before including header)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    sweet_alert_msg('Session expired. Please login again.', 'error', 'admin_login.php', true);
    exit;
}

$_err = []; // Use consistent error array naming
$profileUpdated = false; // Flag to track if profile was updated
// AWS S3 Setup (needed before header include for S3 operations)
require '../vendor/autoload.php';

use Aws\S3\S3Client;

if (is_post()) {
    // Get form data
    $uname = post("uname") ?? "";
    $uemail = post('uemail') ?? "";
    $umobile = post("umobile") ?? "";

    // Get current admin data for comparison
    $stmt = $_db->prepare("SELECT * FROM user WHERE uid = ? AND status = '1' AND level = '1'");
    $stmt->execute([$_SESSION['admin_id']]);
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



    $s3Client = new S3Client([
        'version'     => 'latest',
        'region'      => 'us-east-1',
    ]);
    $bucketName = 'assm-student-web-bucketss';

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
                if (
                    $currentAdmin->proPic &&
                    $currentAdmin->proPic !== 'profile.png' &&
                    $currentAdmin->proPic !== $newFileName
                ) {
                    try {
                        $s3Client->deleteObject([
                            'Bucket' => $bucketName,
                            'Key'    => 'user-images/' . $currentAdmin->proPic
                        ]);
                    } catch (AwsException $e) {
                        error_log('Failed to delete old admin image from S3: ' . $e->getMessage());
                    }
                }
            } catch (AwsException $e) {
                $_err['upic'] = 'Error uploading file to S3: ' . $e->getMessage();
                $newFileName = $currentAdmin->proPic;
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

            $stmt->execute([$uname, $uemail, $umobile, $newFileName, $_SESSION['admin_id']]);

            if ($stmt->rowCount() > 0 || $imageUpdated) {
                $profileUpdated = true;

                // Update session with new data for immediate reflection
                $_SESSION['admin_name'] = $uname;
                $_SESSION['admin_pic'] = $newFileName;

                sweet_alert_msg('Profile updated successfully', 'success', null, false);
            } else {
                sweet_alert_msg("No changes detected. Record remains the same.", 'info', null, false, true);
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

// Include header AFTER processing the update
include './admin_header.php';

// Set form values for display
$uname = $currentAdmin->uname;
$uemail = $currentAdmin->email;
$umobile = $currentAdmin->contact;
$upic = $currentAdmin->proPic;
$superadmin = $currentAdmin->superadmin;

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
            background: #373737;
            color: white;
            border-radius: 10px;
            padding: 5px 20px;
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

        <?php if ($profileUpdated): ?>
            // Update sidebar in real-time after successful profile update
            updateSidebarProfile();
        <?php endif; ?>
    });

    function updateSidebarProfile() {
        // Update sidebar profile image
        var newImageSrc = '<?= $profilePicUrl ?>';
        var newName = '<?= htmlspecialchars($currentAdmin->uname, ENT_QUOTES) ?>';

        // Update sidebar image
        $('.admin-pic img').attr('src', newImageSrc);

        // Update sidebar name
        $('.admin-name').text(newName);

        // Force image reload to bypass cache
        $('.admin-pic img').attr('src', newImageSrc + '?t=' + new Date().getTime());
    }
</script>

<?php include "admin_footer.php" ?>