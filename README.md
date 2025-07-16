# Progetto Landing Page Lezioni

Questo progetto è una mini-applicazione web che include una landing page front-end con un form dinamico e un back-end **PHP** per l'inserimento dei dati in un **database MySQL**, il tutto orchestrato tramite **Docker**.

---

## Caratteristiche del Progetto

### **Front End (Livello 1):**
- Landing page creata con **HTML**, **CSS** e **JavaScript vanilla**.
- Form per l'inserimento di dati relativi a **lezioni** (Azione, Utente, Azienda, Progetto, ecc.).
- Campo **"Dirigenti" dinamico**:
  - Permette l'aggiunta multipla di nomi, cognomi e codici fiscali.
  - I dati vengono serializzati in un array **JSON**.
- **Validazione lato client** per il campo *Piattaforma* (obbligatorio se la modalità è "Remoto").
- Visualizzazione in **console** del JSON completo dei dati prima dell'invio al backend.
- **Nota:** Il design della landing page non è responsive.

### **Back End (Livello 2):**
- File `index.php` che riceve i dati del form tramite **POST (JSON body)**.
- Connessione al database **MySQL** tramite **PDO (PHP Data Objects)**.
- Utilizzo di **transazioni SQL** (`beginTransaction`, `commit`, `rollBack`) per garantire l'integrità dei dati.
- **Prepared statements** (`prepare`, `execute`) per prevenire SQL injection.
- **Validazione server-side** dei campi obbligatori.
- Gestione degli errori con **logging su file** (`istruzioni_errori.log`).
- Risposta in formato **JSON**:
  ```json
  {"success": true} 
  oppure 
  {"success": false, "error": "..."}

### **Orchestrazione con Docker:**

* `docker-compose.yml`: Definisce due servizi principali:

  * **db:** Un container MySQL 8.0 per il database.
  * **php:** Un container PHP 8.2 con Apache che serve sia il backend PHP che il frontend HTML/CSS/JS.
* `.env`: File per la configurazione delle variabili d'ambiente (credenziali del database).

---

## Tecnologie Utilizzate

* **HTML5**
* **CSS3**
* **JavaScript (Vanilla)**
* **PHP 8.2**
* **MySQL 8.0**
* **Docker**
* **Docker Compose**

---

## 📂 Struttura del Progetto

```
.
├── docker-compose.yml            # Definizione dei servizi Docker
├── Dockerfile-php                # Dockerfile per l'immagine PHP custom
├── php/                          # Contiene i file PHP del backend
│   ├── index.php                 # Logica principale del backend
│   ├── connessione.php           # Gestione della connessione al DB
│   └── istruzioni_errori.log     # File di log degli errori (generato automaticamente)
├── html/                         # Contiene i file del front-end
│   ├── index.html                # La landing page con il form
│   ├── css/
│   │   └── style.css             # Stili CSS
│   └── js/
│       └── script.js             # Logica JavaScript per il form e i dirigenti dinamici
└── .env                          # Variabili d'ambiente per il database (NON committare dati sensibili!)
```

---

## ▶️ Guida all'Avvio del Progetto

### **Prerequisiti**

Assicurati di avere installato:

* [Docker Desktop](https://www.docker.com/products/docker-desktop)
* VS Code (o un editor di codice a tua scelta)

---

### **1. Clonazione del Repository**

```bash
git clone https://github.com/tuo-utente/tuo-repo.git
cd tuo-repo
```

*(Sostituisci `tuo-utente/tuo-repo.git` con l'URL effettivo del tuo repository.)*

---

### **2. Configurazione delle Variabili d'Ambiente**

Crea un file chiamato **`.env`** nella directory principale del progetto (dove si trova `docker-compose.yml`). Inserisci le tue credenziali MySQL:

```env
MYSQL_ROOT_PASSWORD=la_tua_password_root_mysql
MYSQL_DATABASE=nome_del_tuo_db
MYSQL_USER=il_tuo_utente_mysql
MYSQL_PASSWORD=la_tua_password_mysql
```
---

### **3. Avvio dei Container Docker**

```bash
docker-compose up --build -d
```

* `--build`: Costruisce le immagini Docker (necessario la prima volta o dopo modifiche a `Dockerfile-php`).
* `-d`: Avvia i container in background.

Verifica lo stato dei container:

```bash
docker-compose ps
```

---

### **4. Creazione della Tabella MySQL**

Connettiti al container MySQL:

```bash
docker exec -it mysql_db mysql -u<utente_mysql> -p<password_mysql> <nome_db>
```

Esegui la query:

```sql
CREATE TABLE IF NOT EXISTS lezioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    azione VARCHAR(50),
    utente VARCHAR(50),
    azienda VARCHAR(50),
    protocollo VARCHAR(50),
    nomeProgetto VARCHAR(50),
    modulo VARCHAR(50),
    numeroEdizione INT,
    avviso BOOLEAN,
    individuale BOOLEAN,
    dirigenti TEXT, -- Campo TEXT per l'array JSON dei dirigenti
    modalita VARCHAR(50),
    piattaforma VARCHAR(50),
    data DATE,
    oraInizio TIME,
    oraFine TIME,
    oraInizioPausa TIME,
    oraFinePausa TIME,
    note TEXT,
    sede VARCHAR(255),
    docenteCF VARCHAR(50)
);
```

Esci dal prompt:

```bash
exit;
```

---

### **5. Accedere all'Applicazione**

Apri il browser e vai su:

```
http://localhost:8080/html/index.html
```

---

### **6. Controllare i Dati**

* Visualizza il JSON dei dati nella console del browser (**F12**).
* Controlla il database:

```bash
docker exec -it mysql_db mysql -u<utente_mysql> -p<password_mysql> <nome_db>
SELECT * FROM lezioni;
```

* Controlla il log degli errori PHP:

```bash
docker exec php_app cat /var/www/html/istruzioni_errori.log
```

---

### **Spegnere i Container Docker**

```bash
docker-compose down
```

Per rimuovere anche i volumi (⚠️ elimina i dati del DB):

```bash
docker-compose down -v
```

---

## 🔐 **Attenzione**

L'operazione `docker-compose down -v` è **irreversibile** e cancellerà tutti i dati del database.




