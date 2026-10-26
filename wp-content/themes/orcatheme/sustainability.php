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
            <p class="orca-kicker"><?php echo esc_html('Vores ansvar'); ?></p>
            <h1 id="sustainability-title"><?php echo esc_html('Idéer med mindre aftryk.'); ?></h1>
            <p><?php echo esc_html('Vi hjælper virksomheder med at kommunikere mere bevidst, skabe relationer der holder, og vælge løsninger der giver mening både for mennesker og planeten.'); ?></p>
        </div>
    </section>

    <section class="orca-sustainability__intro" aria-labelledby="sustainability-intro-title">
        <div>
            <p class="orca-kicker"><?php echo esc_html('Mere end et løfte'); ?></p>
            <h2 id="sustainability-intro-title"><?php echo esc_html('Bæredygtighed ligger i måden, vi arbejder på.'); ?></h2>
        </div>
        <p><?php echo esc_html('For os handler bæredygtighed ikke om at pynte sig med store ord. Det handler om de små valg i hverdagen: færre unødige produktioner, tydeligere budskaber, inkluderende oplevelser og samarbejder, der skaber værdi over tid.'); ?></p>
    </section>

    <section class="orca-sustainability__initiatives" aria-labelledby="initiatives-title">
        <div class="orca-sustainability__section-heading">
            <p class="orca-kicker"><?php echo esc_html('Sådan gør vi'); ?></p>
            <h2 id="initiatives-title"><?php echo esc_html('Fem områder, hvor omtanke kan mærkes.'); ?></h2>
        </div>

        <div class="orca-sustainability__grid">
            <article class="orca-sustainability__card">
                <span class="orca-sustainability__number">01</span>
                <h3><?php echo esc_html('Kommunikation med formål'); ?></h3>
                <p><?php echo esc_html('Vi gør budskaber enklere og mere relevante, så mennesker finder det, de har brug for, uden støj og spildte ressourcer.'); ?></p>
            </article>
            <article class="orca-sustainability__card">
                <span class="orca-sustainability__number">02</span>
                <h3><?php echo esc_html('Design der holder'); ?></h3>
                <p><?php echo esc_html('Vi skaber identiteter og indhold, der kan udvikle sig over tid i stedet for at blive kasseret ved hver ny tendens.'); ?></p>
            </article>
            <article class="orca-sustainability__card">
                <span class="orca-sustainability__number">03</span>
                <h3><?php echo esc_html('Lokale relationer'); ?></h3>
                <p><?php echo esc_html('Vi prioriterer samarbejder tæt på os og hjælper virksomheder med at fortælle de historier, der styrker deres lokale fællesskaber.'); ?></p>
            </article>
            <article class="orca-sustainability__card">
                <span class="orca-sustainability__number">04</span>
                <h3><?php echo esc_html('Plads til flere'); ?></h3>
                <p><?php echo esc_html('Vi arbejder for kommunikation, hvor flere kan se sig selv, forstå budskabet og føle sig inviteret med.'); ?></p>
            </article>
            <article class="orca-sustainability__card">
                <span class="orca-sustainability__number">05</span>
                <h3><?php echo esc_html('Lettere filer, mindre belastning'); ?></h3>
                <p><?php echo esc_html('Vi optimerer billeder og filer, så de ikke fylder mere end nødvendigt på hjemmesiden. Det mindsker dataoverførslen, energiforbruget og den belastning, digitale besøg kan have på miljøet.'); ?></p>
            </article>
        </div>
    </section>

    <section class="orca-sustainability__feature">
        <figure>
            <img src="https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fm=webp&fit=crop&w=800&q=60" alt="<?php echo esc_attr('Hænder der passer på en lille plante'); ?>" loading="lazy" />
        </figure>
        <div>
            <p class="orca-kicker"><?php echo esc_html('Sammen om næste skridt'); ?></p>
            <h2><?php echo esc_html('Det behøver ikke være perfekt for at gøre en forskel.'); ?></h2>
            <p><?php echo esc_html('Vi tror på ærlige fremskridt. Derfor starter vi med at forstå, hvor I står, og finder derefter de initiativer, der passer til jeres mennesker, mål og hverdag.'); ?></p>
            <a class="orca-sustainability__link" href="<?php echo esc_url(home_url('/contact')); ?>"><?php echo esc_html('Tal med os om jeres næste skridt'); ?> <span aria-hidden="true">→</span></a>
        </div>
    </section>

    <section class="orca-sustainability__quote">
        <p>“</p>
        <blockquote><?php echo esc_html('Når vi vælger med omtanke, bliver kreativitet ikke bare noget, man ser. Det bliver noget, man kan mærke.'); ?></blockquote>
    </section>
</main>

<?php get_footer(); ?>
