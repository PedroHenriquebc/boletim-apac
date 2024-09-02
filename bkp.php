<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boletim</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <style>
        table {
            width: 100%; 
            border-collapse: collapse;
            text-align: center;
            margin: auto;
            box-shadow: 10px 10px 10px #999;
        }
        th, td {
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
        img {
            display: block;
            margin: auto;
            height: 400px;
            margin-top: -120px;
        }
    </style>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
        $tipoBoletimPeriodo = $_POST["tipoBoletimPeriodo"];
        $hoje = date('d/m/Y');

        $mesorregiaof = $_POST["mesorregiao"] ?? '';
        $microrregiaof = $_POST["microrregiao"] ?? '';
        $baciaf = $_POST["bacia"] ?? '';
        $tipoBoletimf = $_POST["tipoBoletim"] ?? '';
        $dadosCompletosf = $_POST["dadosCompletos"] ?? '';

        $dataInicialExplode = explode("-", $_POST["dataInicial"]);
        $dataInicialFormat = $dataInicialExplode[2] . "/" . $dataInicialExplode[1] . "/" . $dataInicialExplode[0];
        $dataInicialFormatUrl = $dataInicialExplode[0] . $dataInicialExplode[1] . $dataInicialExplode[2];
        $dataInicial = DateTime::createFromFormat('d/m/Y', $dataInicialFormat);

        if (!empty($_POST["dataFinal"])) {
            $dataFinalExplode = explode("-", $_POST["dataFinal"]);
            $dataFinalFormatUrl = $dataFinalExplode[0] . $dataFinalExplode[1] . $dataFinalExplode[2];
            $dataFinalFormat = $dataFinalExplode[2] . "/" . $dataFinalExplode[1] . "/" . $dataFinalExplode[0];

            $dataFinal = DateTime::createFromFormat('d/m/Y', $dataFinalFormat);
            $intervalo = $dataInicial->diff($dataFinal);
        }

        $url = $tipoBoletimPeriodo == 'Mensal' ?
            "http://172.17.100.30:41120/blank_json_boletim_met_mes/?DataInicial=$dataInicialFormatUrl&DataFinal=$dataFinalFormatUrl" :
            "http://172.17.100.30:41120/blank_json_boletim_met_mes/?DataInicial=$dataInicialFormatUrl&DataFinal=$dataInicialFormatUrl";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Erro ao fazer a requisição: ' . curl_error($ch);
        }

        curl_close($ch);
        $data = json_decode($response);

    } else {
        header("Location: index.html");
        exit();
    }
    ?>

    <div class="w-full max-w-6xl mx-auto px-4 py-8 md:px-6 md:py-12">
        <header class="flex flex-col items-center gap-4 mb-8">
            <img src="logo3_apac_2024.png" alt="">
            <div class="text-center">
                <h1 class="text-2xl font-bold">Boletim Pluviométrico</h1>
                <?php if ($tipoBoletimPeriodo == 'Mensal') { ?>
                    <p class="text-gray-500"><?php echo $dataInicialFormat . ' - ' . $dataFinalFormat; ?></p>
                <?php } else { ?>
                    <p class="text-gray-500"><?php echo $dataInicialFormat ?></p>
                <?php } ?>
            </div>
        </header>
        <section class="mb-8">
            <!-- MESORREGIAO VAZIA (RETORNA TUDO) -->
            <div>
                <?php if ($mesorregiaof == 'Todas' && $baciaf == 'Todas') { 
                    foreach ($data as $item) { ?>
                        <h3>Mesorregião <?php echo $item->mesoregiao ?></h3>
                        <table id="table_<?php echo $item->mesoregiao ?>">
                            <tr>
                                <th>Município</th>
                                <th>Estação</th>
                                <th>Bacia</th>
                                <th>Microrregião</th>
                                <th>Chuva Total(mm)</th>
                                <?php if ($tipoBoletimPeriodo == 'Mensal') { ?>
                                    <th>Climatologia (mm)</th>
                                    <th>Anomalia (mm)</th>
                                    <th>Desvio Relativo(%)</th>
                                <?php } ?>
                            </tr>
                            <?php foreach ($item->estacoes as $estacao) { ?>
                                <tr>
                                    <td><?php echo $estacao->municipio ?></td>
                                    <td><?php echo $estacao->nomeEstacao ?></td>
                                    <td><?php echo $estacao->bacia ?></td>
                                    <td><?php echo $estacao->microregiao ?></td>
                                    <td><?php echo $estacao->soma_chuva_resultado ?></td>
                                    <?php if ($tipoBoletimPeriodo == 'Mensal') { ?>
                                        <td><?php echo $estacao->climatologia ?></td>
                                        <td><?php echo $estacao->anomalia ?></td>
                                        <td><?php echo $estacao->desvio_relativo ?></td>
                                    <?php } ?>
                                </tr>
                            <?php } ?>
                        </table>
                    <?php } ?>
                <?php } ?>
            </div>

            <!-- FILTRO MESORREGIAO E BACIA -->
            <div>
                <?php 
                $mesorregiaoAtual = "";
                if ($mesorregiaof == 'Todas' && $baciaf != 'Todas') { 
                    foreach ($data as $item) { 
                        foreach ($item->estacoes as $estacao) { 
                            if ($estacao->bacia == $baciaf && $mesorregiaoAtual != $item->mesoregiao) { 
                                $mesorregiaoAtual = $item->mesoregiao; ?>
                                <h3>Mesorregião <?php echo $item->mesoregiao ?></h3>
                                <table id="table_<?php echo $item->mesoregiao ?>">
                                    <tr>
                                        <th>Município</th>
                                        <th>Estação</th>
                                        <th>Bacia</th>
                                        <th>Microrregião</th>
                                        <th>Chuva Total(mm)</th>
                                        <?php if ($tipoBoletimPeriodo == 'Mensal') { ?>
                                            <th>Climatologia (mm)</th>
                                            <th>Anomalia (mm)</th>
                                            <th>Desvio Relativo(%)</th>
                                        <?php } ?>
                                    </tr>
                                    <?php } ?>
                                    <tr>
                                        <td><?php echo $estacao->municipio ?></td>
                                        <td><?php echo $estacao->nomeEstacao ?></td>
                                        <td><?php echo $estacao->bacia ?></td>
                                        <td><?php echo $estacao->microregiao ?></td>
                                        <td><?php echo $estacao->soma_chuva_resultado ?></td>
                                        <?php if ($tipoBoletimPeriodo == 'Mensal') { ?>
                                            <td><?php echo $estacao->climatologia ?></td>
                                            <td><?php echo $estacao->anomalia ?></td>
                                            <td><?php echo $estacao->desvio_relativo ?></td>
                                        <?php } ?>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </table>
                    <?php } ?>
                </div>

            <!-- MESORREGIAO NÃO VAZIA -->
            <div>
                <?php if ($mesorregiaof != "Todas") {
                    foreach ($data as $item) {
                        if ($item->mesoregiao == $mesorregiaof) { ?>
                            <h3>Mesorregião <?php echo $item->mesoregiao ?></h3>
                            <table id="table_<?php echo $item->mesoregiao ?>">
                                <tr>
                                    <th>Município</th>
                                    <th>Estação</th>
                                    <th>Bacia</th>
                                    <th>Microrregião</th>
                                    <th>Chuva Total(mm)</th>
                                    <?php if ($tipoBoletimPeriodo == 'Mensal') { ?>
                                        <th>Climatologia (mm)</th>
                                        <th>Anomalia (mm)</th>
                                        <th>Desvio Relativo(%)</th>
                                    <?php } ?>
                                </tr>
                                <?php foreach ($item->estacoes as $estacao) { ?>
                                    <tr>
                                        <td><?php echo $estacao->municipio ?></td>
                                        <td><?php echo $estacao->nomeEstacao ?></td>
                                        <td><?php echo $estacao->bacia ?></td>
                                        <td><?php echo $estacao->microregiao ?></td>
                                        <td><?php echo $estacao->soma_chuva_resultado ?></td>
                                        <?php if ($tipoBoletimPeriodo == 'Mensal') { ?>
                                            <td><?php echo $estacao->climatologia ?></td>
                                            <td><?php echo $estacao->anomalia ?></td>
                                            <td><?php echo $estacao->desvio_relativo ?></td>
                                        <?php } ?>
                                    </tr>
                                <?php } ?>
                            </table>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            </div>
        </section>
    </div>
</body>

</html>
