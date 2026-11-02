<?php

require_once __DIR__ . "/config/conexao.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!is_int($id) || $id < 1) {
    http_response_code(400);
    exit("ID inválido.");
}

try {

    $sql = "SELECT *
            FROM enderecos
            WHERE id = :id";

    $consulta = $pdo->prepare($sql);

    $consulta->execute([
        ":id" => $id
    ]);

    $endereco = $consulta->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $erro) {

    error_log($erro->getMessage());

    http_response_code(500);
    exit("Erro ao consultar o endereço.");
}

if (!$endereco) {
    http_response_code(404);
    exit("Endereço não encontrado.");
}

$mensagemErro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $identificacao = trim($_POST["identificacao"] ?? "");
    $cep = trim($_POST["cep"] ?? "");
    $logradouro = trim($_POST["logradouro"] ?? "");
    $numero = trim($_POST["numero"] ?? "");
    $complemento = trim($_POST["complemento"] ?? "");
    $bairro = trim($_POST["bairro"] ?? "");
    $cidade = trim($_POST["cidade"] ?? "");
    $uf = strtoupper(trim($_POST["uf"] ?? ""));

    $camposInvalidos = [];

if ($identificacao === "") {
    $camposInvalidos[] = "Identificação";
}

if (!preg_match('/^[0-9]{8}$/', $cep)) {
    $camposInvalidos[] = "CEP";
}

if ($logradouro === "") {
    $camposInvalidos[] = "Rua / Avenida";
}

if ($numero === "") {
    $camposInvalidos[] = "Número";
}

if ($bairro === "") {
    $camposInvalidos[] = "Bairro";
}

if ($cidade === "") {
    $camposInvalidos[] = "Cidade";
}

if (!preg_match('/^[A-Z]{2}$/', $uf)) {
    $camposInvalidos[] = "Estado";
}

if ($camposInvalidos !== []) {

    $mensagemErro = "Corrija os campos: "
        . implode(", ", $camposInvalidos);

} else {

    try {

        $sql = "UPDATE enderecos
                SET identificacao = :identificacao,
                    cep = :cep,
                    logradouro = :logradouro,
                    numero = :numero,
                    complemento = :complemento,
                    bairro = :bairro,
                    cidade = :cidade,
                    uf = :uf
                WHERE id = :id";

        $comando = $pdo->prepare($sql);

        $comando->execute([
            ":identificacao" => $identificacao,
            ":cep" => $cep,
            ":logradouro" => $logradouro,
            ":numero" => $numero,
            ":complemento" => $complemento,
            ":bairro" => $bairro,
            ":cidade" => $cidade,
            ":uf" => $uf,
            ":id" => $id
        ]);

        header("Location: enderecos.php?atualizado=1", true, 303);
        exit;

    } catch (PDOException $erro) {

        error_log($erro->getMessage());

        $mensagemErro = "Não foi possível atualizar o endereço.";

    }

}

}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Editar Endereço - GLW Logistics</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

    <main class="container mt-5">

        <h1>Editar Endereço</h1>

        <p>Atualize as informações do endereço selecionado.</p>

        <form method="POST" class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Identificação</label>

                <input
                    type="text"
                    name="identificacao"
                    class="form-control"
                    maxlength="100"
                    value="<?= htmlspecialchars($endereco['identificacao'], ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">CEP</label>

                <input
                    type="text"
                    name="cep"
                    class="form-control"
                    maxlength="8"
                    pattern="[0-9]{8}"
                    value="<?= htmlspecialchars($endereco['cep'], ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="col-md-8">
                <label class="form-label">Rua / Avenida</label>

                <input
                    type="text"
                    name="logradouro"
                    class="form-control"
                    maxlength="150"
                    value="<?= htmlspecialchars($endereco['logradouro'], ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="col-md-4">
                <label class="form-label">Número</label>

                <input
                    type="text"
                    name="numero"
                    class="form-control"
                    maxlength="15"
                    value="<?= htmlspecialchars($endereco['numero'], ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">Bairro</label>

                <input
                    type="text"
                    name="bairro"
                    class="form-control"
                    maxlength="100"
                    value="<?= htmlspecialchars($endereco['bairro'], ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">Complemento</label>

                <input
                    type="text"
                    name="complemento"
                    class="form-control"
                    maxlength="100"
                    value="<?= htmlspecialchars($endereco['complemento'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="col-md-8">
                <label class="form-label">Cidade</label>

                <input
                    type="text"
                    name="cidade"
                    class="form-control"
                    maxlength="100"
                    value="<?= htmlspecialchars($endereco['cidade'], ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="col-md-4">
                <label class="form-label">Estado (UF)</label>

                <input
                    type="text"
                    name="uf"
                    class="form-control"
                    maxlength="2"
                    value="<?= htmlspecialchars($endereco['uf'], ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="col-12">

                <button type="submit" class="btn btn-success">
    Salvar Alterações
</button>

                <a href="enderecos.php" class="btn btn-secondary">
                    Cancelar
                </a>

            </div>

        </form>

    </main>

</body>
</html>