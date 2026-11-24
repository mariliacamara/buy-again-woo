<?php
/**
 * Utilitários dos testes. Correm no servidor (dev) com `wp eval-file`, contra um carrinho real em memória.
 * Nada aqui é carregado pelo plugin.
 */

if (!defined('ABSPATH')) exit;

$GLOBALS['cl_test_failures'] = 0;

function cl_assert($cond, $msg) {
    if ($cond) {
        WP_CLI::log("OK      $msg");
    } else {
        $GLOBALS['cl_test_failures']++;
        WP_CLI::log("FALHOU  $msg");
    }
}

function cl_test_finish() {
    $n = $GLOBALS['cl_test_failures'];
    if ($n > 0) {
        WP_CLI::error("$n teste(s) falharam");
    }
    WP_CLI::success('todos os testes passaram');
}
