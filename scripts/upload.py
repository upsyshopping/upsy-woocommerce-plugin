
import os
import sys
import argparse
import re
from requests import HTTPError
from google.oauth2 import service_account
from googleapiclient.discovery import build
from googleapiclient.http import MediaFileUpload


def main(credential_path, parent_folder_id, old_folder_id, upload_filename, upload_filepath):
    # Set the scopes that you want to authorize the service account to access
    SCOPES = ['https://www.googleapis.com/auth/drive']
    # Load the service account credentials from the credential path
    creds = None
    if os.path.exists(credential_path):
        creds = service_account.Credentials.from_service_account_file(
            credential_path, scopes=SCOPES)

    # Build the Google Drive API client using the authenticated credentials
    if creds is not None:
        service = build('drive', 'v3', credentials=creds)
        file_ids = []
        try:
            # Retrieve a list of all file IDs in the parent folder except for the folder type
            query = "trashed = false and '{}' in parents and mimeType != 'application/vnd.google-apps.folder'".format(
                parent_folder_id)

            results = service.files().list(
                orderBy="modifiedTime desc",
                q=query, fields='files(id,name)').execute()

            files = results.get('files', [])
            version_number = '1.0.0'

            if files:
                filename = files[0].get('name')
                match = re.search(r'\d+\.\d+\.\d+', filename)
                if match:
                    old_version_number = match.group()
                    parts = old_version_number.split('.')
                    parts[-1] = str(int(parts[-1]) + 1)
                    version_number = '.'.join(
                        parts) if '.'.join(parts) else version_number

            filename_split = os.path.splitext(upload_filename)
            name_part = filename_split[0]
            extension = filename_split[1]

            upload_filename = f"{name_part}-{version_number}{extension}"

            for file in files:
                move(servie=service, file_id=file.get('id'),
                     destination_folder_id=old_folder_id)

            upload(service=service, parent_folder_id=parent_folder_id,
                   upload_filepath=upload_filepath, upload_filename=upload_filename)
        except HTTPError as error:
            print(f'An error occurred: {error}')

    else:
        print('Unable to load service account credentials.')


def upload(service, parent_folder_id, upload_filepath, upload_filename):
    file_metadata = {'name': upload_filename, 'parents': [parent_folder_id]}
    media = MediaFileUpload(upload_filepath, resumable=True)
    file = service.files().create(body=file_metadata,
                                  media_body=media, fields='id,name').execute()
    print(f"File upload success: {file}")


def move(servie, file_id, destination_folder_id):
    file = servie.files().get(
        fileId=file_id, fields='id, name, parents').execute()

    previous_parents = ",".join(file.get('parents', []))
    file = servie.files().update(fileId=file_id, addParents=destination_folder_id,
                                 removeParents=previous_parents,
                                 fields='id, parents').execute()
    return file


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--credential', '-c', type=str,
                        help='path for credentials json')
    parser.add_argument('--parent-folder-id', '-p', type=str,
                        help='Google drive folder id where you want to upload the file')
    parser.add_argument('--old-folder-id', '-o', type=str,
                        help='Google drive folder id where you want to move previous uploaded file')
    parser.add_argument('--filename', '-n', type=str,
                        help='What will be the new name for a uploaded file')
    parser.add_argument('--filepath', '-fp', type=str,
                        help='What will be the file path for a uploaded file')
    args = parser.parse_args()

    credential_path = args.credential
    parent_folder_id = args.parent_folder_id
    old_folder_id = args.old_folder_id
    upload_filename = args.filename
    upload_filepath = args.filepath

    if not credential_path:
        sys.stderr.write('please provide --credential / -c arguments value\n')
        sys.exit(1)
    elif not parent_folder_id:
        sys.stderr.write(
            'please provide --parent-folder-id / -p arguments value\n')
        sys.exit(1)
    elif not old_folder_id:
        sys.stderr.write(
            'please provide --old-folder-id / -o arguments value\n')
        sys.exit(1)
    elif not upload_filename:
        sys.stderr.write(
            'please provide --filename / -n arguments value\n')
        sys.exit(1)
    elif not upload_filepath:
        sys.stderr.write(
            'please provide --filepath / -fp arguments value\n')
        sys.exit(1)

    main(credential_path=credential_path,
         parent_folder_id=parent_folder_id, old_folder_id=old_folder_id, upload_filename=upload_filename, upload_filepath=upload_filepath)
