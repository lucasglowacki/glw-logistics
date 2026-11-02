<?php

require_once __DIR__ . "/config/conexao.php";

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $cnpj = trim($_POST["cnpj"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $email = trim($_POST["email"] ?? "");

    // Validando os dados recebidos

    if ($nome === "" || mb_strlen($nome) > 150) {

        $mensagem = "Informe um nome válido para a transportadora.";

    } elseif (
        $cnpj !== "" &&
        !preg_match('/^[0-9]{14}$/', $cnpj)
    ) {

        $mensagem = "O CNPJ deve conter 14 números.";

    } elseif (
        mb_strlen($telefone) > 20 ||
        mb_strlen($email) > 150 ||
        ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL))
    ) {

        $mensagem = "Verifique o telefone e o e-mail.";

    } else {

        try {

            $sql = "INSERT INTO transportadoras
                    (nome, cnpj, telefone, email)
                    VALUES
                    (:nome, :cnpj, :telefone, :email)";

            $comando = $pdo->prepare($sql);

            $comando->execute([
                ":nome" => $nome,
                ":cnpj" => $cnpj === "" ? null : $cnpj,
                ":telefone" => $telefone,
                ":email" => $email
            ]);

            header("Location: transportadoras.php?cadastrado=1", true, 303);
            exit;

        } catch (PDOException $erro) {

            error_log($erro->getMessage());

            $mensagem = "Não foi possível cadastrar a transportadora.";

        }

    }

}

$transportadoras = [];

try {

    $sql = "SELECT id, nome, cnpj, telefone, email
            FROM transportadoras
            ORDER BY id DESC";

    $consulta = $pdo->query($sql);

    $transportadoras = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $erro) {

    error_log($erro->getMessage());

    $mensagem = "Não foi possível consultar as transportadoras.";

}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Transportadoras - GLW Logistics</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

    <main class="container mt-5">

        <h1>Cadastro de Transportadoras</h1>

        <?php if (($_GET["cadastrado"] ?? "") === "1"): ?>

    <div class="alert alert-success">
        Transportadora cadastrada com sucesso!
    </div>

    <?php if (($_GET["atualizado"] ?? "") === "1"): ?>

    <div class="alert alert-success">
        Transportadora atualizada com sucesso!
    </div>

<?php endif; ?>

<?php endif; ?>

        <p>Preencha as informações da transportadora.</p>

        <?php if ($mensagem !== ""): ?>

            <div class="alert alert-danger">
                <?= htmlspecialchars($mensagem, ENT_QUOTES, "UTF-8") ?>
            </div>

        <?php endif; ?>

        <form method="POST" class="row g-3">

            <div class="col-md-6">

                <label class="form-label">
                    Nome da Transportadora
                </label>

                <input
                    type="text"
                    name="nome"
                    maxlength="150"
                    class="form-control"
                    required
                >

            </div>

            <div class="col-md-6">

                <label class="form-label">
                    CNPJ
                </label>

                <input
                    type="text"
                    name="cnpj"
                    maxlength="14"
                    pattern="[0-9]{14}"
                    class="form-control"
                    placeholder="Somente números"
                >

            </div>

            <div class="col-md-6">

                <label class="form-label">
                    Telefone
                </label>

                <input
                    type="text"
                    name="telefone"
                    maxlength="20"
                    class="form-control"
                >

            </div>

            <div class="col-md-6">

                <label class="form-label">
                    E-mail
                </label>

                <input
                    type="email"
                    name="email"
                    maxlength="150"
                    class="form-control"
                >

            </div>

            <div class="col-12">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Cadastrar Transportadora
                </button>

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    Voltar ao Painel
                </a>

            </div>

        </form>

        <hr class="my-5">

<h2>Transportadoras Cadastradas</h2>

<div class="table-responsive">

    <table class="table table-striped table-hover">

        <thead>
            <tr>
                <th>ID</th>
                <th>Transportadora</th>
                <th>CNPJ</th>
                <th>Telefone</th>
                <th>E-mail</th>
                <th>Ações</th>
            </tr>
        </thead>

        <tbody>

            <?php foreach ($transportadoras as $transportadora): ?>

                <tr>

                    <td>
                        <?= (int) $transportadora["id"] ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($transportadora["nome"], ENT_QUOTES, "UTF-8") ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($transportadora["cnpj"] ?? "", ENT_QUOTES, "UTF-8") ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($transportadora["telefone"] ?? "", ENT_QUOTES, "UTF-8") ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($transportadora["email"] ?? "", ENT_QUOTES, "UTF-8") ?>
                    </td>

                    <td>
    <a
        href="editar_transportadora.php?id=<?= (int) $transportadora['id'] ?>"
        class="btn btn-warning btn-sm"
    >
        Editar
    </a>
</td>
                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

</div>

    </main>

</body>
</html>