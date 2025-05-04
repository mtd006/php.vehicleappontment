<?php
// Start session management (if not already started)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once 'db_config.php';

// Set response header to JSON
header('Content-Type: application/json');

// Basic Input Sanitization Helper
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    // Allow basic HTML in certain fields if needed, but generally strip tags
    // For specific fields like 'cargo_details' or 'description', you might use a different sanitizer
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// --- Authentication Functions ---

function registerUser($conn, $username, $password) {
    if (empty($username) || empty($password)) {
        return ['success' => false, 'message' => 'Username and password are required.'];
    }
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
    }

    $username = sanitize_input($username);
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Check if username already exists
    $stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        return ['success' => false, 'message' => 'Username already taken.'];
    }
    mysqli_stmt_close($stmt);

    // Insert new user
    $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password_hash) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ss", $username, $password_hash);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return ['success' => true, 'message' => 'Registration successful. Please login.'];
    } else {
        $error_msg = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        error_log("Registration failed: " . $error_msg); // Log actual error
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

function loginUser($conn, $username, $password) {
    if (empty($username) || empty($password)) {
        return ['success' => false, 'message' => 'Username and password are required.'];
    }

    $username = sanitize_input($username);
    $stmt = mysqli_prepare($conn, "SELECT user_id, password_hash FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $user_id, $stored_hash);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt); // Close statement ASAP

    if ($user_id && password_verify($password, $stored_hash)) {
        // Regenerate session ID for security
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        // Store last login time? (Optional)
        return ['success' => true, 'message' => 'Login successful.', 'user' => ['id' => $user_id, 'username' => $username]];
    } else {
        return ['success' => false, 'message' => 'Invalid username or password.'];
    }
}

function logoutUser() {
    $_SESSION = array(); // Unset all session variables
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    return ['success' => true, 'message' => 'Logged out successfully.'];
}

function checkSession() {
    if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
        return ['loggedIn' => true, 'user' => ['id' => $_SESSION['user_id'], 'username' => $_SESSION['username']]];
    } else {
        return ['loggedIn' => false];
    }
}

// --- Data Fetching Functions ---

function getDropdownData($conn, $table) {
    $data = [];
    $allowed_tables = ['warehouses', 'transporters', 'vehicles', 'drivers', 'cargo_types', 'purchase_orders']; // Whitelist tables

    if (!in_array($table, $allowed_tables)) {
       return ['success' => false, 'message' => 'Invalid data type requested.'];
    }

    $id_col = 'id';
    $name_col = 'name'; // Default name column
    $active_check = ''; // Condition for active items

    // Adjust columns based on table
    switch ($table) {
        case 'vehicles':
             $name_col = 'vehicle_number';
             $active_check = ' WHERE is_active = TRUE'; // Only show active vehicles
             break;
        case 'drivers':
             $name_col = 'name'; // Keep 'name'
             $active_check = ' WHERE is_active = TRUE'; // Only show active drivers
             break;
        case 'cargo_types': $name_col = 'type_name'; break;
        case 'purchase_orders':
            $name_col = 'po_number';
            $active_check = " WHERE status IN ('Open', 'Partial')"; // Only show open/partial POs?
            break;
        // Warehouses and Transporters use 'name'
    }

    $query = "SELECT $id_col, $name_col FROM $table $active_check ORDER BY $name_col ASC";

    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        mysqli_free_result($result);
        return ['success' => true, 'data' => $data];
    } else {
        error_log("Error fetching dropdown data for $table: " . mysqli_error($conn));
        return ['success' => false, 'message' => "Error fetching data for $table."];
    }
}

// --- Appointment CRUD Functions ---

function getAppointments($conn, $filters = []) {
    $appointments = [];
    // Base query with JOINs for readable data
    $query = "SELECT
                a.*,
                v.vehicle_number, v.type AS vehicle_type,
                d.name AS driver_name, d.contact_number AS driver_contact,
                t.name AS transporter_name,
                w.name AS warehouse_name,
                ct.type_name AS cargo_type_name,
                po.po_number
              FROM appointments a
              LEFT JOIN vehicles v ON a.vehicle_id = v.id
              LEFT JOIN drivers d ON a.driver_id = d.id
              LEFT JOIN transporters t ON a.transporter_id = t.id
              LEFT JOIN warehouses w ON a.warehouse_id = w.id
              LEFT JOIN cargo_types ct ON a.cargo_type_id = ct.id
              LEFT JOIN purchase_orders po ON a.po_id = po.id"; // LEFT JOINs are safer if related data might be deleted/missing

    // Add Filtering Logic
    $where_clauses = [];
    $params = [];
    $types = '';

    if (!empty($filters['status'])) {
        $where_clauses[] = "a.status = ?";
        $params[] = $filters['status']; // Already sanitized by caller
        $types .= 's';
    }
    if (!empty($filters['warehouse_id'])) {
        $where_clauses[] = "a.warehouse_id = ?";
        $params[] = (int)$filters['warehouse_id'];
        $types .= 'i';
    }
     if (!empty($filters['transporter_id'])) {
        $where_clauses[] = "a.transporter_id = ?";
        $params[] = (int)$filters['transporter_id'];
        $types .= 'i';
    }
     if (!empty($filters['date_from'])) {
        $where_clauses[] = "DATE(a.appointment_datetime) >= ?";
        $params[] = $filters['date_from'];
        $types .= 's';
    }
     if (!empty($filters['date_to'])) {
        $where_clauses[] = "DATE(a.appointment_datetime) <= ?";
        $params[] = $filters['date_to'];
        $types .= 's';
    }

    if (!empty($where_clauses)) {
        $query .= " WHERE " . implode(" AND ", $where_clauses);
    }

    $query .= " ORDER BY a.appointment_datetime DESC";

    // Execute Query
    $stmt = null;
    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
             error_log("Prepare failed for getAppointments: (" . $conn->errno . ") " . $conn->error);
             return ['success' => false, 'message' => 'Database error preparing statement.'];
        }
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($conn, $query);
    }

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $appointments[] = $row;
        }
        if ($stmt) mysqli_stmt_close($stmt); else mysqli_free_result($result);
        return ['success' => true, 'data' => $appointments];
    } else {
         if ($stmt) mysqli_stmt_close($stmt);
        error_log("Error fetching appointments: " . mysqli_error($conn));
        return ['success' => false, 'message' => 'Error fetching appointments.'];
    }
}

function addAppointment($conn, $data) {
    // Validation (data is assumed sanitized by caller)
    $required_fields = ['vehicle_id', 'driver_id', 'transporter_id', 'warehouse_id', 'cargo_type_id', 'appointment_datetime', 'cargo_details'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            return ['success' => false, 'message' => "Missing required field: $field"];
        }
    }
    // Date validation? Ensure it's a valid format?
    // Check if related IDs (vehicle, driver etc.) actually exist? (more robust)

    // Generate Unique ID
    $appointment_uid = 'APT-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8)); // More random

    // Prepare Statement
    $query = "INSERT INTO appointments (appointment_uid, vehicle_id, driver_id, transporter_id, warehouse_id, cargo_type_id, po_id, appointment_datetime, cargo_details, special_instructions, status, created_by, estimated_duration_mins)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        error_log("Prepare failed for addAppointment: (" . $conn->errno . ") " . $conn->error);
        return ['success' => false, 'message' => 'Database error preparing statement.'];
    }

    // Sanitize and bind parameters
    $vehicle_id = (int)$data['vehicle_id'];
    $driver_id = (int)$data['driver_id'];
    $transporter_id = (int)$data['transporter_id'];
    $warehouse_id = (int)$data['warehouse_id'];
    $cargo_type_id = (int)$data['cargo_type_id'];
    $po_id = !empty($data['po_id']) ? (int)$data['po_id'] : null;
    $appointment_datetime = $data['appointment_datetime']; // Assume 'YYYY-MM-DD HH:MM:SS' format
    $cargo_details = $data['cargo_details'];
    $special_instructions = $data['special_instructions'] ?? null;
    $status = 'Scheduled'; // Default status
    $created_by = $_SESSION['user_id'] ?? null;
    $estimated_duration_mins = isset($data['estimated_duration_mins']) ? (int)$data['estimated_duration_mins'] : 60;


    mysqli_stmt_bind_param($stmt, "siiiiisssssii",
        $appointment_uid,
        $vehicle_id,
        $driver_id,
        $transporter_id,
        $warehouse_id,
        $cargo_type_id,
        $po_id,
        $appointment_datetime,
        $cargo_details,
        $special_instructions,
        $status,
        $created_by,
        $estimated_duration_mins
    );

    // Execute
    if (mysqli_stmt_execute($stmt)) {
        $new_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        return ['success' => true, 'message' => 'Appointment scheduled successfully.', 'appointment_id' => $new_id, 'appointment_uid' => $appointment_uid];
    } else {
        $error_msg = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        error_log("Error adding appointment: " . $error_msg);
        // Check for duplicate UID - should be unlikely with better generation
        if (mysqli_errno($conn) == 1062) {
             return ['success' => false, 'message' => 'Failed to schedule appointment due to duplicate internal ID. Please try again.'];
        }
        return ['success' => false, 'message' => 'Failed to schedule appointment. ' . $error_msg];
    }
}

function updateAppointmentStatus($conn, $appointment_id, $new_status, $details = []) {
     // Validate status
    $allowed_statuses = ['Scheduled', 'Arrived', 'Unloading', 'Delayed', 'Completed', 'Cancelled'];
    if (!in_array($new_status, $allowed_statuses)) {
        return ['success' => false, 'message' => 'Invalid status provided.'];
    }

    $appointment_id = (int)$appointment_id;
    $update_fields = ["status = ?"];
    $params = [$new_status]; // Assume sanitized by caller
    $types = "s";
    $now = date('Y-m-d H:i:s'); // Current timestamp

    // Update relevant timestamps and details based on status
    switch ($new_status) {
        case 'Arrived':
            $update_fields[] = "arrival_datetime = ?";
            $params[] = $details['arrival_datetime'] ?? $now; // Use provided time or now
            $types .= "s";
            if(isset($details['gate_pass_number'])) { // Only update if provided
                $update_fields[] = "gate_pass_number = ?";
                 $params[] = $details['gate_pass_number'];
                 $types .= "s";
            }
             if(isset($details['unloading_bay_no'])) { // Only update if provided
                $update_fields[] = "unloading_bay_no = ?";
                 $params[] = $details['unloading_bay_no'];
                 $types .= "s";
            }
            break;
        case 'Unloading':
            $update_fields[] = "unloading_start_datetime = ?";
             $params[] = $details['unloading_start_datetime'] ?? $now;
            $types .= "s";
             // Might re-update bay/gatepass if changed
             if(isset($details['gate_pass_number'])) {
                $update_fields[] = "gate_pass_number = ?";
                 $params[] = $details['gate_pass_number'];
                 $types .= "s";
            }
             if(isset($details['unloading_bay_no'])) {
                $update_fields[] = "unloading_bay_no = ?";
                 $params[] = $details['unloading_bay_no'];
                 $types .= "s";
            }
            break;
         case 'Completed':
            $update_fields[] = "unloading_end_datetime = ?";
             $params[] = $details['unloading_end_datetime'] ?? $now;
            $types .= "s";
            $update_fields[] = "departure_datetime = ?";
             $params[] = $details['departure_datetime'] ?? $now;
            $types .= "s";
             // Gate pass might be updated on completion too
             if(isset($details['gate_pass_number'])) {
                $update_fields[] = "gate_pass_number = ?";
                 $params[] = $details['gate_pass_number'];
                 $types .= "s";
            }
            break;
        // Add logic for Delayed, Cancelled if specific fields need updating
        case 'Delayed':
             // Maybe add a 'delay_reason' field to the table and update it here?
             if(isset($details['special_instructions'])) { // Overwrite instructions with reason?
                 $update_fields[] = "special_instructions = ?";
                 $params[] = $details['special_instructions'];
                 $types .= "s";
             }
            break;
         case 'Cancelled':
             // Maybe add a 'cancellation_reason' field?
             if(isset($details['special_instructions'])) {
                 $update_fields[] = "special_instructions = ?";
                 $params[] = $details['special_instructions'];
                 $types .= "s";
             }
             // Clear dynamic fields?
             $update_fields[] = "arrival_datetime = NULL"; $types .= ""; // No type needed for NULL
             $update_fields[] = "unloading_start_datetime = NULL"; $types .= "";
             $update_fields[] = "unloading_end_datetime = NULL"; $types .= "";
             $update_fields[] = "departure_datetime = NULL"; $types .= "";
             $update_fields[] = "gate_pass_number = NULL"; $types .= "";
             $update_fields[] = "unloading_bay_no = NULL"; $types .= "";

             break;
    }

    $params[] = $appointment_id; // Add appointment ID at the end for WHERE clause
    $types .= "i";

    $query = "UPDATE appointments SET " . implode(", ", $update_fields) . " WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        error_log("Prepare failed for updateAppointmentStatus: (" . $conn->errno . ") " . $conn->error);
        return ['success' => false, 'message' => 'Database error preparing statement.'];
    }

    // Need to handle NULL binding correctly if used (e.g., for cancellation)
    // mysqli_stmt_bind_param might need adjustments if NULLs are common, or use execute with array
    if (!empty($params)) { // Ensure params exist
       mysqli_stmt_bind_param($stmt, $types, ...$params);
    } else {
         // Should not happen with current logic, but handle defensively
         error_log("No parameters to bind in updateAppointmentStatus.");
         mysqli_stmt_close($stmt);
         return ['success' => false, 'message' => 'Internal error during status update.'];
    }


    if (mysqli_stmt_execute($stmt)) {
         $affected_rows = mysqli_stmt_affected_rows($stmt);
         mysqli_stmt_close($stmt);
         // Affected rows can be 0 if status is the same but other details change
         // Consider success if execute worked without error
         return ['success' => true, 'message' => 'Appointment status updated successfully.'];

         /* Original logic checking affected_rows > 0:
         if (mysqli_stmt_affected_rows($stmt) > 0) {
             mysqli_stmt_close($stmt);
            return ['success' => true, 'message' => 'Appointment status updated successfully.'];
         } else {
             mysqli_stmt_close($stmt);
             // Could query to check if the record exists vs. no change made
             return ['success' => false, 'message' => 'Appointment not found or status/details not changed.'];
         }
         */
    } else {
        $error_msg = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        error_log("Error updating appointment status: " . $error_msg);
        return ['success' => false, 'message' => 'Failed to update status. ' . $error_msg];
    }
}

// --- CSV Import Placeholder ---
function importFromCSV($conn, $file_path) {
    // !! Security Note: Ensure $file_path comes from a secure upload mechanism
    // !! Validate file type and size before processing.
    // Requires robust error handling and parsing logic (e.g., using fgetcsv)
    // Map CSV columns to database fields carefully.
    /* Example Structure:
    $required_headers = ['vehicle_number', 'driver_contact_number', 'warehouse_name', 'appointment_datetime', ...]; // Define expected headers
    $handle = fopen($file_path, "r");
    if ($handle === FALSE) {
        return ['success' => false, 'message' => 'Could not open CSV file.'];
    }

    $header = fgetcsv($handle);
    if ($header === FALSE || count(array_diff($required_headers, $header)) > 0) { // Check if all required headers exist
        fclose($handle);
        return ['success' => false, 'message' => 'Invalid CSV header. Expected columns: ' . implode(', ', $required_headers)];
    }
    $header_map = array_flip($header); // Map header names to indices

    $rowCount = 0; $successCount = 0;
    $errors = [];
    mysqli_begin_transaction($conn);

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $rowCount++;
        $appointment_data = [];
        // Map CSV data to appointment fields using $header_map
        // e.g., $appointment_data['vehicle_number'] = $data[$header_map['vehicle_number']];
        // --- Look up IDs based on names/numbers ---
        // Find vehicle_id from vehicle_number, driver_id from contact, warehouse_id from name etc.
        // This requires extra queries or pre-loading lookup tables. Handle cases where lookups fail.
        // --- Validate data (date formats, required fields, etc.) ---
        if (validation_fails) {
            $errors[] = "Error on row $rowCount: Validation failed - [Reason]";
            continue; // Skip this row
        }

        // Call addAppointment or perform direct INSERT carefully
        $result = addAppointment($conn, sanitize_input($appointment_data)); // Sanitize before passing
        if (!$result['success']) {
             $errors[] = "Error adding row $rowCount: " . $result['message'];
        } else {
             $successCount++;
        }
    }
    fclose($handle);

    if (empty($errors)) {
        mysqli_commit($conn);
        return ['success' => true, 'message' => "Imported $successCount / $rowCount records successfully."];
    } else {
         mysqli_rollback($conn);
         // Limit number of errors shown
         $error_summary = array_slice($errors, 0, 10);
         if(count($errors) > 10) $error_summary[] = "... (" . (count($errors) - 10) . " more errors)";
         return ['success' => false, 'message' => "Import failed with errors. $successCount / $rowCount records imported before rollback.", 'errors' => $error_summary];
    }
    */
    return ['success' => false, 'message' => 'CSV Import function not fully implemented. Requires backend file processing and data mapping/validation.']; // Placeholder
}


// --- Generic CRUD Functions for Management Data ---

function fetchData($conn, $table, $columns = "*", $joins = "", $where = "", $params = [], $types = "") {
    $data = [];
    $allowed_tables = ['warehouses', 'transporters', 'vehicles', 'drivers', 'cargo_types', 'purchase_orders'];
    if (!in_array($table, $allowed_tables)) {
        return ['success' => false, 'message' => 'Invalid table specified.'];
    }

    $query = "SELECT $columns FROM $table $joins";
    if (!empty($where)) {
        $query .= " WHERE $where";
    }
    // Adjust default order based on table for better UX
    $orderBy = 'id DESC';
     if ($table === 'warehouses' || $table === 'transporters' || $table === 'drivers') $orderBy = 'name ASC';
     if ($table === 'vehicles') $orderBy = 'vehicle_number ASC';
     if ($table === 'cargo_types') $orderBy = 'type_name ASC';
     if ($table === 'purchase_orders') $orderBy = 'po_number DESC';

    $query .= " ORDER BY $orderBy";

    $stmt = null;
    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            error_log("Prepare failed for $table: (" . $conn->errno . ") " . $conn->error);
            return ['success' => false, 'message' => "Database error preparing statement for $table."];
        }
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($conn, $query);
    }

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Format dates/booleans for display if needed (though JS usually handles this)
             if(isset($row['is_active'])) $row['is_active_display'] = $row['is_active'] ? 'Yes' : 'No';
             if(isset($row['requires_special_handling'])) $row['requires_special_handling_display'] = $row['requires_special_handling'] ? 'Yes' : 'No';
            $data[] = $row;
        }
         if ($stmt) mysqli_stmt_close($stmt); else mysqli_free_result($result);
        return ['success' => true, 'data' => $data];
    } else {
        if ($stmt) mysqli_stmt_close($stmt);
        $error_msg = mysqli_error($conn);
        error_log("Error fetching data for $table: " . $error_msg);
        return ['success' => false, 'message' => "Error fetching data for $table: $error_msg"];
    }
}

function addData($conn, $table, $data) {
    $allowed_tables = ['warehouses', 'transporters', 'vehicles', 'drivers', 'cargo_types', 'purchase_orders'];
     if (!in_array($table, $allowed_tables)) return ['success' => false, 'message' => 'Invalid table.'];

    // Basic validation (data assumed sanitized by caller)
    // Example:
    if (($table === 'warehouses' || $table === 'transporters' || $table === 'drivers') && empty($data['name'])) {
         return ['success' => false, 'message' => ucfirst($table) . ' name is required.'];
    }
    if ($table === 'vehicles' && (empty($data['vehicle_number']) || empty($data['transporter_id']))) {
        return ['success' => false, 'message' => 'Vehicle number and transporter are required.'];
    }
     if ($table === 'cargo_types' && empty($data['type_name'])) {
        return ['success' => false, 'message' => 'Cargo type name is required.'];
    }
     if ($table === 'purchase_orders' && empty($data['po_number'])) {
        return ['success' => false, 'message' => 'PO number is required.'];
    }

    // Nullify empty date fields instead of sending empty strings
    $date_fields = ['insurance_expiry', 'last_maintenance', 'license_expiry', 'order_date', 'expected_delivery_date'];
     foreach($date_fields as $field) {
         if (isset($data[$field]) && empty($data[$field])) {
            $data[$field] = null;
         }
     }
     // Allow transporter_id to be optional (null) for drivers
     if ($table === 'drivers' && isset($data['transporter_id']) && empty($data['transporter_id'])) {
         $data['transporter_id'] = null;
     }


    $fields = array_keys($data);
    $placeholders = array_map(function($field) { return '?'; }, $fields);
    $values = array_values($data);
    $types = '';

    // Build types string based on field names/types (more robust)
    foreach($fields as $index => $field) {
        $value = $values[$index];
        if ($value === null) {
            $types .= 's'; // Bind NULL as string technically works, but can use 'i'/'d'/'s' based on column type if needed
        } elseif (in_array($field, ['transporter_id', 'vehicle_id'])) {
            $types .= 'i';
            $values[$index] = (int)$value;
        } elseif (in_array($field, ['capacity_tons'])) {
            $types .= 'd';
            $values[$index] = (float)$value;
        } elseif (in_array($field, ['is_active', 'requires_special_handling'])) {
            $types .= 'i'; // Treat boolean as integer 0 or 1
            $values[$index] = ($value == '1' || $value === true) ? 1 : 0;
        } else {
            $types .= 's'; // Default to string (covers text, varchar, date, enum, etc.)
        }
    }

    $query = "INSERT INTO $table (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
         error_log("Prepare failed for adding to $table: (" . $conn->errno . ") " . $conn->error);
        return ['success' => false, 'message' => "Database error preparing add statement for $table."];
    }

    mysqli_stmt_bind_param($stmt, $types, ...$values);

    if (mysqli_stmt_execute($stmt)) {
        $new_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        return ['success' => true, 'message' => ucfirst(str_replace('_', ' ', $table)) . ' added successfully.', 'id' => $new_id];
    } else {
        $error_msg = mysqli_stmt_error($stmt);
        $error_code = mysqli_stmt_errno($stmt);
        mysqli_stmt_close($stmt);
        error_log("Error adding to $table: [$error_code] $error_msg");
        if ($error_code == 1062) { // Duplicate entry
             return ['success' => false, 'message' => 'Duplicate entry detected. Please check unique fields (e.g., name, code, number, email, phone).'];
        }
        return ['success' => false, 'message' => 'Failed to add ' . str_replace('_', ' ', $table) . '. ' . $error_msg];
    }
}

function updateData($conn, $table, $id, $data) {
    $allowed_tables = ['warehouses', 'transporters', 'vehicles', 'drivers', 'cargo_types', 'purchase_orders'];
     if (!in_array($table, $allowed_tables)) return ['success' => false, 'message' => 'Invalid table.'];
    if (empty($id) || !is_numeric($id)) return ['success' => false, 'message' => 'Invalid ID provided.'];
    if (empty($data)) return ['success' => false, 'message' => 'No data provided for update.'];

    $id = (int)$id;
    unset($data['id']); // Don't update the ID itself
    unset($data['entityType']); // Remove helper field if present

    // Nullify empty date fields
    $date_fields = ['insurance_expiry', 'last_maintenance', 'license_expiry', 'order_date', 'expected_delivery_date'];
     foreach($date_fields as $field) {
         if (isset($data[$field]) && empty($data[$field])) {
            $data[$field] = null;
         }
     }
    // Allow transporter_id to be optional (null) for drivers
     if ($table === 'drivers' && isset($data['transporter_id']) && empty($data['transporter_id'])) {
         $data['transporter_id'] = null;
     }

    $fields = [];
    $values = [];
    $types = '';

    foreach ($data as $key => $value) {
        $fields[] = "$key = ?";
        $values[] = $value; // Add value first

        // Determine type (similar to addData)
         if ($value === null) {
            $types .= 's';
        } elseif (in_array($key, ['transporter_id', 'vehicle_id'])) {
            $types .= 'i';
            $values[count($values)-1] = (int)$value; // Correct the value type
        } elseif (in_array($key, ['capacity_tons'])) {
            $types .= 'd';
             $values[count($values)-1] = (float)$value;
        } elseif (in_array($key, ['is_active', 'requires_special_handling'])) {
            $types .= 'i';
             $values[count($values)-1] = ($value == '1' || $value === true) ? 1 : 0;
        } else {
            $types .= 's';
         }
    }

    if (empty($fields)) {
         return ['success' => false, 'message' => 'No valid fields provided for update.'];
    }


    $values[] = $id; // Add ID for WHERE clause
    $types .= 'i';

    $query = "UPDATE $table SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

     if (!$stmt) {
         error_log("Prepare failed for updating $table: (" . $conn->errno . ") " . $conn->error);
        return ['success' => false, 'message' => "Database error preparing update statement for $table."];
    }

    mysqli_stmt_bind_param($stmt, $types, ...$values);

    if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        // Return success even if affected_rows is 0 (data might be the same)
        return ['success' => true, 'message' => ucfirst(str_replace('_', ' ', $table)) . ' updated successfully.'];
        /*
        if ($affected_rows > 0) {
            return ['success' => true, 'message' => ucfirst(str_replace('_', ' ', $table)) . ' updated successfully.'];
        } else {
             // Check if the record exists - could query first, or just return this message
            return ['success' => true, 'message' => 'No changes detected or record not found.'];
        }
        */
    } else {
        $error_msg = mysqli_stmt_error($stmt);
        $error_code = mysqli_stmt_errno($stmt);
        mysqli_stmt_close($stmt);
        error_log("Error updating $table (ID: $id): [$error_code] $error_msg");
         if ($error_code == 1062) { // Duplicate entry
             return ['success' => false, 'message' => 'Update failed due to duplicate entry on a unique field.'];
        }
        return ['success' => false, 'message' => 'Failed to update ' . str_replace('_', ' ', $table) . '. ' . $error_msg];
    }
}

function deleteData($conn, $table, $id) {
    $allowed_tables = ['warehouses', 'transporters', 'vehicles', 'drivers', 'cargo_types', 'purchase_orders'];
    if (!in_array($table, $allowed_tables)) return ['success' => false, 'message' => 'Invalid table.'];
    if (empty($id) || !is_numeric($id)) return ['success' => false, 'message' => 'Invalid ID provided.'];

    $id = (int)$id;

    // Dependency checks based on ON DELETE constraints (RESTRICT)
    $dependency_checks = [
        'warehouses' => 'appointments',
        'transporters' => 'appointments', // Also checked vehicles earlier, but appointments is direct
        'vehicles' => 'appointments',
        'drivers' => 'appointments',
        'cargo_types' => 'appointments',
        // POs have ON DELETE SET NULL, so no check needed for appointments referencing them
    ];

    if (isset($dependency_checks[$table])) {
        $referencing_table = $dependency_checks[$table];
        $referencing_column = $table === 'cargo_types' ? 'cargo_type_id' : rtrim($table, 's') . '_id'; // e.g., warehouse_id

        $stmt_check = mysqli_prepare($conn, "SELECT id FROM $referencing_table WHERE $referencing_column = ? LIMIT 1");
        if($stmt_check) {
            mysqli_stmt_bind_param($stmt_check, "i", $id);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);
            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                 mysqli_stmt_close($stmt_check);
                 $entity_name = ucfirst(str_replace('_', ' ', rtrim($table, 's')));
                 return ['success' => false, 'message' => "Cannot delete $entity_name. It is currently assigned to one or more appointments."];
            }
             mysqli_stmt_close($stmt_check);
        } else {
             error_log("Failed to prepare dependency check for $table deleting ID $id.");
             // Optionally prevent deletion if check fails, or proceed with caution
             // return ['success' => false, 'message' => 'Could not perform dependency check. Deletion aborted.'];
        }

        // Specific check for Transporter -> Vehicle link (also RESTRICT)
         if ($table === 'transporters') {
            $stmt_check_v = mysqli_prepare($conn, "SELECT id FROM vehicles WHERE transporter_id = ? LIMIT 1");
             if($stmt_check_v) {
                 mysqli_stmt_bind_param($stmt_check_v, "i", $id);
                 mysqli_stmt_execute($stmt_check_v);
                 mysqli_stmt_store_result($stmt_check_v);
                 if (mysqli_stmt_num_rows($stmt_check_v) > 0) {
                     mysqli_stmt_close($stmt_check_v);
                     return ['success' => false, 'message' => 'Cannot delete transporter. Vehicles are associated with it. Remove or reassign vehicles first.'];
                 }
                 mysqli_stmt_close($stmt_check_v);
             }
         }
    }


    $query = "DELETE FROM $table WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
         error_log("Prepare failed for deleting from $table: (" . $conn->errno . ") " . $conn->error);
        return ['success' => false, 'message' => "Database error preparing delete statement for $table."];
    }

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        if ($affected_rows > 0) {
            return ['success' => true, 'message' => ucfirst(str_replace('_', ' ', $table)) . ' deleted successfully.'];
        } else {
            return ['success' => false, 'message' => 'Record not found or already deleted.'];
        }
    } else {
         // Error might be due to constraints not caught above, or other issues
         $error_code = mysqli_stmt_errno($stmt);
         $error_msg = mysqli_stmt_error($stmt);
         mysqli_stmt_close($stmt);
         error_log("Error deleting from $table (ID: $id): [$error_code] $error_msg");

         if ($error_code == 1451) { // Foreign key constraint fails (should be caught above, but as fallback)
             return ['success' => false, 'message' => 'Cannot delete this item because it is referenced by other records.'];
         }
         return ['success' => false, 'message' => 'Failed to delete ' . str_replace('_', ' ', $table) . '. ' . $error_msg];
    }
}


// --- API Routing ---

$action = $_REQUEST['action'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];
$response = ['success' => false, 'message' => 'Invalid action or request method.'];

// --- Authentication Endpoints (Publicly accessible) ---
if ($method === 'POST' && $action === 'register') {
    $data = json_decode(file_get_contents('php://input'), true);
    $response = registerUser($conn, $data['username'] ?? null, $data['password'] ?? null);
} elseif ($method === 'POST' && $action === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    $response = loginUser($conn, $data['username'] ?? null, $data['password'] ?? null);
} elseif ($action === 'logout') { // Can be GET or POST
    $response = logoutUser();
} elseif ($action === 'check_session' && $method === 'GET') { // Should be GET
    $response = checkSession();
}
// --- Protected Endpoints (Require Login) ---
elseif (isset($_SESSION['user_id'])) {
    // Sanitize input data depending on method
    $input_data = [];
    if ($method === 'POST' || $method === 'PUT') {
         $content_type = trim($_SERVER["CONTENT_TYPE"] ?? '');
         if (stripos($content_type, 'application/json') !== false) {
            $json_data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $input_data = sanitize_input($json_data);
            } else {
                 $response = ['success' => false, 'message' => 'Invalid JSON payload.'];
                 goto send_response; // Skip switch if payload is bad
            }
         } elseif (stripos($content_type, 'multipart/form-data') !== false) {
            // Handle file uploads specifically (e.g., for CSV import)
            // Sanitization needs care here, handle $_POST and $_FILES separately
            $input_data = sanitize_input($_POST); // Sanitize text fields
            // File data in $_FILES needs different handling
         }
         else {
             // Assume form-urlencoded
             $input_data = sanitize_input($_POST);
         }
    } elseif ($method === 'GET' || $method === 'DELETE') { // Allow DELETE data in query string for simplicity
        $input_data = sanitize_input($_GET);
    }

    // Extract common parameters after sanitization
     $entity_id = isset($input_data['id']) && is_numeric($input_data['id']) ? (int)$input_data['id'] : null;


    switch ($action) {
        // Dropdown Data
        case 'get_dropdown':
            if ($method === 'GET' && isset($input_data['type'])) {
                $response = getDropdownData($conn, $input_data['type']); // type already sanitized
            } else {
                 $response = ['success' => false, 'message' => 'Missing or invalid dropdown type parameter.'];
            }
            break;

        // Appointments
        case 'get_appointments':
            if ($method === 'GET') {
                $filters = $input_data; // Already sanitized
                unset($filters['action']);
                $response = getAppointments($conn, $filters);
            }
            break;
        case 'add_appointment':
            if ($method === 'POST') {
                $response = addAppointment($conn, $input_data); // Pass sanitized data
            }
            break;
        case 'update_appointment_status':
             if ($method === 'POST') { // Or PUT
                 $response = updateAppointmentStatus(
                     $conn,
                     $input_data['appointment_id'] ?? null, // Ensure ID is passed correctly
                     $input_data['status'] ?? null,
                     $input_data['details'] ?? [] // Details already sanitized
                 );
             }
             break;

        // --- Warehouse CRUD ---
        case 'get_warehouses':
             if ($method === 'GET') $response = fetchData($conn, 'warehouses');
             break;
        case 'add_warehouse':
             if ($method === 'POST') $response = addData($conn, 'warehouses', $input_data);
             break;
        case 'update_warehouse':
             if ($method === 'POST' && $entity_id) $response = updateData($conn, 'warehouses', $entity_id, $input_data);
              else $response = ['success' => false, 'message' => 'Invalid request for update warehouse. Requires valid ID.'];
             break;
        case 'delete_warehouse':
             if (($method === 'POST' || $method === 'DELETE') && $entity_id) $response = deleteData($conn, 'warehouses', $entity_id);
              else $response = ['success' => false, 'message' => 'Invalid request for delete warehouse. Requires valid ID.'];
             break;

        // --- Transporter CRUD ---
        case 'get_transporters':
             if ($method === 'GET') $response = fetchData($conn, 'transporters');
             break;
        case 'add_transporter':
             if ($method === 'POST') $response = addData($conn, 'transporters', $input_data);
             break;
        case 'update_transporter':
             if ($method === 'POST' && $entity_id) $response = updateData($conn, 'transporters', $entity_id, $input_data);
              else $response = ['success' => false, 'message' => 'Invalid request for update transporter. Requires valid ID.'];
             break;
        case 'delete_transporter':
             if (($method === 'POST' || $method === 'DELETE') && $entity_id) $response = deleteData($conn, 'transporters', $entity_id);
              else $response = ['success' => false, 'message' => 'Invalid request for delete transporter. Requires valid ID.'];
             break;

        // --- Vehicle CRUD ---
        case 'get_vehicles':
             if ($method === 'GET') {
                 $columns = "v.*, t.name as transporter_name";
                 $joins = "v LEFT JOIN transporters t ON v.transporter_id = t.id"; // LEFT JOIN safer
                 $response = fetchData($conn, 'vehicles v', $columns, $joins); // Alias table for join clarity
             }
             break;
        case 'add_vehicle':
             if ($method === 'POST') $response = addData($conn, 'vehicles', $input_data);
             break;
        case 'update_vehicle':
             if ($method === 'POST' && $entity_id) $response = updateData($conn, 'vehicles', $entity_id, $input_data);
              else $response = ['success' => false, 'message' => 'Invalid request for update vehicle. Requires valid ID.'];
             break;
        case 'delete_vehicle':
             if (($method === 'POST' || $method === 'DELETE') && $entity_id) $response = deleteData($conn, 'vehicles', $entity_id);
              else $response = ['success' => false, 'message' => 'Invalid request for delete vehicle. Requires valid ID.'];
             break;

        // --- Driver CRUD ---
         case 'get_drivers':
             if ($method === 'GET') {
                 $columns = "d.*, t.name as transporter_name";
                 $joins = "d LEFT JOIN transporters t ON d.transporter_id = t.id";
                 $response = fetchData($conn, 'drivers d', $columns, $joins); // Alias table
             }
             break;
        case 'add_driver':
             if ($method === 'POST') $response = addData($conn, 'drivers', $input_data);
             break;
        case 'update_driver':
             if ($method === 'POST' && $entity_id) $response = updateData($conn, 'drivers', $entity_id, $input_data);
              else $response = ['success' => false, 'message' => 'Invalid request for update driver. Requires valid ID.'];
             break;
        case 'delete_driver':
             if (($method === 'POST' || $method === 'DELETE') && $entity_id) $response = deleteData($conn, 'drivers', $entity_id);
              else $response = ['success' => false, 'message' => 'Invalid request for delete driver. Requires valid ID.'];
             break;

        // --- Cargo Type CRUD ---
        case 'get_cargo_types':
             if ($method === 'GET') $response = fetchData($conn, 'cargo_types');
             break;
        case 'add_cargo_type':
             if ($method === 'POST') $response = addData($conn, 'cargo_types', $input_data);
             break;
        case 'update_cargo_type':
             if ($method === 'POST' && $entity_id) $response = updateData($conn, 'cargo_types', $entity_id, $input_data);
              else $response = ['success' => false, 'message' => 'Invalid request for update cargo type. Requires valid ID.'];
             break;
        case 'delete_cargo_type':
             if (($method === 'POST' || $method === 'DELETE') && $entity_id) $response = deleteData($conn, 'cargo_types', $entity_id);
              else $response = ['success' => false, 'message' => 'Invalid request for delete cargo type. Requires valid ID.'];
             break;

        // --- Purchase Order CRUD ---
        case 'get_purchase_orders':
             if ($method === 'GET') $response = fetchData($conn, 'purchase_orders');
             break;
        case 'add_purchase_order':
             if ($method === 'POST') $response = addData($conn, 'purchase_orders', $input_data);
             break;
        case 'update_purchase_order':
             if ($method === 'POST' && $entity_id) $response = updateData($conn, 'purchase_orders', $entity_id, $input_data);
              else $response = ['success' => false, 'message' => 'Invalid request for update PO. Requires valid ID.'];
             break;
        case 'delete_purchase_order':
             if (($method === 'POST' || $method === 'DELETE') && $entity_id) $response = deleteData($conn, 'purchase_orders', $entity_id);
              else $response = ['success' => false, 'message' => 'Invalid request for delete PO. Requires valid ID.'];
             break;

        // --- Placeholder Endpoints ---
        case 'import_csv':
             if ($method === 'POST') {
                 // Requires proper file handling and security checks for $_FILES['csvFile']
                 if (isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] == UPLOAD_ERR_OK) {
                     // Securely move uploaded file and validate before passing path
                     $tmp_name = $_FILES["csvFile"]["tmp_name"];
                     // $safe_name = basename($_FILES["csvFile"]["name"]); // Basic safe name
                     // $upload_dir = '/path/to/secure/uploads/'; // Define secure upload dir
                     // $destination = $upload_dir . uniqid() . '-' . $safe_name;
                     // if(move_uploaded_file($tmp_name, $destination)) {
                     //    $response = importFromCSV($conn, $destination);
                     //    // Optionally delete file after processing: unlink($destination);
                     // } else {
                     //    $response = ['success' => false, 'message' => 'Failed to move uploaded file.'];
                     // }
                     $response = importFromCSV($conn, $tmp_name); // Using tmp_name directly is less secure but simpler for testing
                 } else {
                     $response = ['success' => false, 'message' => 'No file uploaded or upload error occurred. Error code: ' . ($_FILES['csvFile']['error'] ?? 'N/A')];
                 }
             }
             break;

        case 'fetch_public_data':
             // Requires specific API integration logic
             $response = ['success' => false, 'message' => 'Public data fetching not implemented.'];
             break;

        default:
            $response = ['success' => false, 'message' => 'Invalid action requested or method not allowed for logged-in user.'];
            break;
    }
}
// --- Handle Not Logged In for Protected Actions ---
elseif ($action && !in_array($action, ['register', 'login', 'check_session', 'logout'])) {
    $response = ['success' => false, 'message' => 'Authentication required.', 'auth_required' => true];
    http_response_code(401); // Unauthorized
}

// Label for jumping to the response output
send_response:

// Close the database connection
if (isset($conn) && $conn) {
    mysqli_close($conn);
}

// Output the JSON response
echo json_encode($response);
exit; // Terminate script execution
?>