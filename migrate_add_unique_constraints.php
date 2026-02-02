<?php
/**
 * Script de migração para adicionar constraints únicos
 * Este script remove duplicatas e adiciona índices únicos para email e telemovel na tabela tomazia_clientes
 * e para username na tabela admin_users
 */

require_once 'config.php';

try {
    $db = getDbConnection();
    
    echo "Iniciando migração...\n";
    
    // 1. Primeiro, remover duplicatas de email na tabela tomazia_clientes
    echo "\n1. Limpando duplicatas de email...\n";
    // Usar abordagem compatível com SQLite: criar tabela temporária com IDs a manter
    $db->exec("
        CREATE TEMPORARY TABLE IF NOT EXISTS ids_to_keep_email AS
        SELECT MIN(id) as id FROM tomazia_clientes GROUP BY email
    ");
    $db->exec("
        DELETE FROM tomazia_clientes 
        WHERE id NOT IN (SELECT id FROM ids_to_keep_email)
    ");
    $emailDeleted = $db->changes();
    $db->exec("DROP TABLE IF EXISTS ids_to_keep_email");
    echo "✓ {$emailDeleted} registro(s) duplicado(s) de email removido(s)\n";
    
    // 2. Remover duplicatas de telemovel
    echo "\n2. Limpando duplicatas de telemovel...\n";
    $db->exec("
        CREATE TEMPORARY TABLE IF NOT EXISTS ids_to_keep_telemovel AS
        SELECT MIN(id) as id FROM tomazia_clientes GROUP BY telemovel
    ");
    $db->exec("
        DELETE FROM tomazia_clientes 
        WHERE id NOT IN (SELECT id FROM ids_to_keep_telemovel)
    ");
    $telemovelDeleted = $db->changes();
    $db->exec("DROP TABLE IF EXISTS ids_to_keep_telemovel");
    echo "✓ {$telemovelDeleted} registro(s) duplicado(s) de telemovel removido(s)\n";
    
    // 3. Remover duplicatas de username em admin_users
    echo "\n3. Limpando duplicatas de username...\n";
    $db->exec("
        CREATE TEMPORARY TABLE IF NOT EXISTS ids_to_keep_username AS
        SELECT MIN(id) as id FROM admin_users GROUP BY username
    ");
    $db->exec("
        DELETE FROM admin_users 
        WHERE id NOT IN (SELECT id FROM ids_to_keep_username)
    ");
    $usernameDeleted = $db->changes();
    $db->exec("DROP TABLE IF EXISTS ids_to_keep_username");
    echo "✓ {$usernameDeleted} registro(s) duplicado(s) de username removido(s)\n";
    
    // Verificar se os índices já existem
    $indexes = $db->query("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='tomazia_clientes'");
    $existingIndexes = [];
    while ($row = $indexes->fetchArray(SQLITE3_ASSOC)) {
        $existingIndexes[] = $row['name'];
    }
    
    // 4. Adicionar índice único para email
    echo "\n4. Criando índices únicos...\n";
    if (!in_array('idx_unique_email', $existingIndexes)) {
        echo "  Criando índice único para email...\n";
        $db->exec("CREATE UNIQUE INDEX idx_unique_email ON tomazia_clientes(email)");
        echo "  ✓ Índice único para email criado\n";
    } else {
        echo "  ✓ Índice único para email já existe\n";
    }
    
    // 5. Adicionar índice único para telemovel
    if (!in_array('idx_unique_telemovel', $existingIndexes)) {
        echo "  Criando índice único para telemovel...\n";
        $db->exec("CREATE UNIQUE INDEX idx_unique_telemovel ON tomazia_clientes(telemovel)");
        echo "  ✓ Índice único para telemovel criado\n";
    } else {
        echo "  ✓ Índice único para telemovel já existe\n";
    }
    
    // Verificar índices em admin_users
    $adminIndexes = $db->query("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='admin_users'");
    $existingAdminIndexes = [];
    while ($row = $adminIndexes->fetchArray(SQLITE3_ASSOC)) {
        $existingAdminIndexes[] = $row['name'];
    }
    
    // 6. Adicionar índice único para username
    if (!in_array('idx_unique_username', $existingAdminIndexes)) {
        echo "  Criando índice único para username...\n";
        $db->exec("CREATE UNIQUE INDEX idx_unique_username ON admin_users(username)");
        echo "  ✓ Índice único para username criado\n";
    } else {
        echo "  ✓ Índice único para username já existe\n";
    }
    
    echo "\n✅ Migração concluída com sucesso!\n";
    echo "Total de duplicatas removidas: " . ($emailDeleted + $telemovelDeleted + $usernameDeleted) . "\n";
    
} catch (Exception $e) {
    echo "❌ Erro durante a migração: " . $e->getMessage() . "\n";
    exit(1);
}
?>
