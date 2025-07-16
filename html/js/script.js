// richiamo la function al caricamento della pagina
document.addEventListener('DOMContentLoaded', () => {
    // recupero elementi del dom
    const lessonForm = document.getElementById('lessonForm');
    const modalitaSelect = document.getElementById('modalita');
    const piattaformaInput = document.getElementById('piattaforma');
    const labelPiattaforma = document.getElementById('labelPiattaforma');

    // elementi per la gestione dinamica dei dirigenti
    const dirigentiContainer = document.getElementById('dirigentiContainer');
    const addDirigenteBtn = document.getElementById('addDirigenteBtn');
    const dirigenteNomeInput = document.getElementById('dirigenteNome');
    const dirigenteCognomeInput = document.getElementById('dirigenteCognome');
    const dirigenteCFInput = document.getElementById('dirigenteCF');

    let dirigentiList = []; // array per memorizzare i dati dei dirigenti

    // funzione per mostrare/nascondere il campo piattaforma in base alla modalità selezionata
    const togglePiattaformaField = () => {
        if (modalitaSelect.value === 'Remoto') {
            piattaformaInput.style.display = 'block';
            labelPiattaforma.style.display = 'block';
            piattaformaInput.setAttribute('required', 'required');
        } else {
            piattaformaInput.style.display = 'none';
            labelPiattaforma.style.display = 'none';
            piattaformaInput.removeAttribute('required');
            piattaformaInput.value = ''; // Pulisci il campo se non è richiesto
        }
    };

    // funzione per renderizzare la lista dei dirigenti
    const renderDirigenti = () => {
        dirigentiContainer.innerHTML = ''; // pulisci il contenitore
        if (dirigentiList.length === 0) {
            dirigentiContainer.innerHTML = '<p>Nessun dirigente aggiunto.</p>';
        }
        dirigentiList.forEach((dirigente, index) => {
            const dirigenteDiv = document.createElement('div');
            dirigenteDiv.classList.add('dirigente-item');
            dirigenteDiv.innerHTML = `
                <span>${dirigente.nome} ${dirigente.cognome} (${dirigente.CF})</span>
                <button type="button" data-index="${index}">Rimuovi</button>
            `;
            dirigentiContainer.appendChild(dirigenteDiv);
        });
    };

    // event listener per aggiungere un dirigente
    addDirigenteBtn.addEventListener('click', () => {
        const nome = dirigenteNomeInput.value.trim();
        const cognome = dirigenteCognomeInput.value.trim();
        const cf = dirigenteCFInput.value.trim();

        if (nome && cognome && cf) {
            dirigentiList.push({ nome, cognome, CF: cf });
            renderDirigenti();
            // pulisci i campi del mini-form
            dirigenteNomeInput.value = '';
            dirigenteCognomeInput.value = '';
            dirigenteCFInput.value = '';
        } else {
            alert('Per favore, compila tutti i campi (Nome, Cognome, Codice Fiscale) per il dirigente.');
        }
    });

    // event listener per rimuovere un dirigente
    dirigentiContainer.addEventListener('click', (event) => {
        if (event.target.tagName === 'BUTTON' && event.target.textContent === 'Rimuovi') {
            const indexToRemove = parseInt(event.target.dataset.index);
            dirigentiList.splice(indexToRemove, 1);
            renderDirigenti();
        }
    });

    // inizializzo la visualizzazione del campo piattaforma e dei dirigenti
    togglePiattaformaField();
    renderDirigenti(); // mostra "Nessun dirigente aggiunto." all'inizio
    modalitaSelect.addEventListener('change', togglePiattaformaField);

    lessonForm.addEventListener('submit', async (event) => {
        event.preventDefault(); // impedisce l'invio predefinito del form

        const formData = new FormData(lessonForm); // crea un oggetto FormData con i valori del form
        const data = {}; // oggetto vuoto per contenere i dati

        // raccolgo i dati del form (escludendo i campi dinamici gestiti a parte)
        for (let [key, value] of formData.entries()) {
            // ignoro i campi del mini-form dirigenti, perché c'è dirigentiList
            if (!key.startsWith('dirigente')) {
                data[key] = value;
            }
        }

        // gestione di avviso e individuale (checkbox)
        data.avviso = lessonForm.elements['avviso'].checked;
        data.individuale = lessonForm.elements['individuale'].checked;

        // aggiungo l'array di dirigenti all'oggetto data
        data.dirigenti = dirigentiList;

        // converto NumeroEdizione in numero
        data.numeroEdizione = parseInt(data.numeroEdizione);

        // campi opzionali
        if (!data.oraInizioPausa) data.oraInizioPausa = null;
        if (!data.oraFinePausa) data.oraFinePausa = null;
        if (!data.note) data.note = null;
        if (!data.piattaforma) data.piattaforma = null; // se non "Remoto", sarà null

        // log del JSON completo
        console.log("Dati del form da inviare al backend (JSON):", JSON.stringify(data, null, 2));

        // invio i dati al backend
        try {
            const response = await fetch('http://localhost:8080/index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.success) {
                alert('Dati inseriti con successo!');
                lessonForm.reset(); // resetta il form dopo il successo
                togglePiattaformaField(); // resetta lo stato del campo piattaforma
                dirigentiList = []; // pulisce la lista dei dirigenti
                renderDirigenti(); // aggiorna la visualizzazione
            } else {
                alert('Errore durante l\'inserimento dei dati: ' + result.error);
                console.error('Errore backend:', result.error);
            }
        } catch (error) {
            console.error('Errore di rete o del server:', error);
            alert('Si è verificato un errore di comunicazione con il server.');
        }
    });
});
