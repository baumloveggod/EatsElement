import requests
import json

API_URL = 'https://noadscollective.de/api.php'
API_TOKEN = 'A'

# SQL-Statement, das abgefragt werden soll
sql_statement = "SELECT * FROM zutaten;"

# Daten für die POST-Anfrage
data = {
    'sql': sql_statement,
    'token': API_TOKEN
}

# Header für die Anfrage
headers = {
    'Content-Type': 'application/json'
}

# HTTP POST-Anfrage senden
response = requests.post(API_URL, data=json.dumps(data), headers=headers)

# Antwort prüfen
if response.status_code == 200:
    # Erfolgreiche Anfrage, JSON-Antwort ausgeben
    print(response.json())
else:
    # Fehlerbehandlung
    print(f"Fehler: {response.status_code}")
    print(response.text)
