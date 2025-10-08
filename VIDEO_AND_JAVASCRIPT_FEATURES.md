# Video Background and JavaScript Features

## Descrição
Este documento explica as novas funcionalidades adicionadas ao site Bar da Tomazia, incluindo fundo de vídeo dinâmico e interatividade JavaScript.

## Funcionalidades Implementadas

### 1. Fundo de Vídeo (`index.php`)
- **Elemento de vídeo** com reprodução automática em loop
- **Atributos implementados:**
  - `autoplay`: Reprodução automática ao carregar a página
  - `loop`: Reprodução contínua do vídeo
  - `muted`: Som desativado (necessário para autoplay em navegadores modernos)
  - `playsinline`: Reprodução inline em dispositivos móveis

- **Caminho do vídeo:** `img/cocktail-video.mp4`
  - **NOTA IMPORTANTE:** Se você adicionou um vídeo com nome diferente, altere o nome no arquivo `index.php` na linha do elemento `<video>`. Atualmente existe um vídeo chamado `3772392-hd_1920_1080_25fps.mp4` na pasta img que pode ser usado.

- **Estilos aplicados (CSS):**
  - Posição fixa para ocupar toda a tela
  - `object-fit: cover` para preencher o espaço sem distorção
  - `z-index: -1` para ficar atrás do conteúdo
  - Overlay escuro (60% de opacidade) para garantir legibilidade do texto

### 2. JavaScript Interativo (`js/main.js`)

#### Smooth Scrolling
- **Funcionalidade:** Rolagem suave ao clicar em links âncora (ex: `href="#eventos"`)
- **Como usar:** Qualquer link com `href` começando com `#` terá rolagem animada automaticamente
- **Exemplo:**
  ```html
  <a href="#eventos">Ver Eventos</a>
  ```

#### Animações Fade-in
- **Funcionalidade:** Detecta quando elementos entram na área visível e aplica animação de fade-in
- **Tecnologia:** IntersectionObserver API (moderno e performático)
- **Elementos observados:**
  - Elementos com classe `.fade-in-on-scroll`
  - Cards (`.card`)
  - Containers de formulários (`.form-container`)
  - Seções (`section`)

- **Como adicionar fade-in a novos elementos:**
  ```html
  <div class="fade-in-on-scroll">
    <!-- Seu conteúdo aqui -->
  </div>
  ```

### 3. Estilos CSS (`css/style.css`)

#### Classes de Video Background
```css
.video-background  /* Estilo do vídeo de fundo */
.video-overlay     /* Overlay escuro sobre o vídeo */
```

#### Classes de Animação
```css
.fade-in-on-scroll        /* Estado inicial (invisível) */
.fade-in-visible          /* Estado final (visível) */
```

## Personalização

### Alterar a Intensidade do Overlay
No arquivo `css/style.css`, procure por `.video-overlay` e ajuste o valor rgba:
```css
background: rgba(0, 0, 0, 0.6); /* 0.6 = 60% de opacidade */
```

### Alterar a Velocidade da Animação Fade-in
No arquivo `css/style.css`, procure por `.fade-in-on-scroll` e ajuste:
```css
transition: opacity 0.8s ease-out, transform 0.8s ease-out;
```

### Alterar o Limite de Visibilidade para Animação
No arquivo `js/main.js`, ajuste o `threshold`:
```javascript
const observerOptions = {
    threshold: 0.1, // 0.1 = 10% do elemento visível
    rootMargin: '0px 0px -50px 0px'
};
```

## Compatibilidade
- **Navegadores modernos:** Chrome, Firefox, Safari, Edge
- **Dispositivos móveis:** iOS Safari, Chrome Mobile
- **Graceful degradation:** Se JavaScript estiver desativado, o site continua funcional

## Notas Técnicas
1. O vídeo deve estar em formato MP4 para melhor compatibilidade
2. O atributo `muted` é obrigatório para autoplay funcionar na maioria dos navegadores
3. O `playsinline` garante reprodução inline em iOS (sem fullscreen automático)
4. O IntersectionObserver é mais eficiente que scroll listeners tradicionais

## Arquivos Modificados/Criados
- ✅ **Criado:** `js/main.js` - JavaScript interativo
- ✅ **Modificado:** `index.php` - Adição do elemento de vídeo e link para main.js
- ✅ **Modificado:** `css/style.css` - Estilos para vídeo e animações
