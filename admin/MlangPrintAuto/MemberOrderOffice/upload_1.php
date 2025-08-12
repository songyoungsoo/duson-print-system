<?php
if (isset($_FILES['photofile_1']) && is_uploaded_file($_FILES['photofile_1']['tmp_name'])) {
    $file = $_FILES['photofile_1'];
    $photofile_1_name = basename($file['name']);
    $photofile_1_tmp  = $file['tmp_name'];
    $photofile_1_size = $file['size'];

    $upload_dir = $upload_dir ?? "./upload"; // �⺻ ���ε� ���丮 ����
    $MAXFSIZE = $MAXFSIZE ?? 2048; // �ִ� ũ�� (KB)

    $file_info = pathinfo($photofile_1_name);
    $file_extension = strtolower($file_info['extension'] ?? '');

    // ���� Ȯ���� ����
    $blocked_ext = ['php', 'php3', 'phtml', 'inc', 'asp'];
    if (in_array($file_extension, $blocked_ext)) {
        echo "<script>alert('? ���Ȼ��� ������ .php, .asp ���� ���ε��� �� �����ϴ�. Ȯ���ڸ� �������ּ���.');</script>";
        exit;
    }

    // ���� ������ üũ
    if ($photofile_1_size > ($MAXFSIZE * 1024)) {
        $size_kb = intval($photofile_1_size / 1024);
        echo "<script>alert('? ���� ũ�� �ʰ�: {$size_kb}KB (�ִ� ���: {$MAXFSIZE}KB)');</script>";
        exit;
    }

    // �ѱ� ���ϸ� ��ȯ ó��
    if (preg_match('/[^a-zA-Z0-9_\-\.]/', $file_info['filename'])) {
        $unique_code = date("YmdHis") . rand(1000, 9999);
        $photofile_1_name = "{$unique_code}.{$file_extension}";
    }

    // ���� ���� ���� �� �̸� ����
    $destination = "{$upload_dir}/{$photofile_1_name}";
    if (file_exists($destination)) {
        $photofile_1_name = time() . "_{$photofile_1_name}";
        $destination = "{$upload_dir}/{$photofile_1_name}";
    }

    // ���� ���� �̵�
    if (!move_uploaded_file($photofile_1_tmp, $destination)) {
        echo "<script>alert('? ���� �̵� ����. ���� �Ǵ� ��θ� Ȯ���ϼ���.');</script>";
        exit;
    }

    $photofile_1Name = $photofile_1_name;
}
?>
