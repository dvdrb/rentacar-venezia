<?php
/** Local-only idempotent page provisioner. Set RENTACAR_LOCAL_APPLY=1 to make changes. */
defined( 'ABSPATH' ) || exit( 1 );

$version = '2026-07-26.2';
$arguments = isset( $assoc_args ) && is_array( $assoc_args ) ? $assoc_args : array();
$apply = ! empty( $arguments['apply'] ) || '1' === getenv( 'RENTACAR_LOCAL_APPLY' );
$pages = array(
    'fleet' => array( 'title' => 'Fleet', 'slug' => 'fleet', 'template' => 'page-templates/template-fleet.php', 'content' => '<h2>Rental cars in Venice and Treviso</h2><p>Choose the vehicle that suits your trip and send one straightforward request. Our local team confirms availability, the final price and the rental conditions personally before collection.</p>' ),
    'how_it_works' => array( 'title' => 'How it works', 'slug' => 'how-it-works', 'template' => 'page-templates/template-how-it-works.php', 'content' => '<h2>Send a rental request in five clear steps</h2><ol><li>Enter your trip details.</li><li>Choose your car.</li><li>Complete the short reservation form.</li><li>Receive the final details.</li><li>Collect your vehicle.</li></ol><p><strong>Submitting this request does not immediately confirm the reservation. We will check availability and contact you.</strong></p><p><a href="/fleet/">Browse the fleet</a> or read the <a href="/rental-requirements/">rental requirements</a> before sending a request.</p>' ),
    'rental_requirements' => array( 'title' => 'Rental requirements', 'slug' => 'rental-requirements', 'template' => 'page-templates/template-rental-requirements.php', 'content' => '<h2>Driver and document requirements</h2><p>Drivers must be at least 23, hold an original valid category B licence for at least three years, and present an original driving licence plus a passport or identity document. The licence must be legally accepted for driving in Italy. Additional drivers must meet the same requirements.</p><h2>Payment, deposit and mileage</h2><p>No payment is required to send a request. Rental payment and the deposit are paid at pickup. The deposit is €350 for vehicles up to five seats and €500 for vehicles with seven to nine seats. It is separate from the rental total.</p><p>Each rental day includes 150 km. Additional kilometres cost €0.10 each. Return the vehicle with the same fuel level supplied.</p><h2>Pickup, use and return</h2><p>After-hours pickup, insurance options, child seats and additional drivers are confirmed in the request and rental contract. Only authorised drivers may use the vehicle. Observe traffic, parking, toll and ZTL rules; cross-border travel requires prior authorisation. Report accidents, theft, breakdowns or expected delays immediately and do not arrange repairs without authorisation.</p><p>Read the <a href="/terms-and-conditions/">Terms and Conditions</a> for the complete rental terms.</p>' ),
    'faq' => array( 'title' => 'Frequently asked questions', 'slug' => 'faq', 'template' => 'page-templates/template-faq.php', 'content' => '<h2>Reservation process</h2><details><summary>Is my request a confirmed reservation?</summary><p>No. We check availability, final price and rental conditions personally before confirmation.</p></details><details><summary>Is payment required to send a request?</summary><p>No payment is required to send a request. Payment is made at pickup.</p></details><h2>Airport pickup</h2><details><summary>How are airport pickup details arranged?</summary><p>Our team confirms the practical pickup details directly with you before collection.</p></details>' ),
    'contact' => array( 'title' => 'Contact', 'slug' => 'contact', 'template' => 'page-templates/template-contact.php', 'content' => '<h2>Contact Rent a Car Venezia</h2><p>Phone and WhatsApp: <a href="tel:+393445068823">+39 344 506 8823</a><br>Email: <a href="mailto:info@rentacarvenezia.it">info@rentacarvenezia.it</a><br>Office hours: Monday–Friday, 08:00–17:00.</p><p>Requests can be sent online at any time. For a reservation request, please use the vehicle request flow.</p>' ),
    'terms' => array( 'title' => 'Terms and Conditions', 'slug' => 'terms-and-conditions', 'template' => 'page-templates/template-terms.php', 'content' => '<nav aria-label="Contents"><strong>Contents</strong><ol><li><a href="#reservation">Reservation and confirmation</a></li><li><a href="#drivers">Drivers and documents</a></li><li><a href="#payment">Payment, deposit and insurance</a></li><li><a href="#use">Vehicle use, pickup and return</a></li><li><a href="#incidents">Accidents, damage and charges</a></li></ol></nav><h2 id="reservation">Reservation and confirmation</h2><p>A request is not a confirmed reservation. We check the selected vehicle, availability, final price and rental conditions personally before confirmation. Free cancellation is available up to 24 hours before pickup. Later cancellation, no-show or changes may have consequences communicated in the confirmation; changes remain subject to availability and any applicable price adjustment.</p><h2 id="drivers">Drivers and documents</h2><p>Drivers must be at least 23 and must hold an original, valid category B licence for at least three years, plus a valid passport or national identity card. The licence must be legally accepted in Italy. Non-EU/EEA licences may require an international driving permit or an official Italian translation. Any additional driver must meet the same conditions.</p><h2 id="payment">Payment, deposit and insurance</h2><p>No payment is required to send a request. Rental payment and the deposit are due at pickup; credit and debit cards are accepted. Displayed rental prices include VAT and RCA. The deposit is €350 for vehicles up to five seats and €500 for vehicles with seven to nine seats. It is separate from the rental total and is released after return inspection when no charges remain, subject to bank processing time.</p><p>Insurance choices, coverage, exclusions and any remaining customer responsibility are confirmed in the rental contract. Damage-protection limits are not deductibles and do not by themselves remove every responsibility.</p><h2 id="use">Mileage, fuel, pickup, return and permitted use</h2><p>Each rental day includes 150 km; additional kilometres cost €0.10 each. Return the vehicle with the same fuel level as supplied; missing fuel may be charged. Airport pickup arrangements are confirmed personally. Exact return instructions are agreed personally. Early return does not automatically create a refund and expected delays must be reported.</p><p>Only authorised drivers may use the vehicle. The vehicle must not be used for racing, off-road driving, unauthorised towing, dangerous goods, illegal activity, paid passenger transport or driving under the influence. Smoking is not permitted. Animals require prior approval and suitable transport arrangements. Travel outside Italy requires advance authorisation and may require documents, coverage, restrictions or fees.</p><h2 id="incidents">Accidents, theft, damage and charges</h2><p>Contact us immediately after an accident, theft, attempted theft, vandalism or breakdown. Do not arrange repairs without authorisation. Complete a CID accident form when applicable and provide photos and third-party details where possible. A police report is required for theft, attempted theft or vandalism. Mechanical roadside support is available for failures not caused by misuse; lost keys, wrong fuel, negligent battery discharge or misuse may result in charges.</p><p>The customer is responsible for fines, tolls, parking and ZTL violations, and an administrative handling fee may apply if confirmed with the request. These terms are governed by applicable Italian law. For privacy questions, use the site Privacy Policy or contact <a href="mailto:info@rentacarvenezia.it">info@rentacarvenezia.it</a>. Last updated: 26 July 2026.</p>' ),
    'guides' => array( 'title' => 'Guides', 'slug' => 'guides', 'template' => '', 'content' => '<h2>Venice and Treviso travel guides</h2><p>Practical guidance for airport pickup, driving, parking and rental preparation.</p>' ),
    'venice_marco_polo' => array( 'title' => 'Venice Marco Polo Airport car rental', 'slug' => 'venice-marco-polo-airport-car-rental', 'template' => 'page-templates/template-airport-location.php', 'location_key' => 'venice_marco_polo', 'content' => '<h2>Car rental at Venice Marco Polo Airport</h2><p>Arrange a request for pickup at Venice Marco Polo Airport. Choose a vehicle and enter your travel details. We check availability and personally confirm the practical pickup details, final price and rental conditions before collection.</p><p><a href="/fleet/">Browse the fleet</a> or see the <a href="/how-it-works/">rental-request steps</a>.</p>' ),
    'treviso_airport' => array( 'title' => 'Treviso Airport car rental', 'slug' => 'treviso-airport-car-rental', 'template' => 'page-templates/template-airport-location.php', 'location_key' => 'treviso_airport', 'content' => '<h2>Car rental at Treviso Airport</h2><p>Arrange a request for pickup at Treviso Airport. Choose a vehicle and enter your travel details. We check availability and personally confirm the practical pickup details, final price and rental conditions before collection.</p><p><a href="/fleet/">Browse the fleet</a> or see the <a href="/how-it-works/">rental-request steps</a>.</p>' ),
);
$report = array();
$localized_pages = array(
    'it' => array(
        'fleet' => array( 'title' => 'Auto a noleggio a Venezia e Treviso', 'content' => '<h2>Auto a noleggio a Venezia e Treviso</h2><p>Scegli il veicolo più adatto al tuo viaggio e invia una richiesta semplice. Il nostro team locale conferma personalmente disponibilità, prezzo finale e condizioni di noleggio prima del ritiro.</p>' ),
        'how_it_works' => array( 'title' => 'Come funziona', 'content' => '<h2>Invia una richiesta di noleggio in pochi passaggi</h2><ol><li>Scegli il veicolo che preferisci dalla flotta.</li><li>Inserisci date, orari e aeroporti di ritiro e riconsegna.</li><li>Per il ritiro in aeroporto, indica il numero di volo per permetterci di seguire l’arrivo.</li><li>Scegli l’assicurazione e gli eventuali extra.</li><li>Invia la richiesta senza effettuare alcun pagamento.</li><li>Il nostro team verifica il veicolo selezionato e conferma personalmente disponibilità, prezzo finale e condizioni di noleggio.</li><li>Conferma direttamente con il nostro team prima del ritiro.</li></ol><p><strong>L’invio di questa richiesta non conferma immediatamente la prenotazione. Verificheremo la disponibilità e ti contatteremo.</strong></p><p><a href="/fleet/">Esplora la flotta</a> oppure leggi i <a href="/rental-requirements/">requisiti di noleggio</a> prima di inviare una richiesta.</p>' ),
        'rental_requirements' => array( 'title' => 'Requisiti di noleggio', 'content' => '<h2>Requisiti per conducente e documenti</h2><p>I conducenti devono avere almeno 23 anni, possedere una patente B originale e valida da almeno tre anni e presentare passaporto o carta d’identità validi. La patente deve essere legalmente accettata per guidare in Italia. Per patenti non UE/SEE potrebbero essere necessari un permesso internazionale di guida o una traduzione ufficiale in italiano. Gli eventuali conducenti aggiuntivi devono rispettare gli stessi requisiti.</p><h2>Pagamento, deposito e chilometraggio</h2><p>Non è richiesto alcun pagamento per inviare una richiesta. Il noleggio e il deposito si pagano al ritiro; sono accettate carte di credito e di debito. I prezzi pubblicati includono IVA e RCA. Il deposito è di €350 per i veicoli fino a cinque posti e di €500 per i veicoli da sette a nove posti. È separato dal totale del noleggio e viene rilasciato dopo l’ispezione di riconsegna quando non restano addebiti; i tempi bancari possono variare.</p><p>Ogni giorno di noleggio include 150 km. I chilometri aggiuntivi costano €0,10 ciascuno. Il veicolo deve essere restituito con lo stesso livello di carburante con cui è stato consegnato; il carburante mancante può essere addebitato.</p><h2>Ritiro, utilizzo e riconsegna</h2><p>Per il ritiro in aeroporto indica un numero di volo valido e contattaci se cambia il piano di arrivo. Ritiro fuori orario, opzioni assicurative, seggiolini e conducente aggiuntivo vengono confermati nella richiesta e nel contratto. Solo i conducenti autorizzati possono usare il veicolo. Rispetta regole del traffico, parcheggi, pedaggi e ZTL; i viaggi fuori dall’Italia richiedono autorizzazione preventiva. Comunica subito incidenti, furti, guasti o ritardi previsti e non organizzare riparazioni senza autorizzazione.</p><p>La cancellazione è gratuita fino a 24 ore prima del ritiro. Le modifiche restano soggette a disponibilità e possibili variazioni di prezzo. Leggi i <a href="/terms-and-conditions/">Termini e condizioni</a> per le condizioni complete.</p>' ),
        'terms' => array( 'title' => 'Termini e condizioni', 'content' => '<nav aria-label="Indice"><strong>Indice</strong><ol><li><a href="#prenotazione">Richiesta e conferma</a></li><li><a href="#conducenti">Conducenti e documenti</a></li><li><a href="#pagamento">Pagamento, deposito e assicurazione</a></li><li><a href="#utilizzo">Uso, ritiro e riconsegna</a></li><li><a href="#sinistri">Sinistri, danni e addebiti</a></li></ol></nav><h2 id="prenotazione">Richiesta e conferma</h2><p>Una richiesta non costituisce una prenotazione confermata. Verifichiamo personalmente il veicolo selezionato, disponibilità, prezzo finale e condizioni prima della conferma. La cancellazione è gratuita fino a 24 ore prima del ritiro. Cancellazioni tardive, mancata presentazione o modifiche possono comportare conseguenze comunicate nella conferma; le modifiche restano soggette a disponibilità e all’eventuale adeguamento del prezzo.</p><h2 id="conducenti">Conducenti e documenti</h2><p>I conducenti devono avere almeno 23 anni e possedere patente B originale e valida da almeno tre anni, oltre a passaporto o carta d’identità validi. La patente deve essere legalmente accettata in Italia. Per patenti non UE/SEE potrebbero essere necessari permesso internazionale di guida o traduzione ufficiale italiana. Ogni conducente aggiuntivo deve rispettare le stesse condizioni.</p><h2 id="pagamento">Pagamento, deposito e assicurazione</h2><p>Non è richiesto alcun pagamento per inviare una richiesta. Il noleggio e il deposito sono dovuti al ritiro; sono accettate carte di credito e di debito. I prezzi pubblicati includono IVA e RCA. Il deposito è di €350 per i veicoli fino a cinque posti e di €500 per i veicoli da sette a nove posti. È separato dal totale e viene rilasciato dopo l’ispezione di riconsegna quando non restano addebiti, secondo i tempi bancari.</p><p>Scelte assicurative, coperture, esclusioni ed eventuali responsabilità residue vengono confermate nel contratto. I limiti di protezione danni non sono franchigie e non eliminano automaticamente ogni responsabilità.</p><h2 id="utilizzo">Chilometraggio, carburante, ritiro, riconsegna e uso consentito</h2><p>Ogni giorno di noleggio include 150 km; i chilometri aggiuntivi costano €0,10 ciascuno. Restituisci il veicolo con lo stesso livello di carburante; il carburante mancante può essere addebitato. Per il ritiro in aeroporto è richiesto un numero di volo valido. Le istruzioni di riconsegna vengono concordate personalmente. La restituzione anticipata non dà automaticamente diritto a rimborso e i ritardi previsti devono essere comunicati.</p><p>Solo i conducenti autorizzati possono usare il veicolo. Sono vietati gare, fuoristrada, traino non autorizzato, merci pericolose, attività illecite, trasporto professionale di passeggeri e guida in stato di alterazione. È vietato fumare. Gli animali richiedono approvazione preventiva e un trasporto adeguato. L’uscita dall’Italia richiede autorizzazione preventiva e può comportare documenti, coperture, limiti o costi.</p><h2 id="sinistri">Sinistri, furto, danni e addebiti</h2><p>Contattaci subito in caso di incidente, furto, tentato furto, vandalismo o guasto. Non organizzare riparazioni senza autorizzazione. Compila il modulo CID quando applicabile e fornisci, se possibile, foto e dati di terzi. Per furto, tentato furto o vandalismo è richiesta denuncia alle autorità. Il soccorso meccanico è disponibile per guasti non dovuti a uso improprio; chiavi smarrite, carburante errato, batteria scarica per negligenza o uso improprio possono comportare addebiti.</p><p>Il cliente è responsabile di multe, pedaggi, parcheggi e violazioni ZTL; può essere applicata una commissione amministrativa se confermata con la richiesta. Si applica la legge italiana. Per domande sulla privacy, consulta l’Informativa privacy o scrivi a <a href="mailto:info@rentacarvenezia.it">info@rentacarvenezia.it</a>. Ultimo aggiornamento: 26 luglio 2026.</p>' ),
        'guides' => array( 'title' => 'Guide', 'content' => '<h2>Guide per Venezia e Treviso</h2><p>Indicazioni pratiche per il ritiro in aeroporto, la guida, il parcheggio e la preparazione della richiesta di noleggio.</p>' ),
        'venice_marco_polo' => array( 'title' => 'Noleggio auto Aeroporto di Venezia Marco Polo', 'content' => '<h2>Noleggio auto all’Aeroporto di Venezia Marco Polo</h2><p>Organizza una richiesta per il ritiro all’Aeroporto di Venezia Marco Polo, Viale Galileo Galilei 30, 30173 Venezia VE, Italia. Scegli un veicolo, inserisci i dettagli di arrivo e un numero di volo valido. Prima del ritiro verifichiamo disponibilità e confermiamo personalmente dettagli pratici, prezzo finale e condizioni di noleggio.</p><p><a href="/fleet/">Esplora la flotta</a> oppure scopri <a href="/how-it-works/">come funziona la richiesta</a>.</p>' ),
        'treviso_airport' => array( 'title' => 'Noleggio auto Aeroporto di Treviso', 'content' => '<h2>Noleggio auto all’Aeroporto di Treviso</h2><p>Organizza una richiesta per il ritiro all’Aeroporto di Treviso, Via Noalese 63/E, 31100 Treviso, Italia. Scegli un veicolo, inserisci i dettagli di arrivo e un numero di volo valido. Prima del ritiro verifichiamo disponibilità e confermiamo personalmente dettagli pratici, prezzo finale e condizioni di noleggio.</p><p><a href="/fleet/">Esplora la flotta</a> oppure scopri <a href="/how-it-works/">come funziona la richiesta</a>.</p>' ),
    ),
    'en' => array(
        'fleet' => array( 'title' => 'Rental cars in Venice and Treviso', 'slug' => 'fleet', 'content' => '<h2>Rental cars in Venice and Treviso</h2><p>Choose your preferred vehicle and send a request. Availability, final price and rental conditions are confirmed personally.</p>' ),
    ),
    'ro' => array(
        'fleet' => array( 'title' => 'Mașini de închiriat în Veneția și Treviso', 'slug' => 'flota', 'content' => '<h2>Mașini de închiriat în Veneția și Treviso</h2><p>Alegeți vehiculul preferat și trimiteți o solicitare. Disponibilitatea, prețul final și condițiile sunt confirmate personal.</p>' ),
        'how_it_works' => array( 'title' => 'Cum funcționează', 'slug' => 'cum-functioneaza', 'content' => '<h2>O solicitare simplă, confirmată personal</h2><p>Alegeți o mașină, introduceți detaliile călătoriei și trimiteți solicitarea fără plată. Echipa noastră confirmă personal disponibilitatea, prețul final și condițiile.</p>' ),
        'rental_requirements' => array( 'title' => 'Condiții de închiriere', 'slug' => 'conditii-inchiriere', 'content' => '<h2>Înainte de solicitare</h2><p>Șoferii trebuie să aibă cel puțin 23 de ani și permis categoria B de minimum trei ani. Plata și depozitul se achită la preluare; prețurile includ TVA și RCA.</p>' ),
        'terms' => array( 'title' => 'Termeni și condiții', 'slug' => 'termeni-conditii', 'content' => '<h2>Condiții de închiriere</h2><p>Solicitările sunt supuse disponibilității și confirmării personale. Anularea este gratuită până la 24 de ore înainte de preluare.</p>' ),
        'guides' => array( 'title' => 'Ghiduri', 'slug' => 'ghiduri', 'content' => '<h2>Ghiduri pentru Veneția și Treviso</h2><p>Informații practice pentru preluarea de la aeroport, condus și pregătirea solicitării de închiriere.</p>' ),
        'venice_marco_polo' => array( 'title' => 'Închirieri auto Aeroportul Veneția Marco Polo', 'slug' => 'inchirieri-auto-aeroport-venetia-marco-polo', 'content' => '<h2>Preluare la Aeroportul Veneția Marco Polo</h2><p>Preluarea este organizată personal lângă Viale Galileo Galilei 30, 30173 Venezia VE, Italia.</p>' ),
        'treviso_airport' => array( 'title' => 'Închirieri auto Aeroportul Treviso', 'slug' => 'inchirieri-auto-aeroport-treviso', 'content' => '<h2>Preluare la Aeroportul Treviso</h2><p>Preluarea este organizată personal la Via Noalese 63/E, 31100 Treviso, Italia.</p>' ),
    ),
    'ru' => array(
        'fleet' => array( 'title' => 'Автомобили в аренду в Венеции и Тревизо', 'slug' => 'avtopark', 'content' => '<h2>Автомобили в аренду в Венеции и Тревизо</h2><p>Выберите автомобиль и отправьте запрос. Доступность, окончательная цена и условия подтверждаются лично.</p>' ),
        'how_it_works' => array( 'title' => 'Как это работает', 'slug' => 'kak-eto-rabotaet', 'content' => '<h2>Простой запрос с личным подтверждением</h2><p>Выберите автомобиль, укажите детали поездки и отправьте запрос без оплаты. Наша команда лично подтверждает доступность, цену и условия.</p>' ),
        'rental_requirements' => array( 'title' => 'Условия аренды', 'slug' => 'usloviya-arendy', 'content' => '<h2>Перед отправкой запроса</h2><p>Водителю должно быть не менее 23 лет, а права категории B должны быть выданы не менее трёх лет назад. Оплата и депозит производятся при получении; цены включают НДС и RCA.</p>' ),
        'terms' => array( 'title' => 'Условия и положения', 'slug' => 'usloviya-i-polozheniya', 'content' => '<h2>Условия аренды</h2><p>Все запросы зависят от доступности и личного подтверждения. Бесплатная отмена возможна не позднее чем за 24 часа до получения.</p>' ),
        'guides' => array( 'title' => 'Путеводители', 'slug' => 'putevoditeli', 'content' => '<h2>Путеводители по Венеции и Тревизо</h2><p>Практическая информация о получении в аэропорту, вождении и подготовке запроса на аренду.</p>' ),
        'venice_marco_polo' => array( 'title' => 'Прокат авто в аэропорту Венеция Марко Поло', 'slug' => 'prokat-avto-aeroport-veneciya-marko-polo', 'content' => '<h2>Получение в аэропорту Венеция Марко Поло</h2><p>Получение организуется лично рядом с Viale Galileo Galilei 30, 30173 Venezia VE, Италия.</p>' ),
        'treviso_airport' => array( 'title' => 'Прокат авто в аэропорту Тревизо', 'slug' => 'prokat-avto-aeroport-treviso', 'content' => '<h2>Получение в аэропорту Тревизо</h2><p>Получение организуется лично по адресу Via Noalese 63/E, 31100 Treviso, Италия.</p>' ),
    ),
);
$localized_pages['it']['how_it_works']['content'] = str_replace( '<li>Per il ritiro in aeroporto, indica il numero di volo per permetterci di seguire l’arrivo.</li>', '', $localized_pages['it']['how_it_works']['content'] );
$localized_pages['it']['rental_requirements']['content'] = str_replace( 'Per il ritiro in aeroporto indica un numero di volo valido e contattaci se cambia il piano di arrivo. ', '', $localized_pages['it']['rental_requirements']['content'] );
$localized_pages['it']['terms']['content'] = str_replace( 'Per il ritiro in aeroporto è richiesto un numero di volo valido. ', 'Le modalità di ritiro in aeroporto vengono confermate personalmente. ', $localized_pages['it']['terms']['content'] );
foreach ( array( 'venice_marco_polo', 'treviso_airport' ) as $location_key ) {
    $localized_pages['it'][ $location_key ]['content'] = str_replace( 'Scegli un veicolo, inserisci i dettagli di arrivo e un numero di volo valido.', 'Scegli un veicolo e inserisci i dettagli del viaggio.', $localized_pages['it'][ $location_key ]['content'] );
}
foreach ( $pages as $key => $page ) {
    $source_copy = isset( $localized_pages['it'][ $key ] ) ? array_merge( $page, $localized_pages['it'][ $key ] ) : $page;
    $existing = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => 1, 'meta_key' => '_rc_provisioning_key', 'meta_value' => $key, 'fields' => 'ids' ) );
    if ( ! $existing ) $existing = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'name' => $page['slug'], 'posts_per_page' => 1, 'fields' => 'ids' ) );
    if ( $existing && function_exists( 'pll_get_post' ) ) {
        $italian_id = (int) pll_get_post( (int) $existing[0], 'it' );
        if ( $italian_id ) $existing[0] = $italian_id;
    }
    $post = array( 'post_title' => $source_copy['title'], 'post_name' => $page['slug'], 'post_status' => 'publish', 'post_type' => 'page' );
    if ( $existing ) {
        $post['ID'] = (int) $existing[0];
        $content_is_empty = '' === trim( (string) get_post_field( 'post_content', $post['ID'] ) );
        $is_provisioned_content = '1' === (string) get_post_meta( $post['ID'], '_rc_provisioned_content', true );
        if ( ! $is_provisioned_content && ! in_array( $key, array( 'faq', 'contact' ), true ) && '' !== (string) get_post_meta( $post['ID'], '_rc_provisioning_version', true ) ) {
            $is_provisioned_content = true;
            if ( $apply ) update_post_meta( $post['ID'], '_rc_provisioned_content', '1' );
        }
        if ( $content_is_empty || $is_provisioned_content ) {
            $post['post_content'] = $source_copy['content'];
        } else {
            unset( $post['post_title'], $post['post_name'] );
        }
        if ( ! $apply ) {
            $report[] = $key . ': would update ' . $post['ID'] . ( $content_is_empty ? ' (empty content populated)' : ( $is_provisioned_content ? ' (provisioned content refreshed)' : ' (existing content preserved)' ) );
            continue;
        }
        $changed = false;
        foreach ( array( 'post_title', 'post_name', 'post_content', 'post_status' ) as $field ) {
            if ( isset( $post[ $field ] ) && (string) $post[ $field ] !== (string) get_post_field( $field, $post['ID'] ) ) {
                $changed = true;
                break;
            }
        }
        $id = $changed ? wp_update_post( wp_slash( $post ), true ) : $post['ID'];
    } else {
        if ( ! $apply ) {
            $report[] = $key . ': would create';
            continue;
        }
        $post['post_content'] = $page['content'];
        $id = wp_insert_post( wp_slash( $post ), true );
    }
    if ( is_wp_error( $id ) ) { $report[] = $key . ': ' . $id->get_error_message(); continue; }
    update_post_meta( $id, '_rc_provisioning_key', $key );
    update_post_meta( $id, '_rc_provisioning_version', $version );
    if ( ! in_array( $key, array( 'faq', 'contact' ), true ) ) update_post_meta( $id, '_rc_provisioned_content', '1' );
    if ( $page['template'] ) update_post_meta( $id, '_wp_page_template', $page['template'] );
    if ( ! empty( $page['location_key'] ) ) update_post_meta( $id, '_rentacar_location_key', $page['location_key'] );
    $report[] = $key . ': ' . $id;
}

if ( function_exists( 'pll_set_post_language' ) && function_exists( 'pll_save_post_translations' ) ) {
    foreach ( $pages as $key => $page ) {
        $source = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1, 'meta_key' => '_rc_provisioning_key', 'meta_value' => $key, 'fields' => 'ids' ) );
        if ( ! $source ) continue;
        $source_id = (int) $source[0];
        if ( function_exists( 'pll_get_post' ) ) {
            $italian_id = (int) pll_get_post( $source_id, 'it' );
            if ( $italian_id ) $source_id = $italian_id;
        }
        $translations = (array) pll_get_post_translations( $source_id );
        foreach ( array( 'en', 'ro', 'ru' ) as $language ) {
            $copy = isset( $localized_pages[ $language ][ $key ] ) ? array_merge( $page, $localized_pages[ $language ][ $key ] ) : $page;
            if ( ! empty( $translations[ $language ] ) ) {
                $translation_id = (int) $translations[ $language ];
                $is_provisioned_content = '1' === (string) get_post_meta( $translation_id, '_rc_provisioned_content', true );
                if ( ! $is_provisioned_content && ! in_array( $key, array( 'faq', 'contact' ), true ) && '' !== (string) get_post_meta( $translation_id, '_rc_provisioning_version', true ) ) {
                    $is_provisioned_content = true;
                    if ( $apply ) update_post_meta( $translation_id, '_rc_provisioned_content', '1' );
                }
                if ( $apply && $is_provisioned_content && ( $copy['title'] !== get_the_title( $translation_id ) || $copy['content'] !== get_post_field( 'post_content', $translation_id ) ) ) {
            wp_update_post( wp_slash( array( 'ID' => $translation_id, 'post_title' => $copy['title'], 'post_content' => $copy['content'] ) ) );
                }
                continue;
            }
            if ( ! $apply ) { $report[] = $key . ': would create ' . $language . ' translation'; continue; }
            $id = wp_insert_post( wp_slash( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $copy['title'], 'post_name' => $copy['slug'], 'post_content' => $copy['content'] ) ), true );
            if ( is_wp_error( $id ) ) { $report[] = $key . ': ' . $language . ' ' . $id->get_error_message(); continue; }
            pll_set_post_language( $id, $language ); update_post_meta( $id, '_rc_provisioning_key', $key ); update_post_meta( $id, '_rc_provisioning_version', $version ); if ( ! in_array( $key, array( 'faq', 'contact' ), true ) ) update_post_meta( $id, '_rc_provisioned_content', '1' ); if ( $page['template'] ) update_post_meta( $id, '_wp_page_template', $page['template'] ); if ( ! empty( $page['location_key'] ) ) update_post_meta( $id, '_rentacar_location_key', $page['location_key'] ); $translations[ $language ] = $id; pll_save_post_translations( $translations ); $report[] = $key . ': created ' . $language . ' ' . $id;
        }
    }
}

if ( function_exists( 'wp_create_nav_menu' ) && function_exists( 'pll_get_post' ) ) {
    $menu_keys = array(
        'primary' => array( 'fleet', 'venice_marco_polo', 'treviso_airport', 'how_it_works', 'faq', 'guides', 'contact' ),
        'footer'  => array( 'fleet', 'how_it_works', 'rental_requirements', 'faq', 'guides', 'contact', 'terms' ),
    );
    $menu_labels = array(
        'it' => array( 'fleet' => 'Flotta', 'venice_marco_polo' => 'Aeroporto Venezia Marco Polo', 'treviso_airport' => 'Aeroporto Treviso', 'how_it_works' => 'Come funziona', 'faq' => 'FAQ', 'guides' => 'Guide', 'contact' => 'Contatti', 'rental_requirements' => 'Requisiti di noleggio', 'terms' => 'Termini e condizioni' ),
        'en' => array( 'fleet' => 'Fleet', 'venice_marco_polo' => 'Venice Marco Polo Airport', 'treviso_airport' => 'Treviso Airport', 'how_it_works' => 'How it works', 'faq' => 'FAQ', 'guides' => 'Guides', 'contact' => 'Contact', 'rental_requirements' => 'Rental requirements', 'terms' => 'Terms and Conditions' ),
        'ro' => array( 'fleet' => 'Flotă', 'venice_marco_polo' => 'Aeroportul Veneția Marco Polo', 'treviso_airport' => 'Aeroportul Treviso', 'how_it_works' => 'Cum funcționează', 'faq' => 'Întrebări frecvente', 'guides' => 'Ghiduri', 'contact' => 'Contacte', 'rental_requirements' => 'Condiții de închiriere', 'terms' => 'Termeni și condiții' ),
        'ru' => array( 'fleet' => 'Автопарк', 'venice_marco_polo' => 'Аэропорт Венеция Марко Поло', 'treviso_airport' => 'Аэропорт Тревизо', 'how_it_works' => 'Как это работает', 'faq' => 'Частые вопросы', 'guides' => 'Путеводители', 'contact' => 'Контакты', 'rental_requirements' => 'Условия аренды', 'terms' => 'Условия и положения' ),
    );
    $menu_assignments = array();
    foreach ( array( 'it', 'en', 'ro', 'ru' ) as $language ) {
        foreach ( $menu_keys as $location => $keys ) {
            $menu_name = 'Rentacar ' . ucfirst( $location ) . ' ' . strtoupper( $language );
            $menu = wp_get_nav_menu_object( $menu_name );
            if ( ! $menu && ! $apply ) { $report[] = 'would create ' . $menu_name; continue; }
            $menu_id = $menu ? (int) $menu->term_id : wp_create_nav_menu( $menu_name );
            if ( is_wp_error( $menu_id ) ) { $report[] = $menu_name . ': ' . $menu_id->get_error_message(); continue; }
            if ( function_exists( 'pll_set_term_language' ) ) pll_set_term_language( $menu_id, $language );
            $items = wp_get_nav_menu_items( $menu_id );
            if ( ! $items ) foreach ( $keys as $position => $key ) {
                $source = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1, 'meta_key' => '_rc_provisioning_key', 'meta_value' => $key, 'fields' => 'ids' ) );
                $page_id = $source ? (int) pll_get_post( $source[0], $language ) : 0;
                if ( $page_id ) wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-object-id' => $page_id, 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish', 'menu-item-position' => $position + 1, 'menu-item-title' => $menu_labels[ $language ][ $key ] ) );
            }
            foreach ( (array) $items as $position => $item ) {
                $key = isset( $keys[ $position ] ) ? $keys[ $position ] : '';
                if ( ! $key ) continue;
                $source = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1, 'meta_key' => '_rc_provisioning_key', 'meta_value' => $key, 'fields' => 'ids' ) );
                $page_id = $source ? (int) pll_get_post( $source[0], $language ) : 0;
                if ( $page_id ) wp_update_nav_menu_item( $menu_id, $item->ID, array( 'menu-item-title' => $menu_labels[ $language ][ $key ], 'menu-item-object-id' => $page_id, 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish', 'menu-item-position' => $position + 1 ) );
            }
            $menu_assignments[ $location . '___' . $language ] = $menu_id;
            if ( 'it' === $language ) $menu_assignments[ $location ] = $menu_id;
            $report[] = $menu_name . ': ' . $menu_id;
        }
    }
    if ( $apply && $menu_assignments ) {
        $locations = (array) get_theme_mod( 'nav_menu_locations', array() );
        set_theme_mod( 'nav_menu_locations', array_merge( $locations, $menu_assignments ) );
        if ( function_exists( 'PLL' ) && isset( PLL()->options ) && method_exists( PLL()->options, 'get' ) && method_exists( PLL()->options, 'set' ) ) {
            $polylang_locations = (array) PLL()->options->get( 'nav_menus' );
            $stylesheet = wp_get_theme()->get_stylesheet();
            foreach ( $menu_keys as $location => $keys ) {
                foreach ( array( 'it', 'en', 'ro', 'ru' ) as $language ) {
                    $assignment = $location . '___' . $language;
                    if ( isset( $menu_assignments[ $assignment ] ) ) $polylang_locations[ $stylesheet ][ $location ][ $language ] = (int) $menu_assignments[ $assignment ];
                }
            }
            PLL()->options->set( 'nav_menus', $polylang_locations );
        }
    }
}

if ( defined( 'WPSEO_VERSION' ) ) {
    $managed_pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => -1, 'meta_key' => '_rc_provisioning_version' ) );
    foreach ( $managed_pages as $managed_page ) {
        $current_title = (string) get_post_meta( $managed_page->ID, '_yoast_wpseo_title', true );
        $current_description = (string) get_post_meta( $managed_page->ID, '_yoast_wpseo_metadesc', true );
        $plain_content = preg_replace( '#<nav\b[^>]*>.*?</nav>#is', '', (string) $managed_page->post_content );
        $description = wp_trim_words( wp_strip_all_tags( $plain_content ), 28, '' );
        if ( $apply && '' === $current_title ) update_post_meta( $managed_page->ID, '_yoast_wpseo_title', $managed_page->post_title . ' | Rent a Car Venezia' );
        if ( $apply && '' === $current_description && '' !== $description ) update_post_meta( $managed_page->ID, '_yoast_wpseo_metadesc', $description );
    }
    $report[] = 'Yoast metadata: managed pages with empty metadata populated';
}

if ( $apply ) {
    $managed_pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => -1, 'meta_key' => '_rc_provisioned_content', 'meta_value' => '1' ) );
    foreach ( $managed_pages as $managed_page ) {
        $content = (string) $managed_page->post_content;
        $repaired = str_replace( 'Airport pickup requires a valid flight number. ', 'Airport pickup arrangements are confirmed personally. ', $content );
        if ( $repaired !== $content ) {
            wp_update_post( wp_slash( array( 'ID' => $managed_page->ID, 'post_content' => $repaired ) ) );
            $report[] = 'removed legacy flight instruction from ' . $managed_page->ID;
        }
    }
}
WP_CLI::success( ( $apply ? 'Applied: ' : 'Dry run: ' ) . implode( '; ', $report ) );
