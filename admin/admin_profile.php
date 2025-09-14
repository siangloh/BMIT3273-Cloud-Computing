<?php
$_title = "My Profile";
require_once '../_base.php';
global $_adminID;

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

$err = [];
if (is_post()) {
    // Handle announcement submission
    if (isset($_POST['action']) && $_POST['action'] == 'send_announcement') {
        $announcement_subject = trim(post('announcement_subject'));
        $announcement_message = trim(post('announcement_message'));

        // Validation
        if (empty($announcement_subject)) {
            $_err['announcement_subject'] = 'Subject is required';
        } elseif (strlen($announcement_subject) > 100) {
            $_err['announcement_subject'] = 'Subject must not exceed 100 characters';
        }

        if (empty($announcement_message)) {
            $_err['announcement_message'] = 'Message is required';
        } elseif (strlen($announcement_message) > 1000) {
            $_err['announcement_message'] = 'Message must not exceed 1000 characters';
        }

        $_err = array_filter($_err);

        if (empty($_err)) {
            // Include and call SNS service
            require_once 'sns_service.php';

            try {
                sendMessage($announcement_subject, $announcement_message);
                sweet_alert_msg('Announcement sent successfully!', 'success', null, false, true);
            } catch (Exception $e) {
                sweet_alert_msg('Failed to send announcement: ' . $e->getMessage(), 'error', null, false, true);
            }
        }
    }
    // Handle profile update
    else {
        $upic = post("upic");
        $uname = post("uname");
        $uemail = post('uemail');
        $umobile = post("umobile");

        $me = $_db->query("SELECT * FROM user WHERE uid = $_adminID")->fetch();

        if ($uname != $me->uname) $_err["uname"] = checkUsername($uname) ?? '';
        if ($uemail != $me->email) $_err["uemail"] = checkRegisterEmail($uemail) ?? '';
        if ($umobile != $me->contact)  $_err["umobile"] = checkContact($umobile) ?? '';

        if (isset($_FILES['upic'])) {
            $file = $_FILES['upic'];
            $_err["upic"] = checkUploadPic($file);

            // no error
            if (empty($_err["upic"])) {
                // create a unique id and use it as file name
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $newFileName = isset($_FILES["upic"]) && !empty($_FILES["upic"] && !empty($ext)) ? uniqid() . '.' . $ext : $me->proPic;

                // Upload the image to S3
                try {
                    // Upload the image file to S3 in 'user-images' folder
                    $result = $s3Client->upload(
                        $bucketName,
                        'user-images/' . $newFileName,
                        fopen($file['tmp_name'], 'rb')
                    );

                    // Get the URL of the uploaded file
                    $fileUrl = $result['ObjectURL'];

                    // Delete old picture from S3 if it exists
                    if ($me->proPic != null) {
                        try {
                            $s3Client->deleteObject([
                                'Bucket' => $bucketName,
                                'Key' => 'user-images/' . $me->proPic
                            ]);
                        } catch (AwsException $e) {
                            $_err['upic'] = 'Error deleting old file from S3: ' . $e->getMessage();
                        }
                    }
                } catch (AwsException $e) {
                    $_err['upic'] = 'Error uploading file to S3: ' . $e->getMessage();
                    $newFileName = $me->proPic;
                }
            } else {
                $newFileName = $me->proPic;
            }
        } else {
            $newFileName = $me->proPic;
        }

        $_err = array_filter($_err);

        if (empty($_err)) {
            $stmt = $_db->prepare("UPDATE user SET uname = ?, email = ?, contact = ?, proPic = ? WHERE uid = ?");
            $stmt->execute([$uname, $uemail, $umobile, $newFileName, $_adminID]);
            if ($stmt->rowCount() < 1) {
                sweet_alert_msg("No changes made.", 'info', null, false);
            } else {
                sweet_alert_msg('Record update successful', 'success', null, false);
            }
        }
    }
}

include './admin_header.php';

$uname = $admin->uname;
$umobile = $admin->contact;
$uemail = $admin->email;
$upic = $admin->proPic;
$superadmin = $admin->superadmin;
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

        /* Announcement button styles */
        .announcement-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px 0;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .announcement-btn:hover {
            background: #0056b3;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: none;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .modal-title {
            margin: 0;
            color: #333;
            font-size: 24px;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
        }

        .close:hover,
        .close:focus {
            color: #000;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group .error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-cancel:hover {
            background: #5a6268;
        }

        .btn-send {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-send:hover {
            background: #218838;
        }

        .char-counter {
            font-size: 12px;
            color: #666;
            text-align: right;
            margin-top: 2px;
        }
    </style>
</head>

<div class="profile-box">
    <!-- Announcement Button -->
    <div style="margin-bottom: 20px;">
        <button type="button" class="announcement-btn" onclick="openAnnouncementModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m22 8-6 4 6 4V8Z" />
                <rect width="14" height="12" x="2" y="6" rx="2" ry="2" />
            </svg>
            Send Announcement to All Staff
        </button>
    </div>

    <div id="page-content">
        <div id="account-details">
            <form method="post" enctype="multipart/form-data">
                <div class="edit-area disabled-edit">
                    <div class="photo-area">
                        <div class="d-flex-center">
                            <!-- photo preview -->
                            <label class="photo" for="upic" id="upload-preview" tabindex="0">
                                <?= html_file('upic', 'image/*', 'hidden') ?>
                                <img src="<?= $upic ? 'https://' . $bucketName . '.s3.amazonaws.com/user-images/' . $upic : '../profilePic/profile.png' ?>">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 15v4c0 1.1.9 2 2 2h14a2 2 0 0 0 2-2v-4M17 8l-5-5-5 5M12 4.2v10.3" />
                                    </svg>
                                </span>
                            </label>
                        </div>
                        <?= err('upic') ?>

                        <?php if ($admin->superadmin == 1): ?>
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
                            <label for="mobile" class="required">Mobile</label>
                            <?= html_text('umobile', "placeholder='Enter Mobile Number (e.g. 0123456789)'") ?>
                            <?= err("umobile") ?>
                        </div>
                    </div>
                    <input type="submit" class="show-edit form-button" value="Save">
                </div>
                <input type="button" class="form-button hide-edit" value="Edit Info" id="enable-edit">
            </form>
        </div>
    </div>
</div>

<!-- Announcement Modal -->
<div id="announcementModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Send Announcement</h2>
            <span class="close" onclick="closeAnnouncementModal()">&times;</span>
        </div>
        <form method="post" id="announcementForm">
            <input type="hidden" name="action" value="send_announcement">

            <div class="form-group">
                <label for="announcement_subject">Subject *</label>
                <input type="text" id="announcement_subject" name="announcement_subject"
                    maxlength="100" placeholder="Enter announcement subject..."
                    value="<?= isset($_POST['announcement_subject']) ? htmlspecialchars($_POST['announcement_subject']) : '' ?>">
                <div class="char-counter">
                    <span id="subject-count">0</span>/100 characters
                </div>
                <?php if (isset($_err['announcement_subject'])): ?>
                    <div class="error"><?= $_err['announcement_subject'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="announcement_message">Message *</label>
                <textarea id="announcement_message" name="announcement_message"
                    maxlength="1000" placeholder="Enter your announcement message..."
                    rows="6"><?= isset($_POST['announcement_message']) ? htmlspecialchars($_POST['announcement_message']) : '' ?></textarea>
                <div class="char-counter">
                    <span id="message-count">0</span>/1000 characters
                </div>
                <?php if (isset($_err['announcement_message'])): ?>
                    <div class="error"><?= $_err['announcement_message'] ?></div>
                <?php endif; ?>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAnnouncementModal()">Cancel</button>
                <button type="submit" class="btn-send">Send Announcement</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Character counters
    function updateCharCount(input, counterId) {
        const count = input.value.length;
        document.getElementById(counterId).textContent = count;
    }

    document.getElementById('announcement_subject').addEventListener('input', function() {
        updateCharCount(this, 'subject-count');
    });

    document.getElementById('announcement_message').addEventListener('input', function() {
        updateCharCount(this, 'message-count');
    });

    // Modal functions
    function openAnnouncementModal() {
        document.getElementById('announcementModal').style.display = 'block';
        // Update character counts
        updateCharCount(document.getElementById('announcement_subject'), 'subject-count');
        updateCharCount(document.getElementById('announcement_message'), 'message-count');
    }

    function closeAnnouncementModal() {
        document.getElementById('announcementModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('announcementModal');
        if (event.target == modal) {
            closeAnnouncementModal();
        }
    }

    // Show modal if there are validation errors
    <?php if (!empty($_err) && isset($_POST['action']) && $_POST['action'] == 'send_announcement'): ?>
        openAnnouncementModal();
    <?php endif; ?>

    // Original profile edit functionality
    $('#enable-edit').click(function() {
        e = $('.disabled-edit:has(+ #enable-edit)')[0];
        $('.disabled-edit input').prop("disabled", false);
        $('.disabled-edit .show-edit').prop("hidden", false);
        $(".hide-edit").prop("hidden", true);
        $(e).removeClass("disabled-edit");
        $(e).addClass("active");
    });
</script>

<?php include "admin_footer.php" ?>