## SMART WEATHER STATION ##

La Smart Weather Station è una stazione meteo che invia i dati raccolti ad un database online. I dati in questione sono temperatura, umidità e pressione.

I dati possono essere consultati in due modi diversi:
 * tramite un sito internet, con la visualizzazione di grafici e statistiche in tempo reale;
 * tramite un chatbot su Telegram realizzato con IBM Watson.

 ### Sito internet ###
Il sito internet in questione è raggiungibile dal link: https://www.smartweatherstation.altervista.org 
 * Mostra in *tempo reale* i valori della temperatura, umidità e pressione con i rispettivi massimi e minimi raccolti durante tutta la giornata.
 * Mostra le statistiche nelle ultime 12 ore, mostrando la massima, la minima e la media della temperatura, umidità e pressione.
 * Mostra lo storico giornaliero relativo all’andamento dei dati di un’intera giornata sotto forma di grafici o di tabelle (si ha anche la possibilità di vedere i dati di altre giornate).
 * Storico mensile: mostra l’andamento mensile dei dati con la massima, minima e media di ogni valore. E’ possibile visualizzare l’andamento degli altri mesi.
 
 ### ChatBot Telegram ###
Link al bot: http://t.me/SmartWeatherStationBot 
Il ChatBot è realizzato con IBM Watson e si può interagire chiedendo le seguenti informazioni:
* Temperatura, sotto forma di domanda, si può chiedere la temperatura (Quanti gradi ci sono? Qual’è la temperatura attuale?)
* Umidità, puoi chiedergli il livello di umidità (Quanta umidità c'è? Qual’è il livello di umidità?)
* Pressione, puoi chiedergli la pressione attuale (Dimmi quanta pressione c’è?)
* Riepilogo della giornata, puoi chiedergli di fare un resoconto dei dati (Fammi un riepilogo della giornata oppure Che tempo fa?)
* Massimo e minimo di un dato, puoi chiedergli per esempio la temperatura massima (Dimmi la temperatura massima oppure dimmi la temperatura minima)

 ### REALIZZAZIONE ###
  #### SITO INTERNET #### 
  Il sito internet è costruito utilizzando PHP con un database MySQL. Per quanto riguarda il front-end ho utilizzato le librerie di Bootstrap e per i grafici la libreria Chart.js (tutti prodotti open source). Ho utilizzato anche Javascript e Ajax. Infine ho utilizzato le Api di OpenWeatherMap per sapere il meteo di Bergamo (siccome la stazione meteo può essere posizionata in casa, il calcolo del meteo risulterebbe errato, così ho deciso di utilizzare queste API per avere il meteo corretto). Le pagine php e il database MySQL sono presenti su Altervista.
  #### CHATBOT TELEGRAM #### 
Il chatBot in Telegram risponde ad una pagina PHP che si interfaccia mediante API a IBM Watson: tutto quello che l’utente inserisce in Telegram viene passato ad Watson (previa sanitizzazione) che restituisce un json contenente tutte le informazioni che riesce a riconoscere. Come per esempio gli intenti della frase e le parole chiavi rilevate. La pagina PHP analizza la risposta da Watson e, in base a quello che l’utente vuole sapere, si interfaccia mediante API al database. Una volta raccolti i dati dal database, la pagina risponde all’utente su Telegram. La pagina PHP è collegata al bot su Telegram utilizzando un servizio sulla piattaforma Heroku. Watson è un servizio istanziato sul cloud di IBM. 
  ####  STAZIONE METEO - HARDWARE #### 
La stazione meteo è realizzata con i seguenti componenti:
  * NodeMCU, è una piattaforma open source sviluppata specificatamente per l'IoT. E’ programmabile con l’IDE di Arduino e presenta sulla scheda un modulo wifi ESP8266. 
 * DHT22, sensore di temperatura e di umidità. L’intervallo di temperatura che registra va dai -40°C a 125°C con una precisione di ±0.2 °C. Registra l’umidità dallo 0 al 100% con una precisione di ± 2% 
 * BMP085, sensore di pressione e di temperatura. L’intervallo di misurazione della pressione va dai 300 ai 1100 hPa, mentre quello della temperatura dai -45°C a 85°C.
La stazione meteo invia ogni 30 secondi i dati che raccoglie ad un database online e dopo va in deep sleep, consumando così poca energia. 
