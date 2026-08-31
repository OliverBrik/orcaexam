<?php
/**
 * Template Name: Kontakt
 *
 * @package Orca_Theme
 */

$contact_status = isset($_GET['contact-status']) ? sanitize_key(wp_unslash($_GET['contact-status'])) : '';
$contact_type   = isset($_GET['contact-type']) ? sanitize_key(wp_unslash($_GET['contact-type'])) : 'quote';
$contact_type   = in_array($contact_type, array('quote', 'support'), true) ? $contact_type : 'quote';
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('orca-contact-page'); ?>>
<?php wp_body_open(); ?>

<main id="primary" class="orca-contact">
    <section class="orca-contact__hero" aria-labelledby="contact-title">
        <div class="orca-contact__eyebrow">Kontakt Orca</div>
        <h1 id="contact-title">Lad os skabe noget,<br><span>der bliver set.</span></h1>
        <p>Fortæl os om din virksomhed, dit projekt eller din udfordring. Vi hjælper med alt fra hjemmeside og branding til kampagner og digital synlighed.</p>
        <div class="orca-contact__facts" aria-label="Fordele ved at kontakte Orca">
            <span>Uforpligtende dialog</span>
            <span>Svar inden for 1–2 hverdage</span>
            <span>Løsninger tilpasset jer</span>
        </div>
    </section>

    <section class="orca-contact__workspace" aria-labelledby="contact-form-title">
        <div class="orca-contact__intro">
            <p class="orca-contact__kicker">Hvad kan vi hjælpe med?</p>
            <h2 id="contact-form-title">Vælg den rette vej ind</h2>
            <p>Skal vi udvikle noget nyt, eller har du allerede en løsning hos Orca, som du har brug for hjælp til?</p>
        </div>

        <?php if ('success' === $contact_status) : ?>
            <div class="orca-notice orca-notice--success" role="status">
                <strong>Tak for din henvendelse.</strong>
                <span><?php echo 'support' === $contact_type ? 'Din supportsag er modtaget. Vi vender tilbage hurtigst muligt.' : 'Vi har modtaget dit projekt og vender tilbage inden for 1–2 hverdage.'; ?></span>
            </div>
        <?php elseif ('error' === $contact_status) : ?>
            <div class="orca-notice orca-notice--error" role="alert"><strong>Vi kunne ikke sende din besked.</strong><span>Kontrollér de obligatoriske felter, og prøv igen.</span></div>
        <?php endif; ?>

        <div class="orca-contact__tabs" role="tablist" aria-label="Kontaktmuligheder">
            <button class="orca-contact__tab<?php echo 'quote' === $contact_type ? ' is-active' : ''; ?>" type="button" role="tab" id="tab-quote" aria-controls="panel-quote" aria-selected="<?php echo 'quote' === $contact_type ? 'true' : 'false'; ?>" data-contact-tab="quote">
                <span class="orca-contact__tab-icon" aria-hidden="true">↗</span><span><strong>Få et tilbud</strong><small>Start et nyt projekt med os</small></span>
            </button>
            <button class="orca-contact__tab<?php echo 'support' === $contact_type ? ' is-active' : ''; ?>" type="button" role="tab" id="tab-support" aria-controls="panel-support" aria-selected="<?php echo 'support' === $contact_type ? 'true' : 'false'; ?>" data-contact-tab="support">
                <span class="orca-contact__tab-icon" aria-hidden="true">?</span><span><strong>Få support</strong><small>Hjælp til en eksisterende løsning</small></span>
            </button>
        </div>

        <div class="orca-contact__panel" id="panel-quote" role="tabpanel" aria-labelledby="tab-quote"<?php echo 'quote' !== $contact_type ? ' hidden' : ''; ?>>
            <form class="orca-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="orca_submit_contact"><input type="hidden" name="contact_type" value="quote">
                <?php wp_nonce_field('orca_contact_form', 'orca_contact_nonce'); ?>
                <div class="orca-form__grid">
                    <label>Dit navn <span>*</span><input type="text" name="name" autocomplete="name" required></label>
                    <label>Virksomhed <span>*</span><input type="text" name="company" autocomplete="organization" required></label>
                    <label>E-mail <span>*</span><input type="email" name="email" autocomplete="email" required></label>
                    <label>Telefon<input type="tel" name="phone" autocomplete="tel"></label>
                    <label class="orca-form__full">Hvad skal vi hjælpe med? <span>*</span><select name="service" required><option value="">Vælg en service</option><option value="website">Hjemmeside eller webshop</option><option value="branding">Branding og visuel identitet</option><option value="marketing">Reklame og kampagner</option><option value="social-media">Sociale medier</option><option value="seo">SEO og online synlighed</option><option value="complete">En samlet digital løsning</option><option value="other">Andet</option></select></label>
                    <label>Budget<select name="budget"><option value="">Vælg budgetniveau</option><option value="under-10000">Under 10.000 kr.</option><option value="10000-25000">10.000–25.000 kr.</option><option value="25000-50000">25.000–50.000 kr.</option><option value="over-50000">Over 50.000 kr.</option><option value="unsure">Ikke afklaret endnu</option></select></label>
                    <label>Ønsket deadline<input type="date" name="deadline"></label>
                    <label class="orca-form__full">Fortæl kort om projektet <span>*</span><textarea name="message" rows="6" placeholder="Hvad vil I gerne opnå, og hvordan kan Orca hjælpe?" required></textarea></label>
                </div>
                <label class="orca-form__consent"><input type="checkbox" name="consent" value="1" required><span>Jeg accepterer, at Orca må behandle mine oplysninger for at besvare henvendelsen. *</span></label>
                <button class="orca-form__submit" type="submit">Send forespørgsel <span aria-hidden="true">→</span></button>
            </form>
        </div>

        <div class="orca-contact__panel" id="panel-support" role="tabpanel" aria-labelledby="tab-support"<?php echo 'support' !== $contact_type ? ' hidden' : ''; ?>>
            <form class="orca-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="orca_submit_contact"><input type="hidden" name="contact_type" value="support">
                <?php wp_nonce_field('orca_contact_form', 'orca_contact_nonce'); ?>
                <div class="orca-form__grid">
                    <label>Dit navn <span>*</span><input type="text" name="name" autocomplete="name" required></label>
                    <label>Virksomhed <span>*</span><input type="text" name="company" autocomplete="organization" required></label>
                    <label>E-mail <span>*</span><input type="email" name="email" autocomplete="email" required></label>
                    <label>Link til jeres løsning<input type="url" name="website" placeholder="https://"></label>
                    <label class="orca-form__full">Hvad drejer det sig om? <span>*</span><select name="service" required><option value="">Vælg emne</option><option value="technical">Teknisk problem</option><option value="content">Rettelse af indhold</option><option value="access">Login eller adgang</option><option value="billing">Faktura eller abonnement</option><option value="other">Andet</option></select></label>
                    <label class="orca-form__full">Beskriv problemet <span>*</span><textarea name="message" rows="6" placeholder="Beskriv hvad der sker, og hvad du forventede skulle ske." required></textarea></label>
                </div>
                <label class="orca-form__consent"><input type="checkbox" name="consent" value="1" required><span>Jeg accepterer, at Orca må behandle mine oplysninger for at besvare henvendelsen. *</span></label>
                <button class="orca-form__submit" type="submit">Send supportsag <span aria-hidden="true">→</span></button>
            </form>
        </div>
    </section>

    <section class="orca-contact__direct" aria-label="Direkte kontakt"><p>Foretrækker du at skrive direkte?</p><a href="mailto:kontakt@orca.dk">kontakt@orca.dk</a></section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('[data-contact-tab]');
    const panels = document.querySelectorAll('.orca-contact__panel');
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (item) { const active = item === tab; item.classList.toggle('is-active', active); item.setAttribute('aria-selected', active ? 'true' : 'false'); });
            panels.forEach(function (panel) { panel.hidden = panel.id !== 'panel-' + tab.dataset.contactTab; });
        });
    });
});
</script>
<?php wp_footer(); ?>
</body>
</html>
