<?php

session_start();

require_once __DIR__ . "/config/conexao.php";

// Aceitar somente requisições POST.

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Método não permitido.");
}

// Validar o token CSRF.

$token = $_POST["csrf_token"] ?? "";

if (
    !is_string($token) ||
    empty($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    http_response_code(403);
    exit("Requisição inválida.");
}

// Receber e validar o ID.

$id = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);

if (!is_int($id) || $id < 1) {
    http_response_code(400);
    exit("ID inválido.");
}

try {

    $sql = "DELETE FROM enderecos
            WHERE id = :id";

    $comando = $pdo->prepare($sql);

    $comando->execute([
        ":id" => $id
    ]);

    header(
        "Location: enderecos.php?excluido=1",
        true,
        303
    );

    exit;

} catch (PDOException $erro) {

    error_log($erro->getMessage());

    // Impedir a exclusão de endereços usados em fretes.

    if (($erro->errorInfo[1] ?? null) == 1451) {

        header(
            "Location: enderecos.php?vinculado=1",
            true,
            303
        );

        exit;
    }

    header(
        "Location: enderecos.php?erro=1",
        true,
        303
    );

    exit;
}