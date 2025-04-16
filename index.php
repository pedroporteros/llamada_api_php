<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formula 1 - Datos Históricos</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
  <header>
    <div class="container">
      <h1><i class="fas fa-flag-checkered"></i> Formula 1 - Datos Históricos</h1>
    </div>
  </header>
  
  <div class="container">
    <div class="controls">
      <h2>Selecciona una temporada</h2>
      <div>
        <select id="year-select">
          <option value="2023">2023</option>
          <option value="2022">2022</option>
          <option value="2021">2021</option>
          <option value="2020">2020</option>
          <option value="2019">2019</option>
          <option value="2018">2018</option>
          <option value="2017">2017</option>
          <option value="2016">2016</option>
          <option value="2015">2015</option>
          <option value="2014">2014</option>
        </select>
        <button id="consulta-btn"><i class="fas fa-search"></i> Consultar</button>
      </div>
    </div>
    
    <!-- Área donde se mostrarán los resultados -->
    <div id="resultado">
      <div class="loading-placeholder" style="display:none;">
        <p>Cargando datos de la temporada...</p>
      </div>
    </div>
  </div>
  
  <script src="assets/app.js"></script>
</body>
</html>