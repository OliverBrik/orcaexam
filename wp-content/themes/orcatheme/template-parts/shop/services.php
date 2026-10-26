<?php
/** Shop view: services. */
defined('ABSPATH') || exit;
?>
    <section class="orca-shop__services" aria-labelledby="orca-services-title">
        <div><p class="orca-shop__eyebrow"><?php echo esc_html('FRA PRODUKT TIL VISUEL IDENTITET'); ?></p>
        <h2 id="orca-services-title"><?php echo esc_html('Skal jeres brand hænge sammen hele vejen?'); ?></h2>
        <p><?php echo esc_html('Et logo på en trøje er en begyndelse. Vi kan også hjælpe med logo, visuel identitet, hjemmeside og kampagner, så kunderne genkender jer på tværs af produkter og digitale kanaler.'); ?></p></div>
        <div class="orca-shop__service-cards">
            <a href="<?php echo esc_url(orca_shop_enquiry_url('branding')); ?>"><span>01 / BRANDING</span><h3><?php echo esc_html('Et logo med retning'); ?></h3><p><?php echo esc_html('Få hjælp til logo, farver og et visuelt udtryk, der passer til virksomheden.'); ?></p><strong><?php echo esc_html('Tal med os om branding'); ?> ↗</strong></a>
            <a href="<?php echo esc_url(orca_shop_enquiry_url('complete')); ?>"><span>02 / <?php echo esc_html('DEN SAMLEDE LØSNING'); ?></span><h3><?php echo esc_html('Fra merchandise til hjemmeside'); ?></h3><p><?php echo esc_html('Saml produkter, hjemmeside og markedsføring i ét genkendeligt brand.'); ?></p><strong><?php echo esc_html('Få en snak om jeres projekt'); ?> ↗</strong></a>
        </div>
    </section>
    <?php
