<?php

require_once __DIR__ . "/config/conexao.php";

$mensagem = "";

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
    $camposInvalidos[] = "Rua";
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

    $mensagem = "Corrija os campos: "
        . implode(", ", $camposInvalidos);

} else {

        try {

            $sql = "INSERT INTO enderecos
                    (identificacao, cep, logradouro, numero,
                     complemento, bairro, cidade, uf)
                    VALUES
                    (:identificacao, :cep, :logradouro, :numero,
                     :complemento, :bairro, :cidade, :uf)";

            $comando = $pdo->prepare($sql);

            $comando->execute([
                ":identificacao" => $identificacao,
                ":cep" => $cep,
                ":logradouro" => $logradouro,
                ":numero" => $numero,
                ":complemento" => $complemento,
                ":bairro" => $bairro,
                ":cidade" => $cidade,
                ":uf" => $uf
            ]);

            header("Location: enderecos.php?cadastrado=1", true, 303);
            exit;

        } catch (PDOException $erro) {

            error_log($erro->getMessage());

            $mensagem = "Não foi possível cadastrar o endereço.";

        }

    }

}

$enderecos = [];

try {

    $sql = "SELECT * FROM enderecos ORDER BY id DESC";

    $consulta = $pdo->query($sql);

    $enderecos = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $erro) {

    error_log($erro->getMessage());

    $mensagem = "Não foi possível consultar os endereços.";

}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Endereços - GLW Logistics</title>

    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    >
</head>

<body class="bg-light">

<main class="container mt-5">

<h1>Cadastro de Endereços</h1>

<?php if (($_GET["cadastrado"] ?? "") === "1"): ?>

    <div class="alert alert-success">
        Endereço cadastrado com sucesso!
    </div>

<?php endif; ?>

<?php if (($_GET["atualizado"] ?? "") === "1"): ?>

    <div class="alert alert-success">
        Endereço atualizado com sucesso!
    </div>

<?php endif; ?>

<p>Preencha as informações do endereço.</p>

<?php if ($mensagem !== ""): ?>
<div class="alert alert-danger">
    <?= $mensagem ?>
</div>
<?php endif; ?>

<form method="POST" class="row g-3">

<div class="col-md-6">

    <label class="form-label">
        Identificação do Endereço
    </label>

    <input
        type="text"
        name="identificacao"
        maxlength="100"
        class="form-control"
        required
    >

</div>

<div class="col-md-6">
    <label class="form-label">CEP</label>
    <input type="text" name="cep"
    maxlength="8" pattern="[0-9]{8}"
    class="form-control" required>
</div>

<div class="col-md-8">
    <label class="form-label">Rua / Avenida</label>
    <input type="text" name="logradouro"
    maxlength="150" class="form-control" required>
</div>

<div class="col-md-4">
                <label class="form-label">Número</label>
                <input type="text" name="numero"
                       maxlength="15" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Bairro</label>
                <input type="text" name="bairro"
                       maxlength="100" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Complemento</label>
                <input type="text" name="complemento"
                       maxlength="100" class="form-control">
            </div>

            <div class="col-md-8">
                <label class="form-label">Cidade</label>
                <input type="text" name="cidade"
                       maxlength="100" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Estado (UF)</label>
                <input type="text" name="uf"
                       maxlength="2" class="form-control" required>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    Cadastrar Endereço
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Voltar ao Painel
                </a>
            </div>

        </form>

        <section class="mt-5">

    <h2>Endereços Cadastrados</h2>

    <?php if (empty($enderecos)): ?>

        <div class="alert alert-info">
            Nenhum endereço cadastrado até o momento.
        </div>

    <?php else: ?>

        <div class="table-responsive">

            <table class="table table-striped table-hover">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Identificação</th>
                        <th>Endereço</th>
                        <th>Cidade / UF</th>
                        <th>CEP</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($enderecos as $endereco): ?>

                        <tr>

                            <td>
                                <?= (int) $endereco["id"] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($endereco["identificacao"], ENT_QUOTES, "UTF-8") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($endereco["logradouro"], ENT_QUOTES, "UTF-8") ?>,
                                <?= htmlspecialchars($endereco["numero"], ENT_QUOTES, "UTF-8") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($endereco["cidade"], ENT_QUOTES, "UTF-8") ?>
                                /
                                <?= htmlspecialchars($endereco["uf"], ENT_QUOTES, "UTF-8") ?>
                            </td>

                            <td>
    <?= htmlspecialchars($endereco["cep"], ENT_QUOTES, "UTF-8") ?>
</td>
                                <td>
    <a
        href="editar_endereco.php?id=<?= (int) $endereco['id'] ?>"
        class="btn btn-warning btn-sm"
    >
        Editar
    </a>
                                <a href="excluir_endereco.php?id=<?= $endereco["id"] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este endereço?')">
                                    Excluir
                                </a>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</section>

    </main>

</body>
</html>