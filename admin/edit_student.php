<?php
$_title = "Edit Student";
require_once "../_base.php";
include "admin_header.php";
// check if the logged in user is superadmin
checkSuperadmin();

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

// get method -- check if the url have id
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if (is_exists($id, 'student', 'studid')) {
        $_err = []; // Use consistent error array naming
        
        if (is_post()) {
            // Get current student data
            $student = $_db->query("SELECT * FROM student WHERE studid = '$id'")->fetch();

            // Get form data
            $studName = post("sname") ?? "";
            $studEmail = post("semail") ?? "";
            $studPhone = post("sphone") ?? "";
            $studAddress = post("saddress") ?? "";
            $studCity = post("scity") ?? "";
            $studState = post("sstate") ?? "";

            // Validate only changed fields
            if ($studName != $student->studName) {
                $nameCheck = checkUsername($studName);
                if ($nameCheck) $_err["sname"] = $nameCheck;
            }
            
            if ($studEmail != $student->studEmail) {
                $emailCheck = checkRegisterEmail($studEmail);
                if ($emailCheck) $_err["semail"] = $emailCheck;
            }
            
            if ($studPhone != $student->studPhone) {
                $phoneCheck = checkContact($studPhone);
                if ($phoneCheck) $_err["sphone"] = $phoneCheck;
            }
            
            if ($studAddress != $student->studAddress) {
                $addressCheck = checkAddress($studAddress);
                if ($addressCheck) $_err["saddress"] = $addressCheck;
            }
            
            if ($studCity != $student->studCity) {
                $cityCheck = checkCity($studCity);
                if ($cityCheck) $_err["scity"] = $cityCheck;
            }
            
            if ($studState != $student->studState) {
                $stateCheck = checkState($studState);
                if ($stateCheck) $_err["sstate"] = $stateCheck;
            }

            // Handle profile picture upload
            $newFileName = $student->studPic; // Default to current image
            $imageUpdated = false;
            
            if (isset($_FILES['spic']) && $_FILES['spic']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['spic'];
                $uploadCheck = checkUploadPic($file);
                
                if ($uploadCheck) {
                    $_err["spic"] = $uploadCheck;
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
                        if ($student->studPic && 
                            $student->studPic !== 'profile.png' && 
                            $student->studPic !== $newFileName) {
                            
                            try {
                                $s3Client->deleteObject([
                                    'Bucket' => $bucketName,
                                    'Key'    => 'user-images/' . $student->studPic
                                ]);
                            } catch (AwsException $e) {
                                // Log the error but don't fail the update
                                error_log('Failed to delete old image from S3: ' . $e->getMessage());
                            }
                        }
                        
                    } catch (AwsException $e) {
                        $_err['spic'] = 'Error uploading file to S3: ' . $e->getMessage();
                        $newFileName = $student->studPic; // Revert to existing file
                        $imageUpdated = false;
                    }
                }
            }

            // Filter out empty errors
            $_err = array_filter($_err);

            // Update student record if no errors
            if (empty($_err)) {
                try {
                    $stmt = $_db->prepare("
                        UPDATE student 
                        SET studName = ?, studPic = ?, studEmail = ?, studPhone = ?, studAddress = ?, studCity = ?, studState = ? 
                        WHERE studid = ?
                    ");
                    
                    $stmt->execute([
                        $studName, 
                        $newFileName, 
                        $studEmail, 
                        $studPhone, 
                        $studAddress, 
                        $studCity, 
                        $studState, 
                        $id
                    ]);
                    
                    if ($stmt->rowCount() < 1 && !$imageUpdated) {
                        sweet_alert_msg("No changes detected. Record remains the same.", 'info', null, false, true);
                    } else {
                        sweet_alert_msg('Record updated successfully', 'success', null, false);
                    }
                    
                } catch (PDOException $e) {
                    // If database update fails and we uploaded a new image, clean up S3
                    if ($imageUpdated && $newFileName !== $student->studPic) {
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
                    error_log('Database update error: ' . $e->getMessage());
                }
            }
        }
    } else {
        sweet_alert_msg('Student does not exist.', 'error', $_SERVER['HTTP_REFERER'], false);
        exit;
    }
} else {
    sweet_alert_msg("No student selected.", 'error', $_SERVER['HTTP_REFERER'], false);
    exit;
}

// Fetch current student data for display
$result = $_db->query("SELECT * FROM student WHERE studid = '$id'");
$s = $result->fetch();

if (!$s) {
    sweet_alert_msg('Student not found.', 'error', $_SERVER['HTTP_REFERER'], false);
    exit;
}

// Generate the S3 URL for the profile picture
$profilePicUrl = $s->studPic && $s->studPic !== 'profile.png' 
    ? "https://assm-student-web-bucketss.s3.amazonaws.com/user-images/{$s->studPic}" 
    : "https://assm-student-web-bucketss.s3.amazonaws.com/user-images/profile.png";

// Set form values (will be overridden by POST data if form was submitted)
$sname = $s->studName;
$semail = $s->studEmail;
$sphone = $s->studPhone;
$saddress = $s->studAddress;
$scity = $s->studCity;
$sstate = $s->studState;
?>

<head>
    <link href="../css/login.css" rel="stylesheet" type="text/css" />
    <link href="../css/edit_staff.css" rel="stylesheet" type="text/css" />
</head>

<form method="post" enctype="multipart/form-data">
    <div class="form-box">
        <?php if (isset($_err['general'])): ?>
            <div class="error-message"><?= $_err['general'] ?></div>
        <?php endif; ?>
        
        <div class="photo-area">
            <div class="photo">
                <!-- photo preview -->
                <label id="upload-preview" tabindex="0">
                    <?= html_file('spic', 'image/*', 'hidden') ?>
                    <img src="<?= $profilePicUrl ?>" alt="Profile Picture">
                    <span>Upload Profile Picture</span>
                </label>
                <?= err('spic') ?>
            </div>
        </div>
        
        <div class="data-area">
            <div class="data">
                <div class="input-field">
                    <div>Student ID : <?= htmlspecialchars($s->studid) ?></div>
                </div>
                
                <div class="input-field">
                    <label for="sname" class="required">Full Name</label>
                    <?= html_text('sname', "placeholder='Enter Full Name'") ?>
                    <?= err("sname") ?>
                </div>
                
                <div class="input-field">
                    <label for="semail" class="required">Email Address</label>
                    <?= html_text('semail', "placeholder='Enter Email (e.g. xxxx@xxx.xxx)'") ?>
                    <?= err("semail") ?>
                </div>
                
                <div class="input-field">
                    <label for="sphone" class="required">Mobile</label>
                    <?= html_text('sphone', "placeholder='Enter Mobile Number (e.g. 0123456789)'") ?>
                    <?= err("sphone") ?>
                </div>
                
                <div class="input-field">
                    <label for="saddress" class="required">Address</label>
                    <?= html_text('saddress', "placeholder='Enter Address'") ?>
                    <?= err("saddress") ?>
                </div>
                
                <div class="input-field">
                    <label for="scity" class="required">City</label>
                    <?= html_text('scity', "placeholder='Enter City'") ?>
                    <?= err("scity") ?>
                </div>
                
                <div class="input-field">
                    <label for="sstate" class="required">State</label>
                    <?= html_text('sstate', "placeholder='Enter State'") ?>
                    <?= err("sstate") ?>
                </div>
                
                <!-- submit button -->
                <div class="submit-button">
                    <input type="submit" class="form-button" value="Save" name="save" id="save" />
                </div>
            </div>
        </div>
    </div>
</form>

<?php include "admin_footer.php" ?>