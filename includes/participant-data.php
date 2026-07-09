<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../database/connection.php';

function participant_current_user_id()
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function participant_user_has_private_event_access($event_id)
{
    $event_id = (int) $event_id;

    return $event_id > 0 && !empty($_SESSION['private_event_access'][$event_id]);
}

function participant_flash($type, $message)
{
    $_SESSION['participant_' . $type] = $message;
}

function participant_get_flash($type)
{
    $key = 'participant_' . $type;
    $message = $_SESSION[$key] ?? '';
    unset($_SESSION[$key]);

    return $message;
}

function participant_redirect_back($fallback = 'events.php')
{
    $redirect_url = $_SERVER['REQUEST_URI'] ?? $fallback;
    header('Location: ' . $redirect_url);
    exit;
}

function participant_format_event_date_time($date, $time)
{
    $event_timestamp = strtotime($date . ' ' . $time);

    if (!$event_timestamp) {
        return 'Event date';
    }

    return strtoupper(date('D, M j', $event_timestamp)) . ' - ' . strtoupper(date('gA', $event_timestamp));
}

function participant_format_date($date)
{
    $timestamp = strtotime($date);

    return $timestamp ? date('m/d/Y', $timestamp) : 'N/A';
}

function participant_format_time($time)
{
    $timestamp = strtotime($time);

    return $timestamp ? date('g:i A', $timestamp) : 'N/A';
}

function participant_time_filter($date)
{
    $event_timestamp = strtotime($date);

    if (!$event_timestamp) {
        return 'all';
    }

    if (date('Y-m-d', $event_timestamp) === date('Y-m-d')) {
        return 'today';
    }

    $day_of_week = (int) date('N', $event_timestamp);

    return $day_of_week >= 6 ? 'weekend' : 'all';
}

function participant_event_is_available($status)
{
    return in_array(strtolower($status), ['open', 'published', 'approved', 'active'], true);
}

function participant_event_publish_is_active($event)
{
    $publish_date = trim((string) ($event['publish_date'] ?? ''));

    if ($publish_date === '') {
        return true;
    }

    $publish_time = trim((string) ($event['publish_time'] ?? '00:00:00'));
    $publish_timestamp = strtotime($publish_date . ' ' . ($publish_time !== '' ? $publish_time : '00:00:00'));

    return $publish_timestamp ? $publish_timestamp <= time() : true;
}

function participant_event_has_ended($event)
{
    $event_date = trim((string) ($event['event_date'] ?? ''));

    if ($event_date === '') {
        return false;
    }

    $end_time = trim((string) ($event['event_end_time'] ?? ''));
    $start_time = trim((string) ($event['event_time'] ?? '23:59:59'));
    $event_time = $end_time !== '' ? $end_time : $start_time;
    $event_timestamp = strtotime($event_date . ' ' . $event_time);

    return $event_timestamp ? $event_timestamp < time() : false;
}

function participant_user_can_view_event($event, $user_id)
{
    $user_id = (int) $user_id;
    $is_owner = $user_id > 0 && (int) ($event['created_by'] ?? 0) === $user_id;
    $registration_status = strtolower(trim((string) ($event['current_user_registration_status'] ?? '')));

    if ($is_owner) {
        return true;
    }

    if (!participant_event_is_available($event['status'] ?? '')) {
        return $registration_status === 'registered';
    }

    if (!participant_event_publish_is_active($event)) {
        return false;
    }

    if (strtolower(trim((string) ($event['visibility'] ?? 'public'))) === 'private') {
        return $registration_status === 'registered'
            || participant_user_has_private_event_access((int) ($event['event_id'] ?? 0));
    }

    return true;
}

function participant_event_registration_is_open($event, $user_id)
{
    return participant_event_is_available($event['status'] ?? '')
        && participant_event_publish_is_active($event)
        && !participant_event_has_ended($event)
        && participant_user_can_view_event($event, $user_id);
}

function participant_attendance_code_exists($conn, $attendance_code)
{
    $sql = 'SELECT registration_id FROM registrations WHERE attendance_code = ? LIMIT 1';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return true;
    }

    mysqli_stmt_bind_param($stmt, 's', $attendance_code);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    return $exists;
}

function participant_generate_attendance_code($conn)
{
    $year = date('Y');

    for ($attempt = 0; $attempt < 10; $attempt++) {
        try {
            $random_part = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        } catch (Exception $exception) {
            $random_part = strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 6));
        }

        $attendance_code = 'SHNV-' . $year . '-' . $random_part;

        if (!participant_attendance_code_exists($conn, $attendance_code)) {
            return $attendance_code;
        }
    }

    return 'SHNV-' . $year . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
}

function participant_private_access_key_exists($conn, $private_access_key)
{
    $sql = 'SELECT event_id FROM events WHERE private_access_key = ? LIMIT 1';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return true;
    }

    mysqli_stmt_bind_param($stmt, 's', $private_access_key);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    return $exists;
}

function participant_generate_private_access_key($conn)
{
    for ($attempt = 0; $attempt < 10; $attempt++) {
        try {
            $random_part = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        } catch (Exception $exception) {
            $random_part = strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 6));
        }

        $private_access_key = 'PRIVATE-SHNV-' . $random_part;

        if (!participant_private_access_key_exists($conn, $private_access_key)) {
            return $private_access_key;
        }
    }

    return 'PRIVATE-SHNV-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
}

function participant_registration_success_message($event, $attendance_code, $is_restored = false)
{
    $message = $is_restored ? 'Registration restored successfully.' : 'Registration successful.';
    $message .= ' Your attendance code is ' . $attendance_code . '.';
    $event_type = strtolower(trim((string) ($event['event_type'] ?? '')));
    $online_link = trim((string) ($event['online_link'] ?? ''));
    $location = trim((string) ($event['event_location'] ?? ''));

    if ($event_type === 'online' && $online_link !== '') {
        $message .= ' Meeting link: ' . $online_link . '.';
    } elseif ($location !== '') {
        $message .= ' Venue information: ' . $location . '.';
    }

    $message .= ' Your ticket has been added to your Tickets page.';

    return $message;
}

function participant_event_location_type($location)
{
    $location_text = strtolower($location);
    $online_keywords = ['online', 'virtual', 'zoom', 'meet', 'session', 'studio', 'webinar', 'link', 'room'];

    foreach ($online_keywords as $keyword) {
        if (strpos($location_text, $keyword) !== false) {
            return 'online';
        }
    }

    return 'physical';
}

function participant_event_select_fields($alias = 'e')
{
    $prefix = $alias . '.';

    return $prefix . 'event_id, ' . $prefix . 'event_title, ' . $prefix . 'event_summary, '
        . $prefix . 'event_description, ' . $prefix . 'event_tags, ' . $prefix . 'event_category, '
        . $prefix . 'event_type, ' . $prefix . 'event_location, ' . $prefix . 'event_country, '
        . $prefix . 'event_province, ' . $prefix . 'event_city, ' . $prefix . 'event_address, '
        . $prefix . 'event_venue, ' . $prefix . 'online_link, ' . $prefix . 'online_platform, '
        . $prefix . 'event_date, ' . $prefix . 'event_time, ' . $prefix . 'event_end_time, '
        . $prefix . 'capacity, ' . $prefix . 'banner_image, ' . $prefix . 'visibility, '
        . $prefix . 'audience, ' . $prefix . 'private_access_key, ' . $prefix . 'publish_date, ' . $prefix . 'publish_time, '
        . $prefix . 'status, ' . $prefix . 'created_by, ' . $prefix . 'created_at';
}

function participant_event_banner_src($event, $base_path = '../')
{
    $banner_image = trim((string) ($event['banner_image'] ?? ''));

    if ($banner_image === '') {
        return '';
    }

    return $base_path . ltrim($banner_image, '/');
}

function participant_country_city_options_map()
{
    return [
        'Afghanistan' => ['Kabul', 'Kandahar', 'Herat', 'Mazar-i-Sharif', 'Jalalabad'],
        'Albania' => ['Tirana', 'Durres', 'Vlore', 'Shkoder', 'Fier'],
        'Algeria' => ['Algiers', 'Oran', 'Constantine', 'Annaba', 'Blida'],
        'Andorra' => ['Andorra la Vella', 'Escaldes-Engordany', 'Encamp', 'La Massana', 'Sant Julia de Loria'],
        'Angola' => ['Luanda', 'Huambo', 'Lobito', 'Benguela', 'Lubango'],
        'Antigua and Barbuda' => ['St. Johns', 'All Saints', 'Liberta', 'Bolans', 'Piggotts'],
        'Argentina' => ['Buenos Aires', 'Cordoba', 'Rosario', 'Mendoza', 'La Plata'],
        'Armenia' => ['Yerevan', 'Gyumri', 'Vanadzor', 'Vagharshapat', 'Abovyan'],
        'Australia' => ['Canberra', 'Sydney', 'Melbourne', 'Brisbane', 'Perth'],
        'Austria' => ['Vienna', 'Graz', 'Linz', 'Salzburg', 'Innsbruck'],
        'Azerbaijan' => ['Baku', 'Ganja', 'Sumqayit', 'Mingachevir', 'Lankaran'],
        'Bahamas' => ['Nassau', 'Freeport', 'West End', 'Coopers Town', 'Marsh Harbour'],
        'Bahrain' => ['Manama', 'Riffa', 'Muharraq', 'Hamad Town', 'Isa Town'],
        'Bangladesh' => ['Dhaka', 'Chittagong', 'Khulna', 'Rajshahi', 'Sylhet'],
        'Barbados' => ['Bridgetown', 'Speightstown', 'Oistins', 'Holetown', 'Bathsheba'],
        'Belarus' => ['Minsk', 'Gomel', 'Mogilev', 'Vitebsk', 'Grodno'],
        'Belgium' => ['Brussels', 'Antwerp', 'Ghent', 'Charleroi', 'Liege'],
        'Belize' => ['Belmopan', 'Belize City', 'San Ignacio', 'Orange Walk Town', 'Dangriga'],
        'Benin' => ['Porto-Novo', 'Cotonou', 'Parakou', 'Djougou', 'Bohicon'],
        'Bhutan' => ['Thimphu', 'Phuntsholing', 'Paro', 'Punakha', 'Gelephu'],
        'Bolivia' => ['Sucre', 'La Paz', 'Santa Cruz de la Sierra', 'Cochabamba', 'Oruro'],
        'Bosnia and Herzegovina' => ['Sarajevo', 'Banja Luka', 'Tuzla', 'Zenica', 'Mostar'],
        'Botswana' => ['Gaborone', 'Francistown', 'Molepolole', 'Maun', 'Serowe'],
        'Brazil' => ['Brasilia', 'Sao Paulo', 'Rio de Janeiro', 'Salvador', 'Fortaleza'],
        'Brunei' => ['Bandar Seri Begawan', 'Kuala Belait', 'Seria', 'Tutong', 'Muara'],
        'Bulgaria' => ['Sofia', 'Plovdiv', 'Varna', 'Burgas', 'Ruse'],
        'Burkina Faso' => ['Ouagadougou', 'Bobo-Dioulasso', 'Koudougou', 'Banfora', 'Ouahigouya'],
        'Burundi' => ['Gitega', 'Bujumbura', 'Ngozi', 'Rumonge', 'Muyinga'],
        'Cabo Verde' => ['Praia', 'Mindelo', 'Santa Maria', 'Assomada', 'Espargos'],
        'Cambodia' => ['Phnom Penh', 'Siem Reap', 'Battambang', 'Sihanoukville', 'Kampong Cham'],
        'Cameroon' => ['Yaounde', 'Douala', 'Bamenda', 'Bafoussam', 'Garoua'],
        'Canada' => ['Ottawa', 'Toronto', 'Vancouver', 'Montreal', 'Calgary'],
        'Central African Republic' => ['Bangui', 'Bimbo', 'Berberati', 'Carnot', 'Bambari'],
        'Chad' => ['NDjamena', 'Moundou', 'Sarh', 'Abeche', 'Kelo'],
        'Chile' => ['Santiago', 'Valparaiso', 'Concepcion', 'La Serena', 'Antofagasta'],
        'China' => ['Beijing', 'Shanghai', 'Guangzhou', 'Shenzhen', 'Chengdu'],
        'Colombia' => ['Bogota', 'Medellin', 'Cali', 'Barranquilla', 'Cartagena'],
        'Comoros' => ['Moroni', 'Mutsamudu', 'Fomboni', 'Domoni', 'Mitsamiouli'],
        'Congo' => ['Brazzaville', 'Pointe-Noire', 'Dolisie', 'Nkayi', 'Owando'],
        'Costa Rica' => ['San Jose', 'Alajuela', 'Cartago', 'Heredia', 'Puntarenas'],
        'Cote d Ivoire' => ['Yamoussoukro', 'Abidjan', 'Bouake', 'Daloa', 'San Pedro'],
        'Croatia' => ['Zagreb', 'Split', 'Rijeka', 'Osijek', 'Zadar'],
        'Cuba' => ['Havana', 'Santiago de Cuba', 'Camaguey', 'Holguin', 'Santa Clara'],
        'Cyprus' => ['Nicosia', 'Limassol', 'Larnaca', 'Paphos', 'Famagusta'],
        'Czechia' => ['Prague', 'Brno', 'Ostrava', 'Plzen', 'Liberec'],
        'Democratic Republic of the Congo' => ['Kinshasa', 'Lubumbashi', 'Mbuji-Mayi', 'Kisangani', 'Goma'],
        'Denmark' => ['Copenhagen', 'Aarhus', 'Odense', 'Aalborg', 'Esbjerg'],
        'Djibouti' => ['Djibouti', 'Ali Sabieh', 'Tadjoura', 'Dikhil', 'Obock'],
        'Dominica' => ['Roseau', 'Portsmouth', 'Marigot', 'Mahaut', 'Grand Bay'],
        'Dominican Republic' => ['Santo Domingo', 'Santiago de los Caballeros', 'La Romana', 'San Pedro de Macoris', 'Puerto Plata'],
        'Ecuador' => ['Quito', 'Guayaquil', 'Cuenca', 'Santo Domingo', 'Machala'],
        'Egypt' => ['Cairo', 'Alexandria', 'Giza', 'Shubra El Kheima', 'Port Said'],
        'El Salvador' => ['San Salvador', 'Santa Ana', 'San Miguel', 'Soyapango', 'Mejicanos'],
        'Equatorial Guinea' => ['Malabo', 'Bata', 'Ebebiyin', 'Mongomo', 'Luba'],
        'Eritrea' => ['Asmara', 'Keren', 'Massawa', 'Assab', 'Mendefera'],
        'Estonia' => ['Tallinn', 'Tartu', 'Narva', 'Parnu', 'Kohtla-Jarve'],
        'Eswatini' => ['Mbabane', 'Manzini', 'Lobamba', 'Siteki', 'Nhlangano'],
        'Ethiopia' => ['Addis Ababa', 'Dire Dawa', 'Mekelle', 'Gondar', 'Bahir Dar'],
        'Fiji' => ['Suva', 'Nadi', 'Lautoka', 'Labasa', 'Ba'],
        'Finland' => ['Helsinki', 'Espoo', 'Tampere', 'Vantaa', 'Turku'],
        'France' => ['Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice'],
        'Gabon' => ['Libreville', 'Port-Gentil', 'Franceville', 'Oyem', 'Moanda'],
        'Gambia' => ['Banjul', 'Serekunda', 'Brikama', 'Bakau', 'Farafenni'],
        'Georgia' => ['Tbilisi', 'Batumi', 'Kutaisi', 'Rustavi', 'Gori'],
        'Germany' => ['Berlin', 'Munich', 'Hamburg', 'Frankfurt', 'Cologne'],
        'Ghana' => ['Accra', 'Kumasi', 'Tamale', 'Sekondi-Takoradi', 'Tema'],
        'Greece' => ['Athens', 'Thessaloniki', 'Patras', 'Heraklion', 'Larissa'],
        'Grenada' => ['St. Georges', 'Gouyave', 'Grenville', 'Sauteurs', 'Victoria'],
        'Guatemala' => ['Guatemala City', 'Quetzaltenango', 'Escuintla', 'Mixco', 'Villa Nueva'],
        'Guinea' => ['Conakry', 'Kankan', 'Nzerekore', 'Kindia', 'Labe'],
        'Guinea-Bissau' => ['Bissau', 'Bafata', 'Gabu', 'Cacheu', 'Bolama'],
        'Guyana' => ['Georgetown', 'Linden', 'New Amsterdam', 'Anna Regina', 'Bartica'],
        'Haiti' => ['Port-au-Prince', 'Cap-Haitien', 'Les Cayes', 'Gonaives', 'Jacmel'],
        'Honduras' => ['Tegucigalpa', 'San Pedro Sula', 'Choloma', 'La Ceiba', 'El Progreso'],
        'Hungary' => ['Budapest', 'Debrecen', 'Szeged', 'Miskolc', 'Pecs'],
        'Iceland' => ['Reykjavik', 'Kopavogur', 'Hafnarfjordur', 'Akureyri', 'Reykjanesbaer'],
        'India' => ['New Delhi', 'Mumbai', 'Bengaluru', 'Chennai', 'Hyderabad'],
        'Indonesia' => ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang'],
        'Iran' => ['Tehran', 'Mashhad', 'Isfahan', 'Shiraz', 'Tabriz'],
        'Iraq' => ['Baghdad', 'Basra', 'Mosul', 'Erbil', 'Najaf'],
        'Ireland' => ['Dublin', 'Cork', 'Limerick', 'Galway', 'Waterford'],
        'Israel' => ['Jerusalem', 'Tel Aviv', 'Haifa', 'Rishon LeZion', 'Petah Tikva'],
        'Italy' => ['Rome', 'Milan', 'Naples', 'Turin', 'Florence'],
        'Jamaica' => ['Kingston', 'Montego Bay', 'Spanish Town', 'Portmore', 'Mandeville'],
        'Japan' => ['Tokyo', 'Osaka', 'Kyoto', 'Yokohama', 'Nagoya'],
        'Jordan' => ['Amman', 'Zarqa', 'Irbid', 'Aqaba', 'Madaba'],
        'Kazakhstan' => ['Astana', 'Almaty', 'Shymkent', 'Karaganda', 'Aktobe'],
        'Kenya' => ['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret'],
        'Kiribati' => ['South Tarawa', 'Betio', 'Bikenibeu', 'Bairiki', 'Teaoraereke'],
        'Kuwait' => ['Kuwait City', 'Hawalli', 'Salmiya', 'Farwaniya', 'Jahra'],
        'Kyrgyzstan' => ['Bishkek', 'Osh', 'Jalal-Abad', 'Karakol', 'Tokmok'],
        'Laos' => ['Vientiane', 'Luang Prabang', 'Savannakhet', 'Pakse', 'Thakhek'],
        'Latvia' => ['Riga', 'Daugavpils', 'Liepaja', 'Jelgava', 'Jurmala'],
        'Lebanon' => ['Beirut', 'Tripoli', 'Sidon', 'Tyre', 'Zahle'],
        'Lesotho' => ['Maseru', 'Teyateyaneng', 'Mafeteng', 'Leribe', 'Mohales Hoek'],
        'Liberia' => ['Monrovia', 'Gbarnga', 'Buchanan', 'Ganta', 'Kakata'],
        'Libya' => ['Tripoli', 'Benghazi', 'Misrata', 'Zawiya', 'Sabha'],
        'Liechtenstein' => ['Vaduz', 'Schaan', 'Triesen', 'Balzers', 'Eschen'],
        'Lithuania' => ['Vilnius', 'Kaunas', 'Klaipeda', 'Siauliai', 'Panevezys'],
        'Luxembourg' => ['Luxembourg City', 'Esch-sur-Alzette', 'Differdange', 'Dudelange', 'Ettelbruck'],
        'Madagascar' => ['Antananarivo', 'Toamasina', 'Antsirabe', 'Fianarantsoa', 'Mahajanga'],
        'Malawi' => ['Lilongwe', 'Blantyre', 'Mzuzu', 'Zomba', 'Kasungu'],
        'Malaysia' => ['Kuala Lumpur', 'George Town', 'Johor Bahru', 'Ipoh', 'Kota Kinabalu'],
        'Maldives' => ['Male', 'Addu City', 'Fuvahmulah', 'Kulhudhuffushi', 'Thinadhoo'],
        'Mali' => ['Bamako', 'Sikasso', 'Segou', 'Mopti', 'Kayes'],
        'Malta' => ['Valletta', 'Birkirkara', 'Sliema', 'Mosta', 'Qormi'],
        'Marshall Islands' => ['Majuro', 'Ebeye', 'Laura', 'Jaluit', 'Wotje'],
        'Mauritania' => ['Nouakchott', 'Nouadhibou', 'Kiffa', 'Kaedi', 'Rosso'],
        'Mauritius' => ['Port Louis', 'Beau Bassin-Rose Hill', 'Vacoas-Phoenix', 'Curepipe', 'Quatre Bornes'],
        'Mexico' => ['Mexico City', 'Guadalajara', 'Monterrey', 'Puebla', 'Tijuana'],
        'Micronesia' => ['Palikir', 'Weno', 'Kolonia', 'Tofol', 'Yap'],
        'Moldova' => ['Chisinau', 'Balti', 'Tiraspol', 'Bender', 'Cahul'],
        'Monaco' => ['Monaco', 'Monte Carlo', 'La Condamine', 'Fontvieille', 'Moneghetti'],
        'Mongolia' => ['Ulaanbaatar', 'Erdenet', 'Darkhan', 'Choibalsan', 'Moron'],
        'Montenegro' => ['Podgorica', 'Niksic', 'Herceg Novi', 'Pljevlja', 'Bar'],
        'Morocco' => ['Rabat', 'Casablanca', 'Marrakesh', 'Fes', 'Tangier'],
        'Mozambique' => ['Maputo', 'Matola', 'Beira', 'Nampula', 'Chimoio'],
        'Myanmar' => ['Naypyidaw', 'Yangon', 'Mandalay', 'Mawlamyine', 'Bago'],
        'Namibia' => ['Windhoek', 'Walvis Bay', 'Swakopmund', 'Rundu', 'Oshakati'],
        'Nauru' => ['Yaren', 'Aiwo', 'Anabar', 'Meneng', 'Denigomodu'],
        'Nepal' => ['Kathmandu', 'Pokhara', 'Lalitpur', 'Biratnagar', 'Bharatpur'],
        'Netherlands' => ['Amsterdam', 'Rotterdam', 'The Hague', 'Utrecht', 'Eindhoven'],
        'New Zealand' => ['Wellington', 'Auckland', 'Christchurch', 'Hamilton', 'Dunedin'],
        'Nicaragua' => ['Managua', 'Leon', 'Masaya', 'Granada', 'Chinandega'],
        'Niger' => ['Niamey', 'Zinder', 'Maradi', 'Agadez', 'Tahoua'],
        'Nigeria' => ['Abuja', 'Lagos', 'Kano', 'Ibadan', 'Port Harcourt'],
        'North Korea' => ['Pyongyang', 'Hamhung', 'Chongjin', 'Nampo', 'Wonsan'],
        'North Macedonia' => ['Skopje', 'Bitola', 'Kumanovo', 'Prilep', 'Tetovo'],
        'Norway' => ['Oslo', 'Bergen', 'Trondheim', 'Stavanger', 'Drammen'],
        'Oman' => ['Muscat', 'Salalah', 'Sohar', 'Nizwa', 'Sur'],
        'Pakistan' => ['Islamabad', 'Karachi', 'Lahore', 'Faisalabad', 'Rawalpindi'],
        'Palau' => ['Ngerulmud', 'Koror', 'Airai', 'Melekeok', 'Angaur'],
        'Palestine' => ['Ramallah', 'Gaza City', 'Hebron', 'Nablus', 'Bethlehem'],
        'Panama' => ['Panama City', 'San Miguelito', 'David', 'Colon', 'Santiago'],
        'Papua New Guinea' => ['Port Moresby', 'Lae', 'Mount Hagen', 'Madang', 'Goroka'],
        'Paraguay' => ['Asuncion', 'Ciudad del Este', 'San Lorenzo', 'Luque', 'Capiata'],
        'Peru' => ['Lima', 'Arequipa', 'Trujillo', 'Chiclayo', 'Cusco'],
        'Philippines' => [
            'Manila',
            'Quezon City',
            'Makati',
            'Pasig',
            'Taguig',
            'Mandaluyong',
            'Caloocan',
            'Pasay',
            'Paranaque',
            'Las Pinas',
            'Muntinlupa',
            'Marikina',
            'Valenzuela',
            'San Juan',
            'Cebu City',
            'Davao City',
            'Baguio',
            'Iloilo City',
            'Bacolod',
            'Cagayan de Oro',
            'General Santos',
            'Zamboanga City',
            'Angeles',
            'Lipa',
            'Batangas City',
            'Dasmarinas',
            'Imus',
            'Bacoor',
            'Cavite',
            'Bulacan',
            'Malolos',
            'Meycauayan',
            'San Jose del Monte',
            'Santa Maria',
            'Laguna',
            'Santa Rosa',
            'Binan',
            'Calamba',
            'Antipolo',
            'Rizal',
            'Pampanga',
            'San Fernando',
            'Tarlac City',
            'Lucena',
            'Naga',
            'Legazpi',
            'Puerto Princesa',
        ],
        'Poland' => ['Warsaw', 'Krakow', 'Lodz', 'Wroclaw', 'Poznan'],
        'Portugal' => ['Lisbon', 'Porto', 'Vila Nova de Gaia', 'Braga', 'Coimbra'],
        'Qatar' => ['Doha', 'Al Rayyan', 'Al Wakrah', 'Al Khor', 'Umm Salal'],
        'Romania' => ['Bucharest', 'Cluj-Napoca', 'Timisoara', 'Iasi', 'Constanta'],
        'Russia' => ['Moscow', 'Saint Petersburg', 'Novosibirsk', 'Yekaterinburg', 'Kazan'],
        'Rwanda' => ['Kigali', 'Butare', 'Gisenyi', 'Ruhengeri', 'Byumba'],
        'Saint Kitts and Nevis' => ['Basseterre', 'Charlestown', 'Sandy Point Town', 'Cayon', 'Dieppe Bay Town'],
        'Saint Lucia' => ['Castries', 'Vieux Fort', 'Soufriere', 'Gros Islet', 'Dennery'],
        'Saint Vincent and the Grenadines' => ['Kingstown', 'Georgetown', 'Barrouallie', 'Chateaubelair', 'Port Elizabeth'],
        'Samoa' => ['Apia', 'Vaitele', 'Faleula', 'Siusega', 'Malie'],
        'San Marino' => ['San Marino', 'Serravalle', 'Borgo Maggiore', 'Domagnano', 'Fiorentino'],
        'Sao Tome and Principe' => ['Sao Tome', 'Santo Antonio', 'Neves', 'Trindade', 'Santana'],
        'Saudi Arabia' => ['Riyadh', 'Jeddah', 'Mecca', 'Medina', 'Dammam'],
        'Senegal' => ['Dakar', 'Touba', 'Thies', 'Saint-Louis', 'Kaolack'],
        'Serbia' => ['Belgrade', 'Novi Sad', 'Nis', 'Kragujevac', 'Subotica'],
        'Seychelles' => ['Victoria', 'Anse Boileau', 'Beau Vallon', 'Cascade', 'Anse Royale'],
        'Sierra Leone' => ['Freetown', 'Bo', 'Kenema', 'Makeni', 'Koidu'],
        'Singapore' => ['Singapore', 'Jurong', 'Tampines', 'Woodlands', 'Bedok'],
        'Slovakia' => ['Bratislava', 'Kosice', 'Presov', 'Zilina', 'Nitra'],
        'Slovenia' => ['Ljubljana', 'Maribor', 'Celje', 'Kranj', 'Koper'],
        'Solomon Islands' => ['Honiara', 'Gizo', 'Auki', 'Noro', 'Tulagi'],
        'Somalia' => ['Mogadishu', 'Hargeisa', 'Kismayo', 'Bosaso', 'Baidoa'],
        'South Africa' => ['Pretoria', 'Cape Town', 'Johannesburg', 'Durban', 'Port Elizabeth'],
        'South Korea' => ['Seoul', 'Busan', 'Incheon', 'Daegu', 'Daejeon'],
        'South Sudan' => ['Juba', 'Wau', 'Malakal', 'Bor', 'Yei'],
        'Spain' => ['Madrid', 'Barcelona', 'Valencia', 'Seville', 'Bilbao'],
        'Sri Lanka' => ['Sri Jayawardenepura Kotte', 'Colombo', 'Kandy', 'Galle', 'Jaffna'],
        'Sudan' => ['Khartoum', 'Omdurman', 'Port Sudan', 'Kassala', 'El Obeid'],
        'Suriname' => ['Paramaribo', 'Lelydorp', 'Nieuw Nickerie', 'Moengo', 'Albina'],
        'Sweden' => ['Stockholm', 'Gothenburg', 'Malmo', 'Uppsala', 'Vasteras'],
        'Switzerland' => ['Bern', 'Zurich', 'Geneva', 'Basel', 'Lausanne'],
        'Syria' => ['Damascus', 'Aleppo', 'Homs', 'Hama', 'Latakia'],
        'Taiwan' => ['Taipei', 'Kaohsiung', 'Taichung', 'Tainan', 'Taoyuan'],
        'Tajikistan' => ['Dushanbe', 'Khujand', 'Kulob', 'Bokhtar', 'Istaravshan'],
        'Tanzania' => ['Dodoma', 'Dar es Salaam', 'Mwanza', 'Arusha', 'Mbeya'],
        'Thailand' => ['Bangkok', 'Chiang Mai', 'Phuket', 'Pattaya', 'Khon Kaen'],
        'Timor-Leste' => ['Dili', 'Baucau', 'Maliana', 'Suai', 'Same'],
        'Togo' => ['Lome', 'Sokode', 'Kara', 'Kpalime', 'Atakpame'],
        'Tonga' => ['Nukualofa', 'Neiafu', 'Haveluloto', 'Vaini', 'Pangai'],
        'Trinidad and Tobago' => ['Port of Spain', 'San Fernando', 'Chaguanas', 'Arima', 'Point Fortin'],
        'Tunisia' => ['Tunis', 'Sfax', 'Sousse', 'Kairouan', 'Bizerte'],
        'Turkey' => ['Ankara', 'Istanbul', 'Izmir', 'Bursa', 'Antalya'],
        'Turkmenistan' => ['Ashgabat', 'Turkmenabat', 'Dashoguz', 'Mary', 'Balkanabat'],
        'Tuvalu' => ['Funafuti', 'Vaiaku', 'Asau', 'Savave', 'Tanrake'],
        'Uganda' => ['Kampala', 'Gulu', 'Lira', 'Mbarara', 'Jinja'],
        'Ukraine' => ['Kyiv', 'Kharkiv', 'Odesa', 'Dnipro', 'Lviv'],
        'United Arab Emirates' => ['Abu Dhabi', 'Dubai', 'Sharjah', 'Ajman', 'Al Ain'],
        'United Kingdom' => ['London', 'Manchester', 'Birmingham', 'Liverpool', 'Glasgow'],
        'United States' => ['Washington, D.C.', 'New York', 'Los Angeles', 'Chicago', 'Houston'],
        'Uruguay' => ['Montevideo', 'Salto', 'Ciudad de la Costa', 'Paysandu', 'Las Piedras'],
        'Uzbekistan' => ['Tashkent', 'Samarkand', 'Namangan', 'Andijan', 'Bukhara'],
        'Vanuatu' => ['Port Vila', 'Luganville', 'Norsup', 'Lenakel', 'Isangel'],
        'Vatican City' => ['Vatican City', 'St. Peters Square', 'Vatican Museums', 'Vatican Gardens', 'Apostolic Palace'],
        'Venezuela' => ['Caracas', 'Maracaibo', 'Valencia', 'Barquisimeto', 'Maracay'],
        'Vietnam' => ['Hanoi', 'Ho Chi Minh City', 'Da Nang', 'Hue', 'Can Tho'],
        'Yemen' => ['Sanaa', 'Aden', 'Taiz', 'Al Hudaydah', 'Ibb'],
        'Zambia' => ['Lusaka', 'Kitwe', 'Ndola', 'Kabwe', 'Livingstone'],
        'Zimbabwe' => ['Harare', 'Bulawayo', 'Chitungwiza', 'Mutare', 'Gweru'],
    ];
}

function participant_country_city_options($country)
{
    $country = trim((string) $country);
    $city_options = participant_country_city_options_map();

    if (isset($city_options[$country])) {
        return $city_options[$country];
    }

    $fallback_country = $country !== '' ? $country : 'Main';

    return [
        $fallback_country . ' City',
        $fallback_country . ' Central',
        $fallback_country . ' North',
        $fallback_country . ' South',
        $fallback_country . ' Business District',
    ];
}

function participant_destination_image_file($city)
{
    $normalized_city = strtolower(trim((string) $city));
    $normalized_city = preg_replace('/[^a-z0-9]/', '', $normalized_city);
    $city_images = [
        'manila' => 'manila.jpg',
        'quezoncity' => 'qc.jpg',
        'qc' => 'qc.jpg',
        'cebu' => 'cebu.jpg',
        'cebucity' => 'cebu.jpg',
        'davao' => 'davao.jpg',
        'davaocity' => 'davao.jpg',
        'taguig' => 'taguig.jpg',
        'pasig' => 'pasig.jpg',
        'makati' => 'makati.jpg',
        'baguio' => 'baguio.jpg',
    ];
    $image_file = $city_images[$normalized_city] ?? 'manila.jpg';
    $image_path = dirname(__DIR__) . '/assets/images/cities/' . $image_file;

    return is_file($image_path) ? $image_file : 'manila.jpg';
}

function participant_destination_image_path($city, $base_path = '')
{
    return $base_path . 'assets/images/cities/' . participant_destination_image_file($city);
}

function participant_destination_card_background($city, $base_path = '')
{
    $image_path = participant_destination_image_path($city, $base_path);

    return "linear-gradient(180deg, rgba(1, 68, 33, 0.10) 0%, rgba(1, 68, 33, 0.48) 100%), url('" . $image_path . "'), linear-gradient(135deg, rgba(244, 196, 48, 0.20), rgba(0, 107, 63, 0.16))";
}

function participant_render_event_image($event, $base_path = '../')
{
    $banner_src = participant_event_banner_src($event, $base_path);
    $event_title = $event['event_title'] ?? 'Shenanovents event';

    if ($banner_src !== '') {
        ?>
        <div class="event-image-placeholder event-image-has-photo">
            <img src="<?php echo htmlspecialchars($banner_src, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($event_title, ENT_QUOTES, 'UTF-8'); ?> banner">
        </div>
        <?php
        return;
    }

    ?>
    <div class="event-image-placeholder event-image-fallback">
        <span><?php echo htmlspecialchars($event_title, ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
    <?php
}

function participant_event_type_label($event_type)
{
    $event_type = strtolower(trim((string) $event_type));

    if ($event_type === 'online') {
        return 'Online Event';
    }

    if ($event_type === 'tba') {
        return 'Location TBA';
    }

    return 'Venue Event';
}

function participant_profile_picture_src($profile_picture, $base_path = '../')
{
    $profile_picture = trim((string) $profile_picture);

    if ($profile_picture === '') {
        return '';
    }

    $profile_picture = str_replace('\\', '/', ltrim($profile_picture, '/'));
    $profile_picture_path = dirname(__DIR__) . '/' . $profile_picture;

    if (!is_file($profile_picture_path)) {
        return '';
    }

    return $base_path . $profile_picture;
}

function participant_render_profile_avatar($profile_picture, $base_path = '../')
{
    $picture_src = participant_profile_picture_src($profile_picture, $base_path);

    if ($picture_src !== '') {
        ?>
        <img src="<?php echo htmlspecialchars($picture_src, ENT_QUOTES, 'UTF-8'); ?>" alt="Participant profile picture">
        <?php
        return;
    }

    ?>
    <span class="icon icon-user"></span>
    <?php
}

function participant_event_category($title, $description)
{
    $text = strtolower($title . ' ' . $description);

    if (strpos($text, 'cyber') !== false || strpos($text, 'digital') !== false || strpos($text, 'tech') !== false) {
        return 'technology';
    }

    if (strpos($text, 'startup') !== false || strpos($text, 'business') !== false || strpos($text, 'pitch') !== false) {
        return 'business';
    }

    if (strpos($text, 'music') !== false || strpos($text, 'song') !== false || strpos($text, 'guitar') !== false) {
        return 'music';
    }

    if (strpos($text, 'workshop') !== false || strpos($text, 'demo') !== false) {
        return 'workshops';
    }

    if (strpos($text, 'class') !== false || strpos($text, 'learn') !== false) {
        return 'education';
    }

    return 'community';
}

function participant_event_badge($status, $remaining_slots, $capacity)
{
    $capacity = (int) $capacity;
    $remaining_slots = (int) $remaining_slots;

    if ($capacity <= 0) {
        return '';
    }

    $registered_count = max(0, $capacity - max(0, $remaining_slots));

    if ($registered_count >= $capacity) {
        return 'Full';
    }

    if (($registered_count / $capacity) >= 0.9) {
        return 'Almost Full';
    }

    return '';
}

function participant_registration_label($event)
{
    if (($event['current_user_registration_status'] ?? '') === 'registered') {
        return 'Registered';
    }

    if (($event['current_user_registration_status'] ?? '') === 'cancelled') {
        return 'Register Again';
    }

    if (!participant_event_registration_is_open($event, participant_current_user_id()) || (int) ($event['remaining_slots'] ?? 0) <= 0) {
        return 'Unavailable';
    }

    return 'Free Registration';
}

function participant_attendance_status_label($status)
{
    $status = strtolower(trim((string) $status));

    if ($status === '') {
        return 'Pending';
    }

    return ucwords(str_replace(['_', '-'], ' ', $status));
}

function participant_normalize_event_row($row)
{
    $capacity = (int) ($row['capacity'] ?? 0);
    $registered_count = (int) ($row['registered_count'] ?? 0);
    $event_type = strtolower(trim((string) ($row['event_type'] ?? '')));
    $event_category = strtolower(trim((string) ($row['event_category'] ?? '')));
    $start_time_text = participant_format_time($row['event_time'] ?? '');
    $end_time_text = !empty($row['event_end_time']) ? participant_format_time($row['event_end_time']) : '';
    $remaining_slots = max(0, $capacity - $registered_count);
    $row['registered_count'] = $registered_count;
    $row['remaining_slots'] = $remaining_slots;
    $row['date_text'] = participant_format_date($row['event_date']);
    $row['start_time_text'] = $start_time_text;
    $row['end_time_text'] = $end_time_text;
    $row['time_text'] = $end_time_text !== '' ? $start_time_text . ' - ' . $end_time_text : $start_time_text;
    $row['date_time'] = strtoupper(date('D, M j', strtotime($row['event_date']))) . ' - ' . strtoupper($row['time_text']);
    $row['time_filter'] = participant_time_filter($row['event_date']);
    $row['location_type'] = in_array($event_type, ['online', 'physical', 'tba'], true)
        ? $event_type
        : participant_event_location_type($row['event_location']);
    $row['event_type_label'] = participant_event_type_label($row['location_type']);
    $row['category'] = $event_category !== ''
        ? $event_category
        : participant_event_category($row['event_title'], $row['event_description'] . ' ' . ($row['event_tags'] ?? ''));
    $row['event_category'] = $row['category'];
    $row['event_country'] = trim((string) ($row['event_country'] ?? ''));
    $row['event_city'] = trim((string) ($row['event_city'] ?? ''));
    $row['event_province'] = trim((string) ($row['event_province'] ?? ''));
    $row['event_summary'] = trim((string) ($row['event_summary'] ?? ''));
    $row['banner_image'] = trim((string) ($row['banner_image'] ?? ''));
    $row['visibility'] = trim((string) ($row['visibility'] ?? 'public'));
    $row['audience'] = trim((string) ($row['audience'] ?? ''));
    $row['private_access_key'] = trim((string) ($row['private_access_key'] ?? ''));
    $row['status_badge'] = participant_event_badge($row['status'], $remaining_slots, $capacity);
    $row['registration_label'] = participant_registration_label($row);
    $row['organizer_name'] = trim(($row['organizer_first_name'] ?? '') . ' ' . ($row['organizer_last_name'] ?? ''));
    $row['current_user_liked'] = (int) ($row['current_user_liked'] ?? 0);
    $row['attendee_count'] = (int) ($row['attendee_count'] ?? 1);
    $row['attendance_code'] = trim((string) ($row['attendance_code'] ?? ''));
    $row['attendance_status'] = strtolower(trim((string) ($row['attendance_status'] ?? 'pending')));
    $row['attendance_status_label'] = participant_attendance_status_label($row['attendance_status']);
    $row['attendance_marked_at'] = $row['attendance_marked_at'] ?? null;

    return $row;
}

function participant_fetch_profile($conn, $user_id, $role = 'participant')
{
    $sql = 'SELECT user_id, first_name, last_name, email, role, status, profile_picture, created_at
            FROM users
            WHERE user_id = ? AND role = ?
            LIMIT 1';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, 'is', $user_id, $role);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $user ?: null;
}

function participant_normalize_event_filters($filters)
{
    if (is_string($filters)) {
        $filters = ['city' => $filters];
    }

    $filters = is_array($filters) ? $filters : [];
    $allowed_locations = ['online', 'physical'];
    $allowed_time_filters = ['today', 'weekend'];

    return [
        'country' => trim($filters['country'] ?? ''),
        'city' => trim($filters['city'] ?? ''),
        'location' => in_array(($filters['location'] ?? ''), $allowed_locations, true) ? $filters['location'] : '',
        'category' => preg_replace('/[^a-z0-9_-]/', '', strtolower($filters['category'] ?? '')),
        'time' => in_array(($filters['time'] ?? ''), $allowed_time_filters, true) ? $filters['time'] : '',
    ];
}

function participant_event_filter_sql($filters, &$types, &$values, $alias = 'e')
{
    $filters = participant_normalize_event_filters($filters);
    $where = '';
    $prefix = $alias . '.';

    if ($filters['country'] !== '') {
        $where .= ' AND ' . $prefix . 'event_country = ?';
        $types .= 's';
        $values[] = $filters['country'];
    }

    if ($filters['city'] !== '') {
        $where .= ' AND ' . $prefix . 'event_city = ?';
        $types .= 's';
        $values[] = $filters['city'];
    }

    if ($filters['location'] !== '') {
        $where .= ' AND ' . $prefix . 'event_type = ?';
        $types .= 's';
        $values[] = $filters['location'];
    }

    if ($filters['category'] !== '') {
        $where .= ' AND ' . $prefix . 'event_category = ?';
        $types .= 's';
        $values[] = $filters['category'];
    }

    if ($filters['time'] === 'today') {
        $where .= ' AND ' . $prefix . 'event_date = CURDATE()';
    }

    if ($filters['time'] === 'weekend') {
        $where .= ' AND DAYOFWEEK(' . $prefix . 'event_date) IN (1, 7)';
    }

    return $where;
}

function participant_bind_statement($stmt, $types, $values)
{
    $bind_values = [$types];

    foreach ($values as $key => $value) {
        $bind_values[] = &$values[$key];
    }

    return mysqli_stmt_bind_param($stmt, ...$bind_values);
}

function participant_filter_event_items($events, $filters)
{
    $filters = participant_normalize_event_filters($filters);

    return array_values(array_filter($events, function ($event) use ($filters) {
        if ($filters['country'] !== '' && strcasecmp($event['event_country'] ?? '', $filters['country']) !== 0) {
            return false;
        }

        if ($filters['city'] !== '' && strcasecmp($event['event_city'] ?? '', $filters['city']) !== 0) {
            return false;
        }

        if ($filters['location'] !== '' && ($event['location_type'] ?? '') !== $filters['location']) {
            return false;
        }

        if ($filters['category'] !== '' && ($event['event_category'] ?? '') !== $filters['category']) {
            return false;
        }

        if ($filters['time'] !== '' && ($event['time_filter'] ?? '') !== $filters['time']) {
            return false;
        }

        return true;
    }));
}

function participant_current_page($page_param = 'page')
{
    return max(1, (int) ($_GET[$page_param] ?? 1));
}

function participant_paginate_items($items, $current_page, $per_page = 8)
{
    $total_items = count($items);
    $total_pages = max(1, (int) ceil($total_items / $per_page));
    $current_page = min(max(1, (int) $current_page), $total_pages);
    $offset = ($current_page - 1) * $per_page;

    return [
        'items' => array_slice($items, $offset, $per_page),
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'per_page' => $per_page,
    ];
}

function participant_build_url($path, $params = [])
{
    $clean_params = [];

    foreach ($params as $key => $value) {
        if ($value !== '' && $value !== null) {
            $clean_params[$key] = $value;
        }
    }

    $query = http_build_query($clean_params);

    return $query !== '' ? $path . '?' . $query : $path;
}

function participant_render_pagination($path, $params, $current_page, $total_pages, $page_param = 'page')
{
    if ($total_pages <= 1) {
        return;
    }

    ?>
    <nav class="pagination pill-menu server-pagination" aria-label="Event pages">
        <?php if ($current_page > 1): ?>
            <a class="pagination-link pagination-wide" href="<?php echo htmlspecialchars(participant_build_url($path, array_merge($params, [$page_param => $current_page - 1])), ENT_QUOTES, 'UTF-8'); ?>">Previous</a>
        <?php else: ?>
            <span class="pagination-link pagination-wide is-disabled">Previous</span>
        <?php endif; ?>

        <?php for ($page = 1; $page <= $total_pages; $page++): ?>
            <a class="pagination-link<?php echo $page === $current_page ? ' active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url($path, array_merge($params, [$page_param => $page])), ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo (int) $page; ?>
            </a>
        <?php endfor; ?>

        <?php if ($current_page < $total_pages): ?>
            <a class="pagination-link pagination-wide" href="<?php echo htmlspecialchars(participant_build_url($path, array_merge($params, [$page_param => $current_page + 1])), ENT_QUOTES, 'UTF-8'); ?>">Next</a>
        <?php else: ?>
            <span class="pagination-link pagination-wide is-disabled">Next</span>
        <?php endif; ?>
    </nav>
    <?php
}

function participant_render_dashboard_pagination($path, $params, $current_page, $total_pages, $page_param = 'page')
{
    if ($total_pages <= 1) {
        return;
    }

    ?>
    <nav class="dashboard-pagination pill-menu server-pagination" aria-label="Dashboard pages">
        <?php if ($current_page > 1): ?>
            <a class="dashboard-pagination-link pagination-wide" href="<?php echo htmlspecialchars(participant_build_url($path, array_merge($params, [$page_param => $current_page - 1])), ENT_QUOTES, 'UTF-8'); ?>">Previous</a>
        <?php else: ?>
            <span class="dashboard-pagination-link pagination-wide is-disabled">Previous</span>
        <?php endif; ?>

        <?php for ($page = 1; $page <= $total_pages; $page++): ?>
            <a class="dashboard-pagination-link<?php echo $page === $current_page ? ' active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url($path, array_merge($params, [$page_param => $page])), ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo (int) $page; ?>
            </a>
        <?php endfor; ?>

        <?php if ($current_page < $total_pages): ?>
            <a class="dashboard-pagination-link pagination-wide" href="<?php echo htmlspecialchars(participant_build_url($path, array_merge($params, [$page_param => $current_page + 1])), ENT_QUOTES, 'UTF-8'); ?>">Next</a>
        <?php else: ?>
            <span class="dashboard-pagination-link pagination-wide is-disabled">Next</span>
        <?php endif; ?>
    </nav>
    <?php
}

function participant_fetch_events($conn, $user_id, $filters = [])
{
    $events = [];
    $event_fields = participant_event_select_fields('e');
    $types = 'ii';
    $values = [$user_id, $user_id];
    $sql = 'SELECT ' . $event_fields . ',
                   organizer.first_name AS organizer_first_name,
                   organizer.last_name AS organizer_last_name,
                   (SELECT COALESCE(SUM(active_reg.attendee_count), 0)
                    FROM registrations active_reg
                    WHERE active_reg.event_id = e.event_id
                    AND active_reg.registration_status = "registered") AS registered_count,
                   (SELECT user_reg.registration_status
                    FROM registrations user_reg
                    WHERE user_reg.event_id = e.event_id
                    AND user_reg.user_id = ?
                    LIMIT 1) AS current_user_registration_status,
                   EXISTS(
                    SELECT 1
                    FROM liked_events liked
                    WHERE liked.event_id = e.event_id
                    AND liked.user_id = ?
                   ) AS current_user_liked
            FROM events e
            INNER JOIN users organizer ON organizer.user_id = e.created_by
            WHERE LOWER(e.status) IN ("open", "published", "approved", "active")';

    $sql .= participant_event_filter_sql($filters, $types, $values, 'e');
    $sql .= ' ORDER BY e.event_date ASC, e.event_time ASC';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return [];
    }

    participant_bind_statement($stmt, $types, $values);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $event = participant_normalize_event_row($row);

        if (participant_event_registration_is_open($event, $user_id)) {
            $events[] = $event;
        }
    }

    mysqli_stmt_close($stmt);

    return $events;
}

function participant_fetch_landing_events($conn, $limit = 8)
{
    $events = participant_fetch_events($conn, 0, []);

    return array_slice($events, 0, max(1, (int) $limit));
}

function participant_fetch_event_details($conn, $user_id, $event_id)
{
    $event_fields = participant_event_select_fields('e');
    $sql = 'SELECT ' . $event_fields . ',
                   organizer.first_name AS organizer_first_name,
                   organizer.last_name AS organizer_last_name,
                   (SELECT COALESCE(SUM(active_reg.attendee_count), 0)
                    FROM registrations active_reg
                    WHERE active_reg.event_id = e.event_id
                    AND active_reg.registration_status = "registered") AS registered_count,
                   (SELECT user_reg.registration_status
                    FROM registrations user_reg
                    WHERE user_reg.event_id = e.event_id
                    AND user_reg.user_id = ?
                    LIMIT 1) AS current_user_registration_status,
                   EXISTS(
                    SELECT 1
                    FROM liked_events liked
                    WHERE liked.event_id = e.event_id
                    AND liked.user_id = ?
                   ) AS current_user_liked
            FROM events e
            INNER JOIN users organizer ON organizer.user_id = e.created_by
            WHERE e.event_id = ?
            LIMIT 1';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, 'iii', $user_id, $user_id, $event_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $event = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$event) {
        return null;
    }

    $event = participant_normalize_event_row($event);

    return participant_user_can_view_event($event, $user_id) ? $event : null;
}

function participant_unlock_private_event($conn, $user_id, $private_event_code)
{
    $private_event_code = strtoupper(trim((string) $private_event_code));

    if ($private_event_code === '') {
        return ['success' => false, 'message' => 'Enter your private event code.', 'event_id' => 0];
    }

    $event_fields = participant_event_select_fields('e');
    $sql = 'SELECT ' . $event_fields . ',
                   organizer.first_name AS organizer_first_name,
                   organizer.last_name AS organizer_last_name,
                   (SELECT COALESCE(SUM(active_reg.attendee_count), 0)
                    FROM registrations active_reg
                    WHERE active_reg.event_id = e.event_id
                    AND active_reg.registration_status = "registered") AS registered_count,
                   (SELECT user_reg.registration_status
                    FROM registrations user_reg
                    WHERE user_reg.event_id = e.event_id
                    AND user_reg.user_id = ?
                    LIMIT 1) AS current_user_registration_status,
                   0 AS current_user_liked
            FROM events e
            INNER JOIN users organizer ON organizer.user_id = e.created_by
            WHERE e.private_access_key = ?
            AND LOWER(e.visibility) = "private"
            LIMIT 1';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return ['success' => false, 'message' => 'Unable to check the private event code.', 'event_id' => 0];
    }

    mysqli_stmt_bind_param($stmt, 'is', $user_id, $private_event_code);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $event = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$event) {
        return ['success' => false, 'message' => 'Invalid private event code.', 'event_id' => 0];
    }

    $event = participant_normalize_event_row($event);

    if (!participant_event_is_available($event['status'] ?? '') || !participant_event_publish_is_active($event) || participant_event_has_ended($event)) {
        return ['success' => false, 'message' => 'This private event is not available anymore.', 'event_id' => 0];
    }

    $_SESSION['private_event_access'][(int) $event['event_id']] = true;

    return ['success' => true, 'message' => 'Private event unlocked.', 'event_id' => (int) $event['event_id']];
}

function participant_fetch_registered_events($conn, $user_id, $include_cancelled = true)
{
    $events = [];
    $event_fields = participant_event_select_fields('e');
    $sql = 'SELECT r.registration_id, r.registration_status, r.attendance_code,
                   r.attendance_status, r.attendance_marked_at, r.registered_at,
                   ' . $event_fields . ',
                   organizer.first_name AS organizer_first_name,
                   organizer.last_name AS organizer_last_name,
                   r.attendee_count,
                   r.special_notes,
                   (SELECT COALESCE(SUM(active_reg.attendee_count), 0)
                    FROM registrations active_reg
                    WHERE active_reg.event_id = e.event_id
                    AND active_reg.registration_status = "registered") AS registered_count,
                   r.registration_status AS current_user_registration_status,
                   0 AS current_user_liked
            FROM registrations r
            INNER JOIN events e ON e.event_id = r.event_id
            INNER JOIN users organizer ON organizer.user_id = e.created_by
            WHERE r.user_id = ?';

    if (!$include_cancelled) {
        $sql .= ' AND r.registration_status = "registered"';
    }

    $sql .= ' ORDER BY r.registered_at DESC, e.event_date ASC, e.event_time ASC';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return [];
    }

    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $event = participant_normalize_event_row($row);

        if (participant_user_can_view_event($event, $user_id)) {
            $events[] = $event;
        }
    }

    mysqli_stmt_close($stmt);

    return $events;
}

function participant_fetch_hosted_events($conn, $user_id)
{
    $events = [];
    $event_fields = participant_event_select_fields('e');
    $sql = 'SELECT ' . $event_fields . ',
                   organizer.first_name AS organizer_first_name,
                   organizer.last_name AS organizer_last_name,
                   (SELECT COALESCE(SUM(active_reg.attendee_count), 0)
                    FROM registrations active_reg
                    WHERE active_reg.event_id = e.event_id
                    AND active_reg.registration_status = "registered") AS registered_count,
                   NULL AS current_user_registration_status,
                   0 AS current_user_liked
            FROM events e
            INNER JOIN users organizer ON organizer.user_id = e.created_by
            WHERE e.created_by = ?
            ORDER BY e.created_at DESC';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return [];
    }

    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $event = participant_normalize_event_row($row);

        if (participant_user_can_view_event($event, $user_id)) {
            $events[] = $event;
        }
    }

    mysqli_stmt_close($stmt);

    return $events;
}

function participant_fetch_owned_event_dashboard($conn, $user_id, $event_id)
{
    $event_fields = participant_event_select_fields('e');
    $sql = 'SELECT ' . $event_fields . ',
                   organizer.first_name AS organizer_first_name,
                   organizer.last_name AS organizer_last_name,
                   (SELECT COALESCE(SUM(active_reg.attendee_count), 0)
                    FROM registrations active_reg
                    WHERE active_reg.event_id = e.event_id
                    AND active_reg.registration_status = "registered") AS registered_count,
                   (SELECT COUNT(*)
                    FROM registrations active_rows
                    WHERE active_rows.event_id = e.event_id
                    AND active_rows.registration_status = "registered") AS registration_records,
                   (SELECT COALESCE(SUM(cancelled_reg.attendee_count), 0)
                    FROM registrations cancelled_reg
                    WHERE cancelled_reg.event_id = e.event_id
                    AND cancelled_reg.registration_status = "cancelled") AS cancelled_count,
                   (SELECT COALESCE(SUM(present_reg.attendee_count), 0)
                    FROM registrations present_reg
                    WHERE present_reg.event_id = e.event_id
                    AND present_reg.registration_status = "registered"
                    AND LOWER(present_reg.attendance_status) = "present") AS present_count,
                   (SELECT COALESCE(SUM(absent_reg.attendee_count), 0)
                    FROM registrations absent_reg
                    WHERE absent_reg.event_id = e.event_id
                    AND absent_reg.registration_status = "registered"
                    AND LOWER(absent_reg.attendance_status) = "absent") AS absent_count,
                   (SELECT COALESCE(SUM(pending_reg.attendee_count), 0)
                    FROM registrations pending_reg
                    WHERE pending_reg.event_id = e.event_id
                    AND pending_reg.registration_status = "registered"
                    AND LOWER(pending_reg.attendance_status) = "pending") AS pending_count,
                   NULL AS current_user_registration_status,
                   0 AS current_user_liked
            FROM events e
            INNER JOIN users organizer ON organizer.user_id = e.created_by
            WHERE e.event_id = ?
            AND e.created_by = ?
            LIMIT 1';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, 'ii', $event_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $event = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$event) {
        return null;
    }

    $event = participant_normalize_event_row($event);
    $event['registration_records'] = (int) ($event['registration_records'] ?? 0);
    $event['cancelled_count'] = (int) ($event['cancelled_count'] ?? 0);
    $event['present_count'] = (int) ($event['present_count'] ?? 0);
    $event['absent_count'] = (int) ($event['absent_count'] ?? 0);
    $event['pending_count'] = (int) ($event['pending_count'] ?? 0);

    return $event;
}

function participant_fetch_event_registration_bars($conn, $user_id, $event_id)
{
    $bars = [
        ['label' => 'Mon', 'value' => 0, 'height' => 8],
        ['label' => 'Tue', 'value' => 0, 'height' => 8],
        ['label' => 'Wed', 'value' => 0, 'height' => 8],
        ['label' => 'Thu', 'value' => 0, 'height' => 8],
        ['label' => 'Fri', 'value' => 0, 'height' => 8],
        ['label' => 'Sat', 'value' => 0, 'height' => 8],
        ['label' => 'Sun', 'value' => 0, 'height' => 8],
    ];
    $sql = 'SELECT WEEKDAY(r.registered_at) AS weekday_index,
                   COALESCE(SUM(r.attendee_count), 0) AS total_attendees
            FROM registrations r
            INNER JOIN events e ON e.event_id = r.event_id
            WHERE r.event_id = ?
            AND e.created_by = ?
            AND r.registration_status = "registered"
            GROUP BY WEEKDAY(r.registered_at)';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return $bars;
    }

    mysqli_stmt_bind_param($stmt, 'ii', $event_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $max_value = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $index = (int) ($row['weekday_index'] ?? 0);
        $value = (int) ($row['total_attendees'] ?? 0);

        if (isset($bars[$index])) {
            $bars[$index]['value'] = $value;
            $max_value = max($max_value, $value);
        }
    }

    mysqli_stmt_close($stmt);

    foreach ($bars as $index => $bar) {
        $bars[$index]['height'] = $max_value > 0 ? max(12, (int) round(($bar['value'] / $max_value) * 94)) : 8;
    }

    return $bars;
}

function participant_build_event_dashboard_points($event)
{
    $capacity = max(1, (int) ($event['capacity'] ?? 0));
    $points = [
        ['label' => 'Registered', 'count' => (int) ($event['registered_count'] ?? 0), 'x' => 5],
        ['label' => 'Present', 'count' => (int) ($event['present_count'] ?? 0), 'x' => 27],
        ['label' => 'Absent', 'count' => (int) ($event['absent_count'] ?? 0), 'x' => 50],
        ['label' => 'Remaining', 'count' => (int) ($event['remaining_slots'] ?? 0), 'x' => 73],
        ['label' => 'Capacity', 'count' => $capacity, 'x' => 95],
    ];
    $max_count = max(array_column($points, 'count'));

    foreach ($points as $index => $point) {
        $ratio = $max_count > 0 ? $point['count'] / $max_count : 0;
        $points[$index]['y'] = max(12, min(88, 88 - (int) round($ratio * 72)));
    }

    return $points;
}

function participant_build_event_dashboard_bars($event)
{
    $capacity = max(0, (int) ($event['capacity'] ?? 0));
    $bars = [
        ['label' => 'Registered', 'count' => (int) ($event['registered_count'] ?? 0)],
        ['label' => 'Pending', 'count' => (int) ($event['pending_count'] ?? 0)],
        ['label' => 'Present', 'count' => (int) ($event['present_count'] ?? 0)],
        ['label' => 'Absent', 'count' => (int) ($event['absent_count'] ?? 0)],
        ['label' => 'Remaining', 'count' => (int) ($event['remaining_slots'] ?? 0)],
    ];
    $max_count = 1;

    foreach ($bars as $bar) {
        $max_count = max($max_count, (int) $bar['count']);
    }

    foreach ($bars as $index => $bar) {
        $count = max(0, (int) $bar['count']);
        $bars[$index]['height'] = $count > 0 ? max(14, (int) round(($count / $max_count) * 100)) : 6;
        $bars[$index]['class'] = strtolower(str_replace(' ', '-', $bar['label']));
    }

    return $bars;
}

function participant_fetch_owned_event_attendance($conn, $user_id, $event_id)
{
    $user_id = (int) $user_id;
    $event_id = (int) $event_id;
    $sql = 'SELECT r.registration_id, r.user_id, r.event_id, r.registration_full_name,
                   r.registration_email, r.attendee_count, r.registration_status,
                   r.attendance_code, r.attendance_status, r.attendance_marked_at, r.registered_at,
                   u.first_name, u.last_name, u.email AS user_email, u.profile_picture
            FROM registrations r
            INNER JOIN events e ON e.event_id = r.event_id
            INNER JOIN users u ON u.user_id = r.user_id
            WHERE e.created_by = ?
            AND r.event_id = ?
            AND LOWER(r.registration_status) = "registered"
            ORDER BY r.registered_at DESC, u.last_name ASC, u.first_name ASC';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return [];
    }

    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $event_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $participants = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $full_name = trim((string) ($row['registration_full_name'] ?? ''));

        if ($full_name === '') {
            $full_name = trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? ''));
        }

        $row['full_name'] = $full_name !== '' ? $full_name : ($row['user_email'] ?? 'Participant');
        $row['display_email'] = trim((string) ($row['registration_email'] ?? '')) ?: ($row['user_email'] ?? '');
        $row['attendee_count'] = (int) ($row['attendee_count'] ?? 1);
        $row['attendance_status'] = strtolower(trim((string) ($row['attendance_status'] ?? 'pending')));
        $row['attendance_label'] = participant_attendance_status_label($row['attendance_status']);
        $participants[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $participants;
}

function participant_sync_attendance_record($conn, $registration_id, $attendance_status, $marked_by)
{
    $registration_id = (int) $registration_id;
    $marked_by = (int) $marked_by;
    $status_label = participant_attendance_status_label($attendance_status);
    $sql = 'INSERT INTO attendance (registration_id, attendance_status, marked_by)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
                attendance_status = VALUES(attendance_status),
                marked_by = VALUES(marked_by),
                marked_at = CURRENT_TIMESTAMP';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'isi', $registration_id, $status_label, $marked_by);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $success;
}

function participant_mark_owned_registration_attendance($conn, $user_id, $registration_id, $attendance_status)
{
    $user_id = (int) $user_id;
    $registration_id = (int) $registration_id;
    $attendance_status = strtolower(trim((string) $attendance_status));

    if ($registration_id <= 0) {
        return ['success' => false, 'message' => 'Please select a valid registration.'];
    }

    if (!in_array($attendance_status, ['present', 'absent'], true)) {
        return ['success' => false, 'message' => 'Please choose Present or Absent only.'];
    }

    $check_sql = 'SELECT r.registration_id
                  FROM registrations r
                  INNER JOIN events e ON e.event_id = r.event_id
                  WHERE r.registration_id = ?
                  AND e.created_by = ?
                  AND LOWER(r.registration_status) = "registered"
                  LIMIT 1';
    $check_stmt = mysqli_prepare($conn, $check_sql);

    if (!$check_stmt) {
        return ['success' => false, 'message' => 'Unable to verify this registration.'];
    }

    mysqli_stmt_bind_param($check_stmt, 'ii', $registration_id, $user_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    $is_valid = mysqli_stmt_num_rows($check_stmt) > 0;
    mysqli_stmt_close($check_stmt);

    if (!$is_valid) {
        return ['success' => false, 'message' => 'Attendance can only be marked for your active event registrations.'];
    }

    $update_sql = 'UPDATE registrations
                   SET attendance_status = ?,
                       attendance_marked_at = NOW()
                   WHERE registration_id = ?';
    $update_stmt = mysqli_prepare($conn, $update_sql);

    if (!$update_stmt) {
        return ['success' => false, 'message' => 'Unable to prepare the attendance update.'];
    }

    mysqli_stmt_bind_param($update_stmt, 'si', $attendance_status, $registration_id);
    $updated = mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);

    if (!$updated || !participant_sync_attendance_record($conn, $registration_id, $attendance_status, $user_id)) {
        return ['success' => false, 'message' => 'Unable to save attendance.'];
    }

    return ['success' => true, 'message' => 'Attendance marked as ' . participant_attendance_status_label($attendance_status) . '.'];
}

function participant_verify_owned_attendance_code($conn, $user_id, $event_id, $attendance_code)
{
    $user_id = (int) $user_id;
    $event_id = (int) $event_id;
    $attendance_code = strtoupper(trim((string) $attendance_code));

    if ($attendance_code === '') {
        return ['success' => false, 'message' => 'Please enter an attendance code.'];
    }

    $sql = 'SELECT r.registration_id, r.attendance_status
            FROM registrations r
            INNER JOIN events e ON e.event_id = r.event_id
            WHERE e.created_by = ?
            AND r.event_id = ?
            AND r.attendance_code = ?
            AND LOWER(r.registration_status) = "registered"
            LIMIT 1';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return ['success' => false, 'message' => 'Unable to verify the attendance code.'];
    }

    mysqli_stmt_bind_param($stmt, 'iis', $user_id, $event_id, $attendance_code);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $registration = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$registration) {
        return ['success' => false, 'message' => 'Attendance code not found for this event.'];
    }

    $current_status = strtolower(trim((string) ($registration['attendance_status'] ?? 'pending')));

    if ($current_status !== 'pending') {
        return ['success' => false, 'message' => 'This attendance code has already been used or marked.'];
    }

    return participant_mark_owned_registration_attendance($conn, $user_id, (int) $registration['registration_id'], 'present');
}

function participant_finalize_owned_event_attendance($conn, $user_id, $event_id)
{
    $participants = participant_fetch_owned_event_attendance($conn, $user_id, $event_id);
    $finalized_count = 0;

    foreach ($participants as $participant) {
        if (strtolower((string) ($participant['attendance_status'] ?? 'pending')) !== 'pending') {
            continue;
        }

        $result = participant_mark_owned_registration_attendance($conn, $user_id, (int) $participant['registration_id'], 'absent');

        if ($result['success']) {
            $finalized_count++;
        }
    }

    return [
        'success' => true,
        'message' => $finalized_count . ' pending participant' . ($finalized_count === 1 ? ' was' : 's were') . ' finalized as absent.',
    ];
}

function participant_update_owned_event_status($conn, $user_id, $event_id, $status)
{
    $allowed_statuses = ['closed', 'completed'];
    $status = strtolower(trim($status));

    if (!in_array($status, $allowed_statuses, true)) {
        return false;
    }

    $sql = 'UPDATE events
            SET status = ?
            WHERE event_id = ?
            AND created_by = ?';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'sii', $status, $event_id, $user_id);
    $success = mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    return $success;
}

function participant_event_location_can_be_updated($event)
{
    $event_type = strtolower(trim((string) ($event['event_type'] ?? '')));
    $event_location = strtolower(trim((string) ($event['event_location'] ?? '')));
    $event_venue = trim((string) ($event['event_venue'] ?? ''));
    $online_link = trim((string) ($event['online_link'] ?? ''));

    if ($event_location === '' || strpos($event_location, 'to be announced') !== false || strpos($event_location, 'tba') !== false) {
        return true;
    }

    if ($event_type === 'tba') {
        return true;
    }

    if ($event_type === 'online' && $online_link === '') {
        return true;
    }

    if ($event_type === 'physical' && $event_venue === '') {
        return true;
    }

    return false;
}

function participant_prepare_event_location_update_data($form_data)
{
    $location_choice = trim($form_data['event_location'] ?? 'Venue');
    $country = trim($form_data['event_country'] ?? 'Philippines');
    $city = trim($form_data['event_city'] ?? '');
    $address = trim($form_data['event_address'] ?? '');
    $venue = trim($form_data['event_venue'] ?? '');
    $online_link = trim($form_data['event_meeting_link'] ?? '');
    $online_platform = trim($form_data['event_platform'] ?? '');
    $online_platform_other = trim($form_data['event_platform_other'] ?? '');
    $errors = [];
    $event_type = 'physical';
    $event_location = '';

    if ($location_choice === 'Online event') {
        $event_type = 'online';
        $online_platform = $online_platform === 'Other' ? $online_platform_other : $online_platform;

        if ($online_link === '') {
            $errors[] = 'Online event link is required before locking this event location.';
        }

        if ($online_platform === '') {
            $errors[] = 'Online event platform is required before locking this event location.';
        }

        $event_location = trim($online_platform . ' - ' . $online_link, ' -');
        $country = $country !== '' ? $country : 'Philippines';
        $city = '';
        $address = '';
        $venue = '';
    } elseif ($location_choice === 'Venue') {
        if ($country === '') {
            $errors[] = 'Event country is required.';
        }

        if ($city === '') {
            $errors[] = 'Event city is required.';
        }

        if ($address === '') {
            $errors[] = 'Event address is required.';
        }

        if ($venue === '') {
            $errors[] = 'Event venue is required before locking this event location.';
        }

        $event_location = implode(', ', array_filter([$venue, $address, $city, $country]));
        $online_link = '';
        $online_platform = '';
    } else {
        $errors[] = 'Choose Venue or Online event and provide the missing location details.';
    }

    return [
        'success' => empty($errors),
        'errors' => $errors,
        'data' => [
            'event_type' => $event_type,
            'event_location' => $event_location,
            'event_country' => $country !== '' ? $country : 'Philippines',
            'event_province' => '',
            'event_city' => $city,
            'event_address' => $address,
            'event_venue' => $venue,
            'online_link' => $online_link,
            'online_platform' => $online_platform,
        ],
    ];
}

function participant_update_owned_event_location($conn, $user_id, $event_id, $form_data)
{
    $event = participant_fetch_owned_event_dashboard($conn, $user_id, $event_id);

    if (!$event) {
        return ['success' => false, 'errors' => ['This event was not found, or it does not belong to your account.']];
    }

    if (!participant_event_location_can_be_updated($event)) {
        return ['success' => false, 'errors' => ['This event location is already locked.']];
    }

    $prepared_location = participant_prepare_event_location_update_data($form_data);

    if (!$prepared_location['success']) {
        return ['success' => false, 'errors' => $prepared_location['errors']];
    }

    $location = $prepared_location['data'];
    $sql = 'UPDATE events
            SET event_type = ?,
                event_location = ?,
                event_country = ?,
                event_province = ?,
                event_city = ?,
                event_address = ?,
                event_venue = ?,
                online_link = ?,
                online_platform = ?
            WHERE event_id = ?
            AND created_by = ?';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return ['success' => false, 'errors' => ['Unable to update the event location.']];
    }

    mysqli_stmt_bind_param(
        $stmt,
        'sssssssssii',
        $location['event_type'],
        $location['event_location'],
        $location['event_country'],
        $location['event_province'],
        $location['event_city'],
        $location['event_address'],
        $location['event_venue'],
        $location['online_link'],
        $location['online_platform'],
        $event_id,
        $user_id
    );

    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$success) {
        return ['success' => false, 'errors' => ['Unable to save the event location.']];
    }

    return ['success' => true, 'errors' => []];
}

function participant_fetch_liked_events($conn, $user_id)
{
    $events = [];
    $event_fields = participant_event_select_fields('e');
    $sql = 'SELECT liked.liked_event_id, liked.created_at AS liked_at,
                   ' . $event_fields . ',
                   organizer.first_name AS organizer_first_name,
                   organizer.last_name AS organizer_last_name,
                   (SELECT COALESCE(SUM(active_reg.attendee_count), 0)
                    FROM registrations active_reg
                    WHERE active_reg.event_id = e.event_id
                    AND active_reg.registration_status = "registered") AS registered_count,
                   (SELECT user_reg.registration_status
                    FROM registrations user_reg
                    WHERE user_reg.event_id = e.event_id
                    AND user_reg.user_id = ?
                    LIMIT 1) AS current_user_registration_status,
                   1 AS current_user_liked
            FROM liked_events liked
            INNER JOIN events e ON e.event_id = liked.event_id
            INNER JOIN users organizer ON organizer.user_id = e.created_by
            WHERE liked.user_id = ?
            ORDER BY liked.created_at DESC';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return [];
    }

    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = participant_normalize_event_row($row);
    }

    mysqli_stmt_close($stmt);

    return $events;
}

function participant_count_liked_events($conn, $user_id)
{
    return count(participant_fetch_liked_events($conn, $user_id));
}

function participant_fetch_registration_summary($conn, $user_id)
{
    $summary = [
        'registered' => 0,
        'cancelled' => 0,
        'upcoming' => 0,
        'hosted' => 0,
        'liked' => 0,
    ];

    $sql = 'SELECT
                SUM(CASE WHEN r.registration_status = "registered" THEN 1 ELSE 0 END) AS registered_total,
                SUM(CASE WHEN r.registration_status = "cancelled" THEN 1 ELSE 0 END) AS cancelled_total,
                SUM(CASE WHEN r.registration_status = "registered" AND e.event_date >= CURDATE() THEN 1 ELSE 0 END) AS upcoming_total
            FROM registrations r
            INNER JOIN events e ON e.event_id = r.event_id
            WHERE r.user_id = ?';
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        $summary['registered'] = (int) ($row['registered_total'] ?? 0);
        $summary['cancelled'] = (int) ($row['cancelled_total'] ?? 0);
        $summary['upcoming'] = (int) ($row['upcoming_total'] ?? 0);
    }

    $hosted_sql = 'SELECT COUNT(*) AS hosted_total FROM events WHERE created_by = ?';
    $hosted_stmt = mysqli_prepare($conn, $hosted_sql);

    if ($hosted_stmt) {
        mysqli_stmt_bind_param($hosted_stmt, 'i', $user_id);
        mysqli_stmt_execute($hosted_stmt);
        $hosted_result = mysqli_stmt_get_result($hosted_stmt);
        $hosted_row = mysqli_fetch_assoc($hosted_result);
        mysqli_stmt_close($hosted_stmt);
        $summary['hosted'] = (int) ($hosted_row['hosted_total'] ?? 0);
    }

    $summary['liked'] = participant_count_liked_events($conn, $user_id);

    return $summary;
}

function participant_parse_event_date_for_database($date)
{
    $date = trim($date);

    if ($date === '') {
        return '';
    }

    $date_from_picker = DateTime::createFromFormat('Y-m-d', $date);

    if ($date_from_picker && $date_from_picker->format('Y-m-d') === $date) {
        return $date;
    }

    $date_from_wizard = DateTime::createFromFormat('m/d/Y', $date);

    if ($date_from_wizard && $date_from_wizard->format('m/d/Y') === $date) {
        return $date_from_wizard->format('Y-m-d');
    }

    return '';
}

function participant_upload_event_banner($file, $is_required = false)
{
    if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        if ($is_required) {
            return ['success' => false, 'path' => '', 'errors' => ['Event banner image is required.']];
        }

        return ['success' => true, 'path' => '', 'errors' => []];
    }

    $errors = [];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/webp'];
    $max_file_size = 10 * 1024 * 1024;
    $upload_error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($upload_error !== UPLOAD_ERR_OK) {
        return ['success' => false, 'path' => '', 'errors' => ['Event banner upload failed. Please try another image.']];
    }

    $original_name = $file['name'] ?? '';
    $temporary_path = $file['tmp_name'] ?? '';
    $file_size = (int) ($file['size'] ?? 0);
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $image_info = $temporary_path !== '' && is_file($temporary_path) ? getimagesize($temporary_path) : false;
    $mime_type = is_array($image_info) ? ($image_info['mime'] ?? '') : '';

    if (!in_array($extension, $allowed_extensions, true)) {
        $errors[] = 'Event banner must be JPG, PNG, or WEBP.';
    }

    if (!$image_info || !in_array($mime_type, $allowed_mime_types, true)) {
        $errors[] = 'Event banner must be a valid image file.';
    }

    if ($file_size <= 0 || $file_size > $max_file_size) {
        $errors[] = 'Event banner must not exceed 10MB.';
    }

    if ($temporary_path === '' || !is_uploaded_file($temporary_path)) {
        $errors[] = 'Uploaded event banner could not be verified.';
    }

    if (!empty($errors)) {
        return ['success' => false, 'path' => '', 'errors' => $errors];
    }

    $upload_directory = dirname(__DIR__) . '/assets/uploads/event-banners';

    if (!is_dir($upload_directory)) {
        mkdir($upload_directory, 0777, true);
    }

    $new_filename = 'event_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = $upload_directory . '/' . $new_filename;

    if (!move_uploaded_file($temporary_path, $destination)) {
        return ['success' => false, 'path' => '', 'errors' => ['Unable to save the uploaded event banner.']];
    }

    return ['success' => true, 'path' => 'assets/uploads/event-banners/' . $new_filename, 'errors' => []];
}

function participant_prepare_event_form_data($form_data, $files = [])
{
    $title = trim($form_data['event_name'] ?? '');
    $event_date = participant_parse_event_date_for_database($form_data['event_date'] ?? '');
    $event_time = trim($form_data['event_start_time'] ?? '');
    $event_end_time = trim($form_data['event_end_time'] ?? '');
    $tags = trim($form_data['event_tags'] ?? '');
    $location_choice = trim($form_data['event_location'] ?? 'Venue');
    $capacity = (int) ($form_data['event_capacity'] ?? 0);
    $summary = trim($form_data['event_summary'] ?? '');
    $description = trim($form_data['event_description'] ?? '');
    $visibility = trim($form_data['event_visibility_status'] ?? 'public');
    $audience = $visibility === 'private' ? trim($form_data['private_audience'] ?? '') : 'Everyone';
    $publish_later = !empty($form_data['publish_schedule']);
    $publish_date = $publish_later ? participant_parse_event_date_for_database($form_data['publish_date'] ?? '') : null;
    $publish_time = $publish_later ? trim($form_data['publish_time'] ?? '') : null;
    $country = trim($form_data['event_country'] ?? 'Philippines');
    $city = trim($form_data['event_city'] ?? '');
    $address = trim($form_data['event_address'] ?? '');
    $venue = trim($form_data['event_venue'] ?? '');
    $online_link = trim($form_data['event_meeting_link'] ?? '');
    $online_platform = trim($form_data['event_platform'] ?? '');
    $online_platform_other = trim($form_data['event_platform_other'] ?? '');
    $errors = [];

    if ($title === '') {
        $errors[] = 'Event title is required.';
    }

    if ($event_date === '') {
        $errors[] = 'A valid event date is required.';
    }

    if ($event_time === '') {
        $errors[] = 'Start time is required.';
    }

    if ($event_end_time === '') {
        $errors[] = 'End time is required.';
    }

    if ($event_time !== '' && $event_end_time !== '' && $event_time >= $event_end_time) {
        $errors[] = 'Event end time must be after the start time.';
    }

    if ($event_date !== '') {
        if ($event_date < date('Y-m-d')) {
            $errors[] = 'Event date cannot be in the past.';
        }

        $event_end_timestamp = strtotime($event_date . ' ' . ($event_end_time !== '' ? $event_end_time : ($event_time !== '' ? $event_time : '23:59:59')));

        if ($event_end_timestamp && $event_end_timestamp < time()) {
            $errors[] = 'Event schedule must not already be finished.';
        }
    }

    if ($tags === '') {
        $errors[] = 'Event tags are required.';
    }

    if ($capacity < 1) {
        $errors[] = 'Event capacity must be at least 1.';
    }

    if ($summary === '') {
        $errors[] = 'Event summary is required.';
    }

    if ($description === '') {
        $errors[] = 'Event description is required.';
    }

    $event_type = 'physical';
    $event_location = '';

    if ($location_choice === 'Online event') {
        $event_type = 'online';
        $online_platform = $online_platform === 'Other' ? $online_platform_other : $online_platform;

        if ($online_link === '') {
            $errors[] = 'Online event link is required.';
        }

        if ($online_platform === '') {
            $errors[] = 'Online event platform is required.';
        }

        $event_location = trim($online_platform . ' - ' . $online_link, ' -');
    } elseif ($location_choice === 'To be announced') {
        $event_type = 'tba';
        $event_location = 'Location to be announced';
    } else {
        if ($country === '') {
            $errors[] = 'Event country is required.';
        }

        if ($city === '') {
            $errors[] = 'Event city is required.';
        }

        if ($address === '') {
            $errors[] = 'Event address is required.';
        }

        if ($venue === '') {
            $errors[] = 'Event venue is required.';
        }

        $event_location = implode(', ', array_filter([$venue, $address, $city, $country]));
    }

    if (!in_array($visibility, ['private', 'public'], true)) {
        $visibility = 'public';
    }

    if ($visibility === 'private' && $audience === '') {
        $errors[] = 'Choose an audience for the private event.';
    }

    if ($publish_later && $publish_date === '') {
        $errors[] = 'A valid publish date is required when scheduling for later.';
    }

    if ($publish_later && $publish_time === '') {
        $errors[] = 'Publish time is required when scheduling for later.';
    }

    if ($publish_later && $publish_date !== '' && $event_date !== '') {
        $publish_timestamp = strtotime($publish_date . ' ' . ($publish_time !== '' ? $publish_time : '00:00:00'));
        $event_start_timestamp = strtotime($event_date . ' ' . ($event_time !== '' ? $event_time : '00:00:00'));

        if ($publish_timestamp && $event_start_timestamp && $publish_timestamp > $event_start_timestamp) {
            $errors[] = 'Publish schedule cannot be after the event starts.';
        }
    }

    $banner_result = participant_upload_event_banner($files['event_banner'] ?? null, true);

    if (!$banner_result['success']) {
        $errors = array_merge($errors, $banner_result['errors']);
    }

    $category = participant_event_category($title . ' ' . $tags, $summary . ' ' . $description);

    return [
        'success' => empty($errors),
        'errors' => $errors,
        'data' => [
            'event_title' => $title,
            'event_summary' => $summary,
            'event_description' => $description,
            'event_tags' => $tags,
            'event_category' => $category,
            'event_type' => $event_type,
            'event_location' => $event_location,
            'event_country' => $country !== '' ? $country : 'Philippines',
            'event_province' => '',
            'event_city' => $city,
            'event_address' => $address,
            'event_venue' => $venue,
            'online_link' => $online_link,
            'online_platform' => $online_platform,
            'event_date' => $event_date,
            'event_time' => $event_time,
            'event_end_time' => $event_end_time,
            'capacity' => $capacity,
            'banner_image' => $banner_result['path'],
            'visibility' => $visibility,
            'audience' => $audience,
            'private_access_key' => null,
            'publish_date' => $publish_date,
            'publish_time' => $publish_time,
            'status' => 'pending',
        ],
    ];
}

function participant_create_event($conn, $user_id, $form_data, $files = [])
{
    $prepared_event = participant_prepare_event_form_data($form_data, $files);

    if (!$prepared_event['success']) {
        return ['success' => false, 'errors' => $prepared_event['errors'], 'event_id' => 0];
    }

    $event = $prepared_event['data'];
    $event['private_access_key'] = $event['visibility'] === 'private' ? participant_generate_private_access_key($conn) : null;
    $sql = 'INSERT INTO events
            (event_title, event_summary, event_description, event_tags, event_category, event_type,
             event_location, event_country, event_province, event_city, event_address, event_venue,
             online_link, online_platform, event_date, event_time, event_end_time, capacity,
             banner_image, visibility, audience, private_access_key, publish_date, publish_time, status, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return ['success' => false, 'errors' => ['Unable to prepare the event for saving.'], 'event_id' => 0];
    }

    mysqli_stmt_bind_param(
        $stmt,
        'sssssssssssssssssisssssssi',
        $event['event_title'],
        $event['event_summary'],
        $event['event_description'],
        $event['event_tags'],
        $event['event_category'],
        $event['event_type'],
        $event['event_location'],
        $event['event_country'],
        $event['event_province'],
        $event['event_city'],
        $event['event_address'],
        $event['event_venue'],
        $event['online_link'],
        $event['online_platform'],
        $event['event_date'],
        $event['event_time'],
        $event['event_end_time'],
        $event['capacity'],
        $event['banner_image'],
        $event['visibility'],
        $event['audience'],
        $event['private_access_key'],
        $event['publish_date'],
        $event['publish_time'],
        $event['status'],
        $user_id
    );
    mysqli_stmt_execute($stmt);
    $created = mysqli_affected_rows($conn) > 0;
    $event_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    if (!$created) {
        return ['success' => false, 'errors' => ['Unable to save the event.'], 'event_id' => 0];
    }

    return ['success' => true, 'errors' => [], 'event_id' => (int) $event_id];
}

function participant_prepare_registration_form_data($form_data)
{
    $full_name = trim($form_data['full_name'] ?? '');
    $email = trim($form_data['email'] ?? '');
    $contact_number = trim($form_data['contact_number'] ?? '');
    $attendee_count = (int) ($form_data['attendees'] ?? 1);
    $special_notes = trim($form_data['notes'] ?? '');
    $confirmed = !empty($form_data['registration_confirm']);
    $errors = [];

    if ($full_name === '') {
        $errors[] = 'Full name is required.';
    }

    if ($email === '') {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid registration email address.';
    }

    if ($contact_number === '') {
        $errors[] = 'Contact number is required.';
    }

    if ($attendee_count < 1) {
        $errors[] = 'Number of attendees must be at least 1.';
    }

    if (!$confirmed) {
        $errors[] = 'Please confirm that your registration information is correct.';
    }

    return [
        'success' => empty($errors),
        'errors' => $errors,
        'data' => [
            'full_name' => $full_name,
            'email' => $email,
            'contact_number' => $contact_number,
            'attendee_count' => $attendee_count,
            'special_notes' => $special_notes,
        ],
    ];
}

function participant_register_for_event($conn, $user_id, $event_id, $form_data = [])
{
    $prepared_form = participant_prepare_registration_form_data($form_data);

    if (!$prepared_form['success']) {
        return ['success' => false, 'message' => implode(' ', $prepared_form['errors'])];
    }

    $registration_data = $prepared_form['data'];
    mysqli_begin_transaction($conn);

    $event_fields = participant_event_select_fields('e');
    $sql = 'SELECT ' . $event_fields . ',
                   (SELECT COALESCE(SUM(active_reg.attendee_count), 0)
                    FROM registrations active_reg
                    WHERE active_reg.event_id = e.event_id
                    AND active_reg.registration_status = "registered") AS registered_count,
                   (SELECT user_reg.registration_status
                    FROM registrations user_reg
                    WHERE user_reg.event_id = e.event_id
                    AND user_reg.user_id = ?
                    LIMIT 1) AS current_user_registration_status,
                   0 AS current_user_liked
            FROM events e
            WHERE e.event_id = ?
            LIMIT 1';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_rollback($conn);
        return ['success' => false, 'message' => 'Unable to check the selected event.'];
    }

    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $event_id);
    mysqli_stmt_execute($stmt);
    $event_result = mysqli_stmt_get_result($stmt);
    $event = mysqli_fetch_assoc($event_result);
    mysqli_stmt_close($stmt);

    if (!$event) {
        mysqli_rollback($conn);
        return ['success' => false, 'message' => 'The selected event was not found.'];
    }

    $event = participant_normalize_event_row($event);

    if (!participant_event_registration_is_open($event, $user_id)) {
        mysqli_rollback($conn);
        return ['success' => false, 'message' => 'This event is not accepting registrations.'];
    }

    $remaining_slots = (int) $event['capacity'] - (int) $event['registered_count'];

    if ($remaining_slots <= 0) {
        mysqli_rollback($conn);
        return ['success' => false, 'message' => 'This event is already full.'];
    }

    if ($registration_data['attendee_count'] > $remaining_slots) {
        mysqli_rollback($conn);
        return ['success' => false, 'message' => 'Not enough slots are available for the number of attendees.'];
    }

    $existing_sql = 'SELECT registration_id, registration_status, attendance_code
                     FROM registrations
                     WHERE user_id = ? AND event_id = ?
                     LIMIT 1';
    $existing_stmt = mysqli_prepare($conn, $existing_sql);

    if (!$existing_stmt) {
        mysqli_rollback($conn);
        return ['success' => false, 'message' => 'Unable to check your current registration.'];
    }

    mysqli_stmt_bind_param($existing_stmt, 'ii', $user_id, $event_id);
    mysqli_stmt_execute($existing_stmt);
    $existing_result = mysqli_stmt_get_result($existing_stmt);
    $existing = mysqli_fetch_assoc($existing_result);
    mysqli_stmt_close($existing_stmt);

    if ($existing && $existing['registration_status'] === 'registered') {
        mysqli_rollback($conn);
        return ['success' => false, 'message' => 'You are already registered for this event.'];
    }

    if ($existing) {
        $attendance_code = trim((string) ($existing['attendance_code'] ?? ''));

        if ($attendance_code === '') {
            $attendance_code = participant_generate_attendance_code($conn);
        }

        $update_sql = 'UPDATE registrations
                       SET registration_full_name = ?,
                           registration_email = ?,
                           contact_number = ?,
                           attendee_count = ?,
                           special_notes = ?,
                           registration_status = "registered",
                           attendance_code = ?,
                           attendance_status = "pending",
                           attendance_marked_at = NULL,
                           registered_at = NOW()
                       WHERE registration_id = ? AND user_id = ?';
        $update_stmt = mysqli_prepare($conn, $update_sql);

        if (!$update_stmt) {
            mysqli_rollback($conn);
            return ['success' => false, 'message' => 'Unable to restore your registration.'];
        }

        $registration_id = (int) $existing['registration_id'];
        mysqli_stmt_bind_param(
            $update_stmt,
            'sssissii',
            $registration_data['full_name'],
            $registration_data['email'],
            $registration_data['contact_number'],
            $registration_data['attendee_count'],
            $registration_data['special_notes'],
            $attendance_code,
            $registration_id,
            $user_id
        );
        mysqli_stmt_execute($update_stmt);
        $updated = mysqli_affected_rows($conn) >= 0;
        mysqli_stmt_close($update_stmt);

        if ($updated) {
            $attendance_sql = 'DELETE FROM attendance WHERE registration_id = ?';
            $attendance_stmt = mysqli_prepare($conn, $attendance_sql);

            if ($attendance_stmt) {
                mysqli_stmt_bind_param($attendance_stmt, 'i', $registration_id);
                mysqli_stmt_execute($attendance_stmt);
                mysqli_stmt_close($attendance_stmt);
            }

            mysqli_commit($conn);
            return ['success' => true, 'message' => participant_registration_success_message($event, $attendance_code, true)];
        }

        mysqli_rollback($conn);
        return ['success' => false, 'message' => 'Unable to restore your registration.'];
    }

    $attendance_code = participant_generate_attendance_code($conn);
    $insert_sql = 'INSERT INTO registrations
                   (user_id, event_id, registration_full_name, registration_email, contact_number, attendee_count, special_notes, registration_status, attendance_code, attendance_status)
                   VALUES (?, ?, ?, ?, ?, ?, ?, "registered", ?, "pending")';
    $insert_stmt = mysqli_prepare($conn, $insert_sql);

    if (!$insert_stmt) {
        mysqli_rollback($conn);
        return ['success' => false, 'message' => 'Unable to create your registration.'];
    }

    mysqli_stmt_bind_param(
        $insert_stmt,
        'iisssiss',
        $user_id,
        $event_id,
        $registration_data['full_name'],
        $registration_data['email'],
        $registration_data['contact_number'],
        $registration_data['attendee_count'],
        $registration_data['special_notes'],
        $attendance_code
    );
    mysqli_stmt_execute($insert_stmt);
    $inserted = mysqli_affected_rows($conn) > 0;
    mysqli_stmt_close($insert_stmt);

    if ($inserted) {
        mysqli_commit($conn);
        return ['success' => true, 'message' => participant_registration_success_message($event, $attendance_code)];
    }

    mysqli_rollback($conn);
    return ['success' => false, 'message' => 'Unable to complete registration.'];
}

function participant_cancel_registration($conn, $user_id, $registration_id)
{
    mysqli_begin_transaction($conn);

    $sql = 'UPDATE registrations
            SET registration_status = "cancelled",
                attendance_status = "pending",
                attendance_marked_at = NULL
            WHERE registration_id = ?
            AND user_id = ?
            AND registration_status = "registered"';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_rollback($conn);
        return ['success' => false, 'message' => 'Unable to cancel the registration.'];
    }

    mysqli_stmt_bind_param($stmt, 'ii', $registration_id, $user_id);
    mysqli_stmt_execute($stmt);
    $cancelled = mysqli_affected_rows($conn) > 0;
    mysqli_stmt_close($stmt);

    if ($cancelled) {
        $attendance_sql = 'DELETE FROM attendance WHERE registration_id = ?';
        $attendance_stmt = mysqli_prepare($conn, $attendance_sql);

        if ($attendance_stmt) {
            mysqli_stmt_bind_param($attendance_stmt, 'i', $registration_id);
            mysqli_stmt_execute($attendance_stmt);
            mysqli_stmt_close($attendance_stmt);
        }

        mysqli_commit($conn);
        return ['success' => true, 'message' => 'Registration cancelled successfully.'];
    }

    mysqli_rollback($conn);
    return ['success' => false, 'message' => 'Only your active registrations can be cancelled.'];
}

function participant_toggle_liked_event($conn, $user_id, $event_id)
{
    $event = participant_fetch_event_details($conn, $user_id, $event_id);

    if (!$event) {
        return ['success' => false, 'message' => 'The selected event was not found or is not available.'];
    }

    $existing_sql = 'SELECT liked_event_id FROM liked_events WHERE user_id = ? AND event_id = ? LIMIT 1';
    $existing_stmt = mysqli_prepare($conn, $existing_sql);

    if (!$existing_stmt) {
        return ['success' => false, 'message' => 'Unable to check liked event status.'];
    }

    mysqli_stmt_bind_param($existing_stmt, 'ii', $user_id, $event_id);
    mysqli_stmt_execute($existing_stmt);
    $existing_result = mysqli_stmt_get_result($existing_stmt);
    $existing = mysqli_fetch_assoc($existing_result);
    mysqli_stmt_close($existing_stmt);

    if ($existing) {
        $delete_sql = 'DELETE FROM liked_events WHERE liked_event_id = ? AND user_id = ?';
        $delete_stmt = mysqli_prepare($conn, $delete_sql);

        if (!$delete_stmt) {
            return ['success' => false, 'message' => 'Unable to remove liked event.'];
        }

        $liked_event_id = (int) $existing['liked_event_id'];
        mysqli_stmt_bind_param($delete_stmt, 'ii', $liked_event_id, $user_id);
        mysqli_stmt_execute($delete_stmt);
        $removed = mysqli_affected_rows($conn) > 0;
        mysqli_stmt_close($delete_stmt);

        return [
            'success' => $removed,
            'message' => $removed ? 'Event removed from Liked Events.' : 'Unable to remove liked event.',
        ];
    }

    $insert_sql = 'INSERT INTO liked_events (user_id, event_id) VALUES (?, ?)';
    $insert_stmt = mysqli_prepare($conn, $insert_sql);

    if (!$insert_stmt) {
        return ['success' => false, 'message' => 'Unable to like this event.'];
    }

    mysqli_stmt_bind_param($insert_stmt, 'ii', $user_id, $event_id);
    mysqli_stmt_execute($insert_stmt);
    $liked = mysqli_affected_rows($conn) > 0;
    mysqli_stmt_close($insert_stmt);

    return [
        'success' => $liked,
        'message' => $liked ? 'Event added to Liked Events.' : 'Unable to like this event.',
    ];
}

function participant_handle_registration_post($conn)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['participant_action'])) {
        return;
    }

    $user_id = participant_current_user_id();
    $action = $_POST['participant_action'];

    if ($action === 'register_event') {
        $event_id = (int) ($_POST['event_id'] ?? 0);
        $result = participant_register_for_event($conn, $user_id, $event_id, $_POST);
        participant_flash($result['success'] ? 'success' : 'error', $result['message']);
        participant_redirect_back();
    }

    if ($action === 'toggle_like') {
        $event_id = (int) ($_POST['event_id'] ?? 0);
        $result = participant_toggle_liked_event($conn, $user_id, $event_id);
        participant_flash($result['success'] ? 'success' : 'error', $result['message']);
        participant_redirect_back();
    }

    if ($action === 'cancel_registration') {
        $registration_id = (int) ($_POST['registration_id'] ?? 0);
        $result = participant_cancel_registration($conn, $user_id, $registration_id);
        participant_flash($result['success'] ? 'success' : 'error', $result['message']);
        participant_redirect_back();
    }
}

function participant_upload_profile_picture($file, $current_picture = '')
{
    if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'path' => $current_picture, 'errors' => []];
    }

    $errors = [];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $max_file_size = 2 * 1024 * 1024;
    $upload_error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($upload_error !== UPLOAD_ERR_OK) {
        return ['success' => false, 'path' => $current_picture, 'errors' => ['Profile picture upload failed. Please try another image.']];
    }

    $original_name = $file['name'] ?? '';
    $temporary_path = $file['tmp_name'] ?? '';
    $file_size = (int) ($file['size'] ?? 0);
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowed_extensions, true)) {
        $errors[] = 'Profile picture must be JPG, PNG, GIF, or WEBP.';
    }

    if ($file_size <= 0 || $file_size > $max_file_size) {
        $errors[] = 'Profile picture must not exceed 2MB.';
    }

    if ($temporary_path === '' || !is_uploaded_file($temporary_path)) {
        $errors[] = 'Uploaded profile picture could not be verified.';
    }

    if (!empty($errors)) {
        return ['success' => false, 'path' => $current_picture, 'errors' => $errors];
    }

    $upload_directory = dirname(__DIR__) . '/assets/uploads/profile-pictures';

    if (!is_dir($upload_directory)) {
        mkdir($upload_directory, 0777, true);
    }

    $new_filename = 'profile_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = $upload_directory . '/' . $new_filename;

    if (!move_uploaded_file($temporary_path, $destination)) {
        return ['success' => false, 'path' => $current_picture, 'errors' => ['Unable to save the uploaded profile picture.']];
    }

    $new_path = 'assets/uploads/profile-pictures/' . $new_filename;
    $old_path = dirname(__DIR__) . '/' . ltrim($current_picture, '/');

    if ($current_picture !== '' && strpos(realpath(dirname($old_path)) ?: '', realpath($upload_directory) ?: '') === 0 && is_file($old_path)) {
        unlink($old_path);
    }

    return ['success' => true, 'path' => $new_path, 'errors' => []];
}

function participant_update_profile($conn, $user_id, $first_name, $last_name, $email, $profile_picture_file = null, $role = 'participant')
{
    $first_name = trim($first_name);
    $last_name = trim($last_name);
    $email = trim($email);
    $errors = [];

    if ($first_name === '') {
        $errors[] = 'First name is required.';
    }

    if ($last_name === '') {
        $errors[] = 'Last name is required.';
    }

    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $duplicate_sql = 'SELECT user_id FROM users WHERE email = ? AND user_id != ? LIMIT 1';
    $duplicate_stmt = mysqli_prepare($conn, $duplicate_sql);

    if (!$duplicate_stmt) {
        return ['success' => false, 'errors' => ['Unable to check email availability.']];
    }

    mysqli_stmt_bind_param($duplicate_stmt, 'si', $email, $user_id);
    mysqli_stmt_execute($duplicate_stmt);
    $duplicate_result = mysqli_stmt_get_result($duplicate_stmt);
    $duplicate = mysqli_fetch_assoc($duplicate_result);
    mysqli_stmt_close($duplicate_stmt);

    if ($duplicate) {
        return ['success' => false, 'errors' => ['This email address is already used by another account.']];
    }

    $current_user = participant_fetch_profile($conn, $user_id, $role);

    if (!$current_user) {
        return ['success' => false, 'errors' => [ucwords($role) . ' account was not found.']];
    }

    $upload_result = participant_upload_profile_picture($profile_picture_file, $current_user['profile_picture'] ?? '');

    if (!$upload_result['success']) {
        return ['success' => false, 'errors' => $upload_result['errors']];
    }

    $profile_picture = $upload_result['path'];
    $update_sql = 'UPDATE users SET first_name = ?, last_name = ?, email = ?, profile_picture = ? WHERE user_id = ? AND role = ?';
    $update_stmt = mysqli_prepare($conn, $update_sql);

    if (!$update_stmt) {
        return ['success' => false, 'errors' => ['Unable to update your profile.']];
    }

    mysqli_stmt_bind_param($update_stmt, 'ssssis', $first_name, $last_name, $email, $profile_picture, $user_id, $role);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);

    $_SESSION['user_name'] = trim($first_name . ' ' . $last_name);
    $_SESSION['user_email'] = $email;

    return ['success' => true, 'errors' => []];
}

function participant_change_password($conn, $user_id, $current_password, $new_password, $confirm_password, $role = 'participant')
{
    $errors = [];

    if ($current_password === '') {
        $errors[] = 'Current password is required.';
    }

    if ($new_password === '') {
        $errors[] = 'New password is required.';
    } elseif (strlen($new_password) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    }

    if ($new_password !== $confirm_password) {
        $errors[] = 'New password and confirmation do not match.';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $sql = 'SELECT password FROM users WHERE user_id = ? AND role = ? LIMIT 1';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return ['success' => false, 'errors' => ['Unable to verify your current password.']];
    }

    mysqli_stmt_bind_param($stmt, 'is', $user_id, $role);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$user || !password_verify($current_password, $user['password'])) {
        return ['success' => false, 'errors' => ['Current password is incorrect.']];
    }

    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $update_sql = 'UPDATE users
                   SET password = ?, remember_token_hash = NULL, remember_token_expires_at = NULL
                   WHERE user_id = ? AND role = ?';
    $update_stmt = mysqli_prepare($conn, $update_sql);

    if (!$update_stmt) {
        return ['success' => false, 'errors' => ['Unable to update your password.']];
    }

    mysqli_stmt_bind_param($update_stmt, 'sis', $password_hash, $user_id, $role);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);
    delete_remember_cookie();

    return ['success' => true, 'errors' => []];
}

function participant_render_like_button($event)
{
    $event_id = (int) ($event['event_id'] ?? 0);
    $is_liked = !empty($event['current_user_liked']);
    ?>
    <form class="event-like-form" action="" method="post" data-like-form>
        <input type="hidden" name="participant_action" value="toggle_like">
        <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
        <button class="event-heart<?php echo $is_liked ? ' liked' : ''; ?>" type="submit" aria-label="<?php echo $is_liked ? 'Remove liked event' : 'Like event'; ?>" aria-pressed="<?php echo $is_liked ? 'true' : 'false'; ?>" data-like-button>
            <?php echo $is_liked ? '&#9829;' : '&#9825;'; ?>
        </button>
    </form>
    <?php
}

function participant_render_event_action($event, $mode = 'browse')
{
    if ($mode === 'registered') {
        if (($event['registration_status'] ?? '') === 'registered') {
            ?>
            <form class="event-action-form" action="" method="post">
                <input type="hidden" name="participant_action" value="cancel_registration">
                <input type="hidden" name="registration_id" value="<?php echo (int) $event['registration_id']; ?>">
                <button class="event-register event-register-danger" type="submit">Cancel</button>
            </form>
            <?php
        } else {
            ?>
            <span class="event-register event-register-static">Cancelled</span>
            <?php
        }

        return;
    }

    if (($event['current_user_registration_status'] ?? '') === 'registered') {
        ?>
        <span class="event-register event-register-static">Registered</span>
        <?php
        return;
    }

    if (!participant_event_registration_is_open($event, participant_current_user_id()) || (int) ($event['remaining_slots'] ?? 0) <= 0) {
        ?>
        <span class="event-register event-register-static">Unavailable</span>
        <?php
        return;
    }

    ?>
    <button class="event-register" type="button" data-event-register data-registration-event-id="<?php echo (int) $event['event_id']; ?>" data-registration-price="Free Registration">Register</button>
    <?php
}

function participant_render_event_card($event, $mode = 'browse')
{
    $event_id = (int) ($event['event_id'] ?? 0);
    $title = $event['event_title'] ?? 'Untitled Event';
    $location = $event['event_location'] ?? 'Event location';
    $date_time = $event['date_time'] ?? 'Event date';
    $filter = $event['time_filter'] ?? 'all';
    $location_type = $event['location_type'] ?? participant_event_location_type($location);
    $category = $event['category'] ?? 'community';
    $status_badge = $event['status_badge'] ?? '';
    $registration_label = participant_registration_label($event);
    $details_href = 'event-details.php?event=' . $event_id;

    if ($mode === 'registered') {
        $registration_label = ucwords($event['registration_status'] ?? 'registered');
    }
    ?>
    <article class="event-card" role="link" tabindex="0" data-event-href="<?php echo htmlspecialchars($details_href, ENT_QUOTES, 'UTF-8'); ?>" data-event-filter="<?php echo htmlspecialchars($filter, ENT_QUOTES, 'UTF-8'); ?>" data-event-category="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>" data-event-location-type="<?php echo htmlspecialchars($location_type, ENT_QUOTES, 'UTF-8'); ?>" data-event-country="<?php echo htmlspecialchars($event['event_country'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"<?php echo isset($event['registration_status']) ? ' data-registration-status="' . htmlspecialchars($event['registration_status'], ENT_QUOTES, 'UTF-8') . '"' : ''; ?> data-event-id="event-<?php echo $event_id; ?>" data-registration-event data-event-title="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>" data-event-date-time="<?php echo htmlspecialchars($date_time, ENT_QUOTES, 'UTF-8'); ?>" data-event-location="<?php echo htmlspecialchars($location, ENT_QUOTES, 'UTF-8'); ?>">
        <?php participant_render_event_image($event); ?>

        <div class="event-card-top">
            <div class="event-badges" aria-label="Event badges">
                <span class="event-badge">Free</span>
                <span class="event-badge"><?php echo htmlspecialchars($event['event_type_label'] ?? participant_event_type_label($location_type), ENT_QUOTES, 'UTF-8'); ?></span>
                <?php if ($status_badge !== ''): ?>
                    <span class="event-badge"><?php echo htmlspecialchars($status_badge, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
            <?php if ($mode === 'browse'): ?>
                <?php participant_render_like_button($event); ?>
            <?php endif; ?>
        </div>

        <div class="event-meta">
            <div class="event-body">
                <p class="event-time"><?php echo htmlspecialchars($date_time, ENT_QUOTES, 'UTF-8'); ?></p>
                <h3><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="event-location">
                    <span class="icon icon-location" aria-hidden="true"></span>
                    <?php echo htmlspecialchars($location, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            </div>

            <div class="event-bottom-row">
                <div class="event-ticket-meta">
                    <p class="event-registration-label"><?php echo htmlspecialchars($registration_label, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php if ($mode === 'registered' && !empty($event['attendance_code'])): ?>
                        <small>Attendance</small>
                    <?php endif; ?>
                </div>
                <div class="event-card-actions<?php echo $mode === 'registered' && !empty($event['attendance_code']) ? ' event-card-actions-stacked' : ''; ?>">
                    <?php participant_render_event_action($event, $mode); ?>
                    <?php if ($mode === 'registered' && !empty($event['attendance_code'])): ?>
                        <button class="event-register attendance-key-toggle" type="button" data-attendance-toggle data-attendance-code="<?php echo htmlspecialchars($event['attendance_code'], ENT_QUOTES, 'UTF-8'); ?>" aria-pressed="false">
                            <span>Key</span>
                            <strong data-attendance-value>&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</strong>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </article>
    <?php
}

function participant_render_guest_event_card($event, $base_path = '')
{
    $event_id = (int) ($event['event_id'] ?? 0);
    $title = $event['event_title'] ?? 'Untitled Event';
    $location = $event['event_location'] ?? 'Event location';
    $date_time = $event['date_time'] ?? 'Event date';
    $filter = $event['time_filter'] ?? 'all';
    $location_type = $event['location_type'] ?? participant_event_location_type($location);
    $category = $event['category'] ?? 'community';
    $status_badge = $event['status_badge'] ?? '';
    $details_href = $base_path . 'participant/event-details.php?event=' . $event_id;
    ?>
    <article class="event-card" role="link" tabindex="0" data-event-href="<?php echo htmlspecialchars($details_href, ENT_QUOTES, 'UTF-8'); ?>" data-event-filter="<?php echo htmlspecialchars($filter, ENT_QUOTES, 'UTF-8'); ?>" data-event-category="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>" data-event-location-type="<?php echo htmlspecialchars($location_type, ENT_QUOTES, 'UTF-8'); ?>" data-event-country="<?php echo htmlspecialchars($event['event_country'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-event-id="landing-event-<?php echo $event_id; ?>" data-event-title="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>" data-event-date-time="<?php echo htmlspecialchars($date_time, ENT_QUOTES, 'UTF-8'); ?>" data-event-location="<?php echo htmlspecialchars($location, ENT_QUOTES, 'UTF-8'); ?>">
        <?php participant_render_event_image($event, $base_path); ?>

        <div class="event-card-top">
            <div class="event-badges" aria-label="Event badges">
                <span class="event-badge">Free</span>
                <span class="event-badge"><?php echo htmlspecialchars($event['event_type_label'] ?? participant_event_type_label($location_type), ENT_QUOTES, 'UTF-8'); ?></span>
                <?php if ($status_badge !== ''): ?>
                    <span class="event-badge"><?php echo htmlspecialchars($status_badge, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
            <button class="event-heart" type="button" aria-label="Like event" data-auth-required-like>&#9825;</button>
        </div>

        <div class="event-meta">
            <div class="event-body">
                <p class="event-time"><?php echo htmlspecialchars($date_time, ENT_QUOTES, 'UTF-8'); ?></p>
                <h3><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="event-location">
                    <span class="icon icon-location" aria-hidden="true"></span>
                    <?php echo htmlspecialchars($location, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            </div>

            <div class="event-bottom-row">
                <p class="event-registration-label">Free Registration</p>
                <button class="event-register" type="button" data-auth-required-register>Register</button>
            </div>
        </div>
    </article>
    <?php
}

function participant_render_empty_state($title, $message, $link_href = '', $link_text = '')
{
    ?>
    <div class="city-empty-state participant-empty-state">
        <span class="icon icon-ticket" aria-hidden="true"></span>
        <h2><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h2>
        <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php if ($link_href !== '' && $link_text !== ''): ?>
            <a class="button button-primary" href="<?php echo htmlspecialchars($link_href, ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($link_text, ENT_QUOTES, 'UTF-8'); ?>
            </a>
        <?php endif; ?>
    </div>
    <?php
}

function participant_render_feedback($success_message, $error_message, $errors = [])
{
    if ($success_message !== '') {
        ?>
        <div class="participant-feedback participant-feedback-success" role="status">
            <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php
    }

    if ($error_message !== '' || !empty($errors)) {
        ?>
        <div class="participant-feedback participant-feedback-error" role="alert">
            <?php if ($error_message !== ''): ?>
                <p><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php
    }
}
