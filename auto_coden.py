import os
import subprocess
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
    # Diese Funktion holt die Änderungen über Git
    changed_files = []
    result = subprocess.run(["git", "diff", "--name-only"], capture_output=True, text=True)
    if result.stdout:
        changed_files = result.stdout.strip().split('\n')
    return changed_files

def send_to_stable_diffusion(changed_files):
    # Sendet die gesammelten Änderungen an Stable Diffusion
    # und erhält eine Antwort für die Commit-Nachricht.
    # Diese Funktion muss entsprechend Ihrer Stable Diffusion API implementiert werden.
    commit_message = "Update based on latest changes"
    return commit_message

def git_commit_and_push(commit_message):
    # Führt Git-Commit und Push mit der bereitgestellten Nachricht durch
    try:
        subprocess.run(["git", "add", "."], check=True)
        subprocess.run(["git", "commit", "-m", commit_message], check=True)
        subprocess.run(["git", "push"], check=True)
    except subprocess.CalledProcessError as e:
        print(f"Fehler beim Ausführen von Git-Befehlen: {e}")

# Hauptskript
        
write_file_info_to_output('./http/', 'output.txt',['sql_create'])
changed_files = get_git_changes()
commit_message = send_to_stable_diffusion(changed_files)
git_commit_and_push(commit_message)


