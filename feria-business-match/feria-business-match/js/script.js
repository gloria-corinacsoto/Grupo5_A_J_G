// =========================================================
// CONTADOR DE CUENTA ATRÁS (petición 6)
// =========================================================

// Cambia aquí la fecha y hora de inicio de la feria (año, mes, día, hora, minuto).
// OJO: los meses empiezan en 0, así que enero = 0.
const fechaFeria = new Date(2027, 0, 21, 9, 0, 0);

// Pone un 0 delante si el número tiene una sola cifra (5 -> "05")
function dosCifras(numero) {
  return String(numero).padStart(2, "0");
}

function actualizarContador() {
  const ahora = new Date();
  let falta = fechaFeria - ahora;   // milisegundos que quedan

  // Si la feria ya ha empezado, dejamos todo a cero
  if (falta < 0) {
    falta = 0;
  }

  const segundosTotales = Math.floor(falta / 1000);
  const dias = Math.floor(segundosTotales / 86400);
  const horas = Math.floor((segundosTotales % 86400) / 3600);
  const minutos = Math.floor((segundosTotales % 3600) / 60);
  const segundos = segundosTotales % 60;

  document.getElementById("dias").textContent = dosCifras(dias);
  document.getElementById("horas").textContent = dosCifras(horas);
  document.getElementById("minutos").textContent = dosCifras(minutos);
  document.getElementById("segundos").textContent = dosCifras(segundos);
}

actualizarContador();                   // lo ejecutamos una vez al cargar
setInterval(actualizarContador, 1000);  // y después cada segundo
