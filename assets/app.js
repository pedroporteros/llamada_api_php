// assets/app.js

document.getElementById('consulta-btn').addEventListener('click', function() {
    var year = document.getElementById('year-select').value;
    
    // Mostrar indicador de carga
    document.getElementById('resultado').innerHTML = '<div class="loading-placeholder">Cargando datos de la temporada ' + year + '... <i class="fas fa-spinner fa-spin"></i></div>';
    
    fetch('api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ year: year })
    })
    .then(response => response.json())
    .then(data => {
        if(data.error) {
          alert("Error: " + data.error);
        } else {
          displayData(data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Se produjo un error al realizar la consulta.");
    });
  });
  
  function displayData(data) {
    var resultadoDiv = document.getElementById('resultado');
    resultadoDiv.innerHTML = formatDataAsCard(data);
  }
  
  function formatDataAsCard(data) {
    let html = `<h2 class="season-title">Temporada F1 ${data.temporada}</h2>`;
    
    // Sección 1: Clasificación Mundial de Pilotos
    html += `<div class="section">
               <h2><i class="fas fa-user"></i> Clasificación Mundial de Pilotos</h2>`;
    
    if (data.clasificacionPilotos && data.clasificacionPilotos.length > 0) {
      html += `<table>
                 <thead>
                   <tr>
                     <th>Pos</th>
                     <th>Piloto</th>
                     <th>Equipo</th>
                     <th>Puntos</th>
                   </tr>
                 </thead>
                 <tbody>`;
      
      // Destacar los tres primeros con clases especiales
      data.clasificacionPilotos.forEach(item => {
        let positionClass = '';
        if (item.posicion === 1) positionClass = 'gold';
        else if (item.posicion === 2) positionClass = 'silver';
        else if (item.posicion === 3) positionClass = 'bronze';
        
        html += `<tr class="${positionClass}">
                   <td>${item.posicion}</td>
                   <td>${item.piloto}</td>
                   <td>${item.equipo}</td>
                   <td><strong>${item.puntos}</strong></td>
                 </tr>`;
      });
      
      html += `</tbody></table>`;
    } else {
      html += `<p>No hay datos de clasificación de pilotos disponibles.</p>`;
    }
    html += `</div>`;
  
    // Sección 2: Clasificación Mundial de Constructores
    html += `<div class="section">
               <h2><i class="fas fa-building"></i> Clasificación Mundial de Constructores</h2>`;
    
    if (data.clasificacionConstructores && data.clasificacionConstructores.length > 0) {
      html += `<table>
                 <thead>
                   <tr>
                     <th>Pos</th>
                     <th>Equipo</th>
                     <th>Puntos</th>
                   </tr>
                 </thead>
                 <tbody>`;
      
      data.clasificacionConstructores.forEach(item => {
        let positionClass = '';
        if (item.posicion === 1) positionClass = 'gold';
        else if (item.posicion === 2) positionClass = 'silver';
        else if (item.posicion === 3) positionClass = 'bronze';
        
        html += `<tr class="${positionClass}">
                   <td>${item.posicion}</td>
                   <td>${item.equipo}</td>
                   <td><strong>${item.puntos}</strong></td>
                 </tr>`;
      });
      
      html += `</tbody></table>`;
    } else {
      html += `<p>No hay datos de clasificación de constructores disponibles.</p>`;
    }
    html += `</div>`;
  
    // Sección 3: Carreras
    html += `<div class="section">
               <h2><i class="fas fa-flag-checkered"></i> Carreras ${data.temporada}</h2>`;
    
    if (data.carreras && data.carreras.length > 0) {
      data.carreras.forEach(carrera => {
        html += `<div class="race">
                   <h4>${carrera.nombre}</h4>
                   <div class="race-info">
                     <span><i class="fas fa-map-marker-alt"></i> ${carrera.circuito}</span>
                     <span><i class="far fa-calendar-alt"></i> ${formatDate(carrera.fecha)}</span>
                   </div>`;
        
        if (carrera.podio && carrera.podio.length > 0) {
          html += `<h5>Podio:</h5>
                   <div class="podium-container">`;
          
          // Ordenamos el podio por posición (1, 2, 3)
          const podioOrdenado = [...carrera.podio].sort((a, b) => a.posicion - b.posicion);
          
          // Creamos elemento visual para cada posición del podio
          podioOrdenado.forEach(p => {
            html += `<div class="podium-position position-${p.posicion}">
                       <div class="podium-pos">P${p.posicion}</div>
                       <div class="podium-name">${p.piloto}</div>
                       <div class="podium-team">${p.equipo}</div>
                     </div>`;
          });
          
          html += `</div>`;
        } else {
          html += `<p>No hay datos del podio disponibles.</p>`;
        }
        
        html += `</div>`;
      });
    } else {
      html += `<p>No hay datos de carreras disponibles.</p>`;
    }
    html += `</div>`;
  
    // Sección 4: Equipos y Alineación
    html += `<div class="section">
               <h2><i class="fas fa-users"></i> Equipos y Alineación</h2>`;
    
    if (data.equipos && data.equipos.length > 0) {
      data.equipos.forEach(equipo => {
        html += `<div class="team">
                   <h4>${equipo.nombre}</h4>`;
        
        if (equipo.pilotos && equipo.pilotos.length > 0) {
          html += `<table>
                     <thead>
                       <tr>
                         <th>Piloto</th>
                         <th>Número</th>
                         <th>Nacionalidad</th>
                       </tr>
                     </thead>
                     <tbody>`;
          
          equipo.pilotos.forEach(piloto => {
            html += `<tr>
                       <td>${piloto.nombre}</td>
                       <td>${piloto.numero}</td>
                       <td>${piloto.nacionalidad}</td>
                     </tr>`;
          });
          
          html += `</tbody></table>`;
        } else {
          html += `<p>No hay datos de pilotos para este equipo.</p>`;
        }
        
        html += `</div>`;
      });
    } else {
      html += `<p>No hay datos de equipos disponibles.</p>`;
    }
    html += `</div>`;
  
    return html;
  }
  
  // Función auxiliar para formatear fechas
  function formatDate(dateString) {
    try {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('es-ES', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
      }).format(date);
    } catch (e) {
      return dateString; // Si hay error, mostrar la fecha original
    }
  }