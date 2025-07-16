<?php
ini_set('display_errors', 1); // mostra gli errori a video
ini_set('display_startup_errors', 1); // mostra gli errori di avvio di PHP a video
error_reporting(E_ALL); // mostra tutti i tipi degli errori
header('Content-Type: application/json'); // consenti l'invio di risposte JSON
header('Access-Control-Allow-Origin: *'); // consenti richieste da qualsiasi origine
header('Access-Control-Allow-Methods: POST, GET, OPTIONS'); // consenti richieste da metodi GET, POST, e OPTIONS
header('Access-Control-Allow-Headers: Content-Type');

require_once 'connessione.php'; // inclusione del file di connessione

// gestione pre-flight CORS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ricezione dati JSON (tramite POST body)
$input = file_get_contents('php://input'); // legge il contenuto del body della richiesta
$data = json_decode($input, true); // decodifica il JSON in un array associativo

// funzione per loggare gli errori
function logError($message) {
    $logFile = 'istruzioni_errori.log';
    error_log(date('[Y-m-d H:i:s]') . ' ' . $message . PHP_EOL, 3, $logFile);
}

// validazione campi obbligatori
$requiredFields = [
    "azione", "utente", "azienda", "protocollo", "nomeProgetto", "modulo",
    "numeroEdizione", "modalita", "data", "oraInizio", "oraFine", "sede", "docenteCF"
];

foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
        logError("Campo obbligatorio mancante: " . $field);
        echo json_encode(["success" => false, "error" => "Campo obbligatorio '" . $field . "' mancante o vuoto."]);
        exit();
    }
}

// validazione piattaforma se modalita è "remoto"
if ($data['modalita'] === 'Remoto' && (!isset($data['piattaforma']) || trim($data['piattaforma']) === '')) {
    logError("Campo 'piattaforma' obbligatorio per modalità Remoto.");
    echo json_encode(["success" => false, "error" => "Il campo 'Piattaforma' è obbligatorio per la modalità 'Remoto'."]);
    exit();
}
// map dei dati obbligatori
$azione = $data['azione'];
$utente = $data['utente'];
$azienda = $data['azienda'];
$protocollo = $data['protocollo'];
$nomeProgetto = $data['nomeProgetto'];
$modulo = $data['modulo'];
$numeroEdizione = (int) $data['numeroEdizione'];
$avviso = (int) $data['avviso'];
$individuale = (int) $data['individuale'];
$modalita = $data['modalita'];
$dataLezione = $data['data'];
$oraInizio = $data['oraInizio'];
$oraFine = $data['oraFine'];
$sede = $data['sede'];
$docenteCF = $data['docenteCF'];

// campi opzionali
$oraInizioPausa = $data['oraInizioPausa'] ?? null;
$oraFinePausa = $data['oraFinePausa'] ?? null;
$note = $data['note'] ?? null;
$piattaforma = $data['piattaforma'] ?? null;

// gestione del campo 'dirigenti'
$dirigentiJson = null;
if (isset($data['dirigenti'])) {
    if (is_array($data['dirigenti'])) {
        // il front-end invia un array, lo convertiamo in stringa JSON per il DB
        $dirigentiJson = json_encode($data['dirigenti']);
        if ($dirigentiJson === false) {
             logError("Errore nella codifica JSON del campo 'dirigenti'.");
             echo json_encode(["success" => false, "error" => "Errore nella codifica JSON dei dirigenti."]);
             exit();
        }
    } else {
        logError("Il campo 'dirigenti' non è un array valido.");
        echo json_encode(["success" => false, "error" => "Il campo 'dirigenti' deve essere un array di oggetti JSON."]);
        exit();
    }
}

// inizio transazione SQL
try {
    $conn->beginTransaction(); // inizia la transazione SQL

    $sql = "INSERT INTO lezioni (
                azione, utente, azienda, protocollo, nomeProgetto, modulo,
                numeroEdizione, avviso, individuale, dirigenti, modalita,
                piattaforma, data, oraInizio, oraFine, oraInizioPausa,
                oraFinePausa, note, sede, docenteCF
            ) VALUES (
                :azione, :utente, :azienda, :protocollo, :nomeProgetto, :modulo,
                :numeroEdizione, :avviso, :individuale, :dirigenti, :modalita,
                :piattaforma, :data, :oraInizio, :oraFine, :oraInizioPausa,
                :oraFinePausa, :note, :sede, :docenteCF
            )";

    $stmt = $conn->prepare($sql); // preparazione della query SQL con i parametri


    $stmt->bindParam(':azione', $azione);
    $stmt->bindParam(':utente', $utente);
    $stmt->bindParam(':azienda', $azienda);
    $stmt->bindParam(':protocollo', $protocollo);
    $stmt->bindParam(':nomeProgetto', $nomeProgetto);
    $stmt->bindParam(':modulo', $modulo);
    $stmt->bindParam(':numeroEdizione', $numeroEdizione, PDO::PARAM_INT);
    $stmt->bindParam(':avviso', $avviso, PDO::PARAM_BOOL);
    $stmt->bindParam(':individuale', $individuale, PDO::PARAM_BOOL);
    $stmt->bindParam(':dirigenti', $dirigentiJson);
    $stmt->bindParam(':modalita', $modalita);
    $stmt->bindParam(':piattaforma', $piattaforma);
    $stmt->bindParam(':data', $dataLezione);
    $stmt->bindParam(':oraInizio', $oraInizio);
    $stmt->bindParam(':oraFine', $oraFine);
    $stmt->bindParam(':oraInizioPausa', $oraInizioPausa);
    $stmt->bindParam(':oraFinePausa', $oraFinePausa);
    $stmt->bindParam(':note', $note);
    $stmt->bindParam(':sede', $sede);
    $stmt->bindParam(':docenteCF', $docenteCF);

    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Dati inseriti con successo']);

} catch (PDOException $e) {
    $conn->rollBack(); // annulla la transazione SQL in caso di errore
    logError("Errore durante l'inserimento nel DB: " . $e->getMessage() . " - Dati: " . json_encode($data));
    echo json_encode([
        "success" => false,
        "error" => "Errore DB: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    $conn->rollBack();
    logError("Errore generico: " . $e->getMessage() . " - Dati: " . json_encode($data));
    echo json_encode([
        "success" => false,
        "error" => "Errore generico: " . $e->getMessage()
    ]);
}
?>
