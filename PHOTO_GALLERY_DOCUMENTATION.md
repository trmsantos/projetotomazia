# Funcionalidade de Galeria de Fotos - Documentação

## Resumo da Implementação

Foi implementado um sistema completo de galeria de fotos com as seguintes características:

### 1. Estrutura de Base de Dados

**Tabela: `fotos`**
```sql
CREATE TABLE fotos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_foto TEXT NOT NULL,
    caminho TEXT NOT NULL,
    descricao TEXT,
    data_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
    visivel INTEGER DEFAULT 1
)
```

**Campos:**
- `id`: Identificador único da foto
- `nome_foto`: Nome original do arquivo
- `caminho`: Caminho relativo do arquivo (img/uploads/...)
- `descricao`: Descrição opcional da foto
- `data_upload`: Data e hora do upload (automático)
- `visivel`: Flag de visibilidade (1=visível, 0=oculta)

### 2. Sistema de Upload

**Localização:** `img/uploads/`
- Diretório criado automaticamente pela migração
- Permissões: 0755
- Armazena todos os arquivos de fotos enviados

**Validações implementadas:**
- Tipos de arquivo permitidos: JPEG, JPG, PNG, GIF, WEBP
- Tamanho máximo: 5MB
- Nome único gerado automaticamente: `foto_[timestamp]_[random].ext`

### 3. Painel de Administração

**Acesso:** admin.php → Aba "Fotos"

**Funcionalidades:**
1. **Upload de Fotos**
   - Formulário com campo de arquivo
   - Campo de descrição opcional
   - Checkbox de visibilidade
   - Validação de tipo e tamanho
   - Mensagens de sucesso/erro

2. **Gestão de Fotos**
   - Visualização em grid (cards)
   - Preview da imagem (200px altura)
   - Informações: nome, descrição, data de upload
   - Botão "Ocultar/Mostrar" (toggle visibilidade)
   - Botão "Eliminar" (com confirmação)
   - Badge de status (Visível/Oculta)
   - Contador total de fotos

3. **Segurança**
   - Proteção CSRF em todos os formulários
   - Validação de tipos MIME
   - Sanitização de nomes de arquivo
   - Prepared statements para queries

### 4. Galeria Pública (Slideshow)

**Localização:** bemvindo.php → Seção "Galeria"

**Características do Slideshow:**
- Bootstrap Carousel com transição automática
- Intervalo: 3 segundos entre fotos
- Controles de navegação (anterior/próximo)
- Indicadores de slides
- Captions com descrição das fotos
- Design responsivo
- Fundo preto para melhor contraste
- Altura máxima: 500px (object-fit: contain)
- Estilo consistente com o resto do site

**Comportamento:**
- Exibe apenas fotos com `visivel = 1`
- Ordenadas por data de upload (mais recente primeiro)
- Mensagem amigável quando não há fotos

### 5. Navegação

**Menu atualizado em bemvindo.php:**
- Início
- **Galeria** ← NOVO
- Menu
- Eventos
- Onde nos encontrar

### 6. Scripts de Migração e Teste

**migrate_add_photos_table.php**
- Cria a tabela `fotos`
- Cria o diretório `img/uploads/`
- Verifica se já existem (idempotente)

**test_add_sample_photo.php**
- Adiciona fotos de exemplo para teste
- Copia img/tomazia.jpg para uploads
- Insere registro no banco de dados

**test_gallery.php**
- Página de teste completa
- Mostra status da implementação
- Exibe o carousel funcionando
- Lista todas as fotos no banco

## Como Usar

### Para Administradores:

1. **Fazer Login**
   - Acesse login.php
   - Entre com credenciais de admin

2. **Acessar Gestão de Fotos**
   - Vá para admin.php
   - Clique na aba "Fotos"

3. **Upload de Foto**
   - Clique em "Selecionar Foto"
   - Escolha imagem (JPEG, PNG, GIF ou WEBP, máx 5MB)
   - Adicione descrição (opcional)
   - Marque "Visível na galeria" se quiser exibir
   - Clique "Upload Foto"

4. **Gerenciar Fotos**
   - Ver todas as fotos em grid
   - Clicar "Ocultar/Mostrar" para alterar visibilidade
   - Clicar "Eliminar" para remover (com confirmação)

### Para Visitantes:

1. **Ver Galeria**
   - Acesse bemvindo.php (página de boas-vindas)
   - Role até a seção "Galeria de Fotos"
   - Veja o slideshow automático
   - Use as setas para navegar manualmente
   - Clique nos indicadores para ir para foto específica

## Estrutura de Arquivos

```
projetotomazia/
├── admin.php                          # Painel admin (+ aba Fotos)
├── bemvindo.php                       # Página principal (+ galeria)
├── bd/
│   └── bd_teste.db                   # Banco SQLite (+ tabela fotos)
├── img/
│   └── uploads/                      # Diretório de fotos
│       ├── foto_exemplo_1770034633.jpg
│       ├── foto_exemplo_1770034646.jpg
│       └── foto_exemplo_1770034647.jpg
├── migrate_add_photos_table.php      # Script de migração
├── test_add_sample_photo.php         # Script de teste
└── test_gallery.php                  # Página de teste
```

## Tecnologias Utilizadas

- **Backend:** PHP 7.4+ / SQLite3
- **Frontend:** Bootstrap 4.5.2, jQuery 3.5.1
- **Carousel:** Bootstrap Carousel component
- **Segurança:** CSRF tokens, prepared statements, validação de uploads

## Testes Realizados

✅ Migração de banco de dados
✅ Criação de diretório de uploads
✅ Upload de fotos de exemplo
✅ Validação de tipos de arquivo
✅ Inserção no banco de dados
✅ Query de fotos visíveis
✅ Exibição em carousel
✅ Sintaxe PHP (sem erros)

## Status

🎉 **IMPLEMENTAÇÃO COMPLETA E FUNCIONAL**

- [x] Tabela de banco de dados criada
- [x] Sistema de upload funcionando
- [x] Validações implementadas
- [x] Interface de administração completa
- [x] Galeria com slideshow funcionando
- [x] Navegação atualizada
- [x] Fotos de exemplo adicionadas
- [x] Testes realizados com sucesso

## Próximos Passos Possíveis (Opcional)

1. Redimensionamento automático de imagens
2. Múltiplos tamanhos (thumbnail, médio, grande)
3. Watermark automático
4. Ordenação customizável
5. Categorias/tags para fotos
6. Galeria em grid além do slideshow
7. Lightbox para visualização ampliada
8. Paginação para muitas fotos
9. Upload múltiplo simultâneo
10. Edição de fotos (crop, rotate)

---

**Data de Implementação:** 2 de Fevereiro de 2026
**Autor:** GitHub Copilot Agent
