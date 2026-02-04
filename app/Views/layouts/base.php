<?php
/**
 * Base Layout
 * 
 * This is the main layout template that wraps all pages.
 * It provides the basic HTML structure with head, body, and common assets.
 * 
 * Variables expected:
 * - $title: string - Page title
 * - $content: string - Page content (rendered from view)
 * - $bodyClass: string - Optional body CSS class
 * 
 * @package App\Views\Layouts
 */

// Default values
$title = $title ?? 'Bar da Tomazia';
$bodyClass = $bodyClass ?? '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="img/tomazia.png" type="image/png">
    
    <!-- External Styles -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;500;600;700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- Application Styles -->
    <link rel="stylesheet" href="css/style.css">
    
    <?php if (isset($additionalStyles)): ?>
    <?php echo $additionalStyles; ?>
    <?php endif; ?>
</head>
<body class="<?php echo htmlspecialchars($bodyClass); ?>">
    <?php 
    // Render page content
    if (isset($content)) {
        echo $content;
    }
    ?>
    
    <?php if (isset($additionalScripts)): ?>
    <?php echo $additionalScripts; ?>
    <?php endif; ?>
</body>
</html>
