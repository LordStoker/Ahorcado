<?php
session_start(); // Iniciar la sesión
//Constantes de conexión
if(isset($_POST["reset"])){
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
}

DEFINE("HOST", "gbd_mysql_A");
DEFINE("USERNAME", "root");
DEFINE("PASSWD", "");
DEFINE("DB", "Peliculas");

// Crear conexión.
$conn = mysqli_connect(HOST, USERNAME, PASSWD, DB);
// Consulta para obtener un título de película aleatorio


// Verificar si es la primera vez que se inicia la sesión para inicializar las variables del juego.
if (!isset($_SESSION["variablesInicializadas"])) {
    $sql = "SELECT titulo FROM Peliculas ORDER BY RAND() LIMIT 1";
    $result = $conn->query($sql);

    // Obtener el título de la película
    $row = $result->fetch_assoc();

    // Inicializar las variables del juego.
    $_SESSION["vidas"] = 6; // Número de intentos permitidos.
    $_SESSION["peliculaOculta"] = ""; //String que guarda los intentos de las letras
    $_SESSION["peliculaAdivinada"] = false;  //Booleano que determina si se acierta o no la peli
    $_SESSION["tituloPelicula"] = strtoupper($row["titulo"]); // Convertir el título a mayúsculas.
    $_SESSION["letraUsada"] = "";
    // Inicializar el string de la peliculaOculta con guiones bajos o espacios si tiene más de 1 palabra.
    for ($i = 0; $i < strlen($_SESSION["tituloPelicula"]); $i++) {
        if ($_SESSION["tituloPelicula"][$i] != " ") {
            $_SESSION["peliculaOculta"] .= "_";
        } else if($_SESSION["tituloPelicula"][$i] == " "){
            $_SESSION["peliculaOculta"] .= " "; // Varios espacios para diferenciarlos de los guiones
        }
    }
    $_SESSION["variablesInicializadas"] = true;
}


//Funciones
function guardarLetraUsada($letraUsada){
    $exists = false;
    for($i = 0; $i < strlen($_SESSION["letraUsada"]); $i++){
        if($_SESSION["letraUsada"][$i] === $letraUsada){
            $exists = true;
            echo "<script>alert('Esta letra ya se ha utilizado')</script>";
            break;
        }
    }
    if(!$exists){
        $_SESSION["letraUsada"] .= " [  " . $letraUsada . "  ] ";
    }
    echo "Letras utilizadas :" . $_SESSION["letraUsada"];
}



function mostrarPeliOculta($peliOculta){
    echo "<br> Título de la película: <span style = 'letter-spacing:10px'>" . $peliOculta . "</span><br>";
}

function comprobarPeliAcertada(){
    if ($_SESSION["peliculaOculta"] == $_SESSION["tituloPelicula"]){
        //(strpos($_SESSION["peliculaOculta"], "_") === false) {
        //$_SESSION["peliculaAdivinada"] = true;
        echo "Has adivinado la película. ¡Enhorabuena!";
        return true;
    }
    else{
        return false;
    }
}

// Función para dibujar el ahorcado
function dibujarAhorcado($intentosRestantes){
    echo "Intentos restantes: " . $_SESSION["vidas"] . "<br>";
    $ahorcado = [
        "  ____",
        "  |  |",
        "  |  " . ($intentosRestantes < 6 ? "O" : ""),
        "  | " . ($intentosRestantes < 4 ? "/" : "") . ($intentosRestantes < 5 ? "|" : "") . ($intentosRestantes < 3 ? "\\" : ""),
        "  | " . ($intentosRestantes < 2 ? "/" : "") . " " . ($intentosRestantes < 1 ? "\\" : ""),
        "__|__"
    ];
    foreach ($ahorcado as $linea) {
        echo $linea . "<br>";
    }
}
 // Mostrar el título de la película con guiones que se irá rellenando según se acierten las letras.
function comprobarLetras($letra){
    $encontrada = false;

    for ($i = 0; $i < strlen($_SESSION["tituloPelicula"]); $i++) {
        if ($_SESSION["tituloPelicula"][$i] == $letra) {
            $_SESSION["peliculaOculta"][$i] = $letra;
            $encontrada = true;
        }
        
    }
    if(!$encontrada){
        $_SESSION["vidas"] --;
    }

}

function verificarVidas(){
    if ($_SESSION["vidas"] == 0) {
        echo "¡Has perdido! La película era: " . $_SESSION["tituloPelicula"] . "<br>";
        return true;
    }
    return false;
}

function haGanado(){
    return (verificarVidas() == false) && comprobarPeliAcertada();
}

function jugarAhorcado(){
    // Dibujar el ahorcado   
    dibujarAhorcado($_SESSION["vidas"]);
    // Verificar si se ha enviado una letra para adivinar
    if (!haGanado()){
        if (isset($_POST["boton"])) {

        
            $letra = strtoupper($_POST["letra"]);
             // Convertir la letra a mayúsculas introducida en el input text del formulario con name letra.
            // Verificar si la letra adivinada está en el título de la película.
            //$letraAdivinada = false;
            //Guarda las letras que se van usando cada vez que se introduce.
            guardarLetraUsada($letra);

            
            comprobarLetras($letra);
                // Mostrar el título de la película con las letras adivinadas y los guiones bajos. 
            mostrarPeliOculta($_SESSION["peliculaOculta"]);

        }
        else{
            echo "Has ganado";
                // Mostrar el título de la película con las letras adivinadas y los guiones bajos. 
            mostrarPeliOculta($_SESSION["peliculaOculta"]);
        }        
    }   
}
 
if (!verificarVidas() && !comprobarPeliAcertada()){
    echo "<h1>Juego del Ahorcado</h1>";

    jugarAhorcado();
}

// Verificar si el usuario ha perdido todas las vidas o ha acertado.

/*if ($_SESSION["peliculaAdivinada"]) {
    echo "Has adivinado la película. ¡Enhorabuena!";
}*/
// Cerrar conexión 
//  session_destroy();
mysqli_close($conn);

?>
<!DOCTYPE html>
<html>

<head>
    <title>Juego del Ahorcado</title>
</head>

<body>

    <form action="" method="post">
        <label for="letra">Introduce una letra:</label>
        <input type="text" id="letra" name="letra" maxlength="1" required autofocus>
        <input type="submit" value="Adivinar" name="boton">
    </form>
    <form action="" method="post">
        <button value="Nueva Partida" name="reset">Nueva Partida</button>  
    </form>

</body>

</html>