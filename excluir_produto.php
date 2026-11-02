<?php

session_start();

require_once __DIR__ . "/config/conexao.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Método não permitido.");
}

$tokenRecebido = $_POST["csrf_token"] ?? "";
$tokenEsperado = $_SESSION["csrf_token"] ?? "";

if (
    !is_string($tokenRecebido) ||
    !is_string($tokenEsperado) ||
    $tokenEsperado === "" ||
    !hash_equals($tokenEsperado, $tokenRecebido)
) {
    http_response_code(403);
    exit("Solicitação inválida.");
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("ID inválido.");
}

try {

    $sql = "DELETE FROM produtos WHERE id = :id";

    $comando = $pdo->prepare($sql);

    $comando->execute([
        ":id" => $id
    ]);

    header("Location: produtos.php?excluido=1", true, 303);
    exit;

} catch (PDOException $erro) {

    error_log($erro->getMessage());

    header("Location: produtos.php?erro_exclusao=1", true, 303);
    exit;

}