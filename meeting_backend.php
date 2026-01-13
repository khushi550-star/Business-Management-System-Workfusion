<?php
include "Connect.php";
header('Content-Type: application/json');

// ==== CREATE MEETING ====
if (isset($_POST['action']) && $_POST['action'] === 'create_meeting') {
    $title = $_POST['title'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $meeting_link = $_POST['meeting_link'];
    $invite_type = $_POST['invite_type'];
    $created_by = 1;

    $conn->query("INSERT INTO meetings (title, description, date, start_time, end_time, meeting_link, created_by, status, created_at)
                  VALUES ('$title','','$date','$start_time','$end_time','$meeting_link',$created_by,'scheduled',NOW())");
    $mid = $conn->insert_id;

    // Invite employees
    if ($invite_type == 'all') {
        $users = $conn->query("SELECT id FROM users");
        while($u = $users->fetch_assoc()) {
            $conn->query("INSERT INTO meeting_invites (meeting_id, emp_id, invited_at) VALUES ($mid, {$u['id']}, NOW())");
        }
    } elseif (!empty($_POST['invitees'])) {
        foreach($_POST['invitees'] as $uid) {
            $conn->query("INSERT INTO meeting_invites (meeting_id, emp_id, invited_at) VALUES ($mid, $uid, NOW())");
        }
    }

    echo json_encode(["ok"=>true,"message"=>"Meeting created successfully!"]);
    exit;
}

// ==== FETCH MEETINGS ====
if (isset($_GET['action']) && $_GET['action'] === 'fetch_meetings') {
    $meetings = $conn->query("SELECT * FROM meetings ORDER BY id DESC");
    $data=[];
    while($m=$meetings->fetch_assoc()){
        $mid=$m['id'];
        $total = $conn->query("SELECT COUNT(*) AS c FROM meeting_invites WHERE meeting_id=$mid")->fetch_assoc()['c'];
        $att = $conn->query("SELECT COUNT(*) AS c FROM meeting_attendance WHERE meeting_id=$mid")->fetch_assoc()['c'];
        $m['total']=$total; $m['attended']=$att;
        $data[]=$m;
    }
    echo json_encode($data);
    exit;
}

// ==== UPLOAD FILE ====
if (isset($_POST['action']) && $_POST['action'] === 'upload_file') {
    $mid = intval($_POST['meeting_id']);
    if(!empty($_FILES['file']['name'])){
        $dir = __DIR__ . "/uploads/meeting_files/";
        if(!is_dir($dir)) mkdir($dir,0777,true);
        $name = time()."_".basename($_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'],$dir.$name);
        $conn->query("INSERT INTO meeting_files (meeting_id, file_name, file_path) VALUES ($mid, '{$_FILES['file']['name']}', '$name')");
        echo json_encode(["ok"=>true,"message"=>"File uploaded successfully!"]);
    } else {
        echo json_encode(["ok"=>false,"message"=>"No file selected."]);
    }
    exit;
}

// ==== VIEW FILES ====
if (isset($_GET['action']) && $_GET['action'] === 'view_files') {
    $mid = intval($_GET['meeting_id']);
    $files = $conn->query("SELECT id, file_name, file_path FROM meeting_files WHERE meeting_id=$mid ORDER BY id DESC");
    $data=[];
    while($f=$files->fetch_assoc()) $data[]=$f;
    echo json_encode($data);
    exit;
}

// ==== DELETE FILE ====
if (isset($_POST['action']) && $_POST['action'] === 'delete_file') {
    $fid = intval($_POST['file_id']);
    $q = $conn->query("SELECT file_path, meeting_id FROM meeting_files WHERE id=$fid");
    if($q && $q->num_rows>0){
        $f=$q->fetch_assoc();
        $path = __DIR__."/uploads/meeting_files/".$f['file_path'];
        if(file_exists($path)) unlink($path);
        $conn->query("DELETE FROM meeting_files WHERE id=$fid");
        echo json_encode(["ok"=>true,"message"=>"File deleted successfully!","meeting_id"=>$f['meeting_id']]);
    } else {
        echo json_encode(["ok"=>false,"message"=>"File not found."]);
    }
    exit;
}

// ==== VIEW ATTENDANCE ====
if (isset($_GET['action']) && $_GET['action'] === 'view_attendance') {
    $mid = intval($_GET['meeting_id']);
    $rows = $conn->query("
        SELECT u.full_name, u.mobile_email,
               IF(a.id IS NULL,'Not Marked','Marked') AS attendance_status,
               a.marked_at
        FROM meeting_invites i
        JOIN users u ON u.id=i.emp_id
        LEFT JOIN meeting_attendance a ON a.user_id=u.id AND a.meeting_id=i.meeting_id
        WHERE i.meeting_id=$mid
    ");
    $data=[];
    while($r=$rows->fetch_assoc()) $data[]=$r;
    echo json_encode($data);
    exit;
}
?>
