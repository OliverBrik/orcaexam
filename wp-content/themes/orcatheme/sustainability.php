<?php
/**
 * Template Name: Sustainability Initiatives
 *
 * @package Orca_Theme
 */

get_header();
?>

<main class="orca-sustainability">
    <section class="orca-sustainability__hero" aria-labelledby="sustainability-title">
        <div class="orca-sustainability__hero-content">
            <p class="orca-contact__kicker"><?php echo esc_html(orca_text('Vores ansvar', 'Our responsibility')); ?></p>
            <h1 id="sustainability-title"><?php echo esc_html(orca_text('Idéer med mindre aftryk.', 'Ideas with a lighter footprint.')); ?></h1>
            <p><?php echo esc_html(orca_text('Vi hjælper virksomheder med at kommunikere mere bevidst, skabe relationer der holder, og vælge løsninger der giver mening både for mennesker og planeten.', 'We help businesses communicate more consciously, build relationships that last, and choose solutions that make sense for people and the planet.')); ?></p>
        </div>
    </section>

    <section class="orca-sustainability__intro" aria-labelledby="sustainability-intro-title">
        <div>
            <p class="orca-contact__kicker"><?php echo esc_html(orca_text('Mere end et løfte', 'More than a promise')); ?></p>
            <h2 id="sustainability-intro-title"><?php echo esc_html(orca_text('Bæredygtighed ligger i måden, vi arbejder på.', 'Sustainability lives in the way we work.')); ?></h2>
        </div>
        <p><?php echo esc_html(orca_text('For os handler bæredygtighed ikke om at pynte sig med store ord. Det handler om de små valg i hverdagen: færre unødige produktioner, tydeligere budskaber, inkluderende oplevelser og samarbejder, der skaber værdi over tid.', 'To us, sustainability is not about decorating our work with big words. It is about everyday choices: fewer unnecessary productions, clearer messages, inclusive experiences, and partnerships that create value over time.')); ?></p>
    </section>

    <section class="orca-sustainability__initiatives" aria-labelledby="initiatives-title">
        <div class="orca-sustainability__section-heading">
            <p class="orca-contact__kicker"><?php echo esc_html(orca_text('Sådan gør vi', 'How we contribute')); ?></p>
            <h2 id="initiatives-title"><?php echo esc_html(orca_text('Fem områder, hvor omtanke kan mærkes.', 'Five areas where care makes a difference.')); ?></h2>
        </div>

        <div class="orca-sustainability__grid">
            <article class="orca-sustainability__card">
                <span class="orca-sustainability__number">01</span>
                <h3><?php echo esc_html(orca_text('Kommunikation med formål', 'Purposeful communication')); ?></h3>
                <p><?php echo esc_html(orca_text('Vi gør budskaber enklere og mere relevante, så mennesker finder det, de har brug for, uden støj og spildte ressourcer.', 'We make messages simpler and more relevant, so people find what they need without noise or wasted effort.')); ?></p>
            </article>
            <article class="orca-sustainability__card">
                <span class="orca-sustainability__number">02</span>
                <h3><?php echo esc_html(orca_text('Design der holder', 'Design that lasts')); ?></h3>
                <p><?php echo esc_html(orca_text('Vi skaber identiteter og indhold, der kan udvikle sig over tid i stedet for at blive kasseret ved hver ny tendens.', 'We create identities and content that can evolve over time instead of being discarded with every new trend.')); ?></p>
            </article>
            <article class="orca-sustainability__card">
                <span class="orca-sustainability__number">03</span>
                <h3><?php echo esc_html(orca_text('Lokale relationer', 'Local relationships')); ?></h3>
                <p><?php echo esc_html(orca_text('Vi prioriterer samarbejder tæt på os og hjælper virksomheder med at fortælle de historier, der styrker deres lokale fællesskaber.', 'We prioritize relationships close to home and help businesses tell stories that strengthen their local communities.')); ?></p>
            </article>
            <article class="orca-sustainability__card">
                <span class="orca-sustainability__number">04</span>
                <h3><?php echo esc_html(orca_text('Plads til flere', 'Room for more')); ?></h3>
                <p><?php echo esc_html(orca_text('Vi arbejder for kommunikation, hvor flere kan se sig selv, forstå budskabet og føle sig inviteret med.', 'We work toward communication where more people can see themselves, understand the message, and feel invited in.')); ?></p>
            </article>
            <article class="orca-sustainability__card">
                <span class="orca-sustainability__number">05</span>
                <h3><?php echo esc_html(orca_text('Lettere filer, mindre belastning', 'Lighter files, less impact')); ?></h3>
                <p><?php echo esc_html(orca_text('Vi optimerer billeder og filer, så de ikke fylder mere end nødvendigt på hjemmesiden. Det mindsker dataoverførslen, energiforbruget og den belastning, digitale besøg kan have på miljøet.', 'We optimize images and files so they do not take up more space than necessary on a website. This reduces data transfer, energy use, and the environmental impact of digital visits.')); ?></p>
            </article>
        </div>
    </section>

    <section class="orca-sustainability__feature">
        <figure>
            <img src="https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fm=webp&fit=crop&w=800&q=60" alt="<?php echo esc_attr(orca_text('Hænder der passer på en lille plante', 'Hands caring for a small plant')); ?>" loading="lazy" />
        </figure>
        <div>
            <p class="orca-contact__kicker"><?php echo esc_html(orca_text('Sammen om næste skridt', 'The next step, together')); ?></p>
            <h2><?php echo esc_html(orca_text('Det behøver ikke være perfekt for at gøre en forskel.', 'It does not have to be perfect to make a difference.')); ?></h2>
            <p><?php echo esc_html(orca_text('Vi tror på ærlige fremskridt. Derfor starter vi med at forstå, hvor I står, og finder derefter de initiativer, der passer til jeres mennesker, mål og hverdag.', 'We believe in honest progress. We start by understanding where you are, then find the initiatives that fit your people, goals, and everyday work.')); ?></p>
            <a class="orca-sustainability__link" href="<?php echo esc_url(home_url('/contact')); ?>"><?php echo esc_html(orca_text('Tal med os om jeres næste skridt', 'Talk to us about your next step')); ?> <span aria-hidden="true">→</span></a>
        </div>
    </section>

    <section class="orca-sustainability__quote">
        <p>“</p>
        <blockquote><?php echo esc_html(orca_text('Når vi vælger med omtanke, bliver kreativitet ikke bare noget, man ser. Det bliver noget, man kan mærke.', 'When we choose with care, creativity becomes more than something people see. It becomes something they can feel.')); ?></blockquote>
    </section>
</main>

<?php get_footer(); ?>
