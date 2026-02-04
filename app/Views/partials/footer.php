<?php
/**
 * Footer Partial
 * 
 * This view partial renders the common footer section.
 * Used across multiple pages for consistent footer content.
 * 
 * Variables expected:
 * - $showScripts: bool - Whether to include JavaScript files (default: true)
 * 
 * @package App\Views\Partials
 */

$showScripts = $showScripts ?? true;
?>
<footer class="site-footer" style="background-color: #3D0F24; padding: 2rem 0; text-align: center; border-top: 1px solid rgba(212, 175, 55, 0.2);">
    <div class="container">
        <p style="color: #a0a0a0; margin: 0; font-size: 0.9rem;">
            &copy; <?php echo date('Y'); ?> Bar da Tomazia. Todos os direitos reservados.
        </p>
    </div>
</footer>

<?php if ($showScripts): ?>
<!-- JavaScript Dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<?php endif; ?>
