<?php
/**
 * Script de migração para adicionar tabela de fotos
 * Este script cria a tabela 'fotos' para armazenar galeria de fotos do bar
 */

require_once 'config.php';

try {
    $db = getDbConnection();
    
    echo "Iniciando migração da tabela de fotos...\n";
    
    // Verificar se a tabela já existe
    $tableExists = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='fotos'");
    
    if ($tableExists) {
        echo "✓ Tabela 'fotos' já existe\n";
    } else {
        echo "Criando tabela 'fotos'...\n";
        
        $sql = "CREATE TABLE fotos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome_foto TEXT NOT NULL,
            caminho TEXT NOT NULL,
            descricao TEXT,
            data_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
            visivel INTEGER DEFAULT 1
        )";
        
        $db->exec($sql);
        echo "✓ Tabela 'fotos' criada com sucesso\n";
    }
    
    // Criar diretório para uploads se não existir
    $uploadDir = __DIR__ . '/img/uploads';
    if (!file_exists($uploadDir)) {
        if (mkdir($uploadDir, 0755, true)) {
            echo "✓ Diretório de uploads criado: img/uploads/\n";
        } else {
            echo "⚠ Aviso: Não foi possível criar o diretório de uploads\n";
        }
    } else {
        echo "✓ Diretório de uploads já existe\n";
    }
    
    echo "\n✅ Migração concluída com sucesso!\n";
    
} catch (Exception $e) {
    echo "❌ Erro durante a migração: " . $e->getMessage() . "\n";
    exit(1);
}
?>
