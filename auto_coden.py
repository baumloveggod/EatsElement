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

def write_file_info_to_output(paths, output_file):
    with open(output_file, 'w', encoding='utf-8') as f_output:
        print("A: ")
        for path in paths:
            print("B: " + path)
            print(os.path.isdir(path))
            print(os.path.isfile(path))
            if os.path.isdir(path):
                
                print("D: " + path)
                for root, dirs, files in os.walk(path):
                    if '.git' in dirs:
                        dirs.remove('.git')
                    if '.godot' in dirs:
                        dirs.remove('.godot')
                    for filename in files:
                        print(files)
                        process_file(os.path.join(root, filename), f_output)
            elif os.path.isfile(path):
                print("C: " + path)
                process_file(path, f_output)

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
    prompt = f"Based on the following changes, generate a concise and informative commit message, respond in max 50 characters:\n\n{changelog}"
    completion = client.chat.completions.create(
        model="code-davinci-002",
        messages=[
            {"role": "system", "content": "Generate a concise and informative git commit message based on the detailed changelog provided."},
            {"role": "user", "content": prompt}
        ],
        temperature=0.5,
    )

    # Debugging: Print the message object to understand its structure
    print(completion.choices[0].message)

    # Assuming 'completion.choices[0].message' has 'content' accessible as an attribute
    try:
        # If 'content' is directly accessible
        commit_message = completion.choices[0].message.content.strip()
    except AttributeError:
        # Fallback or error handling if 'content' is not accessible as expected
        print("Failed to access message content directly. Check the structure of 'message'.")
        commit_message = "Default commit message due to error."

    return commit_message




def git_commit_and_push(commit_message):
    try:
        subprocess.run(["git", "add", "."], check=True)
        subprocess.run(["git", "commit", "-m", commit_message], check=True)
        subprocess.run(["git", "push"], check=True)
    except subprocess.CalledProcessError as e:
        print(f"Fehler beim Ausführen von Git-Befehlen: {e}")
#,''altes_zutaten.php',
# Main script
write_file_info_to_output(['./http/Views/templates/zutatenFormular.php','./http/Views/templates/einheitenFormular.php','./http/Views/pages/einheiten.php', './http/Views/pages/zutaten.php','sql_create'], 'output.txt')
changed_files = get_git_changes()
if changed_files:  # Only proceed if there are changed files
    commit_message = generate_commit_message(changed_files)
    git_commit_and_push(commit_message)
else:
    print("No changes to commit.")
