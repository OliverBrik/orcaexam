# Orca-temaet

Projektet er en klassisk WordPress-installation med WooCommerce. Egen kode ligger i dette tema og i `wp-content/mu-plugins/orca-local-cache.php`. WordPress-core og tredjepartsplugins vedligeholdes gennem deres normale opdateringer.

## Struktur

| Placering | Ansvar |
| --- | --- |
| `functions.php` | Indlæser temaets moduler. |
| `inc/setup.php` | Temastøtte, navigation og versionsstyret indlæsning af CSS/JavaScript. |
| `inc/security.php` | Gæstesessioner, nonce-kontrol, inputformater og sikker visning. |
| `inc/contact.php` | Fælles serviceliste, formularvalidering og lagring af henvendelser. |
| `inc/contact-inbox.php` | Privat indbakke under **Henvendelser** i WordPress. |
| `inc/testimonials.php` | Anmeldelser og administratorens godkendelse. |
| `inc/blog-page.php` | Blogformularer og filtertilstand, indlæst før bloggens HTML. |
| `inc/woocommerce.php` | WooCommerce-integration og links til forespørgsler. |
| `template-parts/shop/` | Webshoppens hero, katalogintroduktion, services og footerindhold. |
| `assets/css/theme.css` | Fælles design og sidestile i den oprindelige kaskaderækkefølge. |
| `assets/css/shop.css` | Stile afgrænset til webshoppen. |
| `assets/css/fonts.css`, `assets/fonts/` | Lokal Archivo Narrow 700 (latin/latin-ext) og SIL OFL-licens; ingen Google Fonts-forespørgsel ved sidebygning. |
| `assets/js/` | Forsidens galleri, kontaktsidens video/faner og bloginteraktioner. |
| `header.php`, `footer.php` | Fælles dokumentskal og WordPress-hooks. |
| `gallery/` | Forsidens billeder; alle billeder i mappen bruges automatisk. |
| `contact-media/` | Kontaktbilleder og video, der først indlæses ved afspilning. |

Rodens `style.css` er temaets WordPress-manifest. CSS og JavaScript indlæses via enqueue-funktionerne med filernes ændringstid som version, så ændringer opdaterer browsercachen. Sidescripts indlæses kun på de relevante sider.

## Hastighed og kurv

Behold WP-Optimizes sidecache. Fonten leveres lokalt, så pluginets CSS-behandling ikke skal hente filstørrelsen hos Google. Efter deployment skal installationens HTML-cache tømmes; filversioner alene ændrer ikke HTML, som allerede ligger i sidecachen.

Kurv, checkout og konto undtages fra WP-Optimize-minificering via `wpo_minify_run_on_page`. Under browserkontrollen viste den minificerede kurv en tom kurv trods en vare i Store API; de oprindelige WooCommerce-filer viste varen korrekt. Sidecachen og minificering på almindelige sider bevares.

`orca_needs_commerce_assets()` bevarer WooCommerce-ressourcer på shop-, produkt-, kurv-, checkout- og kontosider samt sider med WooCommerce-blokke/-shortcodes. Andre integrationer kan bruge filteret med samme navn til at bevare ressourcer, fx når produkter indsættes fra en anden kilde end sidens gemte indhold.

På almindelige indholdssider fjernes de generelle WooCommerce-stile, produktknapscriptet og cart-fragments. Den lille `assets/js/cart-link.js` opdaterer headerens kurv via WooCommerces eksisterende endpoint, når `woocommerce_items_in_cart` viser, at der er varer. En tom kurv udløser ingen forespørgsel. Browserhistorik og WooCommerces lagerhændelser fra andre faner opdaterer også visningen. Shopsider bruger fortsat WooCommerces fragments-script. På blokbaseret kurv/checkout følger headeren `wc/store/cart` direkte, også ved antalændringer og fjernelse af sidste vare, uden endnu et HTTP-kald. Ordreattribution bevares.

Gæstens signerede formularsession gendannes også ved nonce-verifikation, fordi kontaktens `admin-post.php` ikke kører `template_redirect`. POST med manglende eller ugyldig sessionscookie bliver fortsat afvist.

Kør `php -n tests/performance-regression.php` fra temamappen for databasefri kontrol af WordPress-nonces og politikken for shopressourcer. Kør `node tests/cart-link.test.cjs` for kurvtællerens tomme/aktive kurv, browserhistorik, fanesynkronisering og blokhændelser. Testene erstatter ikke en browserkontrol af køb eller rigtig maillevering.

## Sideskabeloner

Bevar navnene `contact.php`, `blog.php`, `testimonial.php` og `sustainability.php`: WordPress gemmer disse filnavne i sidernes skabelonvalg. `index.php` er forsiden/fallback. `single.php` viser blogindlæg. `shop-page.php` viser WooCommerces gemte indhold for kurv, checkout og konto; produktoversigten og produktsiderne bruger WooCommerces egne templates med temaets wrappers.

## Genbrug

- Skriv brugerrettet tekst på dansk og brug eksisterende escape-funktioner ved output.
- Brug `orca-hero`, `orca-eyebrow` og `orca-kicker` til fælles hero- og sektionsstile. Farver og overflader findes som CSS-variabler øverst i `theme.css`.
- Kontaktformularens muligheder og servervalidering kommer fra `orca_contact_services()`; tilføj nye services dér.
- Brug `orca_shop_enquiry_url()` til links fra produkter og services til kontaktformularen.
- Afslut skabeloner med `get_footer()`, så WordPress og plugins får deres scripts indlæst én gang.

## Runtime og data

Cache, statistiksessioner, WooCommerce-logfiler, Wordfence-runtimefiler og backup-logs versionsstyres ikke. De skal blive på installationen og gendannes/vedligeholdes af de respektive plugins. Beskyttelsesfiler og konfiguration er ikke slettet lokalt under oprydningen. Uploadede billeder og andre mediefiler er bevaret; en fil kan være refereret fra databasen uden en reference i PHP-koden.

Den lokale installation bruger fortsat den eksterne database. Undgå derfor at antage, at ændringer af indhold, produkter eller pluginindstillinger kun rammer en lokal database. `orca-local-cache.php` giver lokale WP-Optimize-overrides og bevarer den fælles databases cacheindstillinger. Formularsider og handel må ikke dele gæsters sikkerhedstokens via sidecache.

Webshoppen leder til forespørgsler om logo og tilpasning. Logoupload, produktforhåndsvisning, endelige priser, fragt og betaling skal afklares separat. Stripe-pluginet er installeret, men var ikke aktiveret ved oprydningen.

## Kontrol efter ændringer

Kontrollér PHP med `php -l` og scripts med `node --check`. Gennemgå forside, kontakt, blog, anmeldelser, bæredygtighed, shop, produkt, kurv og konto. Kontrollér sprogvalg, formularernes nonce- og sessionskontrol, produktforespørgsler samt kurv. Tøm den lokale forsidecache, når markup eller assetstier ændres. HTTP- og syntakskontrol erstatter ikke en visuel browserkontrol.
