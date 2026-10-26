<?php
/**
 * Template Name: Kontakt
 *
 * @package Orca_Theme
 */

$contact_status = isset($_GET['contact-status']) ? sanitize_key(wp_unslash($_GET['contact-status'])) : '';
$contact_type   = isset($_GET['contact-type']) ? sanitize_key(wp_unslash($_GET['contact-type'])) : 'quote';
$contact_type   = in_array($contact_type, array('quote', 'support'), true) ? $contact_type : 'quote';

$shop_service = isset($_GET['shop-service']) && is_string($_GET['shop-service']) ? sanitize_key(wp_unslash($_GET['shop-service'])) : '';
$shop_service = in_array($shop_service, array('branding', 'complete'), true) ? $shop_service : '';
$shop_message = '';
$shop_product_id = isset($_GET['shop-product']) && is_string($_GET['shop-product']) ? absint($_GET['shop-product']) : 0;
if ($shop_product_id && 'product' === get_post_type($shop_product_id) && 'publish' === get_post_status($shop_product_id)) {
    $shop_message = sprintf('Vi ønsker et tilbud på %s med vores virksomhedslogo.', get_the_title($shop_product_id));
    $shop_message .= "\n\n" . "Ønsket antal: \nHvornår skal produkterne bruges: \nHar I et færdigt logo, eller ønsker I hjælp til design: ";
}
get_header();
?>

<main id="primary" class="orca-contact">
    <section class="orca-hero" aria-labelledby="contact-title">
        <div class="orca-eyebrow"><?php echo esc_html('Kontakt Orca'); ?></div>
        <h1 id="contact-title"><?php echo esc_html('Lad os skabe noget,'); ?><br><span><?php echo esc_html('der bliver set.'); ?></span></h1>
        <p><?php echo esc_html('Fortæl os om din virksomhed, dit projekt eller din udfordring. Vi hjælper med alt fra hjemmeside og branding til kampagner og digital synlighed.'); ?></p>
        <div class="orca-contact__facts" aria-label="<?php echo esc_attr('Fordele ved at kontakte Orca'); ?>">
            <span><?php echo esc_html('Uforpligtende dialog'); ?></span>
            <span><?php echo esc_html('Svar inden for 1–2 hverdage'); ?></span>
            <span><?php echo esc_html('Løsninger tilpasset jer'); ?></span>
        </div>
    </section>

    <section class="orca-contact__workspace" aria-labelledby="contact-form-title">
        <div class="orca-contact__intro">
            <p class="orca-kicker"><?php echo esc_html('Hvad kan vi hjælpe med?'); ?></p>
            <h2 id="contact-form-title"><?php echo esc_html('Vælg den rette vej ind'); ?></h2>
            <p><?php echo esc_html('Skal vi udvikle noget nyt, eller har du allerede en løsning hos Orca, som du har brug for hjælp til?'); ?></p>
        </div>

        <?php if ('success' === $contact_status) : ?>
            <div class="orca-notice orca-notice--success" role="status">
                <strong><?php echo esc_html('Tak for din henvendelse.'); ?></strong>
                <span><?php echo esc_html('support' === $contact_type ? 'Din supportsag er modtaget. Vi vender tilbage hurtigst muligt.' : 'Vi har modtaget dit projekt og vender tilbage inden for 1–2 hverdage.'); ?></span>
            </div>
        <?php elseif ('error' === $contact_status) : ?>
            <div class="orca-notice orca-notice--error" role="alert"><strong><?php echo esc_html('Vi kunne ikke sende din besked.'); ?></strong><span><?php echo esc_html('Kontrollér de obligatoriske felter, og prøv igen.'); ?></span></div>
        <?php endif; ?>

        <div class="orca-contact__tabs" role="tablist" aria-label="<?php echo esc_attr('Kontaktmuligheder'); ?>">
            <button class="orca-contact__tab<?php echo 'quote' === $contact_type ? ' is-active' : ''; ?>" type="button" role="tab" id="tab-quote" aria-controls="panel-quote" aria-selected="<?php echo 'quote' === $contact_type ? 'true' : 'false'; ?>" data-contact-tab="quote">
                <span class="orca-contact__tab-icon" aria-hidden="true">↗</span><span><strong><?php echo esc_html('Få et tilbud'); ?></strong><small><?php echo esc_html('Start et nyt projekt med os'); ?></small></span>
            </button>
            <button class="orca-contact__tab<?php echo 'support' === $contact_type ? ' is-active' : ''; ?>" type="button" role="tab" id="tab-support" aria-controls="panel-support" aria-selected="<?php echo 'support' === $contact_type ? 'true' : 'false'; ?>" data-contact-tab="support">
                <span class="orca-contact__tab-icon" aria-hidden="true">?</span><span><strong><?php echo esc_html('Få support'); ?></strong><small><?php echo esc_html('Hjælp til en eksisterende løsning'); ?></small></span>
            </button>
        </div>

        <div class="orca-contact__panel" id="panel-quote" role="tabpanel" aria-labelledby="tab-quote"<?php echo 'quote' !== $contact_type ? ' hidden' : ''; ?>>
            <form class="orca-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="orca_submit_contact"><input type="hidden" name="contact_type" value="quote">
                <?php wp_nonce_field('orca_contact_form', 'orca_contact_nonce'); ?>
                <div class="orca-form__grid">
                    <label><?php echo esc_html('Dit navn'); ?> <span>*</span><input type="text" name="name" autocomplete="name" required></label>
                    <label><?php echo esc_html('Virksomhed'); ?> <span>*</span><input type="text" name="company" autocomplete="organization" required></label>
                    <label>E-mail <span>*</span><input type="email" name="email" autocomplete="email" required></label>
                    <label><?php echo esc_html('Telefon'); ?><input type="tel" name="phone" autocomplete="tel"></label>
                    <label class="orca-form__full"><?php echo esc_html('Hvad skal vi hjælpe med?'); ?> <span>*</span><select name="service" required><?php orca_contact_service_options('quote', $shop_service); ?></select></label>
                    <label>Budget<select name="budget"><option value=""><?php echo esc_html('Vælg budgetniveau'); ?></option><option value="under-10000"><?php echo esc_html('Under 10.000 kr.'); ?></option><option value="10000-25000"><?php echo esc_html('10.000–25.000 kr.'); ?></option><option value="25000-50000"><?php echo esc_html('25.000–50.000 kr.'); ?></option><option value="over-50000"><?php echo esc_html('Over 50.000 kr.'); ?></option><option value="unsure"><?php echo esc_html('Ikke afklaret endnu'); ?></option></select></label>
                    <label><?php echo esc_html('Ønsket deadline'); ?><input type="date" name="deadline"></label>
                    <label class="orca-form__full"><?php echo esc_html('Fortæl kort om projektet'); ?> <span>*</span><textarea name="message" rows="6" placeholder="<?php echo esc_attr('Hvad vil I gerne opnå, og hvordan kan Orca hjælpe?'); ?>" required><?php echo esc_textarea($shop_message); ?></textarea></label>
                </div>
                <label class="orca-form__consent"><input type="checkbox" name="consent" value="1" required><span><?php echo esc_html('Jeg accepterer, at Orca må behandle mine oplysninger for at besvare henvendelsen. *'); ?></span></label>
                <button class="orca-form__submit" type="submit"><?php echo esc_html('Send forespørgsel'); ?> <span aria-hidden="true">→</span></button>
            </form>
        </div>

        <div class="orca-contact__panel" id="panel-support" role="tabpanel" aria-labelledby="tab-support"<?php echo 'support' !== $contact_type ? ' hidden' : ''; ?>>
            <form class="orca-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="orca_submit_contact"><input type="hidden" name="contact_type" value="support">
                <?php wp_nonce_field('orca_contact_form', 'orca_contact_nonce'); ?>
                <div class="orca-form__grid">
                    <label><?php echo esc_html('Dit navn'); ?> <span>*</span><input type="text" name="name" autocomplete="name" required></label>
                    <label><?php echo esc_html('Virksomhed'); ?> <span>*</span><input type="text" name="company" autocomplete="organization" required></label>
                    <label>E-mail <span>*</span><input type="email" name="email" autocomplete="email" required></label>
                    <label><?php echo esc_html('Link til jeres løsning'); ?><input type="url" name="website" placeholder="https://"></label>
                    <label class="orca-form__full"><?php echo esc_html('Hvad drejer det sig om?'); ?> <span>*</span><select name="service" required><?php orca_contact_service_options('support'); ?></select></label>
                    <label class="orca-form__full"><?php echo esc_html('Beskriv problemet'); ?> <span>*</span><textarea name="message" rows="6" placeholder="<?php echo esc_attr('Beskriv hvad der sker, og hvad du forventede skulle ske.'); ?>" required></textarea></label>
                </div>
                <label class="orca-form__consent"><input type="checkbox" name="consent" value="1" required><span><?php echo esc_html('Jeg accepterer, at Orca må behandle mine oplysninger for at besvare henvendelsen. *'); ?></span></label>
                <button class="orca-form__submit" type="submit"><?php echo esc_html('Send supportsag'); ?> <span aria-hidden="true">→</span></button>
            </form>
        </div>
    </section>

    <section class="orca-contact__gallery" aria-labelledby="contact-gallery-title">
        <div class="orca-contact__intro">
            <p class="orca-kicker"><?php echo esc_html('Mennesker, idéer og samarbejde'); ?></p>
            <h2 id="contact-gallery-title"><?php echo esc_html('Mød os, før du skriver'); ?></h2>
        </div>
        <div class="orca-contact__video">
            <a class="orca-contact__video-play" href="<?php echo esc_url(get_theme_file_uri('/contact-media/kontakt-os.mp4')); ?>" data-contact-video aria-controls="contact-video-player" aria-expanded="false">
                <span aria-hidden="true">▶</span> <?php echo esc_html('Se vores kontaktvideo'); ?>
            </a>
            <div id="contact-video-player" hidden data-video-label="<?php echo esc_attr('Kontakt Orca'); ?>"></div>
        </div>
        <div class="orca-contact__gallery-grid">
            <figure class="orca-contact__gallery-card">
                <img src="<?php echo esc_url(get_theme_file_uri('/contact-media/orca-staff.jpg')); ?>" alt="<?php echo esc_attr('Orca-teamet samlet omkring et bord med laptops'); ?>" width="2048" height="1536" loading="lazy" decoding="async">
                <figcaption>
                    <h3><?php echo esc_html('Teamet bag Orca'); ?></h3>
                    <p><?php echo esc_html('Vi glæder os til at høre om dit projekt.'); ?></p>
                </figcaption>
            </figure>
            <figure class="orca-contact__gallery-card">
                <img src="<?php echo esc_url(get_theme_file_uri('/contact-media/orca-digital-loesning.jpg')); ?>" alt="<?php echo esc_attr('Skærme med kode og grafisk arbejde på en digital løsning'); ?>" width="1200" height="800" loading="lazy" decoding="async">
                <figcaption>
                    <h3><?php echo esc_html('Fra idé til digital løsning'); ?></h3>
                    <p><?php echo esc_html('Vi bringer design og udvikling sammen i løsninger til din virksomhed.'); ?></p>
                </figcaption>
            </figure>
            <figure class="orca-contact__gallery-card">
                <img src="<?php echo esc_url(get_theme_file_uri('/contact-media/orca-samtale-staff.jpg')); ?>" alt="<?php echo esc_attr('Fire medlemmer af Orca-teamet drøfter et projekt med en laptop og skitser på bordet'); ?>" width="1536" height="1024" loading="lazy" decoding="async">
                 <figcaption>
                    <h3><?php echo esc_html('Dit projekt starter med en samtale'); ?></h3>
                    <p><?php echo esc_html('Sammen afklarer vi dine ønsker og næste skridt.'); ?></p>
                </figcaption>
            </figure>
        </div>
    </section>

    <section class="orca-contact__direct" aria-label="<?php echo esc_attr('Direkte kontakt'); ?>"><p><?php echo esc_html('Foretrækker du at skrive direkte?'); ?></p><a href="mailto:kontakt@orca.dk">kontakt@orca.dk</a></section>
</main>


<?php get_footer(); ?>
