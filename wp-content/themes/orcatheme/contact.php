<?php
/**
 * Template Name: Kontakt
 *
 * @package Orca_Theme
 */

$contact_status = isset($_GET['contact-status']) ? sanitize_key(wp_unslash($_GET['contact-status'])) : '';
$contact_type   = isset($_GET['contact-type']) ? sanitize_key(wp_unslash($_GET['contact-type'])) : 'quote';
$contact_type   = in_array($contact_type, array('quote', 'support'), true) ? $contact_type : 'quote';

get_header();
?>

<main id="primary" class="orca-contact">
    <section class="orca-contact__hero" aria-labelledby="contact-title">
        <div class="orca-contact__eyebrow"><?php echo esc_html(orca_text('Kontakt Orca', 'Contact Orca')); ?></div>
        <h1 id="contact-title"><?php echo esc_html(orca_text('Lad os skabe noget,', 'Let’s create something')); ?><br><span><?php echo esc_html(orca_text('der bliver set.', 'that gets noticed.')); ?></span></h1>
        <p><?php echo esc_html(orca_text('Fortæl os om din virksomhed, dit projekt eller din udfordring. Vi hjælper med alt fra hjemmeside og branding til kampagner og digital synlighed.', 'Tell us about your business, project, or challenge. We help with everything from websites and branding to campaigns and digital visibility.')); ?></p>
        <div class="orca-contact__facts" aria-label="<?php echo esc_attr(orca_text('Fordele ved at kontakte Orca', 'Benefits of contacting Orca')); ?>">
            <span><?php echo esc_html(orca_text('Uforpligtende dialog', 'No-obligation conversation')); ?></span>
            <span><?php echo esc_html(orca_text('Svar inden for 1–2 hverdage', 'Reply within 1–2 business days')); ?></span>
            <span><?php echo esc_html(orca_text('Løsninger tilpasset jer', 'Solutions tailored to you')); ?></span>
        </div>
    </section>

    <section class="orca-contact__workspace" aria-labelledby="contact-form-title">
        <div class="orca-contact__intro">
            <p class="orca-contact__kicker"><?php echo esc_html(orca_text('Hvad kan vi hjælpe med?', 'How can we help?')); ?></p>
            <h2 id="contact-form-title"><?php echo esc_html(orca_text('Vælg den rette vej ind', 'Choose the right way to reach us')); ?></h2>
            <p><?php echo esc_html(orca_text('Skal vi udvikle noget nyt, eller har du allerede en løsning hos Orca, som du har brug for hjælp til?', 'Are we creating something new, or do you already have an Orca solution that needs support?')); ?></p>
        </div>

        <?php if ('success' === $contact_status) : ?>
            <div class="orca-notice orca-notice--success" role="status">
                <strong><?php echo esc_html(orca_text('Tak for din henvendelse.', 'Thank you for contacting us.')); ?></strong>
                <span><?php echo esc_html('support' === $contact_type ? orca_text('Din supportsag er modtaget. Vi vender tilbage hurtigst muligt.', 'Your support request has been received. We will get back to you as soon as possible.') : orca_text('Vi har modtaget dit projekt og vender tilbage inden for 1–2 hverdage.', 'We have received your project enquiry and will reply within 1–2 business days.')); ?></span>
            </div>
        <?php elseif ('error' === $contact_status) : ?>
            <div class="orca-notice orca-notice--error" role="alert"><strong><?php echo esc_html(orca_text('Vi kunne ikke sende din besked.', 'We could not send your message.')); ?></strong><span><?php echo esc_html(orca_text('Kontrollér de obligatoriske felter, og prøv igen.', 'Check the required fields and try again.')); ?></span></div>
        <?php endif; ?>

        <div class="orca-contact__tabs" role="tablist" aria-label="<?php echo esc_attr(orca_text('Kontaktmuligheder', 'Contact options')); ?>">
            <button class="orca-contact__tab<?php echo 'quote' === $contact_type ? ' is-active' : ''; ?>" type="button" role="tab" id="tab-quote" aria-controls="panel-quote" aria-selected="<?php echo 'quote' === $contact_type ? 'true' : 'false'; ?>" data-contact-tab="quote">
                <span class="orca-contact__tab-icon" aria-hidden="true">↗</span><span><strong><?php echo esc_html(orca_text('Få et tilbud', 'Request a quote')); ?></strong><small><?php echo esc_html(orca_text('Start et nyt projekt med os', 'Start a new project with us')); ?></small></span>
            </button>
            <button class="orca-contact__tab<?php echo 'support' === $contact_type ? ' is-active' : ''; ?>" type="button" role="tab" id="tab-support" aria-controls="panel-support" aria-selected="<?php echo 'support' === $contact_type ? 'true' : 'false'; ?>" data-contact-tab="support">
                <span class="orca-contact__tab-icon" aria-hidden="true">?</span><span><strong><?php echo esc_html(orca_text('Få support', 'Get support')); ?></strong><small><?php echo esc_html(orca_text('Hjælp til en eksisterende løsning', 'Help with an existing solution')); ?></small></span>
            </button>
        </div>

        <div class="orca-contact__panel" id="panel-quote" role="tabpanel" aria-labelledby="tab-quote"<?php echo 'quote' !== $contact_type ? ' hidden' : ''; ?>>
            <form class="orca-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="orca_submit_contact"><input type="hidden" name="contact_type" value="quote">
                <?php wp_nonce_field('orca_contact_form', 'orca_contact_nonce'); ?>
                <div class="orca-form__grid">
                    <label><?php echo esc_html(orca_text('Dit navn', 'Your name')); ?> <span>*</span><input type="text" name="name" autocomplete="name" required></label>
                    <label><?php echo esc_html(orca_text('Virksomhed', 'Company')); ?> <span>*</span><input type="text" name="company" autocomplete="organization" required></label>
                    <label>E-mail <span>*</span><input type="email" name="email" autocomplete="email" required></label>
                    <label><?php echo esc_html(orca_text('Telefon', 'Phone')); ?><input type="tel" name="phone" autocomplete="tel"></label>
                    <label class="orca-form__full"><?php echo esc_html(orca_text('Hvad skal vi hjælpe med?', 'How can we help?')); ?> <span>*</span><select name="service" required><option value=""><?php echo esc_html(orca_text('Vælg en service', 'Choose a service')); ?></option><option value="website"><?php echo esc_html(orca_text('Hjemmeside eller webshop', 'Website or online store')); ?></option><option value="branding"><?php echo esc_html(orca_text('Branding og visuel identitet', 'Branding and visual identity')); ?></option><option value="marketing"><?php echo esc_html(orca_text('Reklame og kampagner', 'Advertising and campaigns')); ?></option><option value="social-media"><?php echo esc_html(orca_text('Sociale medier', 'Social media')); ?></option><option value="seo"><?php echo esc_html(orca_text('SEO og online synlighed', 'SEO and online visibility')); ?></option><option value="complete"><?php echo esc_html(orca_text('En samlet digital løsning', 'A complete digital solution')); ?></option><option value="other"><?php echo esc_html(orca_text('Andet', 'Other')); ?></option></select></label>
                    <label>Budget<select name="budget"><option value=""><?php echo esc_html(orca_text('Vælg budgetniveau', 'Choose a budget range')); ?></option><option value="under-10000"><?php echo esc_html(orca_text('Under 10.000 kr.', 'Under DKK 10,000')); ?></option><option value="10000-25000"><?php echo esc_html(orca_text('10.000–25.000 kr.', 'DKK 10,000–25,000')); ?></option><option value="25000-50000"><?php echo esc_html(orca_text('25.000–50.000 kr.', 'DKK 25,000–50,000')); ?></option><option value="over-50000"><?php echo esc_html(orca_text('Over 50.000 kr.', 'Over DKK 50,000')); ?></option><option value="unsure"><?php echo esc_html(orca_text('Ikke afklaret endnu', 'Not decided yet')); ?></option></select></label>
                    <label><?php echo esc_html(orca_text('Ønsket deadline', 'Preferred deadline')); ?><input type="date" name="deadline"></label>
                    <label class="orca-form__full"><?php echo esc_html(orca_text('Fortæl kort om projektet', 'Tell us briefly about the project')); ?> <span>*</span><textarea name="message" rows="6" placeholder="<?php echo esc_attr(orca_text('Hvad vil I gerne opnå, og hvordan kan Orca hjælpe?', 'What would you like to achieve, and how can Orca help?')); ?>" required></textarea></label>
                </div>
                <label class="orca-form__consent"><input type="checkbox" name="consent" value="1" required><span><?php echo esc_html(orca_text('Jeg accepterer, at Orca må behandle mine oplysninger for at besvare henvendelsen. *', 'I agree that Orca may process my information to respond to my enquiry. *')); ?></span></label>
                <button class="orca-form__submit" type="submit"><?php echo esc_html(orca_text('Send forespørgsel', 'Send enquiry')); ?> <span aria-hidden="true">→</span></button>
            </form>
        </div>

        <div class="orca-contact__panel" id="panel-support" role="tabpanel" aria-labelledby="tab-support"<?php echo 'support' !== $contact_type ? ' hidden' : ''; ?>>
            <form class="orca-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="orca_submit_contact"><input type="hidden" name="contact_type" value="support">
                <?php wp_nonce_field('orca_contact_form', 'orca_contact_nonce'); ?>
                <div class="orca-form__grid">
                    <label><?php echo esc_html(orca_text('Dit navn', 'Your name')); ?> <span>*</span><input type="text" name="name" autocomplete="name" required></label>
                    <label><?php echo esc_html(orca_text('Virksomhed', 'Company')); ?> <span>*</span><input type="text" name="company" autocomplete="organization" required></label>
                    <label>E-mail <span>*</span><input type="email" name="email" autocomplete="email" required></label>
                    <label><?php echo esc_html(orca_text('Link til jeres løsning', 'Link to your solution')); ?><input type="url" name="website" placeholder="https://"></label>
                    <label class="orca-form__full"><?php echo esc_html(orca_text('Hvad drejer det sig om?', 'What is your request about?')); ?> <span>*</span><select name="service" required><option value=""><?php echo esc_html(orca_text('Vælg emne', 'Choose a topic')); ?></option><option value="technical"><?php echo esc_html(orca_text('Teknisk problem', 'Technical issue')); ?></option><option value="content"><?php echo esc_html(orca_text('Rettelse af indhold', 'Content update')); ?></option><option value="access"><?php echo esc_html(orca_text('Login eller adgang', 'Login or access')); ?></option><option value="billing"><?php echo esc_html(orca_text('Faktura eller abonnement', 'Invoice or subscription')); ?></option><option value="other"><?php echo esc_html(orca_text('Andet', 'Other')); ?></option></select></label>
                    <label class="orca-form__full"><?php echo esc_html(orca_text('Beskriv problemet', 'Describe the issue')); ?> <span>*</span><textarea name="message" rows="6" placeholder="<?php echo esc_attr(orca_text('Beskriv hvad der sker, og hvad du forventede skulle ske.', 'Describe what happens and what you expected to happen.')); ?>" required></textarea></label>
                </div>
                <label class="orca-form__consent"><input type="checkbox" name="consent" value="1" required><span><?php echo esc_html(orca_text('Jeg accepterer, at Orca må behandle mine oplysninger for at besvare henvendelsen. *', 'I agree that Orca may process my information to respond to my enquiry. *')); ?></span></label>
                <button class="orca-form__submit" type="submit"><?php echo esc_html(orca_text('Send supportsag', 'Send support request')); ?> <span aria-hidden="true">→</span></button>
            </form>
        </div>
    </section>

    <section class="orca-contact__direct" aria-label="<?php echo esc_attr(orca_text('Direkte kontakt', 'Direct contact')); ?>"><p><?php echo esc_html(orca_text('Foretrækker du at skrive direkte?', 'Would you prefer to email us directly?')); ?></p><a href="mailto:kontakt@orca.dk">kontakt@orca.dk</a></section>
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
