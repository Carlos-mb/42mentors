<?php
// Idiomas de la web: español e inglés. El texto en español es la clave: __('Proyectos') → «Projects» en inglés.
// Las traducciones están en src/lang/en.php; si falta una, se muestra el español. Al añadir o cambiar un texto, actualízalas.
// La política de privacidad y el consentimiento tienen una plantilla por idioma (src/views/privacy.*.php y consent.*.php).
declare(strict_types=1);

const LANGS = ['es', 'en'];
/** Cookie con el idioma elegido en el selector de la barra superior (solo guarda «es» o «en»). */
const LANG_COOKIE = 'lang';
const LANG_COOKIE_DAYS = 365;

/** Idioma de la página: el elegido en el selector (cookie) o, si no, el del navegador. */
function current_lang(): string
{
    static $lang = null;
    if ($lang === null) {
        $cookie = $_COOKIE[LANG_COOKIE] ?? '';
        $lang = is_string($cookie) && in_array($cookie, LANGS, true)
            ? $cookie
            : browser_lang((string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    }
    return $lang;
}

/** Idioma de la web que el navegador prefiere (Accept-Language, con sus pesos q); inglés si no pide ninguno de los dos. */
function browser_lang(string $header): string
{
    $best = 'en';
    $bestQ = 0.0;
    foreach (explode(',', $header) as $part) {
        $params = explode(';', $part);
        $code = strtolower(substr(trim($params[0]), 0, 2));
        $q = 1.0;
        foreach (array_slice($params, 1) as $param) {
            if (preg_match('/^\s*q=([0-9.]+)\s*$/', $param, $m)) {
                $q = (float) $m[1];
            }
        }
        // Con el mismo peso gana el primero de la lista
        if (in_array($code, LANGS, true) && $q > $bestQ) {
            $best = $code;
            $bestQ = $q;
        }
    }
    return $best;
}

/**
 * Selector de idioma: ?lang=xx guarda la cookie y vuelve a la misma página sin ese parámetro
 * (conserva los demás, como mentor.php?login=…). Se llama desde bootstrap.php, antes de la lógica de cada página.
 */
function switch_lang(bool $secure): void
{
    if (!isset($_GET['lang']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
        return;
    }
    if (is_string($_GET['lang']) && in_array($_GET['lang'], LANGS, true)) {
        setcookie(LANG_COOKIE, $_GET['lang'], [
            'expires'  => time() + LANG_COOKIE_DAYS * 86400,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    $params = $_GET;
    unset($params['lang']);
    header('Location: ' . basename($_SERVER['SCRIPT_NAME']) . ($params ? '?' . http_build_query($params) : ''));
    exit;
}

/** Enlace del selector: la página actual en otro idioma. */
function lang_url(string $lang): string
{
    $page = basename($_SERVER['SCRIPT_NAME']);
    // La vuelta del login no se puede repetir, y el código de invitación (?c=) nunca se copia a un enlace
    if ($page === 'callback.php') {
        return 'index.php?lang=' . $lang;
    }
    $params = $_GET;
    unset($params['lang'], $params['c']);
    return $page . '?' . http_build_query(['lang' => $lang] + $params);
}

/** Traduce un texto en español al idioma de la página. {nombre} se sustituye por $vars['nombre']. Devuelve texto plano. */
function __(string $text, array $vars = []): string
{
    static $en = null;
    if (current_lang() === 'en') {
        $en ??= require ROOT_DIR . '/src/lang/en.php';
        $text = $en[$text] ?? $text;
    }
    $replace = [];
    foreach ($vars as $key => $value) {
        $replace['{' . $key . '}'] = (string) $value;
    }
    return strtr($text, $replace);
}

/**
 * Como __(), pero devuelve HTML: el texto y $vars se escapan, y {nombre} de $html se inserta tal cual
 * (para enlaces o <strong> dentro de una frase; lo que va en $html tiene que estar ya escapado).
 */
function _h(string $text, array $vars = [], array $html = []): string
{
    $replace = [];
    foreach ($html as $key => $value) {
        $replace['{' . $key . '}'] = $value;
    }
    return strtr(e(__($text, $vars)), $replace);
}

/** Fecha ISO (2026-10-19) → «19 de octubre de 2026» / «19 October 2026». */
function long_date(string $date): string
{
    $parsed = new DateTimeImmutable($date);
    $month = (int) $parsed->format('n') - 1;
    if (current_lang() === 'en') {
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        return $parsed->format('j') . ' ' . $months[$month] . ' ' . $parsed->format('Y');
    }
    $months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    return $parsed->format('j') . ' de ' . $months[$month] . ' de ' . $parsed->format('Y');
}
