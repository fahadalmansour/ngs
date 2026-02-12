<?php
/**
 * Homepage Template
 *
 * @package NGS_DesignSystem
 */

get_header();
?>

<main id="main" class="ngs-main" role="main">
    <?php
    // Hero Section
    get_template_part('template-parts/home/hero');

    // Value Proposition Cards
    get_template_part('template-parts/home/value-cards');

    // Ecosystem Cards
    get_template_part('template-parts/home/ecosystem');

    // Product Bundles
    get_template_part('template-parts/home/bundles');

    // Trust Statistics
    get_template_part('template-parts/home/trust-stats');

    // Knowledge Base CTA
    get_template_part('template-parts/home/knowledge-cta');

    // Featured Products Grid
    get_template_part('template-parts/home/product-grid');
    ?>
</main>

<?php
get_footer();
