<?php
defined( 'ABSPATH' ) || exit;

/**
 * Theme-interface translations are kept with the owned theme so the local
 * clone remains coherent before a formal WPML String Translation export is
 * approved. WordPress content, vehicles, menus and URLs remain WPML-owned.
 */
function rentacar_venezia_v2_interface_language() {
    $language = apply_filters( 'wpml_current_language', '' );

    return in_array( $language, array( 'it', 'ro', 'ru' ), true ) ? $language : '';
}

function rentacar_venezia_v2_interface_translation_map() {
    static $translations = null;

    if ( null !== $translations ) {
        return $translations;
    }

    $translations = array(
        'it' => array(
            'Car rental in Venice and Treviso' => 'Noleggio auto a Venezia e Treviso',
            'Choose a car. Send a request. We confirm personally.' => 'Scegli un’auto. Invia una richiesta. Confermiamo personalmente.',
            'Select your preferred vehicle, complete one short reservation form, and our team will check availability and contact you.' => 'Scegli il veicolo che preferisci, compila un breve modulo di prenotazione e il nostro team verificherà la disponibilità e ti contatterà.',
            'Choose your car' => 'Scegli la tua auto',
            'Service highlights' => 'Punti di forza del servizio',
            'Local assistance' => 'Assistenza locale',
            'Multilingual support' => 'Supporto multilingue',
            'Availability confirmed personally' => 'Disponibilità confermata personalmente',
            'Explore the fleet' => 'Esplora la flotta',
            'Choose your preferred car' => 'Scegli l’auto che preferisci',
            'View all cars' => 'Vedi tutte le auto',
            'Vehicles will appear here when they are ready to show.' => 'I veicoli appariranno qui quando saranno pronti per essere mostrati.',
            'How it works' => 'Come funziona',
            'One clear request, personally confirmed.' => 'Una richiesta semplice, confermata personalmente.',
            'Choose a vehicle' => 'Scegli un veicolo',
            'Browse our real fleet and select the car you prefer.' => 'Esplora la nostra flotta reale e scegli l’auto che preferisci.',
            'Send the reservation request' => 'Invia la richiesta di prenotazione',
            'Share your trip and contact details in one short form.' => 'Inserisci i dettagli del viaggio e i tuoi recapiti in un breve modulo.',
            'We contact you' => 'Ti contattiamo',
            'Our team checks availability and confirms everything with you.' => 'Il nostro team verifica la disponibilità e conferma tutto con te.',
            'Venice and Treviso' => 'Venezia e Treviso',
            'A local team for your next trip.' => 'Un team locale per il tuo prossimo viaggio.',
            'Tell us where you need the car and when. We will review your request personally and explain the available options.' => 'Dicci dove e quando ti serve l’auto. Esamineremo personalmente la tua richiesta e ti spiegheremo le opzioni disponibili.',
            'Explore the full fleet' => 'Esplora tutta la flotta',
            'Need help choosing?' => 'Hai bisogno di aiuto nella scelta?',
            'Talk to our local team.' => 'Parla con il nostro team locale.',
            'Contact us on WhatsApp' => 'Contattaci su WhatsApp',
            'Indicative prices only. Availability and final price are confirmed by our team.' => 'I prezzi sono indicativi. La disponibilità e il prezzo finale sono confermati dal nostro team.',
            'Submitting this request does not immediately confirm the reservation. We will check availability and contact you.' => 'L’invio di questa richiesta non conferma immediatamente la prenotazione. Verificheremo la disponibilità e ti contatteremo.',
            'We received your reservation request. Our team will check the selected vehicle and contact you to confirm availability, the final price and rental conditions.' => 'Abbiamo ricevuto la tua richiesta di prenotazione. Il nostro team verificherà il veicolo selezionato e ti contatterà per confermare disponibilità, prezzo finale e condizioni di noleggio.',
            'Choose your preferred vehicle. Our team checks availability and confirms the final price personally.' => 'Scegli il veicolo che preferisci. Il nostro team verifica personalmente la disponibilità e conferma il prezzo finale.',
            '%s+ days' => '%s+ giorni',
            '%1$s–%2$s days' => '%1$s–%2$s giorni',
            '€%s/day' => '€%s/giorno',
            'Availability notice' => 'Avviso sulla disponibilità',
            'We check availability personally.' => 'Verifichiamo personalmente la disponibilità.',
            'Availability of a specific model for your dates must be confirmed by our team.' => 'La disponibilità di un modello specifico per le tue date deve essere confermata dal nostro team.',
            'Indicative daily rates' => 'Tariffe giornaliere indicative',
            'Indicative daily price bands' => 'Fasce di prezzo giornaliere indicative',
            'Price to be confirmed' => 'Prezzo da confermare',
            'Reservation' => 'Prenotazione',
            'View details' => 'Vedi dettagli',
            'Vehicle image unavailable' => 'Immagine del veicolo non disponibile',
            'Our fleet' => 'La nostra flotta',
            'Cars' => 'Auto',
            'Explore the vehicle fleet and choose the option that suits your trip.' => 'Esplora la flotta e scegli l’opzione più adatta al tuo viaggio.',
            'Filter and sort cars' => 'Filtra e ordina le auto',
            'Transmission' => 'Cambio',
            'Any transmission' => 'Qualsiasi cambio',
            'Passengers' => 'Passeggeri',
            'Any capacity' => 'Qualsiasi capacità',
            'Doors' => 'Porte',
            'Any doors' => 'Qualsiasi numero di porte',
            'Air conditioning' => 'Aria condizionata',
            'Sort by' => 'Ordina per',
            'Recommended' => 'Consigliato',
            'Price: low to high' => 'Prezzo: dal più basso',
            'Price: high to low' => 'Prezzo: dal più alto',
            'Passenger capacity' => 'Capacità passeggeri',
            'Apply filters' => 'Applica filtri',
            'Clear filters' => 'Cancella filtri',
            'Reservation request' => 'Richiesta di prenotazione',
            'Selected vehicle' => 'Veicolo selezionato',
            'Rental details' => 'Dettagli del noleggio',
            'Your details' => 'I tuoi dati',
            'Pickup date' => 'Data di ritiro',
            'Pickup time' => 'Ora di ritiro',
            'Return date' => 'Data di riconsegna',
            'Return time' => 'Ora di riconsegna',
            'Pickup location' => 'Luogo di ritiro',
            'Return location' => 'Luogo di riconsegna',
            'Full name' => 'Nome e cognome',
            'Phone or WhatsApp' => 'Telefono o WhatsApp',
            'Message (optional)' => 'Messaggio (facoltativo)',
            'Privacy Policy' => 'Informativa sulla privacy',
            'Send reservation request' => 'Invia richiesta di prenotazione',
            'Request received' => 'Richiesta ricevuta',
            'Close' => 'Chiudi',
        ),
        'ro' => array(
            'Car rental in Venice and Treviso' => 'Închiriere auto în Veneția și Treviso',
            'Choose a car. Send a request. We confirm personally.' => 'Alegeți o mașină. Trimiteți o solicitare. Confirmăm personal.',
            'Select your preferred vehicle, complete one short reservation form, and our team will check availability and contact you.' => 'Alegeți vehiculul preferat, completați un formular scurt de rezervare, iar echipa noastră va verifica disponibilitatea și vă va contacta.',
            'Choose your car' => 'Alegeți mașina',
            'Service highlights' => 'Avantajele serviciului',
            'Local assistance' => 'Asistență locală',
            'Multilingual support' => 'Asistență multilingvă',
            'Availability confirmed personally' => 'Disponibilitate confirmată personal',
            'Explore the fleet' => 'Explorați flota',
            'Choose your preferred car' => 'Alegeți mașina preferată',
            'View all cars' => 'Vezi toate mașinile',
            'Vehicles will appear here when they are ready to show.' => 'Vehiculele vor apărea aici când sunt pregătite pentru afișare.',
            'How it works' => 'Cum funcționează',
            'One clear request, personally confirmed.' => 'O solicitare simplă, confirmată personal.',
            'Choose a vehicle' => 'Alegeți un vehicul',
            'Browse our real fleet and select the car you prefer.' => 'Explorați flota noastră reală și alegeți mașina preferată.',
            'Send the reservation request' => 'Trimiteți cererea de rezervare',
            'Share your trip and contact details in one short form.' => 'Introduceți detaliile călătoriei și datele de contact într-un formular scurt.',
            'We contact you' => 'Vă contactăm',
            'Our team checks availability and confirms everything with you.' => 'Echipa noastră verifică disponibilitatea și confirmă toate detaliile cu dumneavoastră.',
            'A local team for your next trip.' => 'O echipă locală pentru următoarea călătorie.',
            'Venice and Treviso' => 'Veneția și Treviso',
            'Tell us where you need the car and when. We will review your request personally and explain the available options.' => 'Spuneți-ne unde și când aveți nevoie de mașină. Vom analiza personal solicitarea și vă vom explica opțiunile disponibile.',
            'Explore the full fleet' => 'Explorați întreaga flotă',
            'Need help choosing?' => 'Aveți nevoie de ajutor pentru alegere?',
            'Talk to our local team.' => 'Discutați cu echipa noastră locală.',
            'Contact us on WhatsApp' => 'Contactați-ne pe WhatsApp',
            'Availability notice' => 'Notificare privind disponibilitatea',
            'We check availability personally.' => 'Verificăm disponibilitatea personal.',
            'Availability of a specific model for your dates must be confirmed by our team.' => 'Disponibilitatea unui model pentru datele alese trebuie confirmată de echipa noastră.',
            'Indicative daily rates' => 'Tarife zilnice orientative',
            'Indicative daily price bands' => 'Intervale de preț zilnice orientative',
            'Price to be confirmed' => 'Preț de confirmat',
            '%s+ days' => '%s+ zile',
            '%1$s–%2$s days' => '%1$s–%2$s zile',
            '€%s/day' => '€%s/zi',
            'Reservation' => 'Rezervare',
            'View details' => 'Vezi detalii',
            'Vehicle image unavailable' => 'Imaginea vehiculului nu este disponibilă',
            'Our fleet' => 'Flota noastră',
            'Cars' => 'Mașini',
            'Transmission' => 'Transmisie',
            'Passengers' => 'Pasageri',
            'Doors' => 'Uși',
            'Air conditioning' => 'Aer condiționat',
            'Any transmission' => 'Orice transmisie',
            'Any capacity' => 'Orice capacitate',
            'Any doors' => 'Orice număr de uși',
            'Sort by' => 'Sortează după',
            'Recommended' => 'Recomandat',
            'Price: low to high' => 'Preț: crescător',
            'Price: high to low' => 'Preț: descrescător',
            'Apply filters' => 'Aplică filtrele',
            'Clear filters' => 'Resetează filtrele',
            'Reservation request' => 'Cerere de rezervare',
            'Selected vehicle' => 'Vehicul selectat',
            'Rental details' => 'Detaliile închirierii',
            'Your details' => 'Datele dumneavoastră',
            'Pickup date' => 'Data preluării',
            'Pickup time' => 'Ora preluării',
            'Return date' => 'Data returnării',
            'Return time' => 'Ora returnării',
            'Pickup location' => 'Locul preluării',
            'Return location' => 'Locul returnării',
            'Full name' => 'Nume complet',
            'Phone or WhatsApp' => 'Telefon sau WhatsApp',
            'Message (optional)' => 'Mesaj (opțional)',
            'Send reservation request' => 'Trimiteți cererea de rezervare',
            'Request received' => 'Cerere primită',
            'Close' => 'Închide',
            'Indicative prices only. Availability and final price are confirmed by our team.' => 'Prețurile sunt orientative. Disponibilitatea și prețul final sunt confirmate de echipa noastră.',
            'Submitting this request does not immediately confirm the reservation. We will check availability and contact you.' => 'Trimiterea acestei solicitări nu confirmă imediat rezervarea. Vom verifica disponibilitatea și vă vom contacta.',
            'We received your reservation request. Our team will check the selected vehicle and contact you to confirm availability, the final price and rental conditions.' => 'Am primit cererea dumneavoastră de rezervare. Echipa noastră va verifica vehiculul selectat și vă va contacta pentru confirmarea disponibilității, a prețului final și a condițiilor de închiriere.',
            'Choose your preferred vehicle. Our team checks availability and confirms the final price personally.' => 'Alegeți vehiculul preferat. Echipa noastră verifică disponibilitatea și confirmă personal prețul final.',
        ),
        'ru' => array(
            'Car rental in Venice and Treviso' => 'Аренда автомобилей в Венеции и Тревизо',
            'Choose a car. Send a request. We confirm personally.' => 'Выберите автомобиль. Отправьте запрос. Мы подтвердим лично.',
            'Select your preferred vehicle, complete one short reservation form, and our team will check availability and contact you.' => 'Выберите подходящий автомобиль, заполните короткую форму запроса, и наша команда проверит доступность и свяжется с вами.',
            'Choose your car' => 'Выберите автомобиль',
            'Service highlights' => 'Преимущества сервиса',
            'Local assistance' => 'Местная поддержка',
            'Multilingual support' => 'Многоязычная поддержка',
            'Availability confirmed personally' => 'Доступность подтверждается лично',
            'Explore the fleet' => 'Посмотрите автопарк',
            'Choose your preferred car' => 'Выберите подходящий автомобиль',
            'View all cars' => 'Все автомобили',
            'Vehicles will appear here when they are ready to show.' => 'Автомобили появятся здесь, когда будут готовы к показу.',
            'How it works' => 'Как это работает',
            'One clear request, personally confirmed.' => 'Один понятный запрос — личное подтверждение.',
            'Choose a vehicle' => 'Выберите автомобиль',
            'Browse our real fleet and select the car you prefer.' => 'Посмотрите наш реальный автопарк и выберите автомобиль.',
            'Send the reservation request' => 'Отправьте запрос на бронирование',
            'Share your trip and contact details in one short form.' => 'Укажите детали поездки и контакты в короткой форме.',
            'We contact you' => 'Мы свяжемся с вами',
            'Our team checks availability and confirms everything with you.' => 'Наша команда проверит доступность и согласует с вами все детали.',
            'A local team for your next trip.' => 'Местная команда для вашей следующей поездки.',
            'Venice and Treviso' => 'Венеция и Тревизо',
            'Tell us where you need the car and when. We will review your request personally and explain the available options.' => 'Расскажите, где и когда вам нужен автомобиль. Мы лично рассмотрим запрос и объясним доступные варианты.',
            'Explore the full fleet' => 'Посмотреть весь автопарк',
            'Need help choosing?' => 'Нужна помощь с выбором?',
            'Talk to our local team.' => 'Свяжитесь с нашей местной командой.',
            'Contact us on WhatsApp' => 'Напишите нам в WhatsApp',
            'Availability notice' => 'Уведомление о доступности',
            'We check availability personally.' => 'Мы проверяем доступность лично.',
            'Availability of a specific model for your dates must be confirmed by our team.' => 'Доступность конкретной модели на выбранные даты должна быть подтверждена нашей командой.',
            'Indicative daily rates' => 'Ориентировочные дневные тарифы',
            'Indicative daily price bands' => 'Ориентировочные диапазоны дневных тарифов',
            'Price to be confirmed' => 'Цена уточняется',
            '%s+ days' => 'от %s дней',
            '%1$s–%2$s days' => '%1$s–%2$s дней',
            '€%s/day' => '€%s/день',
            'Reservation' => 'Бронирование',
            'View details' => 'Подробнее',
            'Vehicle image unavailable' => 'Изображение автомобиля недоступно',
            'Our fleet' => 'Наш автопарк',
            'Cars' => 'Автомобили',
            'Transmission' => 'Коробка передач',
            'Passengers' => 'Пассажиры',
            'Doors' => 'Двери',
            'Air conditioning' => 'Кондиционер',
            'Any transmission' => 'Любая коробка передач',
            'Any capacity' => 'Любая вместимость',
            'Any doors' => 'Любое количество дверей',
            'Sort by' => 'Сортировать',
            'Recommended' => 'Рекомендуемое',
            'Price: low to high' => 'Цена: по возрастанию',
            'Price: high to low' => 'Цена: по убыванию',
            'Apply filters' => 'Применить фильтры',
            'Clear filters' => 'Сбросить фильтры',
            'Reservation request' => 'Запрос на бронирование',
            'Selected vehicle' => 'Выбранный автомобиль',
            'Rental details' => 'Детали аренды',
            'Your details' => 'Ваши данные',
            'Pickup date' => 'Дата получения',
            'Pickup time' => 'Время получения',
            'Return date' => 'Дата возврата',
            'Return time' => 'Время возврата',
            'Pickup location' => 'Место получения',
            'Return location' => 'Место возврата',
            'Full name' => 'Полное имя',
            'Phone or WhatsApp' => 'Телефон или WhatsApp',
            'Message (optional)' => 'Сообщение (необязательно)',
            'Send reservation request' => 'Отправить запрос на бронирование',
            'Request received' => 'Запрос получен',
            'Close' => 'Закрыть',
            'Indicative prices only. Availability and final price are confirmed by our team.' => 'Цены ориентировочные. Доступность и окончательная цена подтверждаются нашей командой.',
            'Submitting this request does not immediately confirm the reservation. We will check availability and contact you.' => 'Отправка запроса не подтверждает бронирование автоматически. Мы проверим доступность и свяжемся с вами.',
            'We received your reservation request. Our team will check the selected vehicle and contact you to confirm availability, the final price and rental conditions.' => 'Мы получили ваш запрос на бронирование. Наша команда проверит выбранный автомобиль и свяжется с вами, чтобы подтвердить доступность, окончательную цену и условия аренды.',
            'Choose your preferred vehicle. Our team checks availability and confirms the final price personally.' => 'Выберите подходящий автомобиль. Наша команда лично проверит доступность и подтвердит окончательную цену.',
        ),
    );

    return $translations;
}

/**
 * Vehicle gearbox values are legacy ACF/meta values, not theme strings. Keep
 * their stored form unchanged for queries while presenting a localised label.
 */
function rentacar_venezia_v2_vehicle_transmission_label( $value ) {
    $value = sanitize_text_field( $value );
    $language = rentacar_venezia_v2_interface_language();
    $labels = array(
        'it' => array( 'Automatic' => 'Automatico', 'Manual' => 'Manuale', 'Direct-shift gearbox' => 'Cambio a doppia frizione', 'SMG' => 'SMG' ),
        'ro' => array( 'Automatic' => 'Automată', 'Manual' => 'Manuală', 'Direct-shift gearbox' => 'Cutie cu dublu ambreiaj', 'SMG' => 'SMG' ),
        'ru' => array( 'Automatic' => 'Автоматическая', 'Manual' => 'Механическая', 'Direct-shift gearbox' => 'Роботизированная коробка', 'SMG' => 'SMG' ),
    );

    return $language && isset( $labels[ $language ][ $value ] ) ? $labels[ $language ][ $value ] : $value;
}

function rentacar_venezia_v2_interface_gettext( $translated, $text, $domain ) {
    if ( 'rentacar-venezia-v2' !== $domain ) {
        return $translated;
    }

    $language = rentacar_venezia_v2_interface_language();
    $translations = rentacar_venezia_v2_interface_translation_map();

    return $language && isset( $translations[ $language ][ $text ] ) ? $translations[ $language ][ $text ] : $translated;
}
add_filter( 'gettext', 'rentacar_venezia_v2_interface_gettext', 20, 3 );

function rentacar_venezia_v2_interface_ngettext( $translated, $single, $plural, $number, $domain ) {
    if ( 'rentacar-venezia-v2' !== $domain ) {
        return $translated;
    }

    $language = rentacar_venezia_v2_interface_language();
    $forms = array(
        'it' => array( '%s passenger' => '%s passeggero', '%s passengers' => '%s passeggeri', '%s door' => '%s porta', '%s doors' => '%s porte' ),
        'ro' => array( '%s passenger' => '%s pasager', '%s passengers' => '%s pasageri', '%s door' => '%s ușă', '%s doors' => '%s uși' ),
        'ru' => array( '%s passenger' => '%s пассажир', '%s passengers' => '%s пассажиров', '%s door' => '%s дверь', '%s doors' => '%s дверей' ),
    );
    $source = 1 === (int) $number ? $single : $plural;

    return $language && isset( $forms[ $language ][ $source ] ) ? $forms[ $language ][ $source ] : $translated;
}
add_filter( 'ngettext', 'rentacar_venezia_v2_interface_ngettext', 20, 5 );
