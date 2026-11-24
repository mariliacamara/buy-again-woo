<?php
/**
 * Definições: valores de partida, leitura, validação e destinos.
 * wp eval-file .../tests/test-settings.php <loja>
 */

use Marilia\BuyAgainWoo\Settings;

require __DIR__ . '/bootstrap.php';

$original = get_option('buy_again_woo_settings', null);
$paginas = array();
register_shutdown_function(function () use ($original, &$paginas) {
    if ($original === null) delete_option('buy_again_woo_settings'); else update_option('buy_again_woo_settings', $original);
    foreach ($paginas as $p) wp_delete_post($p, true);
});

cl_assert(class_exists(Settings::class), 'classe Settings existe');

$d = Settings::defaults();
$esperado = array(
    'order_button_text'    => 'Comprar Novamente',
    'modal_enabled'        => true,
    'modal_only_with_orders' => true,
    'modal_title'          => 'Olá, {nome}',
    'modal_message'        => 'Deseja aceder ao seu histórico de encomendas e refazer uma compra?',
    'modal_primary_text'   => 'Histórico de encomendas',
    'modal_secondary_text' => 'Continuar a compra',
    'modal_secondary_type' => 'home',
    'modal_secondary_page' => 0,
    'modal_secondary_url'  => '',
    'cart_enabled'         => true,
    'cart_text'            => 'Continuar a Comprar',
    'cart_type'            => 'home',
    'cart_page'            => 0,
    'cart_url'             => '',
    'color_primary'        => '#37bdd6',
    'color_primary_hover'  => '#2aa8c0',
    'color_secondary'      => '#081d7a',
    'color_text'           => '#081d7a',
);
ksort($d); ksort($esperado);
cl_assert($d === $esperado, 'valores de partida iguais aos de hoje' . ($d === $esperado ? '' : ': ' . wp_json_encode(array_diff_assoc(array_map('strval', $d), array_map('strval', $esperado)))));

delete_option(Settings::OPTION);
$g = Settings::get(); ksort($g);
cl_assert($g === $d, 'sem opção gravada: get() devolve os valores de partida');

update_option(Settings::OPTION, array('modal_title' => 'X', 'lixo' => 1));
$g = Settings::get();
cl_assert($g['modal_title'] === 'X' && !array_key_exists('lixo', $g) && $g['cart_text'] === 'Continuar a Comprar', 'get() junta o gravado com os valores de partida e ignora chaves desconhecidas');

$s = Settings::sanitize(array('color_primary' => '2A8936'));
cl_assert($s['color_primary'] === '#2a8936', 'cor sem # e em maiúsculas → #2a8936');
cl_assert(Settings::sanitize(array('color_primary' => '#fff'))['color_primary'] === '#ffffff', 'cor de 3 dígitos → #ffffff');
cl_assert(Settings::sanitize(array('color_primary' => 'red;}body{x'))['color_primary'] === '#37bdd6', 'cor inválida → valor de partida');
$s = Settings::sanitize(array());
cl_assert($s['modal_enabled'] === false && $s['cart_enabled'] === false && $s['modal_only_with_orders'] === false, 'interruptores ausentes no formulário → false');
cl_assert(Settings::sanitize(array('cart_text' => ''))['cart_text'] === 'Continuar a Comprar', 'texto vazio → valor de partida');
cl_assert(Settings::sanitize(array('modal_title' => '<b>Oi</b> {nome}'))['modal_title'] === 'Oi {nome}', 'texto sem HTML');
cl_assert(Settings::sanitize(array('cart_url' => 'javascript:alert(1)'))['cart_url'] === '', 'URL javascript: → vazio');
cl_assert(Settings::sanitize(array('cart_url' => 'https://exemplo.pt/a'))['cart_url'] === 'https://exemplo.pt/a', 'URL https aceite');
cl_assert(Settings::sanitize(array('cart_type' => 'x'))['cart_type'] === 'home', 'tipo de destino inválido → home');
cl_assert(Settings::sanitize(array('cart_page' => '12abc'))['cart_page'] === 12, 'página → inteiro');
cl_assert(Settings::sanitize('lixo') === Settings::sanitize(array()), 'entrada que não é array tratada como vazia');

$pub = wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'BA teste destino'));
$apagada = wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'BA teste apagada'));
$paginas = array($pub);
wp_delete_post($apagada, true);
cl_assert(Settings::destination('page', $pub, '') === get_permalink($pub), 'destino página publicada → permalink');
cl_assert(Settings::destination('page', $apagada, '') === home_url('/'), 'destino página apagada → início');
cl_assert(Settings::destination('url', 0, '') === home_url('/'), 'destino URL vazio → início');
cl_assert(Settings::destination('url', 0, 'https://x.pt/a') === 'https://x.pt/a', 'destino URL → o URL');
cl_assert(Settings::destination('home', $pub, 'https://x.pt/a') === home_url('/'), 'destino início ignora página e URL');

cl_test_finish();
