<?php

namespace App\Models;

/**
 * Product Model
 */
class Product extends BaseModel
{
    protected string $table = 'produtos';
    protected string $primaryKey = 'id_produto';

    /**
     * Get products by type
     */
    public function getByType(string $type): array
    {
        return $this->findAll(['tipo' => $type], 'nome_prod ASC');
    }

    /**
     * Get all products grouped by type
     */
    public function getAllGroupedByType(): array
    {
        $products = $this->findAll([], 'tipo ASC, nome_prod ASC');
        
        $grouped = [];
        foreach ($products as $product) {
            $tipo = $product['tipo'] ?? 'Outros';
            if (!isset($grouped[$tipo])) {
                $grouped[$tipo] = [];
            }
            $grouped[$tipo][] = $product;
        }
        
        return $grouped;
    }
}
