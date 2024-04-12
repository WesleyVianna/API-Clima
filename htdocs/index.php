    <!-- EM PHP -->
 <?php

    $cidade = isset($_GET["buscaCidade"]) ? $_GET["buscaCidade"]: "";
    $estado = isset($_GET["estados"]) ? $_GET["estados"]: "";


    // Cria um novo recurso cURL
    $ch = curl_init();

    // Configura a URL e opções
    curl_setopt($ch, CURLOPT_URL, "https://api.hgbrasil.com/weather?key=&city_name=$cidade,$estado");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Obtém os dados
    $dados_json = curl_exec($ch);

    // Fecha o recurso cURL e libera recursos internos
    curl_close($ch);

    // Decodifica os dados JSON
    $dados = json_decode($dados_json);

    // Verifica se a decodificação foi bem-sucedida
    if ($dados === null) {
        echo "Erro ao decodificar JSON.";
        exit;
    }

    // Verifica se a resposta da API possui a chave "results"
    if (!isset($dados->results)) {
        echo "Dados inválidos retornados pela API.";
        exit;
    }

    // Agora, podemos acessar os dados desejados e exibi-los
    $temp = $dados->results->temp;
    $description = $dados->results->description;
    $date = $dados->results->date;
    $time = $dados->results->time;
    $condition_slug = $dados-> results-> condition_slug;

    // Acessa os dados de previsão
    $previsao = $dados -> results -> forecast;

    $bodyClass = '';
    if ($condition_slug) {
        $bodyClass = 'condition-' . $condition_slug;
    }

// API de OpenStreetMap

// Supondo que $cidade e $estado já estejam definidos com base na seleção do usuário
$search_url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($cidade . ', ' . $estado) . "&format=json";

// Cria um contexto de stream com o cabeçalho de User-Agent
$context = stream_context_create(array(
    'http' => array(
        'header' => "User-Agent: MeuScriptDeBuscaDeCidades\r\n"
    )
));

$json = file_get_contents($search_url, false, $context);
$decoded = json_decode($json, true);

if ($decoded === null || !isset($decoded[0]["lat"], $decoded[0]["lon"])) {
    error_log("Dados da API não estão no formato esperado.");
} else {
    $lat = $decoded[0]["lat"];
    $lng = $decoded[0]["lon"];
}


    ?>

<!-- EM HTML -->

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="estilos.css">
        <title>Previsão do Tempo</title>
        <link rel="icon" href="https://assets.hgbrasil.com/weather/icons/conditions/clear_day.svg"> <!-- isso vai fazer com que o ícone fica com um sol -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    </head>
    <body>

        <div class="box">
            <center>
                <nav>
                    |
                    <a href="http://localhost/"> Inicio</a>
                    |
                    <a href="https://www.climatempo.com.br/" target="_blank"> Climatempo</a>
                    |
                    <a href="https://hgbrasil.com/status/weather" target="_blank"> API</a>
                    |
                </nav>
            </center>
        </div>

        <div class="caixa">
            <h4>Instituto Privado de Meteorologia</h4>
            <h5>Faça a sua escolha de cidade e estado para consultar o clima</h5>
        <!-- o action é o que vai definir para onde o formulário irá -->
            <div class="formulario">
            <form action="/index.php" method="GET"> 
                    <label for="estados"></label>
                <select id="estados" name="estados">
                    <option value="" disabled selected>Escolha um estado</option>
                </select>
                <select name="buscaCidade" id="buscaCidade">
                <option value="" disabled selected>Escolha um estado antes da cidade</option></select>
                            <button type="submit" class="button">Pesquisar</button>  
            </form>
            </div>
        </div>
        
            <br>

            <?php if (!empty($cidade) && !empty($estado)) : ?>
                <h5><span class="city" id="cityResults">Resultados para <?= $cidade . ', ' . $estado ?></span></h5>
            <?php endif; ?>
        
        <hr class="quebra">
        <?php if (!empty($cidade) && !empty($estado)) : ?>     
    <!-- Exibindo os dados obtidos -->
    <img class="imagem" id="imagemTemp" src="https://assets.hgbrasil.com/weather/icons/conditions/<?= $condition_slug ?>.svg" >
    
    <div class="temperatura" id="temperatura">
        <span class="temp"><?= $temp ?></span> 
        <span class="celsius">ºC</span>
    </div>
    
    <br>
    <div class="direita" id="direita">
        <div class="clima" id="clima">Clima <br><?= $description ?> </div>
        <div class="datahora" id="datahora"><?= $date . ' - '. $time ?></div>
    </div>
        
    <div id="map" style="width: 100%; height: 160px;"></div>

    <!-- <br><br><br><br><br> -->
    
    <div class="row">
        <?php foreach ($previsao as $dia): ?>
            <div class='col m-4 text-center'>
                <?= $dia->weekday?>
                <br>
                <img class="imagem" alt="Sol" src="https://assets.hgbrasil.com/weather/icons/conditions/<?= $dia -> condition ?>.svg" id="wob_tci" data-csiid="19" data-atf="1">
                <br>
                Max Min
                <br>
                <?= $dia->max?> °    <?= $dia->min ?> °
            </div>
        <?php endforeach; ?>        
            </div>
            <?php endif; ?>     

            
            <!-- EM jQUERY -->
            <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
            <script>

            $(document).ready(function() {
                // Altera o atributo action do formulário para localhost sem index.php
                $("form").attr("action", "http://localhost");
            });
            
            // Carregar os estados do arquivo JSON
            fetch('estados.json')
            .then(response => response.json())
            .then(data => {
                const selectEstado = document.getElementById('estados');
                data.forEach(estado => {
                const option = document.createElement('option');
                option.value = estado.sigla;
                option.text = estado.nome;
                selectEstado.appendChild(option);
                });

                $(selectEstado).on("change", function(){
                    let estado = $(this).val();
                    $.ajax({
                        url: "https://servicodados.ibge.gov.br/api/v1/localidades/estados/"+estado+"/municipios",
                        method: "GET",
                        success: function(data) {
                            $("#cityResults").show();
                            
                            var options = "";    

                            $(data).each(function (indice, cidade) {
                                options += "<option value='"+cidade.nome+"'>"+cidade.nome+"</option>";
                            })

                            $("#buscaCidade").html(options)
                        },

                        error: function() {
                            alert("Não foi possível encontrar as cidades do " + estado);
                        }
                    });
                }); 
                
            })
            .catch(error => console.error('Erro ao carregar estados:', error));
    </script>

            <script>
                var map = L.map('map').setView([<?php echo $lat; ?>, <?php echo $lng; ?>], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
                L.marker([<?php echo $lat; ?>, <?php echo $lng; ?>]).addTo(map)
                    .bindPopup('<? $cidade ?>, <? $estado?>')
                    .openPopup();
                
                
            </script>
    <br>
        <div class="direitos">
            <footer class="main-footer">    
                <strong>Copyright © 2024</strong>
                Todos os direitos reservados.
                <div class="float-right d-none d-sm-inline-block">
                </div>
            </footer>
        </div>

    </body>
    </html>