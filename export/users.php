<?php
require_once '../config/config.php';
requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../users.php');
    exit;
}

$format = sanitize($_POST['format'] ?? 'excel');
$fields = $_POST['fields'] ?? ['basic'];

// Filtros (se houver)
$filters = [
    'search' => sanitize($_POST['search'] ?? ''),
    'status' => sanitize($_POST['status'] ?? ''),
    'date_from' => sanitize($_POST['date_from'] ?? ''),
    'date_to' => sanitize($_POST['date_to'] ?? '')
];

$userModel = new User();
$result = $userModel->getUsers($filters, 1, 10000); // Buscar todos os usuários
$users = $result['data'];

// Preparar dados para exportação
$exportData = [];
$headers = [];

// Definir cabeçalhos baseado nos campos selecionados
if (in_array('basic', $fields)) {
    $headers = array_merge($headers, ['ID', 'Nome', 'Email', 'CPF', 'Telefone', 'Status', 'Data Cadastro']);
}

if (in_array('financial', $fields)) {
    $headers = array_merge($headers, ['Saldo Carteira', 'Total Depositado', 'Total Sacado']);
}

if (in_array('investments', $fields)) {
    $headers = array_merge($headers, ['Investimentos Ativos', 'Código Indicação', 'Indicado Por']);
}

$exportData[] = $headers;

// Preparar dados dos usuários
foreach ($users as $user) {
    $row = [];
    
    if (in_array('basic', $fields)) {
        $row = array_merge($row, [
            $user['id'],
            $user['name'],
            $user['email'],
            $user['cpf'] ?? '',
            $user['telefone'] ?? '',
            ucfirst($user['status']),
            formatDate($user['created_at'], 'd/m/Y H:i')
        ]);
    }
    
    if (in_array('financial', $fields)) {
        $row = array_merge($row, [
            number_format($user['saldo_carteira'] ?? 0, 2, ',', '.'),
            number_format($user['total_depositado'] ?? 0, 2, ',', '.'),
            number_format($user['total_sacado'] ?? 0, 2, ',', '.')
        ]);
    }
    
    if (in_array('investments', $fields)) {
        $row = array_merge($row, [
            $user['investimentos_ativos'] ?? 0,
            $user['codigo_indicacao'],
            $user['codigo_indicador'] ?? ''
        ]);
    }
    
    $exportData[] = $row;
}

// Gerar arquivo baseado no formato
$filename = 'usuarios_' . date('Y-m-d_H-i-s');

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    foreach ($exportData as $row) {
        fputcsv($output, $row, ';');
    }
    
    fclose($output);
    
} else {
    // Excel (HTML table que o Excel pode abrir)
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    
    echo '<html>';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<style>';
    echo 'table { border-collapse: collapse; width: 100%; }';
    echo 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
    echo 'th { background-color: #f2f2f2; font-weight: bold; }';
    echo '</style>';
    echo '</head>';
    echo '<body>';
    echo '<table>';
    
    foreach ($exportData as $index => $row) {
        if ($index === 0) {
            echo '<thead><tr>';
            foreach ($row as $cell) {
                echo '<th>' . htmlspecialchars($cell) . '</th>';
            }
            echo '</tr></thead><tbody>';
        } else {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . htmlspecialchars($cell) . '</td>';
            }
            echo '</tr>';
        }
    }
    
    echo '</tbody></table>';
    echo '</body>';
    echo '</html>';
}

// Log da exportação
logAction('USERS_EXPORTED', "Exportação de usuários - Formato: {$format} - Total: " . count($users), $_SESSION['admin_id']);

exit;
?>