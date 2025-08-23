<?php
$_title = "Add Student";
require_once '../_base.php';
checkSuperadmin();

include 'admin_header.php';
if (isset($_POST['cancel'])) {
    echo "<script>window.location.href = 'display_staff.php';</script>";
}

$_err = [];
// file upload
if (isset($_FILES['upic'])) {
    $file = $_FILES['upic'];
    $_err["upic"] = checkUploadPic($file);

    // no error
    if (empty($_err["upic"])) {
        //everything okey, save the file
        //create a unique id and use it as file name
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newFileName = isset($_FILES["upic"]) && !empty($_FILES["upic"] && !empty($ext)) ? uniqid() . '.' . $ext : null;

        //save the file
        move_uploaded_file($file['tmp_name'], '../profilePic/' . $newFileName);
    } else {
        $newFileName = null;
    }
}


if (is_post()) {
    $uname = post("uname") ?? "";
    $uemail = post("uemail") ?? "";
    $umobile = post("umobile") ?? "";

    $_err["uname"] = checkUsername($uname) ?? '';
    $_err["uemail"] = checkRegisterEmail($uemail) ?? '';
    $_err["umobile"] = checkRegisterContact($umobile) ?? '';

    $_err = array_filter($_err);

    // store new user record
    if (empty($_err)) {
        // generate random password 
        $pass = generate_password();

        $stmt = $_db->prepare("INSERT INTO user (uname, pass, email, contact, proPic, remark) VALUES (?, SHA1(?), ?, ?, ?, ?)");
        $stmt->execute([$uname, $pass, $uemail, $umobile, $newFileName, $pass]);
        if ($stmt->rowCount() == 0) {
            $_err[] = "Unable to insert. Please try again.";
        }
    }
}
?>

<head>
    <link href="../css/login.css" rel="stylesheet" type="text/css" />
</head>

<style>
    #save {
        width: 100%;
        height: 35px;
        line-height: 1.5;
        font-size: 14px;
        font-family: inherit;
        background: #373737;
        color: white;
        border-radius: 10px;
        padding: 5px 20px;
    }
</style>

<div class="add-staff-box w-100">
    <div class="add-staff w-60" style="margin: 20px auto;">
        <h1 class="title text-center">Enter Student Information</h1>
        <div class="main-content">
            <form id="reg" method="post" action="" name="reg" enctype="multipart/form-data">

                <div class="input-field">
                    <label for="uname" class="required">Username</label>
                    <?= html_text('uname', "placeholder='Enter Username' required") ?>
                    <?= err("uname") ?>
                </div>
                <div class="input-field">
                    <label for="uemail" class="required">Email address</label>
                    <?= html_text('uemail', "placeholder='Enter Email (e.g. xxxx@xxx.xxx)' required") ?>
                    <?= err("uemail") ?>
                </div>
                <div class="input-field">
                    <label for="mobile" class="required">Mobile</label>
                    <?= html_text('umobile', "placeholder='Enter Mobile Number (e.g. 0123456789)' required") ?>
                    <?= err("umobile") ?>
                </div>
                <!-- upload profile pic -->
                <div class="input-field">
                    <label for="upic">Profile Picture</label>
                    <div class="custom-file-button">
                        <?= html_file('upic', 'image/*') ?>
                        <label for="upic">Upload Image ... </label>
                    </div>
                    <?= err('upic') ?>
                    <!-- photo preview -->
                    <label id="upload-preview" tabindex="0">
                        <img src="../profilePic/profile.png">
                    </label>
                </div>
                <!-- ----------------------------- -->
                <!-- submit button -->
                <div class="submit-button">
                    <input type="submit" value="Save" name="save" id="save" class="form-button" />
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "admin_footer.php" ?>