var fechaFeria = new Date(2027, 0, 21, 9, 0, 0);

function ponerCero(numero){
    if (numero < 10) {
        return "0" + numero;
    }
    return numero;
}

function actualizarContador(){
    var ahora = new Date();
    var falta = fechaFeria - ahora;

    if (falta < 0) {
        falta = 0;
    }

    var segundosTotales = Math.floor(falta / 1000);
    var dias = Math.floor(segundosTotales / 86400);
    var horas = Math.floor((segundosTotales % 86400) / 3600);
    var minutos = Math.floor((segundosTotales % 3600) / 60);
    var segundos = segundosTotales % 60;

    document.getElementById("dias").textContent = ponerCero(dias);
    document.getElementById("horas").textContent = ponerCero(horas);
    document.getElementById("minutos").textContent = ponerCero(minutos);
    document.getElementById("segundos").textContent = ponerCero(segundos);
}

actualizarContador();
setInterval(actualizarContador, 1000);
