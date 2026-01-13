<?php
// payroll_lib.php
require_once "Connect.php";

/**
 * Return true if working day (Mon-Fri). Adjust if you have holidays table.
 */
function is_working_day($date) {
    $dow = date('N', strtotime($date)); // 1..7
    return ($dow >= 1 && $dow <= 5);
}

/**
 * Check approved leave for the date
 */
function has_approved_leave($conn, $emp_id, $date) {
    $stmt = $conn->prepare("SELECT 1 FROM leaves WHERE emp_id=? AND status='Approved' AND start_date <= ? AND end_date >= ? LIMIT 1");
    $stmt->bind_param("iss", $emp_id, $date, $date);
    $stmt->execute();
    $res = $stmt->get_result();
    $found = $res->num_rows > 0;
    $stmt->close();
    return $found;
}

/**
 * Get attendance status for date (returns 'Present','Half','Absent' or null)
 */
function get_attendance_status($conn, $emp_id, $date) {
    $stmt = $conn->prepare("SELECT status FROM attendance WHERE emp_id=? AND date=? LIMIT 1");
    $stmt->bind_param("is", $emp_id, $date);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $stmt->close();
        return $row['status'];
    }
    $stmt->close();
    return null;
}

/**
 * Determine weight and label for a date
 */
function get_day_weight_and_label($conn, $emp_id, $date) {
    if (has_approved_leave($conn, $emp_id, $date)) {
        return ['status' => 'LeaveApproved', 'weight' => 0.5];
    }
    $att = get_attendance_status($conn, $emp_id, $date);
    if ($att === 'Present') return ['status' => 'Present', 'weight' => 1.0];
    if ($att === 'Half')    return ['status' => 'Half', 'weight' => 0.7];
    // if explicitly Absent or no record treat as Absent
    return ['status' => 'Absent', 'weight' => 0.0];
}

/**
 * Generate payroll for one employee for a given year, month.
 * Returns payroll_id on success, false on failure.
 */
function generate_payroll_for_employee($conn, $emp_id, $year, $month) {
    $first = sprintf("%04d-%02d-01", $year, $month);
    $last = date("Y-m-t", strtotime($first));

    $total_working_days = 0;
    $counted_days = 0.0;
    $breakdown = [];

    for ($d = strtotime($first); $d <= strtotime($last); $d += 86400) {
        $date = date('Y-m-d', $d);
        if (!is_working_day($date)) continue;
        $total_working_days++;
        $res = get_day_weight_and_label($conn, $emp_id, $date);
        $counted_days += $res['weight'];
        $breakdown[] = ['date' => $date, 'status' => $res['status'], 'weight' => $res['weight']];
    }

    // get salary
    $stmt = $conn->prepare("SELECT monthly_salary FROM users WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();
    $r = $stmt->get_result();
    if (!$r || $r->num_rows === 0) { $stmt->close(); return false; }
    $row = $r->fetch_assoc();
    $monthly_salary = (float)$row['monthly_salary'];
    $stmt->close();

    $gross_pay = 0.00;
    if ($total_working_days > 0) {
        $gross_pay = round($monthly_salary * ($counted_days / $total_working_days), 2);
    }

    // Check exist
    $stmt = $conn->prepare("SELECT id FROM payrolls WHERE emp_id=? AND year=? AND month=? LIMIT 1");
    $stmt->bind_param("iii", $emp_id, $year, $month);
    $stmt->execute();
    $rs = $stmt->get_result();
    if ($rs && $rs->num_rows > 0) {
        // update existing
        $existing = $rs->fetch_assoc();
        $payroll_id = $existing['id'];
        $stmt->close();
        $u = $conn->prepare("UPDATE payrolls SET total_working_days=?, counted_days=?, gross_pay=?, created_at=NOW() WHERE id=?");
        $u->bind_param("iddi", $total_working_days, $counted_days, $gross_pay, $payroll_id);
        if (!$u->execute()) { $u->close(); return false; }
        $u->close();
        // delete old details
        $conn->query("DELETE FROM payroll_details WHERE payroll_id=" . intval($payroll_id));
    } else {
        $stmt->close();
        $i = $conn->prepare("INSERT INTO payrolls (emp_id, year, month, total_working_days, counted_days, gross_pay) VALUES (?, ?, ?, ?, ?, ?)");
        $i->bind_param("iiiidd", $emp_id, $year, $month, $total_working_days, $counted_days, $gross_pay);
        if (!$i->execute()) { $i->close(); return false; }
        $payroll_id = $i->insert_id;
        $i->close();
    }

    // insert details
    $pd = $conn->prepare("INSERT INTO payroll_details (payroll_id, date, status, day_weight) VALUES (?, ?, ?, ?)");
    foreach ($breakdown as $b) {
        $pd->bind_param("issd", $payroll_id, $b['date'], $b['status'], $b['weight']);
        $pd->execute();
    }
    $pd->close();

    return $payroll_id;
}

/**
 * Generate payrolls for all employees
 */
function generate_payroll_for_all($conn, $year, $month) {
    $out = [];

    // Change this table name to match your employee table name (example: users or employees)
    $res = $conn->query("SELECT id FROM users");  

    while ($row = $res->fetch_assoc()) {
        $emp_id = (int)$row['id'];
        $pid = generate_payroll_for_employee($conn, $emp_id, $year, $month);
        $out[$emp_id] = $pid ? $pid : false;
    }
    return $out;
}
