<?php
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
    //     "http://172.17.100.30:41120/blank_json_boletim_met_mes/?DataInicial=$dataInicialFormatUrl&DataFinal=$dataFinalFormatUrl" :
    //     "http://172.17.100.30:41120/blank_json_boletim_met_mes/?DataInicial=$dataInicialFormatUrl&DataFinal=$dataInicialFormatUrl";

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <link rel="icon" type="image/x-icon" href="icons8-água-48.png">
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

        p.maior-chuva {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
            color: #d9534f;
        }

        img {
            display: block;
            margin: auto;
            height: 260px;
            margin-top: -120px;
        }

        .btn-download {
            background-color: #383f73;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }

    </style>
</head>

<body>
    <div class="w-full max-w-6xl mx-auto px-4 py-8 md:px-6 md:py-12">
        <header class="flex flex-col items-center gap-4 mb-8">
            <img src="apac_secretaria_recursos_hidricos.png" alt="">
            <div class="text-center">
                <h1 class="text-2xl font-bold">Boletim Pluviométrico</h1>

                <?php if ($tipoBoletimPeriodo == 'Mensal') { ?>
                    <p class="text-gray-500"><?php echo $dataInicialFormat . ' - ' . $dataFinalFormat; ?></p>
                <?php } else { ?>
                    <p class="text-gray-500"><?php echo $dataInicialFormat ?></p>
                <?php } ?>
            </div>
            <!-- Botão de Impressão -->
            <button onclick="downloadPDF()" class="btn-download">Download PDF</button>

        </header>
        
      
        <div id="boletim-content">
            <section class="mb-8">
                <?php
                foreach ($data as $item) {
                    if (($mesorregiaof == 'Todas' || $item->mesoregiao == $mesorregiaof)) {

                        $maiorChuva = null;

                        foreach ($item->estacoes as $estacao) {
                            if (($baciaf == 'Todas' || $estacao->bacia == $baciaf) &&
                                ($microrregiaof == 'Todas' || $microrregiaof == '' || $estacao->microregiao == $microrregiaof)) {
                                if ($maiorChuva === null || $estacao->soma_chuva_resultado > $maiorChuva->soma_chuva_resultado) {
                                    $maiorChuva = $estacao;
                                }
                            }
                        }

                        if ($maiorChuva !== null) {
                            echo "<h3>Mesorregião " . $item->mesoregiao . "</h3>";
                            echo "<p class='maior-chuva'>Maior chuva: " . $maiorChuva->municipio . " - " . $maiorChuva->soma_chuva_resultado . " mm</p>";
                            echo "<table>";
                            echo "<tr>
                                    <th>Município</th>
                                    <th>Estação</th>
                                    <th>Bacia</th>
                                    <th>Microrregião</th>
                                    <th>Chuva Total(mm)</th>";

                            if ($tipoBoletimPeriodo == 'Mensal') {
                                echo "<th>Climatologia (mm)</th>
                                      <th>Anomalia (mm)</th>
                                      <th>Desvio Relativo(%)</th>";
                            }

                            echo "</tr>";

                            foreach ($item->estacoes as $estacao) {
                                if (($baciaf == 'Todas' || $estacao->bacia == $baciaf) &&
                                    ($microrregiaof == 'Todas' || $microrregiaof == '' || $estacao->microregiao == $microrregiaof)) {
                                    echo "<tr>
                                            <td>" . $estacao->municipio . "</td>
                                            <td>" . $estacao->nomeEstacao . "</td>
                                            <td>" . $estacao->bacia . "</td>
                                            <td>" . $estacao->microregiao . "</td>
                                            <td>" . $estacao->soma_chuva_resultado . "</td>";

                                    if ($tipoBoletimPeriodo == 'Mensal') {
                                        echo "<td>" . $estacao->climatologia . "</td>
                                              <td>" . $estacao->anomalia . "</td>
                                              <td>" . $estacao->desvio_relativo . "</td>";
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

    </div>

    <script>
        function downloadPDF() {
            const { jsPDF } = window.jspdf;
            let doc = new jsPDF();

            let boletimContent = document.getElementById("boletim-content").innerHTML;
            let style = `
                <style>
                    table { width: 100%; border-collapse: collapse; text-align: center; }
                    th, td { border: 1px solid black; padding: 8px; text-align: center; }
                    th { background-color: #383f73; color: white; }
                    h3, p { text-align: center; font-weight: bold; }
                </style>
            `;

            let win = window.open("", "", "width=800,height=600");
            win.document.write('<html><head><title>Boletim Pluviométrico</title>' + style + '</head><body>');
            win.document.write(boletimContent);
            win.document.write('</body></html>');
            win.document.close();
            win.print();
        }
    </script>

</body>

</html>