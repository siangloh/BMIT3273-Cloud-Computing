<tr class="table-header-row">
    <td class="w-5"></td>
    <td class="w-10">No</td>
    <td class="sortable">Name</td>
    <td class="sortable">Email</td>
    <td>Address</td>
    <td>City</td>
    <td>State</td>
    <td class="sortable">Contact</td>
    <td>Action</td>
</tr>

<?php
require_once '../_base.php';
// get parameter

if (isset($_POST['status']) && $_POST['status'] != '') {
    $status = $_POST['status'];
}

if (isset($_POST['keyword']) && $_POST['keyword'] != '') {
    $keyword = $_POST['keyword'];
}

if (isset($_POST['sortBy']) && $_POST['sortBy'] != '') {
    $sortBy = $_POST['sortBy'];
}

if (isset($_POST['sortOrder']) && $_POST['sortOrder'] != '') {
    $sortOrder = $_POST['sortOrder'];
}

// get record list
$sql = "SELECT * FROM user WHERE superadmin != 1 AND status != 0" . (isset($status) ? " AND status = '$status'" : '') . (isset($keyword) ? " AND (uname LIKE '%$keyword%' OR email  LIKE '%$keyword%' OR contact  LIKE '%$keyword%') " : '') . " ORDER BY ".(isset($sortBy) ? " $sortBy ". (isset($sortOrder) ? "$sortOrder" : 'ASC') : 'uid');

$userList = $_db->query($sql);
$i = 1;

if ($userList->rowCount() > 0):
    foreach ($userList as $u):
        $checkboxItem = $u->uid;
?>
        <tr>
            <td class="select-item w-5 text-center">
                <?= html_checkbox_group('checkboxItem', '', "form='f' class='checkboxes'") ?>
            </td>
            <td class="w-10"><?= $i++ ?></td>
            <td><?= $u->uname ?></td>
            <td><?= $u->email ?></td>
            <td><?= $u->address ?></td>
            <td><?= $u->city ?></td>
            <td><?= $u->state ?></td>
            <td><?= $u->contact ?></td>
            <td>
                <div class="ind-action-btn w-100">
                    <button class="blue-btn" data-get="edit_student.php?id=<?= $u->uid ?>" style='width:100%'>Edit</button>
                </div>
            </td>
        </tr>
    <?php endforeach;
else: ?>
    <!-- no staff record found -->
    <tr>
        <td colspan="8" class="text-center"> No Record Found </td>
    </tr>
<?php endif ?>