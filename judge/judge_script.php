<?php
// judge/judge_script.php (Phiên bản có ghi log lỗi chi tiết)

// ===== PHẦN HÀM HỖ TRỢ =====

/**
 * Cập nhật trạng thái của submission trong CSDL.
 * @param PDO $pdo Đối tượng kết nối PDO.
 * @param int $submission_id ID của lần nộp bài.
 * @param string $status Trạng thái mới (vd: 'Accepted', 'Runtime Error').
 * @param string|null $details Chi tiết lỗi (nếu có).
 */
function update_status($pdo, $submission_id, $status, $details = null) {
    try {
        $stmt = $pdo->prepare("UPDATE submissions SET status = ?, error_details = ? WHERE id = ?");
        $stmt->execute([$status, $details, $submission_id]);
    } catch (\PDOException $e) {
        // Ghi lỗi vào file log của PHP nếu không thể cập nhật CSDL
        error_log("Failed to update status for submission {$submission_id}: " . $e->getMessage());
    }
}

/**
 * Dọn dẹp (xóa) thư mục làm việc và tất cả các file bên trong.
 * @param string $dir Đường dẫn đến thư mục cần xóa.
 */
function cleanup($dir) {
    if (!is_dir($dir)) {
        return;
    }
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = "$dir/$file";
        is_dir($path) ? cleanup($path) : unlink($path);
    }
    rmdir($dir);
}


// ===== PHẦN LOGIC CHÍNH =====

// 1. Kiểm tra và lấy submission ID từ argument dòng lệnh
if (!isset($argv[1])) {
    exit("Lỗi: Không có submission ID.\n");
}
$submission_id = (int)$argv[1];

// 2. Kết nối CSDL (chú ý đường dẫn tương đối chính xác)
require_once __DIR__ . '/../includes/db.php';

// 3. Lấy thông tin submission và test cases
try {
    $stmt = $pdo->prepare("SELECT * FROM submissions WHERE id = ?");
    $stmt->execute([$submission_id]);
    $submission = $stmt->fetch();

    if (!$submission) {
        exit("Lỗi: Không tìm thấy submission với ID {$submission_id}.\n");
    }

    $stmt = $pdo->prepare("SELECT * FROM test_cases WHERE problem_id = ?");
    $stmt->execute([$submission['problem_id']]);
    $test_cases = $stmt->fetchAll();

} catch (\PDOException $e) {
    exit("Lỗi CSDL: " . $e->getMessage() . "\n");
}


// 4. Tạo thư mục làm việc tạm thời
$working_dir = __DIR__ . '/submissions/' . $submission_id . '/';
if (!mkdir($working_dir, 0777, true) && !is_dir($working_dir)) {
    update_status($pdo, $submission_id, 'System Error', 'Không thể tạo thư mục làm việc.');
    exit("Lỗi: Không thể tạo thư mục làm việc.\n");
}


// 5. Logic chấm bài theo từng ngôn ngữ
$language = $submission['language'];
$source_code = $submission['source_code'];
$compile_error_path = $working_dir . 'compile_error.txt';
$time_limit = 2; // Giới hạn thời gian chạy là 2 giây

try {
    // --- BIÊN DỊCH (NẾU CẦN) ---
    switch ($language) {
        case 'cpp':
            $source_path = $working_dir . 'main.cpp';
            file_put_contents($source_path, $source_code);
            $executable_path = $working_dir . 'main_exec.exe'; // Thêm .exe cho Windows
            // Lệnh biên dịch, chuyển hướng lỗi vào file compile_error.txt
            exec("g++ -static {$source_path} -o {$executable_path} 2> {$compile_error_path}", $output, $return_code);
            if ($return_code !== 0) {
                $error_details = file_get_contents($compile_error_path);
                update_status($pdo, $submission_id, 'Compilation Error', $error_details);
                throw new Exception('Lỗi biên dịch C++');
            }
            break;
        case 'java':
            // Với Java, class chính phải tên là "Main"
            $source_path = $working_dir . 'Main.java';
            file_put_contents($source_path, $source_code);
            exec("javac {$source_path} 2> {$compile_error_path}", $output, $return_code);
            if ($return_code !== 0) {
                $error_details = file_get_contents($compile_error_path);
                update_status($pdo, $submission_id, 'Compilation Error', $error_details);
                throw new Exception('Lỗi biên dịch Java');
            }
            break;
        case 'python':
            $source_path = $working_dir . 'main.py';
            file_put_contents($source_path, $source_code);
            break;
    }

    // --- CHẠY VỚI CÁC TEST CASE ---
    foreach ($test_cases as $index => $case) {
        $input_path = $working_dir . 'input.txt';
        $user_output_path = $working_dir . 'user_output.txt';
        file_put_contents($input_path, $case['input']);

        $exec_command = '';
        switch ($language) {
            case 'cpp':
                $exec_command = escapeshellarg($executable_path);
                break;
            case 'java':
                $exec_command = "java -cp " . escapeshellarg($working_dir) . " Main";
                break;
            case 'python':
                $exec_command = "python " . escapeshellarg($source_path); // Dùng python thay vì python3 cho XAMPP trên Windows
                break;
        }

        // Chạy lệnh và chuyển hướng cả stdout và stderr để bắt lỗi
        $full_command = "{$exec_command} < {$input_path} > {$user_output_path} 2>&1";
        
        $exec_output_array = [];
        exec($full_command, $exec_output_array, $return_code);
        
        // Lấy output của chương trình từ file (vì stderr cũng bị ghi vào đó)
        $user_output = trim(file_get_contents($user_output_path));
        
        // Nếu có lỗi thực thi, output của chương trình chính là chi tiết lỗi
        if ($return_code !== 0) {
            update_status($pdo, $submission_id, 'Runtime Error', $user_output);
            throw new Exception("Lỗi thực thi ở test case " . ($index + 1));
        }

        // So sánh output nếu chạy thành công
        $correct_output = trim($case['output']);
        if ($user_output !== $correct_output) {
            $details = "Input:\n" . $case['input'] . "\n\nExpected Output:\n" . $correct_output . "\n\nYour Output:\n" . $user_output;
            update_status($pdo, $submission_id, 'Wrong Answer', $details);
            throw new Exception("Sai kết quả ở test case " . ($index + 1));
        }
    }

    // Nếu chạy qua hết các test case mà không có lỗi
    update_status($pdo, $submission_id, 'Accepted');

} catch (Exception $e) {
    // Lỗi đã được xử lý và cập nhật status, chỉ cần ghi log hệ thống nếu muốn
    error_log("Submission {$submission_id} failed: " . $e->getMessage());
} finally {
    // 6. Dọn dẹp thư mục làm việc sau khi chấm xong
    cleanup($working_dir); // Tạm thời vô hiệu hóa để bạn có thể vào xem file
}

echo "Chấm xong submission {$submission_id}.\n";
?>