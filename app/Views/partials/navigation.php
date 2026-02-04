<?php
/**
 * Navigation Menu Partial
 * 
 * This view partial renders the main navigation menu.
 * Used across multiple pages for consistent navigation.
 * 
 * Variables expected:
 * - $currentPage: string - Current page identifier for active state
 * 
 * @package App\Views\Partials
 */

// Define navigation links
$navLinks = [
    ['id' => 'home', 'href' => '#home', 'label' => 'Início'],
    ['id' => 'galeria', 'href' => '#galeria', 'label' => 'Galeria'],
    ['id' => 'menu', 'href' => '#menu', 'label' => 'Menu'],
    ['id' => 'eventos', 'href' => '#eventos', 'label' => 'Eventos'],
    ['id' => 'localizacao', 'href' => '#localizacao', 'label' => 'Onde nos encontrar'],
];
?>
<button class="hamburger-menu" aria-label="Toggle menu">
    <span></span>
    <span></span>
    <span></span>
</button>
<nav class="nav-menu" role="navigation" aria-label="Menu principal">
    <?php foreach ($navLinks as $link): ?>
        <a href="<?php echo $link['href']; ?>" 
           <?php if (isset($currentPage) && $currentPage === $link['id']): ?>
           class="active"
           <?php endif; ?>>
            <?php echo htmlspecialchars($link['label']); ?>
        </a>
    <?php endforeach; ?>
</nav>
