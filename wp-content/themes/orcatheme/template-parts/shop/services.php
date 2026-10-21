<?php
/** Shop view: services. */
defined('ABSPATH') || exit;
?>
    <section class="orca-shop__services" aria-labelledby="orca-services-title">
        <div><p class="orca-shop__eyebrow"><?php echo esc_html(orca_text('FRA PRODUKT TIL VISUEL IDENTITET', 'FROM PRODUCT TO VISUAL IDENTITY')); ?></p>
        <h2 id="orca-services-title"><?php echo esc_html(orca_text('Skal jeres brand hænge sammen hele vejen?', 'Ready for a consistent brand?')); ?></h2>
        <p><?php echo esc_html(orca_text('Et logo på en trøje er en begyndelse. Vi kan også hjælpe med logo, visuel identitet, hjemmeside og kampagner, så kunderne genkender jer på tværs af produkter og digitale kanaler.', 'A logo on a shirt is a starting point. We can also help with your logo, visual identity, website and campaigns, so customers recognise you across products and digital channels.')); ?></p></div>
        <div class="orca-shop__service-cards">
            <a href="<?php echo esc_url(orca_shop_enquiry_url('branding')); ?>"><span>01 / BRANDING</span><h3><?php echo esc_html(orca_text('Et logo med retning', 'A logo with direction')); ?></h3><p><?php echo esc_html(orca_text('Få hjælp til logo, farver og et visuelt udtryk, der passer til virksomheden.', 'Get help with your logo, colours and a visual identity that fits your business.')); ?></p><strong><?php echo esc_html(orca_text('Tal med os om branding', 'Talk to us about branding')); ?> ↗</strong></a>
            <a href="<?php echo esc_url(orca_shop_enquiry_url('complete')); ?>"><span>02 / <?php echo esc_html(orca_text('DEN SAMLEDE LØSNING', 'THE COMPLETE SOLUTION')); ?></span><h3><?php echo esc_html(orca_text('Fra merchandise til hjemmeside', 'From merchandise to website')); ?></h3><p><?php echo esc_html(orca_text('Saml produkter, hjemmeside og markedsføring i ét genkendeligt brand.', 'Bring products, your website and marketing together in one recognisable brand.')); ?></p><strong><?php echo esc_html(orca_text('Få en snak om jeres projekt', 'Talk to us about your project')); ?> ↗</strong></a>
        </div>
    </section>
    <?php
