<?php

require_once __DIR__ . "/config/conexao.php";

$fretes = [];
$mensagemErro = "";

$data = $_GET["data"] ?? date("Y-m-d");

$consultar = isset($_GET["data"]);

if ($consultar) {

    if (
        !is_string($data) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)
    ) {

        $mensagemErro = "Informe uma data válida.";

    } else {

        [$ano, $mes, $dia] = array_map(
            "intval",
            explode("-", $data)
        );

        if (!checkdate($mes, $dia, $ano)) {

            $mensagemErro = "A data informada não existe.";

        } else {

            try {

                $sql = "
                    SELECT
                        f.id,
                        f.data_frete,
                        t.nome AS transportadora,
                        eo.identificacao AS origem,
                        ed.identificacao AS destino,
                        p.nome AS produto,
                        fp.quantidade

                    FROM fretes f

                    INNER JOIN transportadoras t
                        ON t.id = f.transportadora_id

                    INNER JOIN enderecos eo
                        ON eo.id = f.origem_id

                    INNER JOIN enderecos ed
                        ON ed.id = f.destino_id

                    INNER JOIN frete_produtos fp
                        ON fp.frete_id = f.id

                    INNER JOIN produtos p
                        ON p.id = fp.produto_id

                    WHERE f.data_frete = :data

                    ORDER BY f.id DESC, p.nome ASC
                ";

                $consulta = $pdo->prepare($sql);

                $consulta->execute([
                    ":data" => $data
                ]);

                $fretes = $consulta->fetchAll(PDO::FETCH_ASSOC);

            } catch (PDOException $erro) {

                error_log($erro->getMessage());

                $mensagemErro = "Não foi possível gerar o relatório.";

            }

        }

    }

}

// Proteção para valores exportados ao CSV.

function protegerCSV($valor): string
{
    $texto = (string) ($valor ?? "");

    if (preg_match('/^\s*[=+\-@]/u', $texto)) {
        return "'" . $texto;
    }

    return $texto;
}


// Verificar se o usuário solicitou o download.

if (
    $consultar &&
    $mensagemErro === "" &&
    ($_GET["exportar"] ?? "") === "csv"
) {

    header("Content-Type: text/csv; charset=UTF-8");

    header(
        'Content-Disposition: attachment; filename="fretes-' .
        $data .
        '.csv"'
    );

    header("Cache-Control: no-store");

    $arquivo = fopen("php://output", "w");

    // BOM para facilitar a leitura de acentos no Excel.

    fwrite($arquivo, "\xEF\xBB\xBF");

    // Cabeçalho do relatório.

    fputcsv(
        $arquivo,
        [
            "ID",
            "Data",
            "Transportadora",
            "Origem",
            "Destino",
            "Produto",
            "Quantidade"
        ],
        ";",
        '"',
        ""
    );

    // Dados do relatório.

    foreach ($fretes as $frete) {

        $linha = [
            $frete["id"],
            $frete["data_frete"],
            $frete["transportadora"],
            $frete["origem"],
            $frete["destino"],
            $frete["produto"],
            $frete["quantidade"]
        ];

        fputcsv(
            $arquivo,
            array_map("protegerCSV", $linha),
            ";",
            '"',
            ""
        );

    }

    fclose($arquivo);

    exit;

}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Relatórios - GLW Logistics</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<?php require __DIR__ . "/includes/menu.php"; ?>

<main class="container mt-5">

    <h1>Relatório Diário de Fretes</h1>

    <p>Selecione uma data para consultar os fretes cadastrados.</p>

    <?php if ($mensagemErro !== ""): ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars(
                $mensagemErro,
                ENT_QUOTES,
                "UTF-8"
            ) ?>

        </div>

    <?php endif; ?>


    <form method="GET" class="row g-3 mb-4">

        <div class="col-md-4">

            <label class="form-label">
                Data do Relatório
            </label>

            <input
                type="date"
                name="data"
                class="form-control"
                value="<?= htmlspecialchars(
                    is_string($data) ? $data : "",
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>"
                required
            >

        </div>

        <div class="col-md-8 d-flex align-items-end gap-2">

            <button
                type="submit"
                class="btn btn-primary"
            >
                Consultar Fretes
            </button>

            <button
                type="submit"
                name="exportar"
                value="csv"
                class="btn btn-success"
            >
                Baixar CSV
            </button>

        </div>

    </form>


    <?php if ($consultar && $mensagemErro === ""): ?>

        <h2>Fretes Encontrados</h2>

        <?php if (empty($fretes)): ?>

            <div class="alert alert-warning">
                Nenhum frete encontrado para esta data.
            </div>

        <?php else: ?>

            <div class="table-responsive">

                <table class="table table-striped table-hover">

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Transportadora</th>
                            <th>Origem</th>
                            <th>Destino</th>
                            <th>Produto</th>
                            <th>Quantidade</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($fretes as $frete): ?>

                            <tr>

                                <td>
                                    <?= (int) $frete["id"] ?>
                                </td>

                                <td>
                                    <?= date(
                                        "d/m/Y",
                                        strtotime($frete["data_frete"])
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $frete["transportadora"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $frete["origem"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $frete["destino"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $frete["produto"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </td>

                                <td>
                                    <?= (int) $frete["quantidade"] ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    <?php endif; ?>


    <a href="index.php" class="btn btn-secondary mt-3">
        Voltar ao Painel
    </a>

</main>

</body>

</html>