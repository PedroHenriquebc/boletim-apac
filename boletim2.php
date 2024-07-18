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
            margin: auto;
        }
        th {
            background-color: #383f73;
            color: white;
            text-align: center;
            margin: auto;
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
            
        }

    </style>

    <?php
    if(isset($_POST["mesorregiao"])){
        $mesorregiaof = $_POST["mesorregiao"];
    }

    if(isset($_POST["microrregiao"])){
        $microrregiaof = $_POST["microrregiao"];
    }
    
    
    if(isset($_POST["bacia"])){
        $baciaf = $_POST["bacia"];
    }

    if(isset($_POST["tipoBoletim"])){
        $tipoBoletimf = $_POST["tipoBoletim"];
    }

    if(isset($_POST["dadosCompletos"])) {
        $dadosCompletosf = $_POST["dadosCompletos"];
    }

    if(isset($_POST["dataFinal"]) and $_POST["dataFinal"] != null) {
        $dataFinal = explode("-", $_POST["dataFinal"]);
        $dataFinalFormatUrl = $dataFinal[0] . $dataFinal[1] . $dataFinal[2];
        $dataFinalFormat = $dataFinal[2] . "/" . $dataFinal[1] . "/" . $dataFinal[0];
    }

    $tipoBoletimPeriodo = $_POST["tipoBoletimPeriodo"];

    $dataInicial = explode("-", $_POST["dataInicial"]);
    $dataInicialFormat = $dataInicial[2] . "/" . $dataInicial[1] . "/" . $dataInicial[0];
    $dataInicialFormatUrl = $dataInicial[0] . $dataInicial[1] . $dataInicial[2];

    // echo "meso: " . $mesorregiaof. "<br>";
    // echo "micro: " . $microrregiaof. "<br>";
    // echo "bacia: " . $baciaf. "<br>";
    // echo "tipo boletim: " . $tipoBoletimf. "<br>";
    // echo "dados completos: " . $dadosCompletosf. "<br>";
    // echo "data inicial: " . $_POST["dataInicial"]. "<br>";
    // echo "data final: " . $_POST["dataFinal"]. "<br>";

    // echo "<hr>";

    $hoje = date('d/m/Y');
    // echo $hoje;

    // URL da API
    if($tipoBoletimPeriodo == 'Mensal'){
        $url = 'http://dados.apac.pe.gov.br:41120/blank_json_boletim_met_mes/?DataInicial='.$dataInicialFormatUrl.'&DataFinal='.$dataFinalFormatUrl.'';
    } else { 
        $url = 'http://dados.apac.pe.gov.br:41120/blank_json_boletim_met_mes/?DataInicial='.$dataInicialFormatUrl.'&DataFinal='.$dataInicialFormatUrl.'';  
    }

    // Inicializa o cURL
    $ch = curl_init();

    // Configura as opções do cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Executa a requisição e obtém a resposta
    $response = curl_exec($ch);

    // Verifica por erros
    if(curl_errno($ch)) {
        echo 'Erro ao fazer a requisição: ' . curl_error($ch);
    }

    // Fecha a conexão cURL
    curl_close($ch);

    $data = json_decode($response);
    
    
    ?>

    
    <div class="w-full max-w-6xl mx-auto px-4 py-8 md:px-6 md:py-12">
        <header class="flex flex-col items-center gap-4 mb-8">

            <img src="logo3_apac_2024.png" alt="">

            <div class="text-center">
                <h1 class="text-2xl font-bold">Boletim Pluviométrico</h1>
                <?php
                if($tipoBoletimPeriodo == 'Mensal'){
                ?>
                    <p class="text-gray-500"><?php echo $dataInicialFormat . ' - ' . $dataFinalFormat; ?></p>
                <?php
                } else {
                ?>
                    <p class="text-gray-500"><?php echo $dataInicialFormat ?></p>
                <?php    
                }
                ?>

            </div>
        </header>
        <section class="mb-8">
<!-- MESORREGIAO VAZIA(RETORNA TUDO) -->
            <div>
                <?php
                if(empty($mesorregiaof)) {    
                    foreach($data as $item){
                ?>
            
                <table>
                    <h3>Mesorregião <?php echo $item->mesoregiao ?></h3>
                    <tr>
                        <th>Município</th>
                        <th>Estação</th>
                        <th>Bacia</th>
                        <th>Microregião</th>
                        
                        <th>Chuva Total (mm)</th>
                        
                        <th>Climatologia (mm)</th>
                        <th>Anomalia (mm)</th>
                        <th>Desvio Relativo</th>
                    </tr>
                    <?php
                        foreach($item->estacoes as $estacao){
                        ?>
                    <tr>
                        <td><?php echo $estacao->municipio ?></td>
                        <td><?php echo $estacao->nomeEstacao ?></td>
                        <td><?php echo $estacao->bacia ?></td> 
                        <td><?php echo $estacao->microregiao ?></td>  
                        <td><?php echo $estacao->soma_chuva_resultado ?></td>
                        <td><?php echo $estacao->climatologia ?></td>
                        <td><?php echo $estacao->anomalia ?></td>
                        <td><?php echo $estacao->desvio_relativo ?></td>
                    </tr>
                    <?php
                        }           
                        }
                    }    
                    ?>
                </table>
            </div>
<!-- MESORREGIAO NÃO VAZIA -->
            <div>
                <?php
                if(!empty($mesorregiaof)) {    
                    foreach($data as $item){
                        if($item->mesoregiao == $mesorregiaof){

                ?>
            
                <table>
                    <h3>Mesorregião <?php echo $item->mesoregiao ?></h3>
                    <tr>
                        <th>Município</th>
                        <th>Estação</th>
                        <th>Bacia</th>
                        <th>Microregião</th>
                        
                        <th>Chuva Total (mm)</th>
                        <th>Climatologia (mm)</th>
                        <th>Anomalia (mm)</th>
                        <th>Desvio Relativo</th>
                    </tr>
                    <?php
                        foreach($item->estacoes as $estacao){
                            if(!empty($microrregiaof)){
                                if(!empty($baciaf) and $estacao->bacia == $baciaf and $estacao->microregiao == $microrregiaof) {
                    ?>
                                    <tr>
                                    <td><?php echo $estacao->municipio ?></td>
                                    <td><?php echo $estacao->nomeEstacao ?></td>
                                    <td><?php echo $estacao->bacia ?></td> 
                                    <td><?php echo $estacao->microregiao ?></td>  
                                    <td><?php echo $estacao->soma_chuva_resultado ?></td>
                                    <td><?php echo $estacao->climatologia ?></td>
                                    <td><?php echo $estacao->anomalia ?></td>
                                    <td><?php echo $estacao->desvio_relativo ?></td>
                                    </tr>
                                <?php 
                                } elseif (empty($baciaf) and $estacao->microregiao == $microrregiaof) {
                                ?>
                                    <tr>
                                    <td><?php echo $estacao->municipio ?></td>
                                    <td><?php echo $estacao->nomeEstacao ?></td>
                                    <td><?php echo $estacao->bacia ?></td> 
                                    <td><?php echo $estacao->microregiao ?></td>  
                                    <td><?php echo $estacao->soma_chuva_resultado ?></td>
                                    <td><?php echo $estacao->climatologia ?></td>
                                    <td><?php echo $estacao->anomalia ?></td>
                                    <td><?php echo $estacao->desvio_relativo ?></td>
                                    </tr>
                                <?php
                                }

                            } elseif (!empty($baciaf) and $estacao->bacia == $baciaf){
                                ?>
                                <tr>
                                <td><?php echo $estacao->municipio ?></td>
                                <td><?php echo $estacao->nomeEstacao ?></td>
                                <td><?php echo $estacao->bacia ?></td> 
                                <td><?php echo $estacao->microregiao ?></td>  
                                <td><?php echo $estacao->soma_chuva_resultado ?></td>
                                <td><?php echo $estacao->climatologia ?></td>
                                <td><?php echo $estacao->anomalia ?></td>
                                <td><?php echo $estacao->desvio_relativo ?></td>
                                </tr>
                                <?php
                            } elseif (empty($baciaf) and empty($microrregiaof)) {
                                ?>
                                <tr>
                                <td><?php echo $estacao->municipio ?></td>
                                <td><?php echo $estacao->nomeEstacao ?></td>
                                <td><?php echo $estacao->bacia ?></td> 
                                <td><?php echo $estacao->microregiao ?></td>  
                                <td><?php echo $estacao->soma_chuva_resultado ?></td>
                                <td><?php echo $estacao->climatologia ?></td>
                                <td><?php echo $estacao->anomalia ?></td>
                                <td><?php echo $estacao->desvio_relativo ?></td>
                                </tr>
                                <?php
                            }
                        }
                        }           
                    }
                    }    
                    ?>
                </table>
            </div>
        </section>
        <!-- <section class="mb-8">
            <h2 class="text-lg font-bold mb-2">Autoridades Responsáveis</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white shadow-lg rounded-lg p-4">
                    <h3 class="font-medium text-lg mb-2">Agência Pernambucana de Águas e Clima</h3>
                    <p class="text-gray-500">Monitoramento e Análise</p>
                </div>
                <div class="bg-white shadow-lg rounded-lg p-4">
                    <h3 class="font-medium text-lg mb-2">Agência Ambiental</h3>
                    <p class="text-gray-500">Gestão Ambiental</p>
                </div>
                <div class="bg-white shadow-lg rounded-lg p-4">
                    <h3 class="font-medium text-lg mb-2">Departamento de Saúde</h3>
                    <p class="text-gray-500">Vigilância em Saúde Pública</p>
                </div>
                <div class="bg-white shadow-lg rounded-lg p-4">
                    <h3 class="font-medium text-lg mb-2">Defesa Civil</h3>
                    <p class="text-gray-500">Resposta a Emergências</p>
                </div>
            </div>
        </section> -->
        
        <section class="mb-8">
            <div class="bg-white shadow-lg rounded-lg p-4">
                <h2 class="text-lg font-bold mb-4">Resumo do Boletim</h2>
                <div class="prose prose-gray dark:prose-invert">
                    <p>
                            O Boletim de Pluviometria fornece dados sobre a quantidade de precipitação registrada em um período
                        específico. Ele é essencial para monitorar o clima, prever eventos meteorológicos e planejar 
                        atividades agrícolas e de gestão de recursos hídricos.
                    </p>
                    <p>
                            O Boletim de Umidade apresenta informações sobre os níveis de umidade relativa 
                        do ar em diversas regiões. Esses dados são importantes para entender o 
                        comportamento climático, prevenir problemas de saúde relacionados ao clima e 
                        auxiliar no planejamento agrícola.
                    </p>
                    <p>
                            O Boletim de Temperatura disponibiliza medições das temperaturas máximas e mínimas
                        registradas, ajudando a acompanhar as variações climáticas. Esse boletim é vital 
                        para a agricultura, saúde pública e planejamento energético, oferecendo uma visão 
                        abrangente das condições térmicas de uma região.
                    </p>
                </div>
            </div>
        </section>
    </div>
</body>

</html>
