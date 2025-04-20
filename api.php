<?php
// api.php

header("Content-Type: application/json");

// Leer el cuerpo de la solicitud (JSON enviado desde JS)
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['year'])) {
    echo json_encode(['error' => 'No se ha especificado el año.']);
    exit;
}

$year = $input['year'];

// Construir el prompt utilizando el año recibido.
// Usamos HEREDOC para mantener el formato de la cadena de forma clara.
$prompt = <<<EOT
Eres un experto en Fórmula 1 y debes generar un JSON con la información correspondiente a la temporada del año "$year". El JSON debe incluir únicamente los siguientes datos y ninguna explicación adicional:

1. Clasificación mundial de pilotos.
2. Clasificación mundial de constructores.
3. Los podios/resultados de cada carrera del calendario de la temporada.
4. Los equipos participantes en esa temporada y, para cada equipo, su alineación de pilotos.

Necesito que me proporciones datos completos sobre la temporada de Fórmula 1 del año $year. Genera un objeto JSON con la siguiente estructura exacta, sin texto explicativo adicional:

{
  "temporada": $year,
  "clasificacionPilotos": [
    {"posicion": 1, "piloto": "Nombre Apellido", "equipo": "Nombre Equipo", "puntos": 000},
    {"posicion": 2, "piloto": "Nombre Apellido", "equipo": "Nombre Equipo", "puntos": 000}
    // Clasificación completa ordenada por posición
  ],
  "clasificacionConstructores": [
    {"posicion": 1, "equipo": "Nombre Equipo", "puntos": 000},
    {"posicion": 2, "equipo": "Nombre Equipo", "puntos": 000}
    // Clasificación completa ordenada por posición
  ],
  "carreras": [
    {
      "nombre": "Gran Premio de X",
      "circuito": "Nombre del Circuito",
      "fecha": "YYYY-MM-DD",
      "podio": [
        {"posicion": 1, "piloto": "Nombre Apellido", "equipo": "Nombre Equipo"},
        {"posicion": 2, "piloto": "Nombre Apellido", "equipo": "Nombre Equipo"},
        {"posicion": 3, "piloto": "Nombre Apellido", "equipo": "Nombre Equipo"}
      ]
    }
    // Información de todas las carreras de la temporada ordenadas cronológicamente
  ],
  "equipos": [
    {
      "nombre": "Nombre Equipo",
      "pilotos": [
        {"nombre": "Nombre Apellido", "numero": 00, "nacionalidad": "País"},
        {"nombre": "Nombre Apellido", "numero": 00, "nacionalidad": "País"}
      ]
    }
    // Información de todos los equipos participantes
  ]
}
Devuelve únicamente el JSON solicitado, sin ningún texto adicional antes o después.
EOT;

// Configurar la API a la que se le hará la consulta al modelo de IA.
$apiKey = 'api_key'; 
$modelName = 'gemini-2.0-flash';
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}";

// Preparar los datos a enviar en el formato correcto para la API de Gemini
$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ],
];

// Inicializar cURL para enviar la solicitud a la API de IA
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);

$response = curl_exec($ch);

// Manejar errores de cURL
if(curl_errno($ch)) {
    echo json_encode(['error' => 'Error en cURL: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Verificar el código de estado HTTP
if ($httpCode >= 400) {
     echo json_encode([
        'error' => 'Error en la API de IA',
        'httpCode' => $httpCode,
        'response' => json_decode($response) // Intenta decodificar la respuesta de error
     ]);
     exit;
}


// Verificar que la respuesta sea un JSON válido
$jsonResponse = json_decode($response, true);
if (!$jsonResponse) {
    // Podría ser que la respuesta no sea JSON o que el JSON esté mal formado
    // Intenta mostrar la respuesta cruda para depurar si es necesario
    error_log("Respuesta no JSON de la API: " . $response);
    echo json_encode(['error' => 'La respuesta del modelo no es JSON válido.']);
    exit;
}

// Extraer el contenido generado
if (isset($jsonResponse['candidates'][0]['content']['parts'][0]['text'])) {
    $generatedContent = $jsonResponse['candidates'][0]['content']['parts'][0]['text'];

    $cleanedContent = preg_replace('/^```(?:json)?\s*|\s*```$/', '', trim($generatedContent));

    // Intenta decodificar el contenido *limpio* si esperas un JSON
    $finalJsonOutput = json_decode($cleanedContent, true); // Usa $cleanedContent aquí

    if ($finalJsonOutput === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log("El contenido generado por la IA no es JSON válido (después de limpiar): " . $cleanedContent);
        error_log("Contenido original de la IA: " . $generatedContent);
        echo json_encode(['error' => 'El contenido generado por la IA no es JSON válido.', 'raw_content' => $generatedContent]);
    } else {
        // Devolver el JSON generado por la IA ya decodificado
        header('Content-Type: application/json');
        echo json_encode($finalJsonOutput);
    }
} elseif (isset($jsonResponse['error'])) {
     // La API devolvió un error estructurado
     echo json_encode(['error' => 'Error de la API: ' . $jsonResponse['error']['message']]);
}
else {
    // La estructura de la respuesta no es la esperada
    error_log("Estructura de respuesta inesperada: " . $response);
    echo json_encode(['error' => 'Respuesta inesperada del modelo.', 'raw_response' => $jsonResponse]);
}

?>
