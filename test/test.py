from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
import requests
import json
import time

def fill_and_submit_form(driver_path, form_url):
    """
    Diese Funktion füllt das Zutaten-Formular auf der angegebenen Webseite aus und sendet es ab.
    """
    driver = webdriver.Chrome(driver_path)
    driver.get(form_url)

    # Warte kurz, damit die Seite vollständig geladen wird
    time.sleep(2)
    
    # Existiert die Zutat unter einem anderen Namen? Checkbox aktivieren/deaktivieren
    exists_under_another_name = driver.find_element(By.NAME, 'existiertUnterAnderemNamen')
    if not exists_under_another_name.is_selected():
        exists_under_another_name.click()

    # Alternativen Namen eingeben, wenn die Checkbox ausgewählt ist
    driver.find_element(By.NAME, 'alternativerName').send_keys('Zucker')

    # Kategorie auswählen
    driver.find_element(By.CLASS_NAME, 'kategorie_id').click()
    # Wählen Sie die erste Kategorie als Beispiel
    first_category_option = driver.find_element(By.CSS_SELECTOR, '.kategorie_id option[value="1"]')
    first_category_option.click()

    # Planetary Health Diet Category auswählen
    driver.find_element(By.CLASS_NAME, 'phd_kategorie_id').click()
    # Wählen Sie die erste PHD-Kategorie als Beispiel
    first_phd_category_option = driver.find_element(By.CSS_SELECTOR, '.phd_kategorie_id option[value="1"]')
    first_phd_category_option.click()

    # Einheit auswählen
    driver.find_element(By.CLASS_NAME, 'einheit_id').click()
    # Wählen Sie die erste Einheit als Beispiel
    first_unit_option = driver.find_element(By.CSS_SELECTOR, '.einheit_id option[value="1"]')
    first_unit_option.click()

    # Volumen eingeben
    driver.find_element(By.NAME, 'volumen').send_keys('500')

    # Formular absenden
    driver.find_element(By.CSS_SELECTOR, 'input[type="submit"][value="Zutat Hinzufügen"]').click()

    # Optional: Warten und Browser schließen
    time.sleep(5)
    driver.close()


def check_result_with_api(api_url, api_token, sql_statement):
    """
    Diese Funktion verwendet die API, um das Ergebnis zu überprüfen.
    """
    data = {
        'sql': sql_statement,
        'token': api_token
    }

    headers = {
        'Content-Type': 'application/json'
    }

    response = requests.post(api_url, data=json.dumps(data), headers=headers)

    if response.status_code == 200:
        print(response.json())
    else:
        print(f"Fehler: {response.status_code}")
        print(response.text)

if __name__ == "__main__":
    # Teil 1: Formular ausfüllen und absenden
    DRIVER_PATH = '/path/to/your/chromedriver'
    FORM_URL = 'https://noadscollective.de/Views/pages/neu_zutaten.php'
    fill_and_submit_form(DRIVER_PATH, FORM_URL)

    # Teil 2: API verwenden, um das Ergebnis zu überprüfen
    API_URL = 'https://noadscollective.de/api.php'
    API_TOKEN = 'A'
    SQL_STATEMENT = "SELECT * FROM zutaten;"
    check_result_with_api(API_URL, API_TOKEN, SQL_STATEMENT)
