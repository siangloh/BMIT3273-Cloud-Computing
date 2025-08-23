<?php
$_title = "Edit Student";
require_once "../_base.php";
include "admin_header.php";
checkSuperadmin();

//get method
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if (is_exists($id, 'user', 'uid')) {
        $err = [];
        if (is_post()) {
            $user = $_db->query("SELECT * FROM user WHERE uid = '$id'")->fetch();

            $uname = post("uname") ?? "";
            $uemail = post("uemail") ?? "";
            $umobile = post("umobile") ?? "";
            $uaddress = post("uaddress") ?? "";
            $ucity = post("ucity") ?? "";
            $ustate = post("ustate") ?? "";

            if ($uname != $user->uname) $_err["uname"] = checkUsername($uname) ?? '';
            if ($uemail != $user->email) $_err["uemail"] = checkRegisterEmail($uemail) ?? '';
            if ($umobile != $user->contact)  $_err["umobile"] = checkContact($umobile) ?? '';
            if ($uaddress != $user->contact)  $_err["uaddress"] = checkAddress($uaddress) ?? '';
            if ($ucity != $user->contact)  $_err["ucity"] = checkCity($ucity) ?? '';
            if ($ustate != $user->contact)  $_err["ustate"] = checkState($ustate) ?? '';

            if (isset($_FILES['upic'])) {
                $file = $_FILES['upic'];
                $_err["upic"] = checkUploadPic($file);

                // no error
                if (empty($_err["upic"])) {
                    //everything okey, save the file
                    //create a unique id and use it as file name
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $newFileName = isset($_FILES["upic"]) && !empty($_FILES["upic"] && !empty($ext)) ? uniqid() . '.' . $ext : $user->proPic;
                } else {
                    $newFileName = $user->proPic;
                }
            }

            $_err = array_filter($_err);

            // store new user record
            if (empty($_err)) {
                $stmt = $_db->prepare("UPDATE user SET uname = ?, email = ?, contact = ?, address = ?, city = ?, state = ?, proPic = ? WHERE uid = ?");
                $stmt->execute([$uname, $uemail, $umobile, $uaddress, $ucity, $ustate, $newFileName, $id]);
                if ($stmt->rowCount() < 1) {
                    alert_msg("Unable to update details. Please try again.");
                } else {
                    //save the file
                    if ($newFileName != $user->proPic) {
                        move_uploaded_file($file['tmp_name'], '../profilePic/' . $newFileName);
                        // delete old pic from file
                        if($user->proPic != null){
                            unlink("../profilePic/$user->proPic");
                        }
                    }
                    alert_msg('Record update successful');
                }
            }
        }
    } else {
        alert_msg('user not exist.', $_SERVER['HTTP_REFERER']);
    }
} else {
    alert_msg("No user selected.", $_SERVER['HTTP_REFERER']);
}

$result = $_db->query("SELECT*FROM user WHERE uid = '$id' AND level = '0'");
$u = $result->fetch();

$uname = $u->uname;
$uemail = $u->email;
$umobile = $u->contact;
$uaddress = $u->address;
$ucity = $u->city;
$ustate = $u->state;
?>

<head>
    <link href="../css/login.css" rel="stylesheet" type="text/css" />
    <link href="../css/edit_staff.css" rel="stylesheet" type="text/css" />
</head>

<form method="post" enctype="multipart/form-data">
    <div class="form-box">
        <div class="photo-area">
            <div class="photo">
                <!-- photo preview -->
                <label id="upload-preview" tabindex="0">
                    <?= html_file('upic', 'image/*', 'hidden') ?>
                    <img src="../profilePic/<?= $u?->proPic ?? 'profile.png'?>">
                    <span>Upload Profile Picture</span>
                </label>
                <?= err('upic') ?>
            </div>
        </div>
        <div class="data-area">
            <div class="data">
                <div class="input-field">
                    <div>UID : <?= $u->uid ?></div>
                </div>
                <div class="input-field">
                    <label for="uname" class="required">Full Name</label>
                    <?= html_text('uname', "placeholder='Enter Full Name'") ?>
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
                <div class="input-field">
                    <label for="address" class="required">Address</label>
                    <?= html_text('uaddress', "placeholder='Enter Address") ?>
                    <?= err("uaddress") ?>
                </div>
                <div class="input-field">
                    <label for="city" class="required">City</label>
                    <?= html_text('ucity', "placeholder='Enter City") ?>
                    <?= err("ucity") ?>
                </div>
                <div class="input-field">
                    <label for="state" class="required">State</label>
                    <?= html_text('ustate', "placeholder='Enter State") ?>
                    <?= err("ustate") ?>
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