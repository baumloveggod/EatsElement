import os
import subprocess
from openai import OpenAI

# Initialize the OpenAI client with your API key and the desired API base URL
client = OpenAI(base_url="http://localhost:1234/v1", api_key="your_api_key_here")

def is_text_file(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as file:
            file.read()
        return True
    except Exception:
        return False

def write_file_info_to_output(directory, output_file, extra_files=[]):
    with open(output_file, 'w') as f_output:
        for root, dirs, files in os.walk(directory):
            if '.git' in dirs:
                dirs.remove('.git')
            if '.godot' in dirs:
                dirs.remove('.godot')
            for filename in files:
                process_file(os.path.join(root, filename), f_output)

        # Process additional files
        for extra_file in extra_files:
            if os.path.exists(extra_file):
                process_file(extra_file, f_output)

def process_file(filepath, f_output):
    if is_text_file(filepath):
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
            f_output.write(f"Path: {filepath}\nContent:\n{content}\n\n")
    else:
        f_output.write(f"Path: {filepath}\n\n")


def get_git_changes():
    changed_files = []
    result = subprocess.run(["git", "diff", "--name-only"], capture_output=True, text=True)
    if result.stdout:
        changed_files = result.stdout.strip().split('\n')
    return changed_files

def generate_commit_message(changelog):
    # Generiert eine Commit-Nachricht basierend auf dem detaillierten Changelog
    prompt = f"Basierend auf den folgenden Änderungen, generiere eine prägnante und informative Commit-Nachricht, antworte dabei in max 50 zeichen:\n\n{changelog}"
    completion = client.chat.completions.create(
        model="code-davinci-002",  # Angenommen, dies ist Ihr Code-spezifisches Modell
        messages=[
            {"role": "system", "content": "Generate a concise and informative git commit message based on the detailed changelog provided."},
            {"role": "user", "content": prompt}
        ],
        temperature=0.5,
    )
    commit_message = completion.choices[0].message.strip()
    return commit_message


def git_commit_and_push(commit_message):
    try:
        subprocess.run(["git", "add", "."], check=True)
        subprocess.run(["git", "commit", "-m", commit_message], check=True)
        subprocess.run(["git", "push"], check=True)
    except subprocess.CalledProcessError as e:
        print(f"Fehler beim Ausführen von Git-Befehlen: {e}")

# Main script
write_file_info_to_output('./http/', 'output.txt', ['sql_create'])
changed_files = get_git_changes()
if changed_files:  # Only proceed if there are changed files
    commit_message = generate_commit_message(changed_files)
    git_commit_and_push(commit_message)
else:
    print("No changes to commit.")
