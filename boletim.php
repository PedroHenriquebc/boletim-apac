<?php

// require('fpdf/fpdf.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    // Dados do formulário
    $tipoBoletimPeriodo = $_POST["tipoBoletimPeriodo"];
    $mesorregiaof = $_POST["mesorregiao"] ?? '';
    $microrregiaof = $_POST["microrregiao"] ?? '';
    $baciaf = $_POST["bacia"] ?? '';
    // $colunasSelecionadas = $_POST["colunas"] ?? ["municipio", "nomeEstacao", "soma_chuva_resultado", "latitude", "longitude", "codigo_gmmc"];
    $colunasSelecionadas = $_POST["colunas"] ?? [];
    $colunasSelecionadas = array_merge($colunasSelecionadas, ["municipio", "nomeEstacao", "soma_chuva_resultado"]);


    // Formatação das datas
    $dataInicialExplode = explode("-", $_POST["dataInicial"]);
    $dataInicialFormatUrl = $dataInicialExplode[0] . $dataInicialExplode[1] . $dataInicialExplode[2];
    $dataFinalFormatUrl = null;
    $dataInicialFormat = $dataInicialExplode[2] . "/" . $dataInicialExplode[1] . "/" . $dataInicialExplode[0];

    if (!empty($_POST["dataFinal"])) {
        $dataFinalExplode = explode("-", $_POST["dataFinal"]);
        $dataFinalFormatUrl = $dataFinalExplode[0] . $dataFinalExplode[1] . $dataFinalExplode[2];
        $dataFinalFormat = $dataFinalExplode[2] . "/" . $dataFinalExplode[1] . "/" . $dataFinalExplode[0];
    }

    // // URL da API
    $url = $tipoBoletimPeriodo == 'Mensal' ?
        "http://dados.apac.pe.gov.br:41120/blank_json_boletim_met_mes/?DataInicial=$dataInicialFormatUrl&DataFinal=$dataFinalFormatUrl" :
        "http://dados.apac.pe.gov.br:41120/blank_json_boletim_met_mes/?DataInicial=$dataInicialFormatUrl&DataFinal=$dataInicialFormatUrl";

    //URL da API
    // $url = $tipoBoletimPeriodo == 'Mensal' ?
    // "http://172.17.100.30:41120/blank_json_boletim_met_mes/?DataInicial=$dataInicialFormatUrl&DataFinal=$dataFinalFormatUrl" :
    // "http://172.17.100.30:41120/blank_json_boletim_met_mes/?DataInicial=$dataInicialFormatUrl&DataFinal=$dataInicialFormatUrl";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Erro ao fazer a requisição: ' . curl_error($ch);
    } else {
        $data = json_decode($response);
    }
    curl_close($ch);
} else {
    header("Location: http://dados.apac.pe.gov.br:41120/boletins/boletim-pluviometrico/");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boletim</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="icons8-água-48.png">
</head>

<body>
    <style>
        /* Estilos gerais da página */
        body {
            font-family: Arial, sans-serif;
        }

        @page {
            size: portrait;
            margin: 10mm;
        }

        /* Oculta elementos que não devem aparecer na impressão */
        .nao-imprimir {
            display: none;
        }

        @media print {
            .nao-imprimir {
                display: none;
            }

            table,
            th,
            td {
                font-size: 10px;
                padding: 4px;
            }
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            margin: auto;
            box-shadow: 10px 10px 10px #999;
        }

        th,
        td {
            border: 1px solid #000000;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #383f73;
            color: white;
        }

        h3 {
            font-style: italic;
            text-align: center;
            margin-top: 50px;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: larger;
        }

        p.maior-chuva {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
            color: #d9534f;
        }

        img {
            display: block;
            margin: auto;
            height: 200px;
            margin-top: -80px;
        }


        .btn-imprimir {
            background-color: #4CAF50;
            /* Verde */
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin: 20px 0;
        }

        /* Efeito hover */
        .btn-imprimir:hover {
            background-color: #45a049;
        }

        p.rodape {
            margin-left: 3px;
        }
    </style>

    <div class="w-full max-w-6xl mx-auto px-4 py-8 md:px-6 md:py-12">
        <header class="flex flex-col items-center gap-4 mb-8">
            <img src="apac_secretaria_recursos_hidricos.png" alt="">
            <div class="text-center">
                <!-- <h1 class="text-2xl font-bold">Boletim Pluviométrico</h1> -->

                <?php if ($tipoBoletimPeriodo == 'Mensal') { ?>
                    <h1 class="text-2xl font-bold">Boletim Pluviométrico de Acumulados</h1>
                    <p class="text-gray-500"><?php echo $dataInicialFormat . ' - ' . $dataFinalFormat; ?></p>
                <?php } else { ?>
                    <h1 class="text-2xl font-bold">Boletim Pluviométrico Diário</h1>
                    <p class="text-gray-500"><?php echo $dataInicialFormat ?></p>
                <?php } ?>
                <!-- Botão com a classe "nao-imprimir" -->
                <button onclick="window.print()" class="btn-imprimir nao-imprimir">Baixar PDF</button>

            </div>

        </header>
        <section class="mb-8">
            <?php
            foreach ($data as $item) {
                if (($mesorregiaof == 'Todas' || $item->mesoregiao == $mesorregiaof)) {

                    $maiorChuva = null;

                    foreach ($item->estacoes as $estacao) {
                        if (($baciaf == 'Todas' || $estacao->bacia == $baciaf) &&
                            ($microrregiaof == 'Todas' || $microrregiaof == '' || $estacao->microregiao == $microrregiaof)
                        ) {
                            if ($maiorChuva === null || $estacao->soma_chuva_resultado > $maiorChuva->soma_chuva_resultado) {
                                $maiorChuva = $estacao;
                            }
                        }
                    }

                    if ($maiorChuva !== null) {
                        echo "<h3>Mesorregião " . $item->mesoregiao . "</h3>";
                        if ($tipoBoletimPeriodo == 'Mensal') {
                            echo "<p class='maior-chuva'>Maior acumulado: " . $maiorChuva->municipio . " - " . $maiorChuva->soma_chuva_resultado . " mm</p>";
                        } else {
                            echo "<p class='maior-chuva'>Maior chuva: " . $maiorChuva->municipio . " - " . $maiorChuva->soma_chuva_resultado . " mm</p>";
                        }
                        echo "<table><tr>";

                        if (in_array("codigo_gmmc", $colunasSelecionadas)) echo "<th>Código Estação</th>";
                        if (in_array("municipio", $colunasSelecionadas)) echo "<th>Município</th>";
                        if (in_array("nomeEstacao", $colunasSelecionadas)) echo "<th>Estação</th>";
                        if (in_array("bacia", $colunasSelecionadas)) echo "<th>Bacia</th>";
                        if (in_array("microregiao", $colunasSelecionadas)) echo "<th>Microrregião</th>";
                        if (in_array("latitude", $colunasSelecionadas)) echo "<th>Latitude</th>";
                        if (in_array("longitude", $colunasSelecionadas)) echo "<th>Longitude</th>";
                        if (in_array("soma_chuva_resultado", $colunasSelecionadas)) echo "<th>Chuva Total (mm)</th>";


                        if ($tipoBoletimPeriodo == 'Mensal') {
                            if (in_array("climatologia", $colunasSelecionadas)) echo "<th>Climatologia (mm)</th>";
                            if (in_array("anomalia", $colunasSelecionadas)) echo "<th>Anomalia (mm)</th>";
                            if (in_array("desvio_relativo", $colunasSelecionadas)) echo "<th>Desvio Relativo (%)</th>";
                        }

                        echo "</tr>";

                        foreach ($item->estacoes as $estacao) {
                            if (($baciaf == 'Todas' || $estacao->bacia == $baciaf) &&
                                ($microrregiaof == 'Todas' || $microrregiaof == '' || $estacao->microregiao == $microrregiaof)
                            ) {

                                echo "<tr>";

                                if (in_array("codigo_gmmc", $colunasSelecionadas)) echo "<td>" . $estacao->codigo_gmmc . "</td>";
                                if (in_array("municipio", $colunasSelecionadas)) echo "<td>" . $estacao->municipio . "</td>";
                                if (in_array("nomeEstacao", $colunasSelecionadas)) echo "<td>" . $estacao->nomeEstacao . "</td>";
                                if (in_array("bacia", $colunasSelecionadas)) echo "<td>" . $estacao->bacia . "</td>";
                                if (in_array("microregiao", $colunasSelecionadas)) echo "<td>" . $estacao->microregiao . "</td>";
                                if (in_array("latitude", $colunasSelecionadas)) echo "<td>" . $estacao->latitude . "</td>";
                                if (in_array("longitude", $colunasSelecionadas)) echo "<td>" . $estacao->longitude . "</td>";
                                if (in_array("soma_chuva_resultado", $colunasSelecionadas)) echo "<td>" . $estacao->soma_chuva_resultado . "</td>";

                                if ($tipoBoletimPeriodo == 'Mensal') {
                                    if (in_array("climatologia", $colunasSelecionadas)) echo "<td>" . $estacao->climatologia . "</td>";
                                    if (in_array("anomalia", $colunasSelecionadas)) echo "<td>" . $estacao->anomalia . "</td>";
                                    if (in_array("desvio_relativo", $colunasSelecionadas)) echo "<td>" . $estacao->desvio_relativo . "</td>";
                                }

                                echo "</tr>";
                            }
                        }

                        echo "</table>";
                    }
                }
            }
            ?>
        </section>
    </div>
    <footer>
        <p class="rodape">1)Convencional: Aparelho de medição manual</p>
        <p class="rodape">2)PCD(Plataforma de Coleta de Dados): Aparelho de medição automática</p>
    </footer>
</body>

</html>
