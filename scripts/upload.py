
import os
import sys
import argparse
import re
from google.oauth2 import service_account
from googleapiclient.discovery import build
from googleapiclient.http import MediaFileUpload


def main(credentials_file, parent_folder_id, destination_folder_id, upload_filename, upload_filepath):
    # Build the Google Drive API client using the authenticated credentials
    service = build_google_drive_service(credentials_file)
    if service:
        try:
            files = get_files(
                service=service, parent_folder_id=parent_folder_id)
            upload_filename = generate_filename(
                files=files, upload_filename=upload_filename)
            # iterate all files from prvided folder id and move them to prodived destination folder(id) one by one
            for file in files:
                print(f"file: {file}")
                moved_file = move(servie=service, file_id=file.get('id'),
                                  destination_folder_id=destination_folder_id)
                print(f"Moved file: {moved_file}")

            # ater moving all the file from parent folder now upload new file to parent folder
            upload(service=service, parent_folder_id=parent_folder_id,
                   upload_filepath=upload_filepath, upload_filename=upload_filename)
        except:
            print(f'An error occurred')
    else:
        print('Unable to load service account credentials.')

# This function will create google drive service to action on google drive


def build_google_drive_service(credentials_file):
    # Set the scopes that you want to authorize the service account to access - In this case google drive is our scope
    SCOPES = ['https://www.googleapis.com/auth/drive']
    # Load the service account credentials from the credential path
    creds = None
    service = None
    if os.path.exists(credentials_file):
        creds = service_account.Credentials.from_service_account_file(
            credentials_file, scopes=SCOPES)
    if creds is not None:
        service = build('drive', 'v3', credentials=creds)
    return service


def get_files(service, parent_folder_id, query=''):
    # Retrieve a list of all file IDs in the parent folder except for the folder type
    query = query if query else "trashed = false and '{}' in parents and mimeType != 'application/vnd.google-apps.folder'".format(
        parent_folder_id)

    results = service.files().list(
        orderBy="modifiedTime desc",
        q=query, fields='files(id,name)').execute()
    # file list in a specific folder (google drive folder id)
    return results.get('files', [])


def generate_filename(files, upload_filename):
    # Default starting version number
    version_number = '3.0.0'
    if files:
        filename = files[0].get('name')
        match = re.search(r'\d+\.\d+\.\d+', filename)
        if match:
            old_version_number = match.group()
            parts = old_version_number.split('.')
            parts[-1] = str(int(parts[-1]) + 1)
            version_number = '.'.join(parts)

    filename_split = os.path.splitext(upload_filename)
    name_part = filename_split[0]
    extension = filename_split[1]
    upload_filename = f"{name_part}-{version_number}{extension}"
    return upload_filename

# This function will upload file to google drive


def upload(service, parent_folder_id, upload_filepath, upload_filename):
    file_metadata = {'name': upload_filename, 'parents': [parent_folder_id]}
    media = MediaFileUpload(upload_filepath, resumable=True)
    uploded_file = service.files().create(body=file_metadata,
                                          media_body=media, fields='id,name').execute()
    return uploded_file

# This file will move file from source folder to destination folder


def move(servie, file_id, destination_folder_id):
    file = servie.files().get(
        fileId=file_id, fields='id, name, parents').execute()

    previous_parents = ",".join(file.get('parents', []))
    moved_file = servie.files().update(fileId=file_id, addParents=destination_folder_id,
                                       removeParents=previous_parents,
                                       fields='id, parents').execute()
    return moved_file


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--credentials-file', type=str)
    parser.add_argument('--parent-folder-id', type=str)
    parser.add_argument('--destination-folder-id', type=str)
    parser.add_argument('--upload-filename', type=str)
    parser.add_argument('--upload-filepath', type=str)
    args = parser.parse_args()

    credentials_file = args.credentials_file
    parent_folder_id = args.parent_folder_id
    destination_folder_id = args.destination_folder_id
    upload_filename = args.upload_filename
    upload_filepath = args.upload_filepath

    if not all([credentials_file, parent_folder_id, destination_folder_id, upload_filename, upload_filepath]):
        sys.stderr.write(
            """please provie those arguments value:
--credentials-file,
--parent-folder-id,
--destination-folder-id,
--upload-filename,
--upload-filepath
""")
        sys.exit(1)

    main(credentials_file=credentials_file,
         parent_folder_id=parent_folder_id, destination_folder_id=destination_folder_id, upload_filename=upload_filename, upload_filepath=upload_filepath)
